@extends('layouts.app')

@section('title', 'Panel Admin - InnoBit')

@section('content')
@php
    $publishedPercent = $articleCount > 0 ? round(($publishedCount / $articleCount) * 100) : 0;
    $hasActiveFilters = request()->filled('search') || request()->filled('status') || request()->filled('category_id') || request()->filled('author_id') || request()->filled('date_from') || request()->filled('date_to') || request()->filled('sort');
    $hasAdvancedFilters = request()->filled('date_from') || request()->filled('date_to') || request('sort', 'newest') !== 'newest';

@endphp

<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
        @include('admin._subnav')

        <header class="overflow-hidden rounded-2xl bg-slate-950 text-white shadow-sm">
            <div class="grid gap-8 px-5 py-7 sm:px-8 sm:py-9 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                <div class="max-w-3xl">
                    <div class="flex items-center gap-3 text-sm font-semibold text-lime-300">
                        <span class="h-px w-8 bg-lime-400"></span>
                        Pusat kontrol InnoBit
                    </div>
                    <h1 class="mt-4 text-3xl font-black tracking-[-0.03em] sm:text-4xl">Selamat bekerja, {{ auth()->user()->name }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/65 sm:text-base">
                        Pantau antrean editorial, pengguna, dan pesan yang perlu ditangani hari ini.
                    </p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <a href="{{ route('articles.create') }}" class="inline-flex items-center justify-center rounded-xl bg-lime-400 px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-lime-300">Tulis artikel</a>
                    <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/15 px-5 py-3 text-sm font-bold text-white/85 transition hover:border-white/30 hover:bg-white/5">
                        Pesan masuk
                        @if ($unreadMessageCount > 0)
                            <span class="rounded-full bg-white px-2 py-0.5 text-xs text-slate-950">{{ $unreadMessageCount }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <div class="border-t border-white/10 bg-white/[0.04] px-5 py-5 sm:px-8">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[1.2fr_1fr_1fr_1fr_1.2fr]">
                    <div class="rounded-xl border border-white/10 bg-white/[0.06] p-4">
                        <p class="text-sm text-white/55">Total artikel</p>
                        <div class="mt-2 flex items-end justify-between gap-3">
                            <p class="text-3xl font-black">{{ $articleCount }}</p>
                            <span class="text-xs font-semibold text-lime-300">{{ $publishedPercent }}% terbit</span>
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-sm text-white/55">Menunggu review</p>
                        <p class="mt-2 text-3xl font-black text-sky-200">{{ $reviewCount }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-sm text-white/55">Pembaruan perlu ditinjau</p>
                        <p class="mt-2 text-3xl font-black text-violet-200">{{ $pendingRevisionCount }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-sm text-white/55">Perlu perbaikan</p>
                        <p class="mt-2 text-3xl font-black text-rose-200">{{ $rejectedCount }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-sm text-white/55">Pengguna aktif</p>
                        <div class="mt-2 flex items-end justify-between gap-3">
                            <p class="text-3xl font-black">{{ $activeUserCount }}</p>
                            <span class="text-xs text-white/45">{{ $userCount }} akun</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        @if ($oldestReview || $pendingRevisionCount > 0)
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @if ($oldestReview)
                    <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-6 text-sky-900">
                        <span class="font-bold">Antrean terlama:</span> {{ $oldestReview->title }}, masuk {{ $oldestReview->updated_at->diffForHumans() }}.
                    </div>
                @endif
                @if ($pendingRevisionCount > 0)
                    <div class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm leading-6 text-violet-900">
                        <span class="font-bold">{{ $pendingRevisionCount }} pembaruan artikel Terbit</span> sedang menunggu keputusan.
                    </div>
                @endif
            </div>
        @endif

        <div class="mt-5">@include('partials.alerts')</div>

        <div class="mt-6 grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_310px]">
            <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-6 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                    <div>
                        <p class="text-sm font-semibold text-lime-700">Operasional konten</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Artikel dan approval</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-500">{{ $articleCount }} artikel dikelola, {{ $publishedCount }} sudah terbit.</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.dashboard') }}" class="border-b border-slate-100 bg-slate-50/60 px-5 py-5 sm:px-6">
                    <div class="grid gap-3 lg:grid-cols-[minmax(220px,1fr)_170px_190px_190px_auto] lg:items-end">
                        <div>
                            <label for="admin-search" class="mb-2 block text-xs font-bold text-slate-600">Cari artikel</label>
                            <input id="admin-search" type="search" name="search" value="{{ request('search') }}" placeholder="Judul atau ringkasan" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        </div>
                        <div>
                            <label for="admin-status" class="mb-2 block text-xs font-bold text-slate-600">Status</label>
                            <select id="admin-status" name="status" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-semibold outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="">Semua status</option>
                                <option value="published" @selected(request('status') === 'published')>Terbit</option>
                                <option value="revision_review" @selected(request('status') === 'revision_review')>Pembaruan menunggu review</option>
                                <option value="revision_rejected" @selected(request('status') === 'revision_rejected')>Pembaruan perlu perbaikan</option>
                                <option value="review" @selected(request('status') === 'review')>Menunggu review</option>
                                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>Perlu perbaikan</option>
                            </select>
                        </div>
                        <div>
                            <label for="admin-category" class="mb-2 block text-xs font-bold text-slate-600">Kategori</label>
                            <select id="admin-category" name="category_id" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-semibold outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="">Semua kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="admin-author" class="mb-2 block text-xs font-bold text-slate-600">Penulis</label>
                            <select id="admin-author" name="author_id" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-semibold outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="">Semua penulis</option>
                                @foreach ($authors as $author)
                                    <option value="{{ $author->id }}" @selected((string) request('author_id') === (string) $author->id)>{{ $author->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="h-11 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white transition hover:bg-lime-700">Terapkan</button>
                    </div>

                    <details class="group mt-3" @if ($hasAdvancedFilters) open @endif>
                        <summary class="flex cursor-pointer list-none items-center gap-2 py-1 text-sm font-semibold text-slate-600 [&::-webkit-details-marker]:hidden">
                            <span class="transition group-open:rotate-90">&rsaquo;</span>
                            Filter lanjutan
                            @if ($hasAdvancedFilters)<span class="rounded-full bg-lime-100 px-2 py-0.5 text-[11px] text-lime-800">aktif</span>@endif
                        </summary>
                        <div class="mt-3 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
                            <div>
                                <label for="admin-date-from" class="mb-2 block text-xs font-bold text-slate-600">Dari tanggal</label>
                                <input id="admin-date-from" type="date" name="date_from" value="{{ request('date_from') }}" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                            </div>
                            <div>
                                <label for="admin-date-to" class="mb-2 block text-xs font-bold text-slate-600">Sampai tanggal</label>
                                <input id="admin-date-to" type="date" name="date_to" value="{{ request('date_to') }}" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                            </div>
                            <div>
                                <label for="admin-sort" class="mb-2 block text-xs font-bold text-slate-600">Urutkan</label>
                                <select id="admin-sort" name="sort" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm font-semibold outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>Terbaru</option>
                                    <option value="oldest" @selected(request('sort') === 'oldest')>Paling lama</option>
                                    <option value="title" @selected(request('sort') === 'title')>Judul A-Z</option>
                                </select>
                            </div>
                            @if ($hasActiveFilters)
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 px-4 text-sm font-bold text-slate-600">Hapus filter</a>
                            @endif
                        </div>
                    </details>

                    @if ($hasActiveFilters)
                        <p class="mt-3 text-sm text-slate-500">Menampilkan {{ $articles->total() }} dari {{ $articleCount }} artikel.</p>
                    @endif
                </form>

                @if ($articles->isNotEmpty())
                    <div class="hidden md:block">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[900px] text-left text-sm">
                                <thead class="border-b border-slate-100 text-xs font-bold text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">Artikel</th>
                                        <th class="px-4 py-4">Penulis</th>
                                        <th class="px-4 py-4">Status</th>
                                        <th class="px-4 py-4">Masuk</th>
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($articles as $article)
                                        @php($publicationStatus = $article->statusMeta())
                                        @php($revisionStatus = $article->revisionStatusMeta())
                                        <tr class="align-top transition hover:bg-slate-50/70">
                                            <td class="px-6 py-5">
                                                <div class="max-w-sm">
                                                    <span class="text-xs font-bold text-lime-700">{{ $article->category->name }}</span>
                                                    <p class="mt-2 font-bold leading-5 text-slate-950">{{ $article->title }}</p>
                                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $article->summary }}</p>
                                                </div>
                                            </td>
                                            <td class="px-4 py-5">
                                                <p class="font-semibold text-slate-800">{{ $article->author->name }}</p>
                                            </td>
                                            <td class="max-w-[220px] px-4 py-5">
                                                <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-xs font-bold {{ $publicationStatus['class'] }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $publicationStatus['dot'] }}"></span>{{ $publicationStatus['label'] }}
                                                </span>
                                                @if ($revisionStatus)
                                                    <span class="mt-2 inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-xs font-bold {{ $revisionStatus['class'] }}">
                                                        <span class="h-1.5 w-1.5 rounded-full {{ $revisionStatus['dot'] }}"></span>{{ $revisionStatus['label'] }}
                                                    </span>
                                                @endif
                                                @if ($article->latestReview?->note)
                                                    <p class="mt-2 text-xs leading-5 text-slate-500">{{ $article->latestReview->note }}</p>
                                                @endif
                                            </td>
                                            <td class="px-4 py-5 text-xs text-slate-500">{{ $article->created_at->format('d M Y') }}</td>
                                            <td class="px-6 py-5">
                                                <div class="flex flex-wrap justify-end gap-2">
                                                    @if ($article->status === 'review')
                                                        <a href="{{ route('admin.articles.review', $article) }}" class="rounded-lg bg-slate-950 px-3 py-2 text-xs font-bold text-white">Tinjau</a>
                                                    @endif
                                                    @if ($article->pendingRevision)
                                                        <a href="{{ route('admin.articles.revisions.review', [$article, $article->pendingRevision]) }}" class="rounded-lg bg-violet-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-violet-800">Tinjau Pembaruan</a>
                                                    @endif
                                                    @if ($article->status === 'published')
                                                        <a href="{{ route('articles.show', $article->slug) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700">Lihat</a>
                                                    @endif
                                                    @if ($article->author_id === auth()->id())
                                                        <a href="{{ route('articles.edit', $article) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700">Edit</a>
                                                    @endif
                                                    <form action="{{ route('articles.destroy', $article) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                    </div>

                    <div class="divide-y divide-slate-100 md:hidden">
                        @foreach ($articles as $article)
                            @php($publicationStatus = $article->statusMeta())
                            @php($revisionStatus = $article->revisionStatusMeta())
                            <article class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-xs font-bold text-lime-700">{{ $article->category->name }}</span>
                                    <div class="flex flex-col items-end gap-1.5">
                                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $publicationStatus['class'] }}"><span class="h-1.5 w-1.5 rounded-full {{ $publicationStatus['dot'] }}"></span>{{ $publicationStatus['label'] }}</span>
                                        @if ($revisionStatus)
                                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $revisionStatus['class'] }}"><span class="h-1.5 w-1.5 rounded-full {{ $revisionStatus['dot'] }}"></span>{{ $revisionStatus['label'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                <h3 class="mt-3 font-black leading-6 text-slate-950">{{ $article->title }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $article->author->name }} &middot; {{ $article->created_at->format('d M Y') }}</p>
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $article->summary }}</p>
                                @if ($article->latestReview?->note)<p class="mt-3 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-600">{{ $article->latestReview->note }}</p>@endif
                                @if ($article->status === 'review')
                                    <a href="{{ route('admin.articles.review', $article) }}" class="mt-4 inline-flex rounded-lg bg-slate-950 px-3 py-2 text-xs font-bold text-white">Tinjau artikel</a>
                                @endif
                                <div class="mt-4 flex items-center gap-2">
                                    @if ($article->pendingRevision)<a href="{{ route('admin.articles.revisions.review', [$article, $article->pendingRevision]) }}" class="rounded-lg bg-violet-700 px-3 py-2 text-xs font-bold text-white">Tinjau Pembaruan</a>@endif
                                    @if ($article->status === 'published')<a href="{{ route('articles.show', $article->slug) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold">Lihat</a>@endif
                                    @if ($article->author_id === auth()->id())<a href="{{ route('articles.edit', $article) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold">Edit</a>@endif
                                    <form action="{{ route('articles.destroy', $article) }}" method="POST" class="ml-auto" onsubmit="return confirm('Yakin ingin menghapus data ini?')">@csrf @method('DELETE')<button class="px-2 py-2 text-xs font-bold text-rose-600">Hapus</button></form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-14 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-lime-100 text-xl font-black text-lime-700">+</div>
                        <h3 class="mt-4 text-lg font-black text-slate-950">{{ $hasActiveFilters ? 'Tidak ada artikel yang cocok' : 'Belum ada artikel' }}</h3>
                        <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">{{ $hasActiveFilters ? 'Ubah pencarian atau hapus filter untuk melihat hasil lain.' : 'Tambahkan artikel pertama untuk mulai mengisi pustaka belajar InnoBit.' }}</p>
                        <a href="{{ $hasActiveFilters ? route('admin.dashboard') : route('articles.create') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white">{{ $hasActiveFilters ? 'Hapus filter' : 'Tambah artikel' }}</a>
                    </div>
                @endif

                @if ($articles->hasPages())<div class="border-t border-slate-100 p-5">{{ $articles->links() }}</div>@endif
            </section>

            <aside class="space-y-5">
                <section class="rounded-2xl border border-violet-200 bg-violet-50/60 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-violet-700">Antrean editorial</p>
                            <h2 class="mt-1 text-lg font-black text-slate-950">Pembaruan artikel Terbit</h2>
                        </div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-black text-violet-700">{{ $pendingRevisionCount }}</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($pendingRevisions as $revision)
                            <article class="rounded-xl border border-violet-100 bg-white p-4">
                                <p class="text-sm font-bold leading-5 text-slate-900">{{ $revision->title }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $revision->author->name }} &middot; {{ $revision->created_at->diffForHumans() }}</p>
                                <a href="{{ route('admin.articles.revisions.review', [$revision->article, $revision]) }}" class="mt-3 inline-flex rounded-lg bg-slate-950 px-3 py-2 text-xs font-bold text-white">Tinjau pembaruan</a>
                            </article>
                        @empty
                            <p class="rounded-xl bg-white/70 p-4 text-sm leading-6 text-slate-500">Tidak ada pembaruan artikel Terbit yang menunggu review.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-black text-slate-950">Kesehatan publikasi</h2>
                        <span class="text-sm font-black text-lime-700">{{ $publishedPercent }}%</span>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-lime-500" style="width: {{ $publishedPercent }}%"></div></div>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl bg-slate-50 p-3"><p class="text-xl font-black">{{ $publishedCount }}</p><p class="text-[11px] text-slate-500">Terbit</p></div>
                        <div class="rounded-xl bg-slate-50 p-3"><p class="text-xl font-black">{{ $draftCount }}</p><p class="text-[11px] text-slate-500">Draft</p></div>
                        <div class="rounded-xl bg-slate-50 p-3"><p class="text-xl font-black">{{ $reviewCount }}</p><p class="text-[11px] text-slate-500">Review</p></div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div><h2 class="text-lg font-black text-slate-950">Pesan terbaru</h2><p class="mt-1 text-xs text-slate-500">{{ $messageCount }} pesan tersimpan, {{ $unreadMessageCount }} belum dibaca.</p></div>
                        <a href="{{ route('admin.messages.index') }}" class="text-xs font-bold text-lime-700">Buka inbox</a>
                    </div>
                    <div class="mt-4 divide-y divide-slate-100">
                        @forelse ($recentMessages as $message)
                            <a href="{{ route('admin.messages.show', $message) }}" class="block py-3 first:pt-0 last:pb-0">
                                <div class="flex justify-between gap-3"><p class="truncate text-sm font-bold text-slate-800">{{ $message->name }}</p><span class="shrink-0 text-[11px] text-slate-400">{{ $message->created_at->format('d M') }}</span></div>
                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $message->latestConversationMessage?->message ?? $message->message }}</p>
                            </a>
                        @empty
                            <p class="py-3 text-sm text-slate-500">Belum ada pesan kontak.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><h2 class="text-lg font-black text-slate-950">Kategori</h2><a href="{{ route('kategori.index') }}" class="text-xs font-bold text-lime-700">{{ $categoryCount }} total</a></div>
                    <div class="mt-3 divide-y divide-slate-100">
                        @forelse ($recentCategories as $category)
                            <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0"><div><p class="text-sm font-bold text-slate-800">{{ $category->name }}</p><p class="text-xs text-slate-500">{{ $category->articles_count }} artikel</p></div><a href="{{ route('kategori.edit', $category) }}" class="text-xs font-bold text-lime-700">Edit</a></div>
                        @empty
                            <p class="py-3 text-sm text-slate-500">Belum ada kategori.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><h2 class="text-lg font-black text-slate-950">Aktivitas admin</h2><a href="{{ route('admin.activity.index') }}" class="text-xs font-bold text-lime-700">Lihat semua</a></div>
                    <div class="mt-3 divide-y divide-slate-100">
                        @forelse ($recentActivity as $log)
                            <div class="py-3 first:pt-0 last:pb-0"><p class="text-sm font-bold text-slate-800">{{ str_replace('.', ' ', $log->action) }}</p><p class="mt-1 text-xs text-slate-500">{{ $log->actor?->name ?? 'System' }} &middot; {{ $log->created_at->diffForHumans() }}</p></div>
                        @empty
                            <p class="py-3 text-sm text-slate-500">Belum ada aktivitas admin.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection
