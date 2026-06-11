@extends('layouts.app')

@section('title', 'Dashboard Penulis - InnoBit')

@section('content')
@php
    $publishedPercent = $articleCount > 0 ? round(($publishedCount / $articleCount) * 100) : 0;
    $hasActiveFilters = request()->filled('search') || request()->filled('status') || request()->filled('category_id') || request()->filled('date_from') || request()->filled('date_to') || request()->filled('sort');
@endphp

<div class="min-h-[calc(100vh-140px)] bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-8 lg:py-10">
        <section class="mb-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-lime-700">Author workspace</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 md:text-5xl">Dashboard Penulis</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Halo, {{ auth()->user()->name }}. Kelola draft, revisi, dan performa microlearning dari satu tempat.</p>
                </div>

                <a href="{{ route('articles.create') }}" class="rounded-lg bg-lime-600 px-4 py-3 text-sm font-black text-white transition hover:bg-lime-700">Tambah Artikel</a>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm text-slate-500">Artikel Saya</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $articleCount }}</p>
                </div>
                <div class="rounded-lg border border-lime-100 bg-lime-50 p-4">
                    <p class="text-sm text-lime-800">Published</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $publishedCount }}</p>
                </div>
                <div class="rounded-lg border border-amber-100 bg-amber-50 p-4">
                    <p class="text-sm text-amber-800">Draft</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $draftCount }}</p>
                </div>
                <div class="rounded-lg border border-sky-100 bg-sky-50 p-4">
                    <p class="text-sm text-sky-800">Review</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $reviewCount }}</p>
                </div>
                <div class="rounded-lg border border-red-100 bg-red-50 p-4">
                    <p class="text-sm text-red-800">Rejected</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $rejectedCount }}</p>
                </div>
                <div class="rounded-lg border border-violet-100 bg-violet-50 p-4">
                    <p class="text-sm text-violet-800">Revisi Review</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $pendingRevisionCount }}</p>
                </div>
            </div>
        </section>

        @include('partials.alerts')

        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            <section class="min-w-0 rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 p-5 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-700">Artikel saya</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Draft, Review, dan Publikasi</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $articles->total() }} artikel ditemukan, {{ $publishedPercent }}% sudah published.</p>
                    </div>
                    <a href="{{ route('articles.create') }}" class="rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-lime-700">Tambah</a>
                </div>

                <form method="GET" action="{{ route('author.dashboard') }}" class="border-b border-slate-200 bg-slate-50/70 p-5">
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_170px_200px] xl:items-end">
                        <div>
                            <label for="author-search" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Search</label>
                            <input id="author-search" type="search" name="search" value="{{ request('search') }}" placeholder="Cari judul atau ringkasan..." class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        </div>
                        <div>
                            <label for="author-status" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Status</label>
                            <select id="author-status" name="status" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="">Semua Status</option>
                                <option value="published" @selected(request('status') === 'published')>Published</option>
                                <option value="revision_review" @selected(request('status') === 'revision_review')>Revisi Review</option>
                                <option value="revision_rejected" @selected(request('status') === 'revision_rejected')>Revisi Rejected</option>
                                <option value="review" @selected(request('status') === 'review')>Review</option>
                                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label for="author-category" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Kategori</label>
                            <select id="author-category" name="category_id" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-[170px_170px_170px_auto] xl:items-end">
                        <div>
                            <label for="author-date-from" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Dari Tanggal</label>
                            <input id="author-date-from" type="date" name="date_from" value="{{ request('date_from') }}" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        </div>
                        <div>
                            <label for="author-date-to" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Sampai</label>
                            <input id="author-date-to" type="date" name="date_to" value="{{ request('date_to') }}" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        </div>
                        <div>
                            <label for="author-sort" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Urutan</label>
                            <select id="author-sort" name="sort" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="newest" @selected(request('sort', 'newest') === 'newest')>Terbaru</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>Terlama</option>
                                <option value="title" @selected(request('sort') === 'title')>Judul A-Z</option>
                                <option value="views" @selected(request('sort') === 'views')>Views</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="h-11 rounded-lg bg-slate-950 px-4 text-sm font-bold text-white transition hover:bg-lime-700">Filter</button>
                            @if ($hasActiveFilters)
                                <a href="{{ route('author.dashboard') }}" class="inline-flex h-11 items-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:border-lime-500 hover:text-lime-700">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[940px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-4">Artikel</th>
                                <th class="px-5 py-4">Kategori</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Performa</th>
                                <th class="px-5 py-4">Update</th>
                                <th class="px-5 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($articles as $article)
                                @php
                                    $statusLabel = ucfirst($article->status);
                                    $statusClass = match ($article->status) {
                                        'published' => 'bg-lime-100 text-lime-800',
                                        'review' => 'bg-sky-100 text-sky-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        default => 'bg-amber-100 text-amber-800',
                                    };

                                    if ($article->pendingRevision) {
                                        $statusLabel = 'Published + Revisi Review';
                                        $statusClass = 'bg-violet-100 text-violet-800';
                                    }
                                @endphp
                                <tr class="align-top transition hover:bg-slate-50">
                                    <td class="px-5 py-4">
                                        <div class="max-w-md">
                                            <p class="font-black leading-5 text-slate-950">{{ $article->title }}</p>
                                            <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $article->summary }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full bg-lime-50 px-3 py-1 text-xs font-bold text-lime-800">{{ $article->category->name }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusClass }}">{{ $statusLabel }}</span>
                                        @if ($article->status === 'rejected' && $article->latestReview?->note)
                                            <div class="mt-3 max-w-sm rounded-lg border border-red-200 bg-red-50 p-3 text-xs leading-5 text-red-800">
                                                <strong>Alasan revisi:</strong> {{ $article->latestReview->note }}
                                            </div>
                                        @endif
                                        @if ($article->latestRevision && $article->latestRevision->status === 'rejected')
                                            <div class="mt-3 max-w-sm rounded-lg border border-red-200 bg-red-50 p-3 text-xs leading-5 text-red-800">
                                                <strong>Revisi published ditolak:</strong> {{ $article->latestRevision->review_note }}
                                            </div>
                                        @endif
                                        @if ($article->reviews->isNotEmpty())
                                            <details class="mt-2 max-w-sm text-xs text-slate-500">
                                                <summary class="cursor-pointer font-bold text-slate-600">Riwayat review ({{ $article->reviews->count() }})</summary>
                                                <div class="mt-2 space-y-2">
                                                    @foreach ($article->reviews as $review)
                                                        <p><strong>{{ ucfirst($review->decision) }}</strong> oleh {{ $review->reviewer?->name ?? 'Admin' }} pada {{ $review->created_at->format('d M Y H:i') }}{{ $review->note ? ': '.$review->note : '' }}</p>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-xs leading-5 text-slate-600">
                                        <p><strong>{{ $article->views_count }}</strong> views</p>
                                        <p><strong>{{ $article->favorites_count }}</strong> favorit, <strong>{{ $article->collections_count }}</strong> koleksi</p>
                                        <p><strong>{{ $article->quiz_attempts_count }}</strong> quiz, avg {{ round((float) $article->quiz_attempts_avg_score) }}%</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-500">{{ $article->updated_at->format('d M Y') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if (in_array($article->status, ['draft', 'rejected'], true))
                                                <form action="{{ route('articles.submit-review', $article) }}" method="POST">
                                                    @csrf
                                                    <button class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-bold text-sky-800 transition hover:bg-sky-100">
                                                        {{ $article->status === 'rejected' ? 'Kirim Ulang' : 'Kirim Review' }}
                                                    </button>
                                                </form>
                                            @endif
                                            @if ($article->status === 'published')
                                                <a href="{{ route('articles.show', $article->slug) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-lime-400 hover:text-lime-700">Lihat</a>
                                            @endif
                                            @if ($article->status !== 'review')
                                                <a href="{{ route('articles.edit', $article) }}" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800 transition hover:bg-amber-100">
                                                    {{ $article->status === 'published' ? 'Buat Revisi' : 'Edit' }}
                                                </a>
                                            @endif
                                            <form action="{{ route('articles.destroy', $article) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                        {{ $hasActiveFilters ? 'Tidak ada artikel yang cocok dengan filter.' : 'Belum ada artikel. Mulai tulis materi pertamamu.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($articles->hasPages())
                    <div class="border-t border-slate-200 p-5">{{ $articles->links() }}</div>
                @endif
            </section>

            <aside class="space-y-6">
                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-black text-slate-950">Performa 30 Hari</h2>
                        <span class="text-sm font-bold text-lime-700">{{ $publishedPercent }}%</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-lime-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-lime-800">Views</p>
                            <p class="mt-1 text-2xl font-black text-slate-950">{{ $performance['views_30_days'] }}</p>
                        </div>
                        <div class="rounded-lg bg-sky-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-sky-800">Quiz</p>
                            <p class="mt-1 text-2xl font-black text-slate-950">{{ $performance['quiz_attempts_30_days'] }}</p>
                        </div>
                    </div>
                    <dl class="mt-4 space-y-2 text-sm text-slate-600">
                        <div class="flex justify-between gap-4"><dt>Total views</dt><dd class="font-bold">{{ $performance['views'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt>Favorit</dt><dd class="font-bold">{{ $performance['favorites'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt>Koleksi</dt><dd class="font-bold">{{ $performance['collections'] }}</dd></div>
                        <div class="flex justify-between gap-4"><dt>Rata-rata quiz</dt><dd class="font-bold">{{ $performance['average_quiz_score'] }}%</dd></div>
                    </dl>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-black text-slate-950">Top Artikel</h2>
                    <div class="mt-4 divide-y divide-slate-200">
                        @forelse ($topArticles as $topArticle)
                            <div class="py-3 first:pt-0 last:pb-0">
                                <p class="font-bold leading-5 text-slate-800">{{ $topArticle->title }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $topArticle->views_count }} views &middot; {{ $topArticle->category->name }}</p>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-slate-500">Belum ada data performa.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-700">Checklist penulis</p>
                    <h2 class="mt-2 text-lg font-black text-slate-950">Sebelum kirim draft</h2>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-lime-600"></span>
                            <p>Judul menyebutkan hasil belajar yang jelas.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-lime-600"></span>
                            <p>Ringkasan cukup singkat untuk dibaca cepat.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-lime-600"></span>
                            <p>Isi artikel punya langkah praktik yang bisa dicoba.</p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
