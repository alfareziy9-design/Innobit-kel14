@extends('layouts.app')

@section('title', 'Inbox Pesan - InnoBit')

@section('content')
@php
    $hasActiveFilters = request()->filled('search') || request()->filled('status');
@endphp

<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
        @include('admin._subnav')

        <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-lime-700">Komunikasi</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950">Pesan masuk</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $messages->total() }} pesan tersimpan dari formulir kontak InnoBit.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-slate-600 hover:text-lime-700">&larr; Kembali ke dashboard</a>
        </header>

        @include('partials.alerts')

        <form method="GET" action="{{ route('admin.messages.index') }}" class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_190px_auto] sm:items-end">
                <div>
                    <label for="message-search" class="mb-2 block text-xs font-bold text-slate-600">Cari pesan</label>
                    <input id="message-search" type="search" name="search" value="{{ request('search') }}" placeholder="Nama, email, atau isi pesan" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                </div>
                <div>
                    <label for="message-status" class="mb-2 block text-xs font-bold text-slate-600">Status baca</label>
                    <select id="message-status" name="status" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm font-semibold outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        <option value="">Semua pesan</option>
                        <option value="unread" @selected(request('status') === 'unread')>Belum dibaca</option>
                        <option value="read" @selected(request('status') === 'read')>Sudah dibaca</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="h-11 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white">Terapkan</button>
                    @if ($hasActiveFilters)<a href="{{ route('admin.messages.index') }}" class="inline-flex h-11 items-center rounded-xl border border-slate-300 px-4 text-sm font-bold text-slate-600">Reset</a>@endif
                </div>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($messages as $message)
                <article class="border-b border-slate-100 p-5 last:border-b-0 sm:p-6 {{ $message->read_at ? '' : 'bg-sky-50/35' }}">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start">
                        <div class="flex min-w-0 flex-1 gap-3">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $message->read_at ? 'bg-slate-200' : 'bg-sky-500' }}"></span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-black text-slate-900">{{ $message->name }}</p>
                                    @if (! $message->read_at)<span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-bold text-sky-700">Baru</span>@endif
                                    @if ($message->user)<span class="rounded-full bg-lime-50 px-2 py-0.5 text-[11px] font-bold text-lime-700">User terdaftar</span>@endif
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ $message->email }}</p>
                                <p class="mt-3 line-clamp-2 max-w-3xl text-sm leading-6 text-slate-600">{{ $message->latestConversationMessage?->message ?? $message->message }}</p>
                                <p class="mt-2 text-xs text-slate-400">{{ ($message->last_message_at ?? $message->created_at)->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 lg:max-w-[300px] lg:justify-end">
                            <a href="{{ route('admin.messages.show', $message) }}" class="rounded-lg bg-slate-950 px-3 py-2 text-xs font-bold text-white">Buka</a>
                            <form action="{{ route('admin.messages.read', $message) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="is_read" value="{{ $message->read_at ? 0 : 1 }}">
                                <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700">{{ $message->read_at ? 'Tandai belum dibaca' : 'Tandai dibaca' }}</button>
                            </form>
                            @if ($message->user_id)
                                <a href="{{ route('admin.messages.show', $message) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700">Balas di web</a>
                            @else
                                <span class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-400">Pesan lama</span>
                            @endif
                            <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Hapus pesan ini secara permanen?')">
                                @csrf @method('DELETE')
                                <button class="px-2 py-2 text-xs font-bold text-rose-600">Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="px-6 py-14 text-center">
                    <h2 class="text-lg font-black text-slate-950">{{ $hasActiveFilters ? 'Pesan tidak ditemukan' : 'Inbox masih kosong' }}</h2>
                    <p class="mt-2 text-sm text-slate-500">{{ $hasActiveFilters ? 'Ubah pencarian atau hapus filter untuk melihat pesan lain.' : 'Pesan dari pengunjung akan muncul di halaman ini.' }}</p>
                    @if ($hasActiveFilters)<a href="{{ route('admin.messages.index') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white">Hapus filter</a>@endif
                </div>
            @endforelse
        </section>

        @if ($messages->hasPages())
            <div class="mt-8 flex items-center justify-center gap-3">
                @if ($messages->onFirstPage())<span class="rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400">Sebelumnya</span>@else<a href="{{ $messages->previousPageUrl() }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700">Sebelumnya</a>@endif
                <span class="text-sm font-bold text-slate-600">Halaman {{ $messages->currentPage() }} dari {{ $messages->lastPage() }}</span>
                @if ($messages->hasMorePages())<a href="{{ $messages->nextPageUrl() }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700">Berikutnya</a>@else<span class="rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400">Berikutnya</span>@endif
            </div>
        @endif
    </main>
</div>
@endsection
