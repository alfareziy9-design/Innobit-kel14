@extends('layouts.app')

@section('title', 'Pesan Saya - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-lime-700">Bantuan InnoBit</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950">Pesan Saya</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Lihat pertanyaan dan balasan dari Admin InnoBit.</p>
            </div>
            <a href="{{ route('contact') }}" class="inline-flex rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white hover:bg-lime-700">Buat Pesan Baru</a>
        </header>

        @include('partials.alerts')

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($threads as $thread)
                @php
                    $preview = $thread->latestConversationMessage?->message ?? $thread->message;
                    $hasUnreadReply = is_null($thread->user_read_at) && $thread->latestConversationMessage?->sender_type === 'admin';
                @endphp
                <a href="{{ route('messages.show', $thread) }}" class="block border-b border-slate-100 p-5 transition last:border-b-0 hover:bg-slate-50 sm:p-6 {{ $hasUnreadReply ? 'bg-lime-50/45' : '' }}">
                    <div class="flex items-start gap-3">
                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $hasUnreadReply ? 'bg-lime-600' : 'bg-slate-200' }}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <h2 class="font-black text-slate-900">{{ Str::limit($thread->message, 55) }}</h2>
                                    @if ($hasUnreadReply)
                                        <span class="rounded-full bg-lime-100 px-2 py-0.5 text-[11px] font-bold text-lime-700">Balasan baru</span>
                                    @endif
                                </div>
                                <time class="text-xs text-slate-400">{{ ($thread->last_message_at ?? $thread->created_at)->format('d M Y H:i') }}</time>
                            </div>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $preview }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-6 py-14 text-center">
                    <h2 class="text-lg font-black text-slate-950">Belum ada percakapan</h2>
                    <p class="mt-2 text-sm text-slate-500">Kirim pertanyaan melalui halaman kontak untuk memulai percakapan dengan admin.</p>
                    <a href="{{ route('contact') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white">Mulai Percakapan</a>
                </div>
            @endforelse
        </section>

        @if ($threads->hasPages())
            <div class="mt-7">{{ $threads->links() }}</div>
        @endif
    </main>
</div>
@endsection
