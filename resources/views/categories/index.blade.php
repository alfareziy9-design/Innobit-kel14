@extends('layouts.app')

@section('title', 'Kategori - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-6xl px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
        @include('admin._subnav')

        <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-lime-700">Struktur materi</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950">Kelola kategori</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $categories->count() }} kategori tersedia untuk mengelompokkan artikel.</p>
            </div>
            <a href="{{ route('kategori.create') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-lime-700">Tambah kategori</a>
        </header>

        @include('partials.alerts')

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if ($categories->isNotEmpty())
                <div class="hidden sm:block">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-100 text-xs font-bold text-slate-500">
                            <tr><th class="px-6 py-4">Kategori</th><th class="px-4 py-4">Slug</th><th class="px-6 py-4 text-right">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($categories as $category)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-6 py-5"><p class="font-bold text-slate-900">{{ $category->name }}</p><p class="mt-1 text-xs text-slate-400">Kategori #{{ $loop->iteration }}</p></td>
                                    <td class="px-4 py-5"><code class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs text-slate-600">{{ $category->slug }}</code></td>
                                    <td class="px-6 py-5">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('kategori.edit', $category) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700">Edit</a>
                                            <form action="{{ route('kategori.destroy', $category) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                @csrf @method('DELETE')
                                                <button class="px-2 py-2 text-xs font-bold text-rose-600">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-slate-100 sm:hidden">
                    @foreach ($categories as $category)
                        <article class="p-5">
                            <p class="font-black text-slate-900">{{ $category->name }}</p>
                            <code class="mt-2 inline-block rounded-lg bg-slate-100 px-2.5 py-1 text-xs text-slate-600">{{ $category->slug }}</code>
                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('kategori.edit', $category) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700">Edit kategori</a>
                                <form action="{{ route('kategori.destroy', $category) }}" method="POST" class="ml-auto" onsubmit="return confirm('Yakin ingin menghapus data ini?')">@csrf @method('DELETE')<button class="px-2 py-2 text-xs font-bold text-rose-600">Hapus</button></form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-14 text-center">
                    <h2 class="text-lg font-black text-slate-950">Belum ada kategori</h2>
                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">Buat kategori pertama agar artikel lebih mudah ditemukan dan dikelola.</p>
                    <a href="{{ route('kategori.create') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white">Tambah kategori</a>
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
