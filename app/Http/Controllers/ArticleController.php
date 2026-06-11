<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\ArticleRevision;
use App\Models\ArticleView;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\LearningCollection;
use App\Models\Media;
use App\Models\QuizAttempt;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class ArticleController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $user = $request->user();
        $article = Article::with(['category', 'author', 'thumbnailMedia', 'normalizedQuiz.questions.options'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        $relatedArticles = collect();
        $learningCollections = collect();

        if ($article) {
            if ($user) {
                $learningCollections = LearningCollection::query()
                    ->where('user_id', $user->id)
                    ->orderBy('name')
                    ->get();

                $article->loadExists([
                    'favorites as is_favorited' => fn ($query) => $query->where('user_id', $user->id),
                    'collections as is_collected' => fn ($query) => $query->where('user_id', $user->id),
                ]);

                ArticleView::create([
                    'user_id' => $user->id,
                    'article_id' => $article->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                    'viewed_at' => now(),
                ]);
            } else {
                $request->session()->put('url.intended', route('articles.show', $article->slug).'#mulai-belajar');
            }

            $relatedArticles = Article::with(['category', 'author', 'thumbnailMedia'])
                ->where('status', 'published')
                ->whereKeyNot($article->id)
                ->where('category_id', $article->category_id)
                ->latest()
                ->take(3)
                ->get();
        }

        return view('articles.show', compact('article', 'relatedArticles', 'learningCollections'));
    }

    public function toggleFavorite(Request $request, Article $article)
    {
        abort_unless($article->status === 'published', 404);

        $favorite = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->where('article_id', $article->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with('success', 'Artikel dihapus dari favorit.');
        }

        Favorite::create([
            'user_id' => $request->user()->id,
            'article_id' => $article->id,
        ]);

        return back()->with('success', 'Artikel ditambahkan ke favorit.');
    }

    public function toggleCollection(Request $request, Article $article)
    {
        abort_unless($article->status === 'published', 404);

        $collection = ArticleCollection::query()
            ->where('user_id', $request->user()->id)
            ->where('article_id', $article->id)
            ->first();

        if ($collection) {
            $collection->delete();

            return back()->with('success', 'Artikel dihapus dari koleksi.');
        }

        $defaultCollection = LearningCollection::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'name' => 'Koleksi Utama',
            ]
        );

        $targetCollection = $defaultCollection;

        if ($request->filled('collection_id')) {
            $targetCollection = LearningCollection::query()
                ->where('user_id', $request->user()->id)
                ->findOrFail($request->integer('collection_id'));
        }

        ArticleCollection::create([
            'user_id' => $request->user()->id,
            'collection_id' => $targetCollection->id,
            'article_id' => $article->id,
        ]);

        return back()->with('success', 'Artikel disimpan ke '.$targetCollection->name.'.');
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedArticleData($request, true);
        $data['content'] = $this->sanitizeArticleContent($data['content']);
        $quizData = $this->normalizedQuizData($data);
        $data['author_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['status'] = $request->user()->isAdmin() ? $data['status'] : 'draft';
        $thumbnailMedia = $this->storeThumbnail($request);
        $data['thumbnail_media_id'] = $thumbnailMedia->id;

        try {
            DB::transaction(function () use ($data, $quizData): void {
                $article = Article::create($data);
                $this->syncArticleQuiz($article, $quizData);
            });
        } catch (Throwable $exception) {
            $this->deleteMedia($thumbnailMedia);

            throw $exception;
        }

        return redirect()->route($request->user()->isAdmin() ? 'admin.dashboard' : 'author.dashboard')
            ->with('success', $request->user()->isAdmin()
                ? 'Artikel berhasil ditambahkan.'
                : 'Artikel berhasil ditambahkan sebagai draft untuk direview admin.');
    }

    public function edit(Article $article)
    {
        $this->authorizeArticleOwnership($article);
        $this->ensureAuthorCanEdit($article);
        $article->load('normalizedQuiz.questions.options', 'thumbnailMedia', 'latestReview.reviewer', 'latestRevision.thumbnailMedia');

        $categories = Category::orderBy('name')->get();
        $revisionDraft = $article->latestRevision && in_array($article->latestRevision->status, ['review', 'rejected'], true)
            ? $article->latestRevision
            : null;

        return view('articles.edit', compact('article', 'categories', 'revisionDraft'));
    }

    public function update(Request $request, Article $article)
    {
        $this->authorizeArticleOwnership($article);
        $this->ensureAuthorCanEdit($article);

        if (! $request->user()->isAdmin() && $article->status === 'published') {
            return $this->storeRevision($request, $article);
        }

        $data = $this->validatedArticleData($request, false);
        $data['content'] = $this->sanitizeArticleContent($data['content']);
        $quizData = $this->normalizedQuizData($data);
        $data['slug'] = $this->uniqueSlug($data['title'], $article->id);
        if ($request->user()->isAdmin()) {
            $data['status'] = $article->status === 'review' ? 'review' : $data['status'];
        } else {
            $data['status'] = $article->status === 'published' ? 'review' : $article->status;
        }

        $oldThumbnailMedia = $article->thumbnailMedia;
        $newThumbnailMedia = $request->hasFile('thumbnail')
            ? $this->storeThumbnail($request)
            : null;

        if ($newThumbnailMedia) {
            $data['thumbnail_media_id'] = $newThumbnailMedia->id;
        }

        try {
            DB::transaction(function () use ($article, $data, $quizData): void {
                $article->update($data);
                $this->syncArticleQuiz($article, $quizData);
            });
        } catch (Throwable $exception) {
            if ($newThumbnailMedia) {
                $this->deleteMedia($newThumbnailMedia);
            }

            throw $exception;
        }

        if ($newThumbnailMedia && $oldThumbnailMedia) {
            $this->deleteMedia($oldThumbnailMedia);
        }

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function storeRevision(Request $request, Article $article)
    {
        $this->authorizeArticleOwnership($article);
        abort_if($request->user()->isAdmin(), 403, 'Aksi ini khusus untuk penulis.');
        abort_unless($article->status === 'published', 422, 'Revisi terpisah hanya tersedia untuk artikel published.');

        $data = $this->validatedArticleData($request, false);
        $data['content'] = $this->sanitizeArticleContent($data['content']);
        $quizData = $this->normalizedQuizData($data);
        $data['slug'] = $this->uniqueSlug($data['title'], $article->id);

        $revision = $article->revisions()
            ->whereIn('status', ['review', 'rejected'])
            ->latest()
            ->first();
        $newThumbnailMedia = $request->hasFile('thumbnail')
            ? $this->storeThumbnail($request)
            : null;
        $oldRevisionThumbnailMedia = $revision?->thumbnailMedia;
        $data['thumbnail_media_id'] = $newThumbnailMedia?->id
            ?? $revision?->thumbnail_media_id
            ?? $article->thumbnail_media_id;

        try {
            DB::transaction(function () use ($article, $request, $revision, $data, $quizData): void {
                $payload = [
                    ...$data,
                    'author_id' => $request->user()->id,
                    'quiz_data' => $quizData,
                    'status' => 'review',
                    'reviewer_id' => null,
                    'review_note' => null,
                    'reviewed_at' => null,
                ];

                if ($revision) {
                    $revision->update($payload);
                } else {
                    $article->revisions()->create($payload);
                }
            });
        } catch (Throwable $exception) {
            if ($newThumbnailMedia) {
                $this->deleteMedia($newThumbnailMedia);
            }

            throw $exception;
        }

        if ($newThumbnailMedia && $oldRevisionThumbnailMedia && $oldRevisionThumbnailMedia->id !== $article->thumbnail_media_id) {
            $this->deleteMedia($oldRevisionThumbnailMedia);
        }

        AuditLog::record('article.revision_submitted', $article, [
            'title' => $data['title'],
        ]);

        return redirect()->route('author.dashboard')->with('success', 'Revisi artikel berhasil dikirim untuk review admin. Versi published lama tetap tayang.');
    }

    public function submitForReview(Request $request, Article $article)
    {
        $this->authorizeArticleOwnership($article);
        abort_if($request->user()->isAdmin(), 403, 'Aksi ini khusus untuk penulis.');
        abort_unless(in_array($article->status, ['draft', 'rejected'], true), 422, 'Artikel tidak dapat dikirim untuk review.');

        $article->update(['status' => 'review']);

        return back()->with('success', 'Artikel berhasil dikirim untuk review admin.');
    }

    public function destroy(Article $article)
    {
        $this->authorizeArticleOwnership($article);

        DB::transaction(fn () => $article->delete());
        AuditLog::record('article.deleted', $article, [
            'title' => $article->title,
        ]);

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'author.dashboard')
            ->with('success', 'Artikel berhasil dihapus.');
    }

    public function submitQuizAttempt(Request $request, Article $article)
    {
        $data = $request->validate([
            'quiz_option_id' => ['required', 'exists:quiz_options,id'],
        ]);

        $article->load('normalizedQuiz.questions.options');

        $quiz = $article->normalizedQuiz;
        $question = $quiz?->questions
            ->first(fn ($question) => $question->options->contains('id', (int) $data['quiz_option_id']));
        $selectedOption = $question?->options->firstWhere('id', (int) $data['quiz_option_id']);

        if (! $quiz || ! $question || ! $selectedOption) {
            return response()->json(['message' => 'Jawaban quiz tidak valid.'], 422);
        }

        $attempt = QuizAttempt::create([
            'user_id' => $request->user()->id,
            'article_id' => $article->id,
            'quiz_id' => $quiz->id,
            'score' => $selectedOption->is_correct ? 100 : 0,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $attempt->answers()->create([
            'quiz_question_id' => $question->id,
            'quiz_option_id' => $selectedOption->id,
            'is_correct' => $selectedOption->is_correct,
        ]);

        return response()->json([
            'correct' => $selectedOption->is_correct,
            'message' => $selectedOption->is_correct
                ? 'Jawaban anda benar'
                : 'Jawaban anda salah',
        ]);
    }

    public function uploadContentMedia(Request $request)
    {
        $request->validate([
            'media' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ], [
            'media.required' => 'Media wajib diunggah.',
            'media.image' => 'Media harus berupa gambar.',
            'media.mimes' => 'Media harus berupa JPG, JPEG, PNG, WEBP, atau GIF.',
            'media.max' => 'Ukuran media maksimal 5MB.',
        ]);

        $file = $request->file('media');
        $name = time().'_'.uniqid().'.'.$file->extension();
        $folder = 'uploads/artikel/content';
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $file->move(public_path($folder), $name);
        $path = $folder.'/'.$name;

        Media::create([
            'user_id' => $request->user()?->id,
            'disk' => 'public',
            'folder' => $folder,
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'usage' => 'content',
        ]);

        return response()->json([
            'location' => asset($path),
        ]);
    }

    private function validatedArticleData(Request $request, bool $thumbnailRequired = true): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'summary' => ['required', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'quizzes' => ['required', 'array', 'min:1', 'max:3'],
            'quizzes.*.question' => ['required', 'string', 'max:500'],
            'quizzes.*.options' => ['required', 'array', 'size:3'],
            'quizzes.*.options.*' => ['required', 'string', 'max:300'],
            'quizzes.*.correct_option' => ['required', 'integer', 'between:0,2'],
            'thumbnail' => [$thumbnailRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($request->user()?->isAdmin()) {
            $rules['status'] = ['required', Rule::in(['draft', 'published'])];
        }

        return $request->validate($rules, [
            'title.required' => 'Judul wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'summary.required' => 'Ringkasan wajib diisi.',
            'summary.max' => 'Ringkasan maksimal 1000 karakter.',
            'content.required' => 'Isi artikel wajib diisi.',
            'quizzes.required' => 'Artikel wajib memiliki minimal 1 quiz.',
            'quizzes.min' => 'Artikel wajib memiliki minimal 1 quiz.',
            'quizzes.max' => 'Artikel maksimal memiliki 3 quiz.',
            'quizzes.*.question.required' => 'Pertanyaan quiz wajib diisi.',
            'quizzes.*.options.size' => 'Setiap quiz harus memiliki 3 pilihan jawaban.',
            'quizzes.*.options.*.required' => 'Semua pilihan jawaban quiz wajib diisi.',
            'quizzes.*.correct_option.required' => 'Jawaban benar quiz wajib dipilih.',
            'thumbnail.required' => 'Thumbnail wajib diunggah.',
            'thumbnail.image' => 'Thumbnail harus berupa gambar.',
            'thumbnail.mimes' => 'Thumbnail harus berupa JPG, JPEG, PNG, atau WEBP.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2MB.',
        ]);
    }

    private function normalizedQuizData(array &$data): ?array
    {
        $quizzes = collect($data['quizzes'] ?? [])
            ->take(3)
            ->map(fn ($quiz) => [
                'question' => trim((string) ($quiz['question'] ?? '')),
                'options' => collect($quiz['options'] ?? [])
                    ->take(3)
                    ->map(fn ($option) => trim((string) $option))
                    ->values()
                    ->all(),
                'correct_option' => (int) ($quiz['correct_option'] ?? 0),
            ])
            ->values()
            ->all();

        unset($data['quizzes']);

        return $quizzes;
    }

    private function authorizeArticleOwnership(Article $article): void
    {
        $user = auth()->user();

        if (! $user || (! $user->isAdmin() && $article->author_id !== $user->id)) {
            abort(403, 'Kamu hanya bisa mengelola artikel milikmu sendiri.');
        }
    }

    private function ensureAuthorCanEdit(Article $article): void
    {
        $user = auth()->user();

        if ($user && ! $user->isAdmin() && $article->status === 'review') {
            abort(403, 'Artikel sedang direview dan belum dapat diedit.');
        }
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while (Article::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }

    private function storeThumbnail(Request $request): Media
    {
        $file = $request->file('thumbnail');
        $folder = 'article/thumbnails';
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $path = $file->store($folder, 'public');

        if (! $path) {
            throw new RuntimeException('Thumbnail gagal disimpan.');
        }

        try {
            return Media::create([
                'user_id' => $request->user()?->id,
                'disk' => 'public',
                'folder' => $folder,
                'path' => $path,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'size' => $size,
                'usage' => 'thumbnail',
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }
    }

    private function deleteMedia(Media $media): void
    {
        $disk = $media->disk;
        $path = $media->path;

        $media->delete();
        Storage::disk($disk)->delete($path);
    }

    private function syncArticleQuiz(Article $article, ?array $quizData): void
    {
        $article->load('normalizedQuiz.questions');

        if (! $quizData) {
            $article->normalizedQuiz?->delete();

            return;
        }

        $quiz = $article->normalizedQuiz()->updateOrCreate(
            ['article_id' => $article->id],
            [
                'title' => 'Quiz '.$article->title,
                'is_active' => true,
            ]
        );

        $quiz->questions()->delete();

        foreach ($quizData as $questionIndex => $questionData) {
            $question = $quiz->questions()->create([
                'question' => $questionData['question'],
                'position' => $questionIndex + 1,
            ]);

            foreach ($questionData['options'] as $index => $option) {
                $question->options()->create([
                    'option_text' => $option,
                    'is_correct' => $index === (int) $questionData['correct_option'],
                    'position' => $index + 1,
                ]);
            }
        }

        $article->updateQuietly(['quiz' => null]);
    }

    private function sanitizeArticleContent(string $content): string
    {
        if (trim($content) === '') {
            return '';
        }

        $document = new DOMDocument;

        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div data-article-root>'.$content.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $root = $document->getElementsByTagName('div')->item(0);

        if (! $root) {
            return strip_tags($content);
        }

        $this->sanitizeNode($root);

        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return trim($html);
    }

    private function sanitizeNode(DOMNode $node): void
    {
        $allowedTags = [
            'a', 'blockquote', 'br', 'caption', 'em', 'figcaption', 'figure', 'h2', 'h3', 'h4',
            'img', 'li', 'ol', 'p', 'span', 'strong', 'table', 'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul',
        ];

        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tagName = strtolower($child->tagName);

                if (! in_array($tagName, $allowedTags, true)) {
                    $this->replaceElementWithChildren($child);

                    continue;
                }

                $this->sanitizeElementAttributes($child);
            }

            if ($child->parentNode) {
                $this->sanitizeNode($child);
            }
        }
    }

    private function replaceElementWithChildren(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    private function sanitizeElementAttributes(DOMElement $element): void
    {
        $allowedAttributes = [
            'a' => ['href', 'title', 'target', 'rel'],
            'img' => ['src', 'alt', 'title', 'width', 'height', 'class', 'style'],
            'figure' => ['class', 'style'],
            'figcaption' => ['class', 'style'],
            'p' => ['class', 'style'],
            'span' => ['class', 'style'],
            'table' => ['class'],
            'td' => ['colspan', 'rowspan'],
            'th' => ['colspan', 'rowspan'],
        ];

        $tagName = strtolower($element->tagName);
        $allowedForTag = $allowedAttributes[$tagName] ?? [];

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);

            if (str_starts_with($name, 'on') || ! in_array($name, $allowedForTag, true)) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($tagName === 'a') {
            $href = $element->getAttribute('href');

            if (! preg_match('/^(https?:|mailto:)/i', $href)) {
                $element->removeAttribute('href');
            } else {
                $element->setAttribute('rel', 'noopener noreferrer');
                if ($element->getAttribute('target') === '_blank') {
                    $element->setAttribute('target', '_blank');
                }
            }
        }

        if ($tagName === 'img') {
            $src = $element->getAttribute('src');
            $normalizedSrc = $this->normalizedContentImageSource($src);

            if (! $normalizedSrc) {
                $element->parentNode?->removeChild($element);

                return;
            }

            $element->setAttribute('src', $normalizedSrc);
            $element->setAttribute('loading', 'lazy');
            $element->setAttribute('decoding', 'async');
        }

        if ($element->hasAttribute('class')) {
            $element->setAttribute('class', $this->sanitizeClassAttribute($element->getAttribute('class')));
        }

        if ($element->hasAttribute('style')) {
            $style = $this->sanitizeStyleAttribute($element->getAttribute('style'));
            if ($style === '') {
                $element->removeAttribute('style');
            } else {
                $element->setAttribute('style', $style);
            }
        }
    }

    private function normalizedContentImageSource(string $src): ?string
    {
        $path = parse_url($src, PHP_URL_PATH) ?: $src;
        $path = str_replace('\\', '/', rawurldecode($path));
        $contentFolder = '/uploads/artikel/content/';
        $folderPosition = stripos($path, $contentFolder);

        if ($folderPosition === false && str_starts_with(ltrim($path, '/'), 'uploads/artikel/content/')) {
            $path = '/'.ltrim($path, '/');
            $folderPosition = 0;
        }

        if ($folderPosition === false) {
            return null;
        }

        $normalizedPath = substr($path, $folderPosition);

        if (! preg_match('/^\/uploads\/artikel\/content\/[^?#]+\.(gif|jpe?g|png|webp)$/i', $normalizedPath)) {
            return null;
        }

        return $normalizedPath;
    }

    private function sanitizeClassAttribute(string $class): string
    {
        $allowedClasses = [
            'article-media',
            'article-media--center',
            'article-media--full',
            'article-media--left',
            'article-media--right',
        ];

        return collect(preg_split('/\s+/', $class))
            ->filter(fn ($className) => in_array($className, $allowedClasses, true))
            ->implode(' ');
    }

    private function sanitizeStyleAttribute(string $style): string
    {
        $allowedProperties = ['height', 'margin-left', 'margin-right', 'text-align', 'width'];
        $clean = [];

        foreach (explode(';', $style) as $declaration) {
            [$property, $value] = array_pad(explode(':', $declaration, 2), 2, null);
            $property = strtolower(trim((string) $property));
            $value = trim((string) $value);

            if (! in_array($property, $allowedProperties, true)) {
                continue;
            }

            if (preg_match('/expression|javascript:|url\s*\(/i', $value)) {
                continue;
            }

            if (in_array($property, ['width', 'height'], true) && ! preg_match('/^(auto|\d{1,4}(\.\d+)?(px|%)?)$/', $value)) {
                continue;
            }

            if ($property === 'text-align' && ! in_array($value, ['left', 'center', 'right'], true)) {
                continue;
            }

            if (in_array($property, ['margin-left', 'margin-right'], true) && ! in_array($value, ['auto', '0', '0px'], true)) {
                continue;
            }

            $clean[] = $property.': '.$value;
        }

        return implode('; ', $clean);
    }
}
