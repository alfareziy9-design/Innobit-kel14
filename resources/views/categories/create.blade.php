@extends('layouts.app')

@section('title', 'Tambah Kategori - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-4xl px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
        @include('admin._subnav')

        <a href="{{ route('kategori.index') }}" class="text-sm font-bold text-slate-600 hover:text-lime-700">&larr; Kembali ke kategori</a>
        <div class="mt-5 grid gap-6 md:grid-cols-[minmax(0,1fr)_250px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm font-semibold text-lime-700">Kategori baru</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950">Tambah kategori</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan nama yang singkat dan mudah dipahami pembaca.</p>
                <div class="mt-6">@include('partials.alerts')</div>
                <form action="{{ route('kategori.store') }}" method="POST" class="mt-6 space-y-5">@include('categories._form')</form>
            </section>
            <aside class="rounded-2xl border border-lime-200 bg-lime-50/70 p-5">
                <h2 class="font-black text-slate-950">Panduan singkat</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Pilih istilah yang cukup luas untuk beberapa artikel, tetapi tetap spesifik bagi pembaca.</p>
            </aside>
        </div>
    </main>
</div>
@endsection
