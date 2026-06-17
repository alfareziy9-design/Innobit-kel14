@extends('layouts.app')

@section('title', $pageTitle.' - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-slate-50">
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        @include('admin._subnav')

        <div class="mb-6 rounded-2xl border border-violet-200 bg-violet-50 p-5">
            <p class="text-sm font-bold text-violet-800">Edit editorial</p>
            <h1 class="mt-1 text-2xl font-black text-slate-950">{{ $pageTitle }}</h1>
            <p class="mt-2 text-sm leading-6 text-violet-900">
                Perubahan disimpan pada naskah yang sedang direview dan tidak langsung memublikasikan artikel.
                @isset($revision)
                    Versi publik tetap tayang sampai pembaruan disetujui.
                @endisset
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            @include('partials.alerts')

            <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @include('articles._form', [
                    'article' => $article,
                    'editorSource' => $editorSource,
                    'adminReviewMode' => true,
                    'formHttpMethod' => 'PUT',
                    'cancelUrl' => $reviewUrl,
                    'submitLabel' => 'Simpan Perubahan Review',
                ])
            </form>
        </div>
    </main>
</div>
@endsection
