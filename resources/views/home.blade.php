@extends('layouts.app')

@section('title', 'Home - Innobit')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-12">
        <div class="md:w-1/2">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-800 mb-2">Naik Level Tanpa Drama</h1>
            <h2 class="text-xl md:text-2xl text-slate-600 mb-2">Platform Praktis untuk Kuasai Skill Digital</h2>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border p-4 md:p-6 mb-8">
        <form method="GET" action="{{ route('home') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Cari Artikel</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul artikel" class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Filter Kategori</label>
                <select name="category_id" class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-lime-500 text-white rounded-lg px-4 py-3 hover:bg-lime-600 transition font-bold">Cari Artikel</button>
            </div>
        </form>
    </div>

    @if ($articles->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($articles as $article)
                <div class="bg-white rounded-2xl shadow-sm border overflow-hidden hover:shadow-lg transition-shadow flex flex-col">
                    <div class="w-full h-52 bg-slate-100 flex items-center justify-center overflow-hidden">
                        @if ($article->thumbnail)
                            <img src="{{ \Illuminate\Support\Str::startsWith($article->thumbnail, 'http') ? $article->thumbnail : asset('uploads/artikel/'.$article->thumbnail) }}" alt="{{ $article->title }}" class="w-full h-full object-contain p-2">
                        @else
                            <div class="text-slate-400 text-sm italic">Tidak ada gambar</div>
                        @endif
                    </div>

                    <div class="p-6 flex flex-col flex-grow">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold bg-lime-100 text-lime-600 px-3 py-1 rounded-full uppercase tracking-wider">{{ $article->category->name }}</span>
                            <span class="text-xs text-slate-400">{{ $article->created_at->format('d M Y') }}</span>
                        </div>

                        <h2 class="text-lg font-bold mb-3 text-slate-800 line-clamp-2">{{ $article->title }}</h2>
                        <p class="text-slate-500 text-sm mb-6 line-clamp-3 leading-relaxed">{{ $article->summary }}</p>

                        <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-400">Oleh <span class="text-slate-600">{{ $article->author->name }}</span></span>
                            <a href="{{ auth()->check() ? route('articles.show', $article->slug) : route('login') }}" class="text-lime-600 text-sm font-bold hover:text-lime-700 flex items-center gap-1">
                                Baca Selengkapnya
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-slate-50 border border-dashed border-slate-200 text-slate-500 rounded-2xl p-12 text-center">
            <h2 class="text-xl font-bold mb-1">Ops! Artikel tidak ditemukan</h2>
            <p>Coba gunakan kata kunci lain atau cek kategori lainnya.</p>
        </div>
    @endif
</div>
@endsection
