@extends('layouts.app')

@section('title', 'Aktivitas Admin - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-6xl px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
        @include('admin._subnav')

        <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-lime-700">Jejak operasional</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950">Aktivitas admin</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $activity->total() }} aktivitas tercatat untuk membantu penelusuran perubahan.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-slate-600 hover:text-lime-700">&larr; Kembali ke dashboard</a>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="relative">
                <div class="absolute bottom-4 left-[15px] top-4 w-px bg-slate-200"></div>
                <div class="space-y-1">
                    @forelse ($activity as $log)
                        <article class="relative flex gap-4 rounded-xl px-1 py-4 transition hover:bg-slate-50 sm:px-3">
                            <span class="relative z-10 mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-4 border-white bg-slate-950"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h2 class="font-black capitalize text-slate-900">{{ str_replace('.', ' ', $log->action) }}</h2>
                                        <p class="mt-1 text-sm text-slate-500">{{ $log->actor?->name ?? 'System' }} &middot; {{ $log->ip_address ?: 'IP tidak tersedia' }}</p>
                                    </div>
                                    <time class="shrink-0 text-xs font-semibold text-slate-400">{{ $log->created_at->format('d M Y H:i') }}</time>
                                </div>
                                @if ($log->metadata)
                                    <details class="mt-3 rounded-lg border border-slate-200 bg-white">
                                        <summary class="cursor-pointer list-none px-3 py-2 text-xs font-bold text-slate-600 [&::-webkit-details-marker]:hidden">Lihat detail perubahan</summary>
                                        <pre class="overflow-x-auto border-t border-slate-100 bg-slate-50 p-3 text-xs leading-5 text-slate-600">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="py-12 text-center"><h2 class="text-lg font-black text-slate-950">Belum ada aktivitas</h2><p class="mt-2 text-sm text-slate-500">Perubahan administratif akan tercatat di halaman ini.</p></div>
                    @endforelse
                </div>
            </div>
        </section>

        @if ($activity->hasPages())<div class="mt-6">{{ $activity->links() }}</div>@endif
    </main>
</div>
@endsection
