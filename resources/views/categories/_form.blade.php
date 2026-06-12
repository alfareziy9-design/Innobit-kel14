@csrf
@isset($category)
    @method('PUT')
@endisset

<div>
    <label for="name" class="mb-2 block text-sm font-bold text-slate-700">Nama kategori</label>
    <input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" placeholder="Contoh: Programming" class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100" required maxlength="100">
    <p class="mt-2 text-xs leading-5 text-slate-500">Slug dibuat otomatis dari nama kategori.</p>
</div>

<div class="flex flex-col gap-3 sm:flex-row">
    <button type="submit" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-lime-700">{{ isset($category) ? 'Simpan perubahan' : 'Simpan kategori' }}</button>
    <a href="{{ route('kategori.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:border-slate-400">Batal</a>
</div>
