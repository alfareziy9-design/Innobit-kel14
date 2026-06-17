@extends('layouts.app')

@section('title', 'Detail Pesan - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
        @include('admin._subnav')

        <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center text-sm font-bold text-slate-600 hover:text-lime-700">&larr; Kembali ke inbox</a>

        <div class="mt-5">
            @include('partials.alerts')
        </div>

        <article
            class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            @if ($contactMessage->user_id)
                data-chat
                data-viewer="admin"
                data-updates-url="{{ route('admin.messages.updates', $contactMessage) }}"
            @endif
        >
            <header class="border-b border-slate-100 px-5 py-6 sm:px-8 sm:py-7">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-lime-700">Pesan kontak</p>
                        <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">{{ $contactMessage->name }}</h1>
                        <p class="mt-2 break-all text-sm text-slate-500">{{ $contactMessage->email }}</p>
                        @if ($contactMessage->user)
                            <p class="mt-3 inline-flex rounded-full bg-lime-50 px-3 py-1 text-xs font-bold text-lime-700">Akun terdaftar: {{ $contactMessage->user->name }}</p>
                        @endif
                    </div>
                    <time class="shrink-0 text-sm text-slate-400">{{ $contactMessage->created_at->format('d M Y H:i') }}</time>
                </div>
            </header>

            <div data-chat-messages class="max-h-[560px] min-h-[320px] space-y-4 overflow-y-auto bg-slate-50/60 px-5 py-7 sm:px-8 sm:py-9">
                @forelse ($contactMessage->conversationMessages as $message)
                    @php($isOwn = $message->sender_type === 'admin')
                    <article data-message-id="{{ $message->id }}" class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-2xl px-4 py-3 {{ $isOwn ? 'bg-slate-950 text-white' : 'border border-slate-200 bg-white text-slate-700' }}">
                            <p class="text-xs font-bold {{ $isOwn ? 'text-lime-300' : 'text-lime-700' }}">{{ $isOwn ? 'Admin InnoBit' : $contactMessage->name }}</p>
                            <p class="mt-1 whitespace-pre-wrap break-words text-sm leading-6">{{ $message->message }}</p>
                            <time class="mt-2 block text-[11px] {{ $isOwn ? 'text-white/55' : 'text-slate-400' }}">{{ $message->created_at->format('d M Y H:i') }}</time>
                        </div>
                    </article>
                @empty
                    <article class="flex justify-start">
                        <div class="max-w-[85%] rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-700">
                            <p class="text-xs font-bold text-lime-700">{{ $contactMessage->name }}</p>
                            <p class="mt-1 whitespace-pre-wrap break-words text-sm leading-6">{{ $contactMessage->message }}</p>
                            <time class="mt-2 block text-[11px] text-slate-400">{{ $contactMessage->created_at->format('d M Y H:i') }}</time>
                        </div>
                    </article>
                @endforelse
            </div>

            <div class="border-t border-slate-100 px-5 py-5 sm:px-8">
                <details class="rounded-xl border border-slate-200 bg-slate-50/70">
                    <summary class="cursor-pointer list-none px-4 py-3 text-sm font-bold text-slate-700 [&::-webkit-details-marker]:hidden">Informasi teknis pengirim</summary>
                    <dl class="grid gap-4 border-t border-slate-200 p-4 text-sm sm:grid-cols-2">
                        <div><dt class="font-bold text-slate-700">IP Address</dt><dd class="mt-1 text-slate-500">{{ $contactMessage->ip_address ?: '-' }}</dd></div>
                        <div><dt class="font-bold text-slate-700">User Agent</dt><dd class="mt-1 break-words text-slate-500">{{ $contactMessage->user_agent ?: '-' }}</dd></div>
                    </dl>
                </details>
            </div>

            @if ($contactMessage->user_id)
                <form data-chat-form action="{{ route('admin.messages.reply', $contactMessage) }}" method="POST" class="border-t border-slate-100 px-5 py-5 sm:px-8">
                    @csrf
                    <label for="admin-chat-message" class="mb-2 block text-sm font-bold text-slate-700">Balas sebagai Admin InnoBit</label>
                    <textarea id="admin-chat-message" name="message" rows="3" required maxlength="5000" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100" placeholder="Tulis balasan untuk pengguna..."></textarea>
                    <div class="mt-3 flex items-center justify-between gap-3">
                        <p data-chat-error class="text-sm font-semibold text-rose-600"></p>
                        <button data-chat-submit class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white hover:bg-lime-700">Kirim Balasan</button>
                    </div>
                </form>
            @else
                <div class="border-t border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800 sm:px-8">
                    Pesan tamu lama ini tidak terhubung ke akun dan hanya dapat dibaca.
                </div>
            @endif

            <footer class="flex flex-wrap gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-5 sm:px-8">
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
