@extends('layouts.app')

@section('title', 'Edit Artikel - InnoBit')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm border p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold mb-6">Edit Artikel</h1>
        @include('partials.alerts')

        <form action="{{ auth()->user()?->isAdmin() || $article->status !== 'published' ? route('articles.update', $article) : route('articles.revisions.store', $article) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @include('articles._form', ['article' => $article])
        </form>
    </div>
</div>
@endsection
