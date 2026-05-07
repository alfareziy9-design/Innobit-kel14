@extends('layouts.app')

@section('title', 'Tambah Kategori - InnoBit')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm border p-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-6">Tambah Kategori</h1>
        @include('partials.alerts')

        <form action="{{ route('kategori.store') }}" method="POST" class="space-y-5">
            @include('categories._form')
        </form>
    </div>
</div>
@endsection
