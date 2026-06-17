@extends('layouts.app')

@section('title', 'Percakapan - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <a href="{{ route('messages.index') }}" class="inline-flex items-center text-sm font-bold text-slate-600 hover:text-lime-700">&larr; Kembali ke Pesan Saya</a>

        <div class="mt-5">
            @include('partials.alerts')
        </div>

        <section
            class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            data-chat
            data-viewer="user"
            data-updates-url="{{ route('messages.updates', $contactMessage) }}"
        >
            <header class="border-b border-slate-100 px-5 py-5 sm:px-7">
                <p class="text-sm font-semibold text-lime-700">Percakapan dengan Admin InnoBit</p>
                <h1 class="mt-1 text-xl font-black text-slate-950">{{ Str::limit($contactMessage->message, 80) }}</h1>
            </header>

            <div data-chat-messages class="max-h-[560px] min-h-[320px] space-y-4 overflow-y-auto bg-slate-50/60 px-5 py-6 sm:px-7">
                @forelse ($contactMessage->conversationMessages as $message)
                    @php($isOwn = $message->sender_type === 'user')
                    <article data-message-id="{{ $message->id }}" class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-2xl px-4 py-3 {{ $isOwn ? 'bg-slate-950 text-white' : 'border border-slate-200 bg-white text-slate-700' }}">
                            <p class="text-xs font-bold {{ $isOwn ? 'text-lime-300' : 'text-lime-700' }}">{{ $isOwn ? 'Anda' : 'Admin InnoBit' }}</p>
                            <p class="mt-1 whitespace-pre-wrap break-words text-sm leading-6">{{ $message->message }}</p>
                            <time class="mt-2 block text-[11px] {{ $isOwn ? 'text-white/55' : 'text-slate-400' }}">{{ $message->created_at->format('d M Y H:i') }}</time>
                        </div>
                    </article>
                @empty
                    <article class="flex justify-end">
                        <div class="max-w-[85%] rounded-2xl bg-slate-950 px-4 py-3 text-white">
                            <p class="text-xs font-bold text-lime-300">Anda</p>
                            <p class="mt-1 whitespace-pre-wrap break-words text-sm leading-6">{{ $contactMessage->message }}</p>
                            <time class="mt-2 block text-[11px] text-white/55">{{ $contactMessage->created_at->format('d M Y H:i') }}</time>
                        </div>
                    </article>
                @endforelse
            </div>

            <form data-chat-form action="{{ route('messages.reply', $contactMessage) }}" method="POST" class="border-t border-slate-100 p-5 sm:p-7">
                @csrf
                <label for="user-chat-message" class="mb-2 block text-sm font-bold text-slate-700">Balas pesan</label>
                <textarea id="user-chat-message" name="message" rows="3" required maxlength="5000" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100" placeholder="Tulis pesan untuk admin..."></textarea>
                <div class="mt-3 flex items-center justify-between gap-3">
                    <p data-chat-error class="text-sm font-semibold text-rose-600"></p>
                    <button data-chat-submit class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white hover:bg-lime-700">Kirim</button>
                </div>
            </form>
        </section>
    </main>
</div>
@endsection
