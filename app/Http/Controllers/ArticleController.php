<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function show(string $slug)
    {
        $article = Article::with(['category', 'author'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        return view('articles.show', compact('article'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedArticleData($request);
        $data['author_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['thumbnail'] = $this->storeThumbnail($request);

        Article::create($data);

        return redirect()->route('admin.dashboard')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article $article)
    {
        $categories = Category::orderBy('name')->get();

        return view('articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $data = $this->validatedArticleData($request, false);
        $data['slug'] = $this->uniqueSlug($data['title'], $article->id);

        if ($request->hasFile('thumbnail')) {
            $this->deleteThumbnail($article);
            $data['thumbnail'] = $this->storeThumbnail($request);
        }

        $article->update($data);

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        $this->deleteThumbnail($article);
        $article->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Artikel berhasil dihapus.');
    }

    private function validatedArticleData(Request $request, bool $thumbnailRequired = true): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'summary' => ['required', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'thumbnail' => [$thumbnailRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'title.required' => 'Judul wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'summary.required' => 'Ringkasan wajib diisi.',
            'content.required' => 'Isi artikel wajib diisi.',
            'thumbnail.required' => 'Thumbnail wajib diunggah.',
            'thumbnail.image' => 'Thumbnail harus berupa gambar.',
            'thumbnail.mimes' => 'Thumbnail harus berupa JPG, JPEG, PNG, atau WEBP.',
            'thumbnail.max' => 'Ukuran thumbnail maksimal 2MB.',
        ]);
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

    private function storeThumbnail(Request $request): string
    {
        $file = $request->file('thumbnail');
        $name = time().'_'.uniqid().'.'.$file->extension();
        $file->move(public_path('uploads/artikel'), $name);

        return $name;
    }

    private function deleteThumbnail(Article $article): void
    {
        if ($article->thumbnail) {
            $path = public_path('uploads/artikel/'.$article->thumbnail);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
