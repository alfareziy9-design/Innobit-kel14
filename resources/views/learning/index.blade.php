@extends('layouts.app')

@section('title', $title.' - InnoBit')

@section('content')
@php
    $fallbackImages = [
        asset('assets/img/microlearning-data-dashboard.png'),
        asset('assets/img/microlearning-clean-code.png'),
        asset('assets/img/microlearning-time-focus.png'),
    ];
@endphp

<div class="min-h-screen bg-lime-50/45 text-slate-950">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.18em] text-lime-800">Menu Belajar</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight">{{ $title }}</h1>
                <p class="mt-2 text-base text-slate-600">{{ $subtitle }}</p>
            </div>
            <a href="{{ route('home') }}" class="rounded-lg border border-lime-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:border-lime-600 hover:text-lime-800">Kembali</a>
        </div>

        <nav class="mb-8 grid gap-2 text-sm font-bold sm:grid-cols-3">
            <a href="{{ route('learning.history') }}" class="rounded-lg border px-4 py-3 text-center {{ request()->routeIs('learning.history') ? 'border-lime-600 bg-lime-100 text-lime-900' : 'border-lime-200 bg-white text-slate-700' }}">Histori</a>
            <a href="{{ route('learning.favorites') }}" class="rounded-lg border px-4 py-3 text-center {{ request()->routeIs('learning.favorites') ? 'border-lime-600 bg-lime-100 text-lime-900' : 'border-lime-200 bg-white text-slate-700' }}">Favorit</a>
            <a href="{{ route('learning.collections') }}" class="rounded-lg border px-4 py-3 text-center {{ request()->routeIs('learning.collections') ? 'border-lime-600 bg-lime-100 text-lime-900' : 'border-lime-200 bg-white text-slate-700' }}">Koleksi</a>
        </nav>

        @if (isset($collectionGroups))
            <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                <section class="aspect-square rounded-lg border border-dashed border-lime-400 bg-white p-4 shadow-sm">
                    <div class="flex h-full flex-col justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.14em] text-lime-800">Album baru</p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Buat koleksi baru</h2>
                            <p class="mt-2 text-xs leading-5 text-slate-600">Beri nama album sesuai topik belajar.</p>
                        </div>
                        <button type="button" data-modal-open="create-collection-modal" class="rounded-lg bg-lime-700 px-4 py-3 text-sm font-black text-white transition hover:bg-lime-800">Buat</button>
                    </div>
                </section>
                @foreach ($collectionGroups as $group)
                    @php
                        $albumArticles = $group['articles'];
                        $coverImages = $albumArticles
                            ->take(3)
                            ->values()
                            ->map(fn ($article, $index) => $article->thumbnailMedia?->url ?? $fallbackImages[$index % count($fallbackImages)]);
                    @endphp

                    <section class="group relative aspect-square overflow-hidden rounded-lg border border-lime-200 bg-lime-100 shadow-sm transition hover:-translate-y-0.5 hover:border-lime-500 hover:shadow-md">
                        @forelse ($coverImages as $coverImage)
                            <img
                                src="{{ $coverImage }}"
                                alt="{{ $group['name'] }}"
                                class="absolute h-[62%] w-[54%] rounded-lg object-cover shadow-md transition duration-300 group-hover:scale-[1.02]
                                    {{ $loop->first ? 'left-[10%] top-[16%] z-30 rotate-[-4deg]' : '' }}
                                    {{ $loop->iteration === 2 ? 'left-[31%] top-[11%] z-20 rotate-[5deg]' : '' }}
                                    {{ $loop->iteration === 3 ? 'left-[45%] top-[20%] z-10 rotate-[10deg]' : '' }}"
                            >
                        @empty
                            <div class="absolute inset-6 flex items-center justify-center rounded-lg border-2 border-dashed border-lime-300 bg-white/60 text-center">
                                <div>
                                    <p class="text-sm font-black uppercase tracking-[0.14em] text-lime-800">Kosong</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-600">Album siap diisi</p>
                                </div>
                            </div>
                        @endforelse

                        <div class="absolute inset-x-0 bottom-0 z-40 bg-gradient-to-t from-slate-950/85 via-slate-950/60 to-transparent p-3 text-white">
                            <div class="flex items-end justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-black uppercase tracking-[0.14em] text-lime-100">Album Koleksi</p>
                                    <h2 class="mt-1 line-clamp-2 text-xl font-black leading-tight">{{ $group['name'] }}</h2>
                                </div>
                                <p class="shrink-0 rounded-full bg-white/90 px-2.5 py-1 text-xs font-black text-lime-900">{{ $albumArticles->count() }}</p>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-2">
                                <button type="button" data-modal-open="rename-collection-{{ $group['id'] }}" class="rounded-lg bg-white/90 px-3 py-2 text-xs font-black text-lime-900 transition hover:bg-lime-100">Rename</button>
                                <form action="{{ route('learning.collections.destroy', $group['id']) }}" method="POST" onsubmit="return confirm('Hapus koleksi ini? Artikel di dalamnya akan keluar dari koleksi.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-600 px-3 py-2 text-xs font-black text-white transition hover:bg-red-700">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </section>

                    <div id="rename-collection-{{ $group['id'] }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4 py-6" data-modal>
                        <div class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.14em] text-lime-800">Rename koleksi</p>
                                    <h2 class="mt-1 text-2xl font-black text-slate-900">{{ $group['name'] }}</h2>
                                </div>
                                <button type="button" data-modal-close class="rounded-lg border border-lime-200 px-3 py-2 text-sm font-black text-slate-600 hover:border-lime-500">Tutup</button>
                            </div>
                            <form action="{{ route('learning.collections.update', $group['id']) }}" method="POST" class="mt-5 grid gap-3">
                                @csrf
                                @method('PUT')
                                <label class="text-sm font-black text-slate-700" for="collection-name-{{ $group['id'] }}">Nama koleksi</label>
                                <input
                                    id="collection-name-{{ $group['id'] }}"
                                    name="name"
                                    type="text"
                                    value="{{ $group['name'] }}"
                                    maxlength="80"
                                    class="min-h-12 rounded-lg border border-lime-200 bg-lime-50/45 px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-lime-600 focus:bg-white focus:ring-4 focus:ring-lime-100"
                                    required
                                >
                                <button type="submit" class="rounded-lg bg-lime-700 px-4 py-3 text-sm font-black text-white transition hover:bg-lime-800">Simpan nama</button>
                            </form>
                        </div>
                    </div>
                @endforeach

                @if ($collectionGroups->isEmpty())
                    <div class="rounded-lg border border-dashed border-lime-300 bg-white p-12 text-center sm:col-span-2 lg:col-span-3">
                        <h2 class="text-xl font-black text-slate-800">{{ $emptyTitle }}</h2>
                        <p class="mt-2 text-slate-600">{{ $emptyMessage }}</p>
                    </div>
                @endif
            </div>

            <div id="create-collection-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4 py-6" data-modal>
                <div class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.14em] text-lime-800">Album baru</p>
                            <h2 class="mt-1 text-2xl font-black text-slate-900">Buat koleksi baru</h2>
                        </div>
                        <button type="button" data-modal-close class="rounded-lg border border-lime-200 px-3 py-2 text-sm font-black text-slate-600 hover:border-lime-500">Tutup</button>
                    </div>
                    <form action="{{ route('learning.collections.store') }}" method="POST" class="mt-5 grid gap-3">
                        @csrf
                        <label class="text-sm font-black text-slate-700" for="collection-name">Nama koleksi</label>
                        <input
                            id="collection-name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            maxlength="80"
                            placeholder="Contoh: Laravel Dasar"
                            class="min-h-12 rounded-lg border border-lime-200 bg-lime-50/45 px-4 text-sm font-bold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-lime-600 focus:bg-white focus:ring-4 focus:ring-lime-100"
                            required
                        >
                        @error('name')
                            <p class="text-sm font-bold text-red-600">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="rounded-lg bg-lime-700 px-4 py-3 text-sm font-black text-white transition hover:bg-lime-800">Buat</button>
                    </form>
                </div>
            </div>

            <script>
                document.querySelectorAll('[data-modal-open]').forEach((button) => {
                    button.addEventListener('click', () => {
                        document.getElementById(button.dataset.modalOpen)?.classList.remove('hidden');
                        document.getElementById(button.dataset.modalOpen)?.classList.add('flex');
                    });
                });

                document.querySelectorAll('[data-modal]').forEach((modal) => {
                    modal.querySelectorAll('[data-modal-close]').forEach((button) => {
                        button.addEventListener('click', () => {
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        });
                    });

                    modal.addEventListener('click', (event) => {
                        if (event.target === modal) {
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        }
                    });
                });
            </script>
        @elseif ($articles->count())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    @php
                        $image = $article->thumbnailMedia?->url
                            ?? $fallbackImages[($loop->iteration - 1) % count($fallbackImages)];
                        $meta = $articleMeta->get($article->id);
                        $metaDate = $meta ? $meta->{$metaKey} : null;
                    @endphp

                    <a href="{{ route('articles.show', $article->slug) }}" class="group overflow-hidden rounded-lg border border-lime-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-lime-500 hover:shadow-md">
                        <img src="{{ $image }}" alt="{{ $article->title }}" class="h-44 w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                        <div class="p-4">
                            <p class="text-xs font-black uppercase tracking-[0.14em] text-lime-800">{{ $article->category->name }}</p>
                            <h2 class="mt-2 line-clamp-2 text-xl font-black leading-tight group-hover:text-lime-800">{{ $article->title }}</h2>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $article->summary }}</p>
                            @if ($metaDate)
                                <p class="mt-4 text-xs font-bold text-slate-400">{{ \Illuminate\Support\Carbon::parse($metaDate)->format('d M Y, H:i') }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-lg border border-dashed border-lime-300 bg-white p-12 text-center">
                <h2 class="text-xl font-black text-slate-800">{{ $emptyTitle }}</h2>
                <p class="mt-2 text-slate-600">{{ $emptyMessage }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
