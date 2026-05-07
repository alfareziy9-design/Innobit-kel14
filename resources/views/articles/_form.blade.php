@csrf
@isset($article)
    @method('PUT')
@endisset

<div>
    <label for="title" class="block mb-2 font-medium">Judul Artikel</label>
    <input type="text" id="title" name="title" value="{{ old('title', $article->title ?? '') }}" class="w-full border rounded-xl px-4 py-3">
</div>

<div>
    <label for="category_id" class="block mb-2 font-medium">Kategori</label>
    <select id="category_id" name="category_id" class="w-full border rounded-xl px-4 py-3">
        <option value="">Pilih kategori</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(old('category_id', $article->category_id ?? '') == $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
</div>

<div>
    <label for="summary" class="block mb-2 font-medium">Ringkasan</label>
    <textarea id="summary" name="summary" rows="3" class="w-full border rounded-xl px-4 py-3">{{ old('summary', $article->summary ?? '') }}</textarea>
</div>

<div>
    <label for="content" class="block mb-2 font-medium">Isi Artikel</label>
    <textarea id="content" name="content" rows="8" class="w-full border rounded-xl px-4 py-3">{{ old('content', $article->content ?? '') }}</textarea>
</div>

@isset($article)
    <div>
        <label class="block mb-2 font-medium">Thumbnail Saat Ini</label>
        @if ($article->thumbnail)
            <img src="{{ asset('uploads/artikel/'.$article->thumbnail) }}" alt="{{ $article->title }}" class="w-full max-w-xs rounded-xl border mb-3">
        @else
            <p class="text-slate-500 mb-3">Belum ada thumbnail.</p>
        @endif
    </div>
@endisset

<div>
    <label for="thumbnail" class="block mb-2 font-medium">{{ isset($article) ? 'Ganti Thumbnail' : 'Thumbnail' }}</label>
    <input type="file" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.webp" class="w-full border rounded-xl px-4 py-3 bg-white">
    <p class="text-sm text-slate-500 mt-2">Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</p>
</div>

<div>
    <label for="status" class="block mb-2 font-medium">Status</label>
    <select id="status" name="status" class="w-full border rounded-xl px-4 py-3">
        <option value="published" @selected(old('status', $article->status ?? 'published') === 'published')>Published</option>
        <option value="draft" @selected(old('status', $article->status ?? '') === 'draft')>Draft</option>
    </select>
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" class="bg-lime-600 text-white px-5 py-3 rounded-xl hover:bg-lime-700">{{ isset($article) ? 'Update Artikel' : 'Simpan Artikel' }}</button>
    <a href="{{ route('admin.dashboard') }}" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl hover:bg-slate-300">Kembali</a>
</div>
