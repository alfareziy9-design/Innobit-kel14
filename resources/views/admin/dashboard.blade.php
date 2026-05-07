@extends('layouts.app')

@section('title', 'Panel Admin - InnoBit')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Dashboard Admin</h1>
        <p class="text-slate-600">Halo, {{ auth()->user()->name }}. Di sini Merupakan Tempat Kelola artikel InnoBit</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl border shadow-sm p-6">
            <h2 class="text-sm text-slate-500 mb-2">Total Artikel</h2>
            <p class="text-3xl font-bold text-lime-600">{{ $articleCount }}</p>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-6">
            <h2 class="text-sm text-slate-500 mb-2">Total Kategori</h2>
            <p class="text-3xl font-bold text-lime-600">{{ $categoryCount }}</p>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-6">
            <h2 class="text-sm text-slate-500 mb-2">Total User</h2>
            <p class="text-3xl font-bold text-lime-700">{{ $userCount }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm p-6 mb-8">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h2 class="text-xl font-semibold">Daftar Artikel</h2>
            <div class="flex gap-3">
                <a href="{{ route('articles.create') }}" class="bg-lime-600 text-white px-4 py-3 rounded-xl hover:bg-lime-700">Tambah Artikel</a>
                <a href="{{ route('kategori.index') }}" class="bg-green-600 text-white px-4 py-3 rounded-xl hover:bg-green-700">Kelola Kategori</a>
            </div>
        </div>

        @include('partials.alerts')

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-left">
                        <th class="p-3">No</th>
                        <th class="p-3">Judul</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Author</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($articles as $article)
                        <tr class="border-b">
                            <td class="p-3">{{ $loop->iteration }}</td>
                            <td class="p-3 font-medium">{{ $article->title }}</td>
                            <td class="p-3">{{ $article->category->name }}</td>
                            <td class="p-3">{{ $article->author->name }}</td>
                            <td class="p-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $article->status === 'published' ? 'bg-lime-100 text-lime-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $article->status }}</span>
                            </td>
                            <td class="p-3">{{ $article->created_at->format('d M Y') }}</td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-2">
                                    @if ($article->status === 'published')
                                        <a href="{{ route('articles.show', $article->slug) }}" class="bg-slate-200 text-slate-700 px-3 py-2 rounded-lg hover:bg-slate-300">Lihat</a>
                                    @endif
                                    <a href="{{ route('articles.edit', $article) }}" class="bg-yellow-400 text-white px-3 py-2 rounded-lg hover:bg-yellow-500">Edit</a>
                                    <form action="{{ route('articles.destroy', $article) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-500">Belum ada artikel. Silakan tambahkan artikel pertama.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
