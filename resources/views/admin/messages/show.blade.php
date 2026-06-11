@extends('layouts.app')

@section('title', 'Detail Pesan - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-slate-50">
    <main class="mx-auto max-w-4xl px-4 py-8 lg:py-10">
        <a href="{{ route('admin.messages.index') }}" class="text-sm font-bold text-lime-800 hover:text-lime-600">&larr; Kembali ke Inbox</a>

        <article class="mt-5 rounded-lg border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-5 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-700">Pesan kontak</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ $contactMessage->name }}</h1>
                    <p class="mt-2 text-slate-600">{{ $contactMessage->email }}</p>
                    @if ($contactMessage->user)
                        <p class="mt-2 text-xs font-bold text-lime-700">Dikirim oleh akun: {{ $contactMessage->user->name }}</p>
                    @endif
                </div>
                <p class="text-sm text-slate-500">{{ $contactMessage->created_at->format('d M Y H:i') }}</p>
            </div>

            <div class="mt-6 whitespace-pre-wrap break-words text-base leading-8 text-slate-700">{{ $contactMessage->message }}</div>

            <dl class="mt-8 grid gap-4 rounded-lg bg-slate-50 p-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="font-black text-slate-700">IP Address</dt>
                    <dd class="mt-1 text-slate-500">{{ $contactMessage->ip_address ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="font-black text-slate-700">User Agent</dt>
                    <dd class="mt-1 break-words text-slate-500">{{ $contactMessage->user_agent ?: '-' }}</dd>
                </div>
            </dl>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Balasan pesan InnoBit') }}" class="rounded-lg bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-lime-700">Balas via Email</a>
                <form action="{{ route('admin.messages.read', $contactMessage) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_read" value="0">
                    <button class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-black text-slate-700">Tandai Belum Dibaca</button>
                </form>
                <form action="{{ route('admin.messages.destroy', $contactMessage) }}" method="POST" onsubmit="return confirm('Hapus pesan ini secara permanen?')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-lg border border-red-200 bg-red-50 px-5 py-3 text-sm font-black text-red-700 transition hover:bg-red-100">Hapus Pesan</button>
                </form>
            </div>
        </article>
    </main>
</div>
@endsection
