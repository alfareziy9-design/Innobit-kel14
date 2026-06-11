@extends('layouts.app')

@section('title', 'Tambah Artikel - InnoBit')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm border p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold mb-6">Tambah Artikel</h1>
        @include('partials.alerts')

        <form action="{{ route('articles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @include('articles._form')
        </form>
    </div>
</div>
@endsection
