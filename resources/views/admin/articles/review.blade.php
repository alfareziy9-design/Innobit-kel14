@extends('layouts.app')

@section('title', 'Review Artikel - InnoBit')

@section('content')
@php
    $isRevision = isset($revision);
    $source = $revision ?? $article;
    $coverImage = $source->thumbnailMedia?->url;
    $quizData = $isRevision
        ? collect($revision->quiz_data ?? [])
        : collect($article->normalizedQuiz?->questions ?? [])->map(fn ($question) => [
            'question' => $question->question,
            'options' => $question->options->pluck('option_text')->all(),
        ]);
@endphp

<div class="min-h-[calc(100vh-140px)] bg-slate-50">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @include('admin._subnav')
        @include('partials.alerts')

        <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold text-lime-700">Antrean editorial</p>
                <h1 class="mt-1 text-3xl font-black text-slate-950">{{ $isRevision ? 'Review Pembaruan Artikel' : 'Review Artikel Baru' }}</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Ditulis oleh <strong>{{ $source->author->name }}</strong>, dikirim {{ $source->updated_at->format('d M Y H:i') }}.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ $isRevision ? route('admin.articles.revisions.review.edit', [$article, $revision]) : route('admin.articles.review.edit', $article) }}" class="rounded-xl bg-violet-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-violet-800">Edit Konten</a>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-slate-600 hover:text-lime-700">&larr; Kembali ke antrean</a>
            </div>
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @if ($coverImage)
                    <img src="{{ $coverImage }}" alt="{{ $source->title }}" class="max-h-[440px] w-full object-cover">
                @endif
                <div class="p-6 sm:p-8">
                    <p class="text-sm font-bold text-lime-700">{{ $source->category->name }}</p>
                    <h2 class="mt-2 text-3xl font-black leading-tight text-slate-950">{{ $source->title }}</h2>
                    <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-7 text-slate-700">{{ $source->summary }}</p>
                    <div class="article-body mt-8">{!! $source->content !!}</div>

                    @if ($quizData->isNotEmpty())
                        <section class="mt-8 border-t border-slate-200 pt-6">
                            <h3 class="text-lg font-black text-slate-950">Quiz ({{ $quizData->count() }})</h3>
                            <div class="mt-4 space-y-4">
                                @foreach ($quizData as $quiz)
                                    <div class="rounded-xl border border-slate-200 p-4">
                                        <p class="font-bold text-slate-900">{{ $quiz['question'] }}</p>
                                        <ol class="mt-3 list-inside list-decimal space-y-1 text-sm text-slate-600">
                                            @foreach ($quiz['options'] as $option)
                                                <li>{{ $option }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </article>

            <aside class="space-y-5 lg:sticky lg:top-6">
                @if ($isRevision)
                    <section class="rounded-2xl border border-violet-200 bg-violet-50 p-5">
                        <p class="text-sm font-bold text-violet-800">Versi publik tetap tayang</p>
                        <p class="mt-2 text-sm leading-6 text-violet-900">Judul publik saat ini: <strong>{{ $article->title }}</strong>.</p>
                        <a href="{{ route('articles.show', $article->slug) }}" target="_blank" rel="noopener" class="mt-3 inline-flex text-sm font-bold text-violet-800 underline">Buka versi publik</a>
                    </section>
                @endif

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-black text-slate-950">Keputusan Review</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Setujui jika siap tayang. Kembalikan dengan alasan yang konkret jika author perlu memperbaiki naskah.</p>

                    <form action="{{ $isRevision ? route('admin.articles.revisions.approve', [$article, $revision]) : route('admin.articles.approve', $article) }}" method="POST" class="mt-5">
                        @csrf
                        <label for="approve-note" class="text-xs font-bold text-slate-600">Catatan persetujuan</label>
                        <textarea id="approve-note" name="note" rows="3" maxlength="2000" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Opsional"></textarea>
                        <button class="mt-3 w-full rounded-xl bg-lime-600 px-4 py-3 text-sm font-black text-white hover:bg-lime-700">
                            {{ $isRevision ? 'Setujui Pembaruan' : 'Setujui dan Terbitkan' }}
                        </button>
                    </form>

                    <form action="{{ $isRevision ? route('admin.articles.revisions.reject', [$article, $revision]) : route('admin.articles.reject', $article) }}" method="POST" class="mt-5 border-t border-slate-100 pt-5">
                        @csrf
                        <label for="reject-note" class="text-xs font-bold text-rose-700">Alasan perbaikan</label>
                        <textarea id="reject-note" name="note" rows="4" required maxlength="2000" class="mt-2 w-full rounded-xl border border-rose-200 px-3 py-2 text-sm" placeholder="Jelaskan bagian yang perlu diperbaiki"></textarea>
                        <button class="mt-3 w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-black text-rose-700 hover:bg-rose-100">
                            Kembalikan ke Author
                        </button>
                    </form>
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection
