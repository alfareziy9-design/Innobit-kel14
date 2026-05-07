@extends('layouts.app')

@section('title', ($article?->title ?? 'Artikel tidak ditemukan').' - InnoBit')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    @if ($article)
        <article class="bg-white rounded-2xl shadow-sm border overflow-hidden">
            @if ($article->thumbnail)
                <img src="{{ asset('uploads/artikel/'.$article->thumbnail) }}" alt="{{ $article->title }}" class="w-full h-72 md:h-96 object-cover">
            @endif

            <div class="p-6 md:p-8">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="text-sm font-semibold bg-lime-100 text-lime-700 px-3 py-1 rounded-full">{{ $article->category->name }}</span>
                    <span class="text-sm text-slate-500">{{ $article->created_at->format('d M Y H:i') }}</span>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $article->title }}</h1>
                <p class="text-slate-600 text-lg mb-6">{{ $article->summary }}</p>

                <div class="text-sm text-slate-500 mb-6">
                    Ditulis oleh <strong>{{ $article->author->name }}</strong>
                </div>

                <div class="prose max-w-none text-slate-700 leading-8">
                    {!! nl2br(e($article->content)) !!}
                </div>

                <div class="mt-8">
                    <a href="{{ route('home') }}" class="inline-block bg-lime-700 text-white px-5 py-3 rounded-xl hover:bg-lime-800">Kembali ke Home</a>
                </div>
            </div>
        </article>
    @else
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl p-6">Artikel tidak ditemukan.</div>
    @endif
</div>
@endsection
