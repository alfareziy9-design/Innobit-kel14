@extends('layouts.app')

@section('title', 'Detail Pesan - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
        @include('admin._subnav')

        <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center text-sm font-bold text-slate-600 hover:text-lime-700">&larr; Kembali ke inbox</a>

        <article class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-100 px-5 py-6 sm:px-8 sm:py-7">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-lime-700">Pesan kontak</p>
                        <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">{{ $contactMessage->name }}</h1>
                        <a href="mailto:{{ $contactMessage->email }}" class="mt-2 inline-block break-all text-sm text-slate-500 hover:text-lime-700">{{ $contactMessage->email }}</a>
                        @if ($contactMessage->user)
                            <p class="mt-3 inline-flex rounded-full bg-lime-50 px-3 py-1 text-xs font-bold text-lime-700">Akun terdaftar: {{ $contactMessage->user->name }}</p>
                        @endif
                    </div>
                    <time class="shrink-0 text-sm text-slate-400">{{ $contactMessage->created_at->format('d M Y H:i') }}</time>
                </div>
            </header>

            <div class="px-5 py-7 sm:px-8 sm:py-9">
                <div class="whitespace-pre-wrap break-words text-base leading-8 text-slate-700">{{ $contactMessage->message }}</div>

                <details class="mt-8 rounded-xl border border-slate-200 bg-slate-50/70">
                    <summary class="cursor-pointer list-none px-4 py-3 text-sm font-bold text-slate-700 [&::-webkit-details-marker]:hidden">Informasi teknis pengirim</summary>
                    <dl class="grid gap-4 border-t border-slate-200 p-4 text-sm sm:grid-cols-2">
                        <div><dt class="font-bold text-slate-700">IP Address</dt><dd class="mt-1 text-slate-500">{{ $contactMessage->ip_address ?: '-' }}</dd></div>
                        <div><dt class="font-bold text-slate-700">User Agent</dt><dd class="mt-1 break-words text-slate-500">{{ $contactMessage->user_agent ?: '-' }}</dd></div>
                    </dl>
                </details>
            </div>

            <footer class="flex flex-wrap gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-5 sm:px-8">
                <a href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Balasan pesan InnoBit') }}" class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-lime-700">Balas via email</a>
                <form action="{{ route('admin.messages.read', $contactMessage) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="is_read" value="0">
                    <button class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700">Tandai belum dibaca</button>
                </form>
                <form action="{{ route('admin.messages.destroy', $contactMessage) }}" method="POST" class="sm:ml-auto" onsubmit="return confirm('Hapus pesan ini secara permanen?')">
                    @csrf @method('DELETE')
                    <button class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-bold text-rose-700">Hapus pesan</button>
                </form>
            </footer>
        </article>
    </main>
</div>
@endsection
