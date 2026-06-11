@extends('layouts.app')

@section('title', 'Inbox Pesan - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-slate-50">
    <main class="mx-auto max-w-6xl px-4 py-8 lg:py-10">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-700">Admin inbox</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Pesan Kontak</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $messages->total() }} pesan tersimpan.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-lime-500 hover:text-lime-700">Kembali ke Dashboard</a>
        </div>

        @include('partials.alerts')

        <form method="GET" action="{{ route('admin.messages.index') }}" class="mb-5 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_180px_auto] sm:items-end">
                <div>
                    <label for="message-search" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Search</label>
                    <input id="message-search" type="search" name="search" value="{{ request('search') }}" placeholder="Cari pengirim, email, atau pesan..." class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                </div>
                <div>
                    <label for="message-status" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Status</label>
                    <select id="message-status" name="status" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm font-semibold outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        <option value="">Semua Pesan</option>
                        <option value="unread" @selected(request('status') === 'unread')>Belum Dibaca</option>
                        <option value="read" @selected(request('status') === 'read')>Sudah Dibaca</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="h-11 rounded-lg bg-slate-950 px-4 text-sm font-bold text-white">Filter</button>
                    <a href="{{ route('admin.messages.index') }}" class="inline-flex h-11 items-center rounded-lg border border-slate-300 px-4 text-sm font-bold text-slate-700">Reset</a>
                </div>
            </div>
        </form>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-4">Pengirim</th>
                            <th class="px-5 py-4">Pesan</th>
                            <th class="px-5 py-4">Waktu</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($messages as $message)
                            <tr class="align-top transition hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <p class="font-black text-slate-900">{{ $message->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $message->email }}</p>
                                    @if (! $message->read_at)
                                        <span class="mt-2 inline-flex rounded-full bg-sky-100 px-2 py-1 text-[11px] font-bold text-sky-800">Belum dibaca</span>
                                    @endif
                                    @if ($message->user)
                                        <span class="mt-2 inline-flex rounded-full bg-lime-50 px-2 py-1 text-[11px] font-bold text-lime-800">User terdaftar</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="max-w-xl line-clamp-2 leading-6 text-slate-600">{{ $message->message }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ $message->created_at->format('d M Y H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.messages.show', $message) }}" class="rounded-lg border border-lime-200 bg-lime-50 px-3 py-2 text-xs font-bold text-lime-800 transition hover:bg-lime-100">Lihat</a>
                                        <form action="{{ route('admin.messages.read', $message) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_read" value="{{ $message->read_at ? 0 : 1 }}">
                                            <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700">{{ $message->read_at ? 'Belum Dibaca' : 'Tandai Dibaca' }}</button>
                                        </form>
                                        <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Balasan pesan InnoBit') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-lime-400 hover:text-lime-700">Balas</a>
                                        <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Hapus pesan ini secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-slate-500">Belum ada pesan kontak.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($messages->hasPages())
            <div class="mt-8 flex items-center justify-center gap-3">
                @if ($messages->onFirstPage())
                    <span class="rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400">Sebelumnya</span>
                @else
                    <a href="{{ $messages->previousPageUrl() }}" class="rounded-lg border border-lime-200 bg-white px-4 py-2 text-sm font-bold text-slate-700">Sebelumnya</a>
                @endif
                <span class="text-sm font-bold text-slate-600">Halaman {{ $messages->currentPage() }} dari {{ $messages->lastPage() }}</span>
                @if ($messages->hasMorePages())
                    <a href="{{ $messages->nextPageUrl() }}" class="rounded-lg border border-lime-200 bg-white px-4 py-2 text-sm font-bold text-slate-700">Berikutnya</a>
                @else
                    <span class="rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400">Berikutnya</span>
                @endif
            </div>
        @endif
    </main>
</div>
@endsection
