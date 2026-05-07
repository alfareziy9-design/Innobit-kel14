<?php

namespace App\Http\Controllers;

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

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedCategoryData($request);
        $data['slug'] = Str::slug($data['name']);

        Category::create($data);

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
        $category->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->articles()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh artikel.');
        }

        $category->delete();

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
