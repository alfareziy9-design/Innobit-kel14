@csrf
@isset($category)
    @method('PUT')
@endisset

<div>
    <label for="name" class="block mb-2 font-medium">Nama Kategori</label>
    <input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" class="w-full border rounded-xl px-4 py-3">
</div>

<div class="flex gap-3">
    <button type="submit" class="bg-lime-600 text-white px-5 py-3 rounded-xl hover:bg-lime-700">{{ isset($category) ? 'Update' : 'Simpan' }}</button>
    <a href="{{ route('kategori.index') }}" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl hover:bg-slate-300">Kembali</a>
</div>
