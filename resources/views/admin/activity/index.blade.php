@extends('layouts.app')

@section('title', 'Aktivitas Admin - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-slate-50">
    <main class="mx-auto max-w-5xl px-4 py-8 lg:py-10">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-700">Audit log</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Aktivitas Admin</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $activity->total() }} aktivitas tercatat.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-lime-500 hover:text-lime-700">Kembali ke Dashboard</a>
        </div>

        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="divide-y divide-slate-200">
                @forelse ($activity as $log)
                    <article class="p-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="font-black text-slate-900">{{ str_replace('.', ' ', $log->action) }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ $log->actor?->name ?? 'System' }} &middot; {{ $log->ip_address ?: '-' }}</p>
                            </div>
                            <p class="text-sm font-semibold text-slate-500">{{ $log->created_at->format('d M Y H:i') }}</p>
                        </div>
                        @if ($log->metadata)
                            <pre class="mt-4 overflow-x-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-600">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        @endif
                    </article>
                @empty
                    <p class="p-8 text-center text-slate-500">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </section>

        @if ($activity->hasPages())
            <div class="mt-6">{{ $activity->links() }}</div>
        @endif
    </main>
</div>
@endsection
