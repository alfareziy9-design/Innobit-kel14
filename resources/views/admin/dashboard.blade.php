@extends('layouts.app')

@section('title', 'Panel Admin - InnoBit')

@section('content')
@php
    $publishedPercent = $articleCount > 0 ? round(($publishedCount / $articleCount) * 100) : 0;
    $hasActiveFilters = request()->filled('search') || request()->filled('status') || request()->filled('category_id') || request()->filled('author_id') || request()->filled('date_from') || request()->filled('date_to') || request()->filled('sort');
@endphp

<div class="min-h-[calc(100vh-140px)] bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-8 lg:py-10">
        <section class="mb-6 rounded-lg border border-slate-200 bg-slate-950 p-6 text-white shadow-sm md:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-lime-300">Admin workspace</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight md:text-5xl">Dashboard Admin</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/65">Halo, {{ auth()->user()->name }}. Kelola post harian, status publikasi, dan kategori InnoBit dari satu tempat.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('articles.create') }}" class="rounded-lg bg-lime-500 px-4 py-3 text-sm font-black text-slate-950 transition hover:bg-lime-400">Tambah Artikel</a>
                    <a href="{{ route('kategori.index') }}" class="rounded-lg border border-white/15 px-4 py-3 text-sm font-black text-white/85 transition hover:border-lime-300 hover:text-lime-200">Kelola Kategori</a>
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-white/15 px-4 py-3 text-sm font-black text-white/85 transition hover:border-lime-300 hover:text-lime-200">Kelola User</a>
                    <a href="{{ route('admin.messages.index') }}" class="rounded-lg border border-white/15 px-4 py-3 text-sm font-black text-white/85 transition hover:border-lime-300 hover:text-lime-200">Pesan ({{ $unreadMessageCount }})</a>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white/60">Total Artikel</p>
                    <p class="mt-2 text-3xl font-black">{{ $articleCount }}</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white/60">Published</p>
                    <p class="mt-2 text-3xl font-black text-lime-300">{{ $publishedCount }}</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white/60">Review Artikel</p>
                    <p class="mt-2 text-3xl font-black text-sky-200">{{ $reviewCount }}</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white/60">Rejected</p>
                    <p class="mt-2 text-3xl font-black text-red-200">{{ $rejectedCount }}</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white/60">User</p>
                    <p class="mt-2 text-3xl font-black">{{ $activeUserCount }}</p>
                    <p class="mt-1 text-xs text-white/50">{{ $userCount }} total</p>
                </div>
            </div>
            @if ($oldestReview)
                <p class="mt-5 rounded-lg border border-sky-200/20 bg-sky-300/10 px-4 py-3 text-sm text-sky-50">
                    Review tertua: <span class="font-bold">{{ $oldestReview->title }}</span>, masuk antrean {{ $oldestReview->updated_at->diffForHumans() }}.
                </p>
            @endif
            @if ($pendingRevisionCount > 0)
                <p class="mt-3 rounded-lg border border-amber-200/20 bg-amber-300/10 px-4 py-3 text-sm text-amber-50">
                    {{ $pendingRevisionCount }} revisi artikel published menunggu review.
                </p>
            @endif
        </section>

        @include('partials.alerts')

        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            <section class="min-w-0 rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 p-5 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-700">Content operations</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Daftar Artikel</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $articleCount }} artikel, {{ $publishedPercent }}% sudah published.</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('articles.create') }}" class="rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-lime-700">Tambah</a>
                        <a href="{{ route('kategori.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-lime-400 hover:text-lime-700">Kategori</a>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.dashboard') }}" class="border-b border-slate-200 bg-slate-50/70 p-5">
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_150px_180px_180px] xl:items-end">
                        <div>
                            <label for="admin-search" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Search</label>
                            <input id="admin-search" type="search" name="search" value="{{ request('search') }}" placeholder="Cari judul atau ringkasan..." class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        </div>

                        <div>
                            <label for="admin-status" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Status</label>
                            <select id="admin-status" name="status" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="">Semua Status</option>
                                <option value="published" @selected(request('status') === 'published')>Published</option>
                                <option value="review" @selected(request('status') === 'review')>Review</option>
                                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                            </select>
                        </div>

                        <div>
                            <label for="admin-category" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Kategori</label>
                            <select id="admin-category" name="category_id" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="admin-author" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Author</label>
                            <select id="admin-author" name="author_id" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="">Semua Author</option>
                                @foreach ($authors as $author)
                                    <option value="{{ $author->id }}" @selected((string) request('author_id') === (string) $author->id)>{{ $author->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-[180px_180px_180px_auto] xl:items-end">
                        <div>
                            <label for="admin-date-from" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Dari Tanggal</label>
                            <input id="admin-date-from" type="date" name="date_from" value="{{ request('date_from') }}" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        </div>
                        <div>
                            <label for="admin-date-to" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Sampai</label>
                            <input id="admin-date-to" type="date" name="date_to" value="{{ request('date_to') }}" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        </div>
                        <div>
                            <label for="admin-sort" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Urutan</label>
                            <select id="admin-sort" name="sort" class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                                <option value="newest" @selected(request('sort', 'newest') === 'newest')>Terbaru</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>Terlama</option>
                                <option value="title" @selected(request('sort') === 'title')>Judul A-Z</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="h-11 rounded-lg bg-slate-950 px-4 text-sm font-bold text-white transition hover:bg-lime-700">Filter</button>
                            @if ($hasActiveFilters)
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex h-11 items-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:border-lime-500 hover:text-lime-700">Reset</a>
                            @endif
                        </div>
                    </div>

                    @if ($hasActiveFilters)
                        <p class="mt-3 text-sm text-slate-500">Menampilkan {{ $articles->total() }} dari {{ $articleCount }} artikel.</p>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-4">Artikel</th>
                                <th class="px-5 py-4">Kategori</th>
                                <th class="px-5 py-4">Author</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Tanggal</th>
                                <th class="px-5 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($articles as $article)
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
                                    <td class="px-5 py-4 text-slate-600">{{ $article->author->name }}</td>
                                    <td class="px-5 py-4">
                                        @php
                                            $statusClass = match ($article->status) {
                                                'published' => 'bg-lime-100 text-lime-800',
                                                'review' => 'bg-sky-100 text-sky-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                default => 'bg-amber-100 text-amber-800',
                                            };
                                        @endphp
                                        <span class="rounded-full px-3 py-1 text-xs font-black {{ $statusClass }}">{{ ucfirst($article->status) }}</span>
                                        @if ($article->latestReview?->note)
                                            <p class="mt-2 max-w-xs text-xs leading-5 text-slate-500">{{ $article->latestReview->note }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-500">{{ $article->created_at->format('d M Y') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if ($article->status === 'review')
                                                <form action="{{ route('admin.articles.approve', $article) }}" method="POST" class="flex max-w-xs flex-wrap justify-end gap-2">
                                                    @csrf
                                                    <input name="note" type="text" maxlength="2000" placeholder="Catatan approve (opsional)" class="h-9 min-w-52 rounded-lg border border-slate-300 px-3 text-xs">
                                                    <button class="rounded-lg border border-lime-200 bg-lime-50 px-3 py-2 text-xs font-bold text-lime-800 transition hover:bg-lime-100">Approve</button>
                                                </form>
                                                <form action="{{ route('admin.articles.reject', $article) }}" method="POST" class="flex max-w-xs flex-wrap justify-end gap-2">
                                                    @csrf
                                                    <input name="note" type="text" required maxlength="2000" placeholder="Alasan revisi (wajib)" class="h-9 min-w-52 rounded-lg border border-red-200 px-3 text-xs">
                                                    <button class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100">Reject</button>
                                                </form>
                                            @endif
                                            @if ($article->status === 'published')
                                                <a href="{{ route('articles.show', $article->slug) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-lime-400 hover:text-lime-700">Lihat</a>
                                            @endif
                                            <a href="{{ route('articles.edit', $article) }}" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800 transition hover:bg-amber-100">Edit</a>
                                            <form action="{{ route('articles.destroy', $article) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                                        {{ $hasActiveFilters ? 'Tidak ada artikel yang cocok dengan filter.' : 'Belum ada artikel. Silakan tambahkan artikel pertama.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($articles->hasPages())
                    <div class="border-t border-slate-200 p-5">
                        {{ $articles->links() }}
                    </div>
                @endif
            </section>

            <aside class="space-y-6">
                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-black text-slate-950">Revisi Published</h2>
                        <span class="text-sm font-bold text-amber-700">{{ $pendingRevisionCount }}</span>
                    </div>
                    <div class="space-y-4">
                        @forelse ($pendingRevisions as $revision)
                            <article class="rounded-lg border border-amber-100 bg-amber-50/50 p-4">
                                <p class="text-sm font-black text-slate-900">{{ $revision->title }}</p>
                                <p class="mt-1 text-xs text-slate-500">Oleh {{ $revision->author->name }} &middot; {{ $revision->created_at->diffForHumans() }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <form action="{{ route('admin.articles.revisions.approve', [$revision->article, $revision]) }}" method="POST" class="flex flex-wrap gap-2">
                                        @csrf
                                        <input name="note" type="text" maxlength="2000" placeholder="Catatan approve" class="h-9 min-w-44 rounded-lg border border-slate-300 px-3 text-xs">
                                        <button class="rounded-lg border border-lime-200 bg-lime-50 px-3 py-2 text-xs font-bold text-lime-800">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.articles.revisions.reject', [$revision->article, $revision]) }}" method="POST" class="flex flex-wrap gap-2">
                                        @csrf
                                        <input name="note" type="text" required maxlength="2000" placeholder="Alasan revisi" class="h-9 min-w-44 rounded-lg border border-red-200 px-3 text-xs">
                                        <button class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700">Reject</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <p class="py-4 text-sm text-slate-500">Tidak ada revisi published yang menunggu review.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-black text-slate-950">Publishing Health</h2>
                        <span class="text-sm font-bold text-lime-700">{{ $publishedPercent }}%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full bg-lime-600" style="width: {{ $publishedPercent }}%"></div>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-lime-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-lime-800">Published</p>
                            <p class="mt-1 text-2xl font-black text-slate-950">{{ $publishedCount }}</p>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-sky-800">Review</p>
                            <p class="mt-1 text-2xl font-black text-slate-950">{{ $reviewCount }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-black text-slate-950">Pesan Terbaru</h2>
                            <p class="mt-1 text-xs text-slate-500">{{ $messageCount }} pesan tersimpan, {{ $unreadMessageCount }} belum dibaca.</p>
                        </div>
                        <a href="{{ route('admin.messages.index') }}" class="text-xs font-black text-lime-700 hover:text-lime-900">Buka Inbox</a>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @forelse ($recentMessages as $message)
                            <a href="{{ route('admin.messages.show', $message) }}" class="block py-3 first:pt-0 last:pb-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-800">{{ $message->name }}</p>
                                        <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $message->message }}</p>
                                    </div>
                                    <span class="shrink-0 text-[11px] font-bold text-slate-400">{{ $message->created_at->format('d M') }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="py-4 text-sm text-slate-500">Belum ada pesan kontak.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-black text-slate-950">Kategori</h2>
                        <span class="text-sm font-bold text-slate-500">{{ $categoryCount }} total</span>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @forelse ($recentCategories as $category)
                            <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                <div>
                                    <p class="font-bold text-slate-800">{{ $category->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $category->articles_count }} artikel</p>
                                </div>
                                <a href="{{ route('kategori.edit', $category) }}" class="rounded-lg px-3 py-2 text-xs font-bold text-lime-700 hover:bg-lime-50">Edit</a>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-slate-500">Belum ada kategori.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h2 class="text-lg font-black text-slate-950">Aktivitas Admin</h2>
                        <a href="{{ route('admin.activity.index') }}" class="text-xs font-black text-lime-700 hover:text-lime-900">Lihat Semua</a>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @forelse ($recentActivity as $log)
                            <div class="py-3 first:pt-0 last:pb-0">
                                <p class="text-sm font-bold text-slate-800">{{ str_replace('.', ' ', $log->action) }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $log->actor?->name ?? 'System' }} &middot; {{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-slate-500">Belum ada aktivitas admin.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-700">Quick checklist</p>
                    <h2 class="mt-2 text-lg font-black text-slate-950">Sebelum publish</h2>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-lime-600"></span>
                            <p>Judul jelas dan bisa dipahami cepat.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-lime-600"></span>
                            <p>Ringkasan menjelaskan manfaat belajar.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-lime-600"></span>
                            <p>Thumbnail bersih dan sesuai topik.</p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
