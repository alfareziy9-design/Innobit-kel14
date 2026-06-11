@extends('layouts.app')

@section('title', 'Edit Kategori - InnoBit')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm border p-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-6">Edit Kategori</h1>
        @include('partials.alerts')

        <form action="{{ route('kategori.update', $category) }}" method="POST" class="space-y-5">
            @include('categories._form', ['category' => $category])
        </form>
    </div>
</div>
@endsection
