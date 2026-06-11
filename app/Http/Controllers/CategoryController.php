<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function show(Request $request, Category $category)
    {
        $user = $request->user();
        $articles = $category->articles()
            ->with(['category', 'author', 'thumbnailMedia'])
            ->when($user, fn ($query) => $query->withExists([
                'favorites as is_favorited' => fn ($query) => $query->where('user_id', $user->id),
            ]))
            ->where('status', 'published')
            ->latest()
            ->paginate(16);

        return view('categories.show', compact('category', 'articles'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedCategoryData($request);
        $data['slug'] = Str::slug($data['name']);

        Category::create($data);
        AuditLog::record('category.created', Category::where('slug', $data['slug'])->first());

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validatedCategoryData($request, $category->id);
        $data['slug'] = Str::slug($data['name']);
        $before = $category->only(['name', 'slug']);
        $category->update($data);
        AuditLog::record('category.updated', $category, [
            'before' => $before,
            'after' => $category->only(['name', 'slug']),
        ]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->articles()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh artikel.');
        }

        $category->delete();
        AuditLog::record('category.deleted', $category, [
            'name' => $category->name,
        ]);

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    private function validatedCategoryData(Request $request, ?int $ignoreId = null): array
    {
        $slug = Str::slug((string) $request->input('name'));

        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($ignoreId)],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => Category::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()
                ? 'Kategori dengan nama tersebut sudah ada.'
                : 'Kategori sudah ada.',
        ]);
    }
}
