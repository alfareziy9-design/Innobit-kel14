@extends('layouts.app')

@section('title', 'Home - Innobit')

@section('content')
@php
    $articleCount = $hasActiveFilters ? $articles->total() : $articles->count();
    $fallbackImages = [
        asset('assets/img/microlearning-data-dashboard.png'),
        asset('assets/img/microlearning-clean-code.png'),
        asset('assets/img/microlearning-time-focus.png'),
    ];
    $historyHref = auth()->check() ? route('learning.history') : route('login');
    $favoriteHref = auth()->check() ? route('learning.favorites') : route('login');
    $collectionHref = auth()->check() ? route('learning.collections') : route('login');
    $accountHref = auth()->check() ? route('profile.show') : route('login');
    $selectedCategory = $hasActiveFilters && filled($filters['category_id'] ?? null)
        ? $categories->firstWhere('id', (int) $filters['category_id'])
        : null;
@endphp

<div class="min-h-screen bg-lime-50/45 text-slate-950">
    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 lg:grid-cols-[15rem_minmax(0,1fr)] lg:py-10">
    <aside class="sticky top-24 hidden max-h-[calc(100vh-7rem)] self-start overflow-y-auto pr-1 lg:block">
        <div class="rounded-lg border border-lime-200 bg-white/90 p-4 shadow-sm backdrop-blur">
        <div class="mb-4 px-3 py-2">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-800">Menu Belajar</p>
        </div>

        <nav class="space-y-1 text-sm font-bold">
            <a href="{{ $historyHref }}" class="flex items-center justify-between rounded-lg px-3 py-3 text-slate-700 transition hover:bg-lime-50 hover:text-lime-800">
                <span>Histori</span>
                <span class="text-slate-400">›</span>
            </a>
            <a href="{{ $favoriteHref }}" class="flex items-center justify-between rounded-lg px-3 py-3 text-slate-700 transition hover:bg-lime-50 hover:text-lime-800">
                <span>Favorit</span>
                <span class="text-slate-400">›</span>
            </a>
            <a href="{{ $collectionHref }}" class="flex items-center justify-between rounded-lg px-3 py-3 text-slate-700 transition hover:bg-lime-50 hover:text-lime-800">
                <span>Koleksi</span>
                <span class="text-slate-400">›</span>
            </a>
            <a href="{{ $accountHref }}" class="flex items-center justify-between rounded-lg px-3 py-3 text-slate-700 transition hover:bg-lime-50 hover:text-lime-800">
                <span>Profil Saya</span>
                <span class="text-slate-400">›</span>
            </a>
        </nav>
        </div>

        <div class="mt-4 rounded-lg border border-lime-200 bg-white/90 p-4 shadow-sm backdrop-blur">
            <div class="mb-3 px-3">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-800">Kategori</p>
            </div>

            <div class="space-y-2">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" class="flex items-center justify-between rounded-lg border border-lime-100 bg-lime-50/60 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-lime-300 hover:bg-white hover:text-lime-800">
                        <span class="truncate">{{ $category->name }}</span>
                        <span class="ml-3 rounded-full bg-white px-2 py-1 text-xs font-black text-lime-800 shadow-sm">{{ $category->published_articles_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </aside>

    <div class="min-w-0">
        <form method="GET" action="{{ route('home') }}" class="mb-8 w-full">
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto] md:items-end">
                <div>
                    <input id="home-search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari artikel, topik, atau skill..." aria-label="Cari artikel" class="h-12 w-full rounded-lg border border-lime-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm outline-none transition placeholder:font-medium placeholder:text-slate-400 focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                </div>

                <div>
                    <select id="home-category" name="category_id" aria-label="Kategori" class="h-12 w-full rounded-lg border border-lime-200 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 md:flex md:items-center">
                    <button type="submit" class="h-12 rounded-lg bg-slate-950 px-5 text-sm font-black text-white shadow-sm transition hover:bg-lime-700 focus:outline-none focus:ring-4 focus:ring-lime-100">Cari Artikel</button>

                    @if ($hasActiveFilters)
                        <a href="{{ route('home') }}" class="inline-flex h-12 items-center justify-center rounded-lg border border-lime-200 bg-white px-4 text-sm font-black text-slate-700 shadow-sm transition hover:border-lime-600 hover:text-lime-800 focus:outline-none focus:ring-4 focus:ring-lime-100">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        @if ($hasActiveFilters)
            <section>
                <div class="mb-5">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-lime-800">Pencarian</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight md:text-4xl">Hasil Artikel</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Ditemukan {{ $articles->total() }} artikel
                        @if (filled($filters['search'] ?? null))
                            untuk kata kunci “{{ $filters['search'] }}”
                        @endif
                        @if ($selectedCategory)
                            {{ filled($filters['search'] ?? null) ? 'dalam' : 'pada' }} kategori {{ $selectedCategory->name }}
                        @endif.
                    </p>
                </div>

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
                    <div class="rounded-lg border border-lime-200 bg-white p-6 shadow-sm md:p-10">
                        <div class="mx-auto max-w-2xl text-center">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-800">Tidak ada hasil</p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Artikel tidak ditemukan</h2>
                            <p class="mt-3 leading-7 text-slate-600">
                                Kami tidak menemukan artikel
                                @if (filled($filters['search'] ?? null))
                                    untuk kata kunci <strong class="text-slate-900">“{{ $filters['search'] }}”</strong>
                                @endif
                                @if ($selectedCategory)
                                    {{ filled($filters['search'] ?? null) ? 'dalam' : 'pada' }} kategori <strong class="text-slate-900">{{ $selectedCategory->name }}</strong>
                                @endif.
                            </p>

                            <div class="mt-6 rounded-lg bg-lime-50 p-4 text-left text-sm leading-6 text-slate-600">
                                <p class="font-black text-slate-800">Coba langkah berikut:</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    <li>Periksa kembali ejaan kata kunci.</li>
                                    <li>Gunakan kata yang lebih umum atau lebih singkat.</li>
                                    <li>Kurangi filter agar pilihan artikel lebih luas.</li>
                                </ul>
                            </div>

                            <div class="mt-6 flex flex-wrap justify-center gap-3">
                                @if (filled($filters['search'] ?? null) && $selectedCategory)
                                    <a href="{{ route('home', ['category_id' => $selectedCategory->id]) }}" class="rounded-lg bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-lime-700">Hapus kata kunci</a>
                                    <a href="{{ route('home', ['search' => $filters['search']]) }}" class="rounded-lg border border-lime-300 bg-white px-5 py-3 text-sm font-black text-lime-800 transition hover:border-lime-600 hover:bg-lime-50">Cari di semua kategori</a>
                                    <a href="{{ route('home') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:border-slate-500">Reset semua</a>
                                @elseif (filled($filters['search'] ?? null))
                                    <a href="{{ route('home') }}" class="rounded-lg bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-lime-700">Reset pencarian</a>
                                @elseif ($selectedCategory)
                                    <a href="{{ route('home') }}" class="rounded-lg bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-lime-700">Lihat semua artikel</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @else
        <section class="mb-8">
            <div class="relative isolate mx-auto flex max-w-6xl items-start overflow-hidden rounded-2xl px-4 pb-8 pt-8 text-left md:pb-12 md:pt-10">
                <div class="absolute inset-0 z-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/img/hero.png') }}');"></div>
                <div class="relative z-10">
                    <h1 class="text-4xl font-black leading-tight tracking-tight text-slate-950 md:text-6xl">Baca Setiap Hari</h1>
                    <p class="mt-4 min-h-8 text-2xl font-black tracking-tight text-lime-700 md:min-h-10 md:text-4xl">
                        <span class="inline-block min-w-[14ch] text-left">
                            <span data-typing-words='["Jadi Lebih Tau","Jadi Lebih Jago","Jadi Lebih Baik"]' aria-label="Jadi Lebih Tau"></span><span class="typing-cursor text-lime-600">|</span>
                        </span>
                    </p>
                    <a href="#artikel-harian" class="mt-8 inline-flex items-center rounded-lg bg-lime-600 px-6 py-3 text-sm font-black text-white transition hover:bg-lime-700">Yuk mulai</a>
                </div>
            </div>

            @if ($recommendedArticles->count())
                <div class="mt-8">
                    <h2 class="text-2xl font-black tracking-tight">Rekomendasi Untukmu</h2>
                </div>
                <div class="mt-6 grid gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($recommendedArticles as $article)
                        @include('partials.article-card', ['article' => $article, 'cardIndex' => $loop->iteration])
                    @endforeach
                    @include('partials.view-more-card', [
                        'href' => route('articles.explore', 'recommended'),
                        'label' => 'Lihat rekomendasi lainnya',
                    ])
                </div>
            @endif
        </section>

        <style>
            .typing-cursor {
                animation: typing-cursor-blink 1s step-end infinite;
            }

            @keyframes typing-cursor-blink {
                50% {
                    opacity: 0;
                }
            }
        </style>

        <script>
            document.querySelectorAll('[data-typing-words]').forEach((element) => {
                const words = JSON.parse(element.dataset.typingWords || '[]');

                if (!words.length) {
                    return;
                }

                let wordIndex = 0;
                let charIndex = 0;
                let deleting = false;

                const type = () => {
                    const word = words[wordIndex];
                    element.textContent = word.slice(0, charIndex);

                    if (deleting) {
                        charIndex -= 1;
                        if (charIndex < 0) {
                            deleting = false;
                            wordIndex = (wordIndex + 1) % words.length;
                            setTimeout(type, 250);
                            return;
                        }
                    } else {
                        charIndex += 1;
                        if (charIndex > words[wordIndex].length) {
                            deleting = true;
                            setTimeout(type, 1300);
                            return;
                        }
                    }

                    setTimeout(type, deleting ? 45 : 80);
                };

                type();
            });
        </script>

        <nav class="mb-6 grid grid-cols-2 gap-2 text-sm font-bold lg:hidden">
            <a href="{{ $historyHref }}" class="rounded-lg border border-lime-200 bg-white px-4 py-3 text-center text-slate-700">Histori</a>
            <a href="{{ $favoriteHref }}" class="rounded-lg border border-lime-200 bg-white px-4 py-3 text-center text-slate-700">Favorit</a>
            <a href="{{ $collectionHref }}" class="rounded-lg border border-lime-200 bg-white px-4 py-3 text-center text-slate-700">Koleksi</a>
            <a href="{{ $accountHref }}" class="rounded-lg border border-lime-200 bg-white px-4 py-3 text-center text-slate-700">Profil Saya</a>
        </nav>

        <section class="mb-8 lg:hidden">
            <div class="mb-3">
                <h2 class="text-xl font-black tracking-tight">Kategori</h2>
                <p class="mt-1 text-sm text-slate-600">Lihat jumlah artikel di setiap kategori.</p>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm font-bold sm:grid-cols-3">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" class="rounded-lg border border-lime-200 bg-white p-4 text-slate-700 shadow-sm transition hover:border-lime-600 hover:text-lime-800">
                        <span class="block truncate">{{ $category->name }}</span>
                        <span class="mt-2 block text-xs font-black uppercase tracking-[0.12em] text-lime-800">{{ $category->published_articles_count }} artikel</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section id="artikel-harian">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black tracking-tight">Baru Terbit</h2>
                    <p class="mt-1 text-sm text-slate-600">Pilih satu materi, baca sebentar, lalu praktikkan.</p>
                </div>
                <span class="hidden text-xs font-black uppercase tracking-[0.18em] text-lime-800 md:inline-flex">Baru Terbit</span>
            </div>

            @if ($articles->count())
                <div class="grid gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($articles as $article)
                        @include('partials.article-card', ['article' => $article, 'cardIndex' => $loop->iteration])
                    @endforeach
                    @include('partials.view-more-card', [
                        'href' => route('articles.explore', 'latest'),
                        'label' => 'Lihat artikel terbaru lainnya',
                    ])
                </div>

            @else
                <div class="rounded-lg border border-dashed border-lime-300 bg-white p-12 text-center text-slate-500">
                    <h2 class="mb-1 text-xl font-black text-slate-800">Artikel tidak ditemukan</h2>
                    <p>Coba gunakan kata kunci lain atau cek kategori lainnya.</p>
                </div>
            @endif
        </section>

        @if ($popularArticles->count())
            <section class="mt-12">
                <div class="mb-5">
                    <h2 class="text-2xl font-black tracking-tight">Sedang Populer</h2>
                    <p class="mt-1 text-sm text-slate-600">Artikel yang paling banyak dibaca oleh pengguna InnoBit.</p>
                </div>

                <div class="grid gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($popularArticles as $article)
                        @include('partials.article-card', ['article' => $article, 'cardIndex' => $loop->iteration + 1])
                    @endforeach
                    @include('partials.view-more-card', [
                        'href' => route('articles.explore', 'popular'),
                        'label' => 'Lihat artikel populer lainnya',
                    ])
                </div>
            </section>
        @endif

        @if ($discoveryArticles->count())
            <section class="mt-12">
                <div class="mb-5">
                    <h2 class="text-2xl font-black tracking-tight">🔍 Yuk Cari Tau Hal Baru</h2>
                    <p class="mt-1 text-sm text-slate-600">Temukan topik baru yang mungkin belum pernah kamu baca.</p>
                </div>

                <div class="grid gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($discoveryArticles as $article)
                        @include('partials.article-card', ['article' => $article, 'cardIndex' => $loop->iteration + 2])
                    @endforeach
                    @include('partials.view-more-card', [
                        'href' => route('articles.explore', 'discovery'),
                        'label' => 'Jelajahi artikel lainnya',
                    ])
                </div>
            </section>
        @endif
        @endif
    </div>
    </div>
</div>
@endsection
