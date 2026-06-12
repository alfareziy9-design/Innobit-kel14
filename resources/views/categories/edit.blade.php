@extends('layouts.app')

@section('title', 'Edit Kategori - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-4xl px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
        @include('admin._subnav')

        <a href="{{ route('kategori.index') }}" class="text-sm font-bold text-slate-600 hover:text-lime-700">&larr; Kembali ke kategori</a>
        <div class="mt-5 grid gap-6 md:grid-cols-[minmax(0,1fr)_250px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm font-semibold text-lime-700">Perbarui kategori</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950">Edit {{ $category->name }}</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Perubahan nama juga akan memperbarui slug kategori secara otomatis.</p>
                <div class="mt-6">@include('partials.alerts')</div>
                <form action="{{ route('kategori.update', $category) }}" method="POST" class="mt-6 space-y-5">@include('categories._form', ['category' => $category])</form>
            </section>
            <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Slug saat ini</p>
                <code class="mt-3 block break-all rounded-lg bg-slate-100 p-3 text-xs text-slate-600">{{ $category->slug }}</code>
            </aside>
        </div>
    </main>
</div>
@endsection
