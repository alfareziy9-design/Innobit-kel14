@extends('layouts.app')

@section('title', 'Dashboard Penulis - InnoBit')

@section('content')
@php
    $publishedPercent = $articleCount > 0 ? round(($publishedCount / $articleCount) * 100) : 0;
    $hasActiveFilters = request()->filled('search') || request()->filled('status') || request()->filled('category_id') || request()->filled('date_from') || request()->filled('date_to') || request()->filled('sort');
    $hasAdvancedFilters = request()->filled('date_from') || request()->filled('date_to') || request('sort', 'newest') !== 'newest';

    $statusMeta = function ($article): array {
        if ($article->pendingRevision) {
            return ['Revisi sedang ditinjau', 'border-violet-200 bg-violet-50 text-violet-700', 'bg-violet-500'];
        }

        return match ($article->status) {
            'published' => ['Terbit', 'border-lime-200 bg-lime-50 text-lime-700', 'bg-lime-500'],
            'review' => ['Menunggu review', 'border-sky-200 bg-sky-50 text-sky-700', 'bg-sky-500'],
            'rejected' => ['Perlu diperbaiki', 'border-rose-200 bg-rose-50 text-rose-700', 'bg-rose-500'],
            default => ['Draft', 'border-amber-200 bg-amber-50 text-amber-700', 'bg-amber-500'],
        };
    };
@endphp

<div class="min-h-[calc(100vh-140px)] bg-[#f7f8f5]">
    <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
        <header class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-8 px-5 py-7 sm:px-8 sm:py-9 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                <div class="max-w-3xl">
                    <div class="flex items-center gap-3 text-sm font-semibold text-lime-700">
                        <span class="h-px w-8 bg-lime-500"></span>
                        Ruang kerja penulis
                    </div>
                    <h1 class="mt-4 text-3xl font-black tracking-[-0.03em] text-slate-950 sm:text-4xl">
                        Selamat datang, {{ auth()->user()->name }}
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Lanjutkan materi yang sedang kamu susun atau pantau artikel yang menunggu tanggapan editor.
                    </p>
                </div>

                <a href="{{ route('articles.create') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-lime-700 focus:outline-none focus:ring-4 focus:ring-lime-100 sm:w-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Tulis artikel
                </a>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-5 sm:px-8">
                <div class="grid gap-4 lg:grid-cols-[1.15fr_1fr_2fr]">
                    <div class="flex items-end justify-between rounded-xl border border-slate-200 bg-white p-5">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Semua artikel</p>
                            <p class="mt-1 text-3xl font-black tracking-tight text-slate-950">{{ $articleCount }}</p>
                        </div>
                        <p class="text-xs font-semibold text-slate-400">milikmu</p>
                    </div>

                    <div class="rounded-xl border border-lime-200 bg-lime-50/70 p-5">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-lime-800">Sudah terbit</p>
                                <p class="mt-1 text-3xl font-black tracking-tight text-slate-950">{{ $publishedCount }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-lime-700 shadow-sm">{{ $publishedPercent }}%</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 bg-white sm:grid-cols-4">
                        @foreach ([
                            ['Draft', $draftCount, 'text-amber-700'],
                            ['Menunggu review', $reviewCount, 'text-sky-700'],
                            ['Perlu revisi', $rejectedCount, 'text-rose-700'],
                            ['Revisi ditinjau', $pendingRevisionCount, 'text-violet-700'],
                        ] as [$label, $count, $color])
                            <div class="border-b border-r border-slate-100 p-4 even:border-r-0 sm:border-b-0 sm:even:border-r sm:last:border-r-0">
                                <p class="text-2xl font-black tracking-tight text-slate-950">{{ $count }}</p>
                                <p class="mt-1 text-xs font-semibold leading-4 {{ $color }}">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </header>

        <div class="mt-5">
            @include('partials.alerts')
        </div>

        <div class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-6 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                    <div>
                        <p class="text-sm font-semibold text-lime-700">Artikel saya</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Kelola materi</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            {{ $articles->total() }} artikel ditemukan. Cek status dan lanjutkan pekerjaan dari sini.
                        </p>
                    </div>
                    <a href="{{ route('articles.create') }}" class="hidden items-center gap-2 text-sm font-bold text-slate-700 transition hover:text-lime-700 sm:inline-flex">
                        Artikel baru
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>

                <form method="GET" action="{{ route('author.dashboard') }}" class="border-b border-slate-100 bg-slate-50/60 px-5 py-5 sm:px-6">
                    <div class="grid gap-3 lg:grid-cols-[minmax(240px,1fr)_180px_210px_auto] lg:items-end">
                        <div>
                            <label for="author-search" class="mb-2 block text-xs font-bold text-slate-600">Cari artikel</label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                                    <path d="m16 16 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <input id="author-search" type="search" name="search" value="{{ request('search') }}" placeholder="Judul atau ringkasan" class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                            </div>
                        </div>
                        <div>
                            <label for="author-status" class="mb-2 block text-xs font-bold text-slate-600">Status</label>
                            <select id="author-status" name="status" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="">Semua status</option>
                                <option value="published" @selected(request('status') === 'published')>Terbit</option>
                                <option value="revision_review" @selected(request('status') === 'revision_review')>Revisi ditinjau</option>
                                <option value="revision_rejected" @selected(request('status') === 'revision_rejected')>Revisi ditolak</option>
                                <option value="review" @selected(request('status') === 'review')>Menunggu review</option>
                                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>Perlu diperbaiki</option>
                            </select>
                        </div>
                        <div>
                            <label for="author-category" class="mb-2 block text-xs font-bold text-slate-600">Kategori</label>
                            <select id="author-category" name="category_id" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="">Semua kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="h-11 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white transition hover:bg-lime-700">Terapkan</button>
                    </div>

                    <details class="group mt-3" @if ($hasAdvancedFilters) open @endif>
                        <summary class="flex cursor-pointer list-none items-center gap-2 py-1 text-sm font-semibold text-slate-600 hover:text-slate-950 [&::-webkit-details-marker]:hidden">
                            <svg class="h-4 w-4 transition group-open:rotate-90" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Filter lanjutan
                            @if ($hasAdvancedFilters)
                                <span class="rounded-full bg-lime-100 px-2 py-0.5 text-[11px] font-bold text-lime-800">aktif</span>
                            @endif
                        </summary>
                        <div class="mt-3 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
                            <div>
                                <label for="author-date-from" class="mb-2 block text-xs font-bold text-slate-600">Dari tanggal</label>
                                <input id="author-date-from" type="date" name="date_from" value="{{ request('date_from') }}" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                            </div>
                            <div>
                                <label for="author-date-to" class="mb-2 block text-xs font-bold text-slate-600">Sampai tanggal</label>
                                <input id="author-date-to" type="date" name="date_to" value="{{ request('date_to') }}" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                            </div>
                            <div>
                                <label for="author-sort" class="mb-2 block text-xs font-bold text-slate-600">Urutkan</label>
                                <select id="author-sort" name="sort" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>Terbaru diperbarui</option>
                                    <option value="oldest" @selected(request('sort') === 'oldest')>Paling lama</option>
                                    <option value="title" @selected(request('sort') === 'title')>Judul A-Z</option>
                                    <option value="views" @selected(request('sort') === 'views')>Paling banyak dilihat</option>
                                </select>
                            </div>
                            @if ($hasActiveFilters)
                                <a href="{{ route('author.dashboard') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 px-4 text-sm font-bold text-slate-600 transition hover:border-slate-400 hover:text-slate-950">Hapus filter</a>
                            @endif
                        </div>
                    </details>
                </form>

                @if ($articles->isNotEmpty())
                    <div class="hidden md:block">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[860px] text-left text-sm">
                                <thead class="border-b border-slate-100 bg-white text-xs font-bold text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">Artikel</th>
                                        <th class="px-4 py-4">Status</th>
                                        <th class="px-4 py-4">Performa</th>
                                        <th class="px-4 py-4">Diperbarui</th>
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($articles as $article)
                                        @php([$statusLabel, $statusClass, $statusDot] = $statusMeta($article))
                                        <tr class="align-top transition hover:bg-slate-50/70">
                                            <td class="px-6 py-5">
                                                <div class="max-w-sm">
                                                    <div class="mb-2 flex items-center gap-2">
                                                        <span class="text-xs font-bold text-lime-700">{{ $article->category->name }}</span>
                                                        <span class="text-slate-300">&middot;</span>
                                                        <span class="text-xs text-slate-400">{{ Str::limit($article->slug, 24) }}</span>
                                                    </div>
                                                    <p class="font-bold leading-5 text-slate-950">{{ $article->title }}</p>
                                                    <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-slate-500">{{ $article->summary }}</p>
                                                </div>
                                            </td>
                                            <td class="max-w-[240px] px-4 py-5">
                                                <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClass }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusDot }}"></span>
                                                    {{ $statusLabel }}
                                                </span>
                                                @if ($article->status === 'rejected' && $article->latestReview?->note)
                                                    <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs leading-5 text-rose-800">
                                                        <strong>Alasan revisi:</strong> {{ $article->latestReview->note }}
                                                    </div>
                                                @endif
                                                @if ($article->latestRevision && $article->latestRevision->status === 'rejected')
                                                    <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs leading-5 text-rose-800">
                                                        <strong>Revisi published ditolak:</strong> {{ $article->latestRevision->review_note }}
                                                    </div>
                                                @endif
                                                @if ($article->reviews->isNotEmpty())
                                                    <details class="mt-2 text-xs text-slate-500">
                                                        <summary class="cursor-pointer font-bold text-slate-600">Riwayat review ({{ $article->reviews->count() }})</summary>
                                                        <div class="mt-2 space-y-2 border-l-2 border-slate-200 pl-3">
                                                            @foreach ($article->reviews as $review)
                                                                <p><strong>{{ ucfirst($review->decision) }}</strong> oleh {{ $review->reviewer?->name ?? 'Admin' }} pada {{ $review->created_at->format('d M Y H:i') }}{{ $review->note ? ': '.$review->note : '' }}</p>
                                                            @endforeach
                                                        </div>
                                                    </details>
                                                @endif
                                            </td>
                                            <td class="px-4 py-5 text-xs leading-5 text-slate-500">
                                                <p><strong>{{ $article->views_count }}</strong> views</p>
                                                <p><strong>{{ $article->favorites_count }}</strong> favorit, <strong>{{ $article->collections_count }}</strong> koleksi</p>
                                                <p><strong>{{ $article->quiz_attempts_count }}</strong> quiz, avg {{ round((float) $article->quiz_attempts_avg_score) }}%</p>
                                            </td>
                                            <td class="px-4 py-5 text-xs text-slate-500">
                                                {{ $article->updated_at->format('d M Y') }}
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="flex max-w-[210px] flex-wrap justify-end gap-2">
                                                    @if (in_array($article->status, ['draft', 'rejected'], true))
                                                        <form action="{{ route('articles.submit-review', $article) }}" method="POST">
                                                            @csrf
                                                            <button class="rounded-lg bg-slate-950 px-3 py-2 text-xs font-bold text-white transition hover:bg-lime-700">
                                                                {{ $article->status === 'rejected' ? 'Kirim ulang' : 'Kirim review' }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if ($article->status === 'published')
                                                        <a href="{{ route('articles.show', $article->slug) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-lime-400 hover:text-lime-700">Lihat</a>
                                                    @endif
                                                    @if ($article->status !== 'review')
                                                        <a href="{{ route('articles.edit', $article) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                                                            {{ $article->status === 'published' ? 'Buat revisi' : 'Edit' }}
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('articles.destroy', $article) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="px-2 py-2 text-xs font-bold text-rose-600 transition hover:text-rose-800">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100 md:hidden">
                        @foreach ($articles as $article)
                            @php([$statusLabel, $statusClass, $statusDot] = $statusMeta($article))
                            <article class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-xs font-bold text-lime-700">{{ $article->category->name }}</span>
                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $statusClass }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $statusDot }}"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <h3 class="mt-3 text-base font-black leading-6 text-slate-950">{{ $article->title }}</h3>
                                <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500">{{ $article->summary }}</p>

                                @if ($article->status === 'rejected' && $article->latestReview?->note)
                                    <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs leading-5 text-rose-800">
                                        <strong>Alasan revisi:</strong> {{ $article->latestReview->note }}
                                    </div>
                                @endif
                                @if ($article->latestRevision && $article->latestRevision->status === 'rejected')
                                    <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs leading-5 text-rose-800">
                                        <strong>Revisi published ditolak:</strong> {{ $article->latestRevision->review_note }}
                                    </div>
                                @endif
                                @if ($article->reviews->isNotEmpty())
                                    <details class="mt-3 text-xs text-slate-500">
                                        <summary class="cursor-pointer font-bold text-slate-600">Riwayat review ({{ $article->reviews->count() }})</summary>
                                        <div class="mt-2 space-y-2 border-l-2 border-slate-200 pl-3">
                                            @foreach ($article->reviews as $review)
                                                <p><strong>{{ ucfirst($review->decision) }}</strong> oleh {{ $review->reviewer?->name ?? 'Admin' }} pada {{ $review->created_at->format('d M Y H:i') }}{{ $review->note ? ': '.$review->note : '' }}</p>
                                            @endforeach
                                        </div>
                                    </details>
                                @endif

                                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1 border-y border-slate-100 py-3 text-xs text-slate-500">
                                    <span><strong class="text-slate-800">{{ $article->views_count }}</strong> views</span>
                                    <span><strong class="text-slate-800">{{ $article->favorites_count }}</strong> favorit</span>
                                    <span><strong class="text-slate-800">{{ $article->quiz_attempts_count }}</strong> quiz</span>
                                    <span>Diperbarui {{ $article->updated_at->format('d M Y') }}</span>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    @if (in_array($article->status, ['draft', 'rejected'], true))
                                        <form action="{{ route('articles.submit-review', $article) }}" method="POST">
                                            @csrf
                                            <button class="rounded-lg bg-slate-950 px-3 py-2 text-xs font-bold text-white">
                                                {{ $article->status === 'rejected' ? 'Kirim ulang' : 'Kirim review' }}
                                            </button>
                                        </form>
                                    @endif
                                    @if ($article->status === 'published')
                                        <a href="{{ route('articles.show', $article->slug) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700">Lihat</a>
                                    @endif
                                    @if ($article->status !== 'review')
                                        <a href="{{ route('articles.edit', $article) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700">
                                            {{ $article->status === 'published' ? 'Buat revisi' : 'Edit' }}
                                        </a>
                                    @endif
                                    <form action="{{ route('articles.destroy', $article) }}" method="POST" class="ml-auto" onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-2 py-2 text-xs font-bold text-rose-600">Hapus</button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-14 text-center sm:px-8">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-lime-100 text-lime-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-black text-slate-950">
                            {{ $hasActiveFilters ? 'Belum menemukan artikel yang cocok' : 'Mulai dari artikel pertamamu' }}
                        </h3>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                            {{ $hasActiveFilters ? 'Coba ubah kata kunci atau hapus beberapa filter untuk melihat hasil lain.' : 'Bagikan satu keterampilan praktis yang bisa langsung dicoba oleh pembaca InnoBit.' }}
                        </p>
                        <a href="{{ $hasActiveFilters ? route('author.dashboard') : route('articles.create') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-lime-700">
                            {{ $hasActiveFilters ? 'Hapus semua filter' : 'Tulis artikel' }}
                        </a>
                    </div>
                @endif

                @if ($articles->hasPages())
                    <div class="border-t border-slate-100 px-5 py-5 sm:px-6">{{ $articles->links() }}</div>
                @endif
            </section>

            <aside class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-lime-700">30 hari terakhir</p>
                            <h2 class="mt-1 text-lg font-black text-slate-950">Respons pembaca</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $publishedPercent }}% terbit</span>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-2xl font-black tracking-tight text-slate-950">{{ $performance['views_30_days'] }}</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">artikel dilihat</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-2xl font-black tracking-tight text-slate-950">{{ $performance['quiz_attempts_30_days'] }}</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">quiz dikerjakan</p>
                        </div>
                    </div>
                    <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Total views</dt><dd class="font-bold text-slate-900">{{ $performance['views'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Disimpan sebagai favorit</dt><dd class="font-bold text-slate-900">{{ $performance['favorites'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Masuk koleksi</dt><dd class="font-bold text-slate-900">{{ $performance['collections'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Rata-rata nilai quiz</dt><dd class="font-bold text-slate-900">{{ $performance['average_quiz_score'] }}%</dd></div>
                    </dl>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-lg font-black text-slate-950">Top Artikel</h2>
                        <span class="text-xs font-semibold text-slate-400">berdasarkan views</span>
                    </div>
                    <div class="mt-4 space-y-1">
                        @forelse ($topArticles as $index => $topArticle)
                            <div class="flex gap-3 rounded-xl px-2 py-3 transition hover:bg-slate-50">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-black text-slate-600">{{ $index + 1 }}</span>
                                <div class="min-w-0">
                                    <p class="line-clamp-2 text-sm font-bold leading-5 text-slate-800">{{ $topArticle->title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $topArticle->views_count }} views &middot; {{ $topArticle->category->name }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-xl bg-slate-50 px-4 py-5 text-sm leading-6 text-slate-500">Data akan muncul setelah artikel mulai dibaca.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-lime-200 bg-lime-50/70 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-lime-800">Sebelum dikirim</p>
                    <h2 class="mt-2 text-lg font-black text-slate-950">Cek sebentar</h2>
                    <ul class="mt-4 space-y-3 text-sm leading-5 text-slate-600">
                        @foreach ([
                            'Tujuan belajar terlihat jelas dari judul.',
                            'Pembaca mendapat langkah yang bisa dipraktikkan.',
                            'Quiz sesuai dengan isi materi.',
                        ] as $item)
                            <li class="flex gap-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-lime-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
