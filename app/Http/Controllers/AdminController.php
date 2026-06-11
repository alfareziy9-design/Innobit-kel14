<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:draft,review,published,rejected'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['nullable', 'in:newest,oldest,title'],
        ]);

        $articles = Article::with(['category', 'author', 'latestReview.reviewer'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['author_id'] ?? null, fn ($query, $authorId) => $query->where('author_id', $authorId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when(
                ($filters['sort'] ?? 'newest') === 'oldest',
                fn ($query) => $query->oldest(),
                fn ($query) => ($filters['sort'] ?? 'newest') === 'title'
                    ? $query->orderBy('title')
                    : $query->latest()
            )
            ->paginate(20)
            ->withQueryString();
        $articleCount = Article::count();
        $publishedCount = Article::where('status', 'published')->count();
        $draftCount = Article::where('status', 'draft')->count();
        $reviewCount = Article::where('status', 'review')->count();
        $rejectedCount = Article::where('status', 'rejected')->count();
        $categoryCount = Category::count();
        $userCount = User::count();
        $categories = Category::orderBy('name')->get();
        $authors = User::query()
            ->whereHas('roleModel', fn ($query) => $query->whereIn('name', ['admin', 'author']))
            ->orderBy('name')
            ->get();
        $recentCategories = Category::withCount('articles')->orderBy('name')->take(5)->get();
        $messageCount = ContactMessage::count();
        $unreadMessageCount = ContactMessage::whereNull('read_at')->count();
        $recentMessages = ContactMessage::with('user')->latest()->take(5)->get();
        $recentActivity = AuditLog::with('actor')->latest()->take(6)->get();
        $oldestReview = Article::where('status', 'review')->oldest('updated_at')->first();
        $activeUserCount = User::where('account_status', 'active')->count();
        $pendingRevisions = ArticleRevision::with(['article', 'author', 'category'])
            ->where('status', 'review')
            ->latest()
            ->take(10)
            ->get();
        $pendingRevisionCount = ArticleRevision::where('status', 'review')->count();

        return view('admin.dashboard', compact(
            'articles',
            'articleCount',
            'publishedCount',
            'draftCount',
            'reviewCount',
            'rejectedCount',
            'categoryCount',
            'userCount',
            'categories',
            'authors',
            'recentCategories',
            'messageCount',
            'unreadMessageCount',
            'recentMessages',
            'recentActivity',
            'oldestReview',
            'activeUserCount',
            'pendingRevisions',
            'pendingRevisionCount'
        ));
    }

    public function messages(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:unread,read'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $messages = ContactMessage::with('user')
            ->when($filters['status'] ?? null, fn ($query, $status) => $status === 'unread'
                ? $query->whereNull('read_at')
                : $query->whereNotNull('read_at'))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }

    public function showMessage(ContactMessage $contactMessage)
    {
        if (! $contactMessage->read_at) {
            $contactMessage->update(['read_at' => now()]);
            AuditLog::record('message.read', $contactMessage);
        }

        $contactMessage->load('user');

        return view('admin.messages.show', compact('contactMessage'));
    }

    public function destroyMessage(ContactMessage $contactMessage)
    {
        AuditLog::record('message.deleted', $contactMessage, [
            'sender' => $contactMessage->email,
        ]);
        $contactMessage->forceDelete();

        return redirect()->route('admin.messages.index')->with('success', 'Pesan berhasil dihapus.');
    }

    public function updateMessageReadStatus(Request $request, ContactMessage $contactMessage)
    {
        $data = $request->validate([
            'is_read' => ['required', 'boolean'],
        ]);

        $contactMessage->update([
            'read_at' => $data['is_read'] ? now() : null,
        ]);
        AuditLog::record($data['is_read'] ? 'message.read' : 'message.unread', $contactMessage);

        return back()->with('success', $data['is_read']
            ? 'Pesan ditandai sudah dibaca.'
            : 'Pesan ditandai belum dibaca.');
    }

    public function users(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'account_status' => ['nullable', 'in:active,suspended'],
        ]);

        $users = User::with('roleModel')
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role_id'] ?? null, fn ($query, $roleId) => $query->where('role_id', $roleId))
            ->when($filters['account_status'] ?? null, fn ($query, $status) => $query->where('account_status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $roles = Role::orderBy('label')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function updateUserRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
        ]);
        $role = Role::findOrFail($data['role_id']);

        abort_if($user->is($request->user()) && $role->name !== 'admin', 422, 'Admin tidak dapat menurunkan role akun sendiri.');

        if ($user->isAdmin() && $role->name !== 'admin') {
            abort_if($this->activeAdminCount() <= 1, 422, 'Admin terakhir tidak dapat diturunkan rolenya.');
        }

        $oldRole = $user->roleName();
        $user->update(['role_id' => $role->id]);
        AuditLog::record('user.role_updated', $user, [
            'from' => $oldRole,
            'to' => $role->name,
        ]);

        return back()->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $data = $request->validate([
            'account_status' => ['required', Rule::in(['active', 'suspended'])],
        ]);

        abort_if($user->is($request->user()) && $data['account_status'] === 'suspended', 422, 'Admin tidak dapat menangguhkan akun sendiri.');

        if ($user->isAdmin() && $data['account_status'] === 'suspended') {
            abort_if($this->activeAdminCount() <= 1, 422, 'Admin aktif terakhir tidak dapat ditangguhkan.');
        }

        $oldStatus = $user->account_status;
        $user->update(['account_status' => $data['account_status']]);
        AuditLog::record('user.status_updated', $user, [
            'from' => $oldStatus,
            'to' => $data['account_status'],
        ]);

        return back()->with('success', $data['account_status'] === 'active'
            ? 'Akun pengguna diaktifkan kembali.'
            : 'Akun pengguna ditangguhkan.');
    }

    public function activity()
    {
        $activity = AuditLog::with('actor')->latest()->paginate(30);

        return view('admin.activity.index', compact('activity'));
    }

    public function authorDashboard()
    {
        $articles = Article::with(['category', 'author', 'latestReview.reviewer', 'reviews.reviewer'])
            ->where('author_id', auth()->id())
            ->latest()
            ->get();
        $articleCount = $articles->count();
        $publishedCount = $articles->where('status', 'published')->count();
        $draftCount = $articles->where('status', 'draft')->count();
        $reviewCount = $articles->where('status', 'review')->count();
        $rejectedCount = $articles->where('status', 'rejected')->count();

        return view('author.dashboard', compact(
            'articles',
            'articleCount',
            'publishedCount',
            'draftCount',
            'reviewCount',
            'rejectedCount'
        ));
    }

    public function approve(Request $request, Article $article)
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($article, $request, $data): void {
            $lockedArticle = Article::whereKey($article->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedArticle->status === 'review', 422, 'Hanya artikel dalam review yang dapat disetujui.');

            $lockedArticle->reviews()->create([
                'reviewer_id' => $request->user()->id,
                'decision' => 'approved',
                'note' => $data['note'] ?? null,
            ]);
            $lockedArticle->update(['status' => 'published']);
        });
        AuditLog::record('article.approved', $article, ['note' => $data['note'] ?? null]);

        return back()->with('success', 'Artikel disetujui dan dipublikasikan.');
    }

    public function reject(Request $request, Article $article)
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ], [
            'note.required' => 'Alasan revisi wajib diisi saat menolak artikel.',
        ]);

        DB::transaction(function () use ($article, $request, $data): void {
            $lockedArticle = Article::whereKey($article->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedArticle->status === 'review', 422, 'Hanya artikel dalam review yang dapat ditolak.');

            $lockedArticle->reviews()->create([
                'reviewer_id' => $request->user()->id,
                'decision' => 'rejected',
                'note' => $data['note'],
            ]);
            $lockedArticle->update(['status' => 'rejected']);
        });
        AuditLog::record('article.rejected', $article, ['note' => $data['note']]);

        return back()->with('success', 'Artikel ditolak dan dikembalikan kepada penulis.');
    }

    public function approveRevision(Request $request, Article $article, ArticleRevision $revision)
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless($revision->article_id === $article->id, 404);

        DB::transaction(function () use ($article, $revision, $request, $data): void {
            $lockedRevision = ArticleRevision::whereKey($revision->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedRevision->status === 'review', 422, 'Hanya revisi yang menunggu review yang dapat disetujui.');

            $lockedArticle = Article::whereKey($article->id)->lockForUpdate()->firstOrFail();
            $oldThumbnailMedia = $lockedArticle->thumbnailMedia;

            $lockedArticle->update([
                'category_id' => $lockedRevision->category_id,
                'title' => $lockedRevision->title,
                'slug' => $lockedRevision->slug,
                'summary' => $lockedRevision->summary,
                'content' => $lockedRevision->content,
                'thumbnail_media_id' => $lockedRevision->thumbnail_media_id,
                'status' => 'published',
            ]);
            $this->syncArticleQuiz($lockedArticle, $lockedRevision->quiz_data ?? []);

            $lockedRevision->update([
                'status' => 'approved',
                'reviewer_id' => $request->user()->id,
                'review_note' => $data['note'] ?? null,
                'reviewed_at' => now(),
            ]);

            if ($oldThumbnailMedia && $oldThumbnailMedia->id !== $lockedRevision->thumbnail_media_id) {
                $this->deleteMedia($oldThumbnailMedia);
            }
        });

        AuditLog::record('article.revision_approved', $revision, [
            'article_id' => $article->id,
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Revisi artikel disetujui dan versi publik diperbarui.');
    }

    public function rejectRevision(Request $request, Article $article, ArticleRevision $revision)
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ], [
            'note.required' => 'Alasan revisi wajib diisi saat menolak revisi.',
        ]);

        abort_unless($revision->article_id === $article->id, 404);

        DB::transaction(function () use ($revision, $request, $data): void {
            $lockedRevision = ArticleRevision::whereKey($revision->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedRevision->status === 'review', 422, 'Hanya revisi yang menunggu review yang dapat ditolak.');

            $lockedRevision->update([
                'status' => 'rejected',
                'reviewer_id' => $request->user()->id,
                'review_note' => $data['note'],
                'reviewed_at' => now(),
            ]);
        });

        AuditLog::record('article.revision_rejected', $revision, [
            'article_id' => $article->id,
            'note' => $data['note'],
        ]);

        return back()->with('success', 'Revisi artikel ditolak dan dikembalikan kepada penulis.');
    }

    private function activeAdminCount(): int
    {
        return User::query()
            ->where('account_status', 'active')
            ->whereHas('roleModel', fn ($query) => $query->where('name', 'admin'))
            ->count();
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

    private function deleteMedia($media): void
    {
        $disk = $media->disk;
        $path = $media->path;

        $media->delete();
        Storage::disk($disk)->delete($path);
    }
}
