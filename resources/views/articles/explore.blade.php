@extends('layouts.app')

@section('title', $sectionTitle.' - InnoBit')

@section('content')
@php
    $fallbackImages = [
        asset('assets/img/microlearning-data-dashboard.png'),
        asset('assets/img/microlearning-clean-code.png'),
        asset('assets/img/microlearning-time-focus.png'),
    ];
@endphp

<div class="min-h-screen bg-lime-50/45 text-slate-950">
    <main class="mx-auto max-w-7xl px-4 py-8 lg:py-10">
        <a href="{{ route('home') }}" class="text-sm font-bold text-lime-800 transition hover:text-lime-600">&larr; Kembali ke Home</a>

        <header class="mb-8 mt-5">
            <h1 class="text-3xl font-black tracking-tight md:text-4xl">{{ $sectionTitle }}</h1>
            <p class="mt-2 text-slate-600">{{ $sectionDescription }}</p>
        </header>

        @if ($articles->count())
            <div class="grid gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($articles as $article)
                    @include('partials.article-card', [
                        'article' => $article,
                        'cardIndex' => ($articles->firstItem() ?? 1) + $loop->index,
                    ])
                @endforeach
            </div>

            @if ($articles->hasPages())
                <div class="mt-10 flex items-center justify-center gap-3">
                    @if ($articles->onFirstPage())
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400">Sebelumnya</span>
                    @else
                        <a href="{{ $articles->previousPageUrl() }}" class="rounded-lg border border-lime-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-lime-600 hover:text-lime-800">Sebelumnya</a>
                    @endif

                    <span class="text-sm font-bold text-slate-600">Halaman {{ $articles->currentPage() }} dari {{ $articles->lastPage() }}</span>

                    @if ($articles->hasMorePages())
                        <a href="{{ $articles->nextPageUrl() }}" class="rounded-lg border border-lime-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-lime-600 hover:text-lime-800">Berikutnya</a>
                    @else
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400">Berikutnya</span>
                    @endif
                </div>
            @endif
        @else
            <div class="rounded-lg border border-dashed border-lime-300 bg-white p-12 text-center text-slate-500">
                Belum ada artikel untuk section ini.
            </div>
        @endif
    </main>
</div>
@endsection
