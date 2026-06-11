@extends('layouts.app')

@section('title', 'Kategori - InnoBit')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm border p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl md:text-3xl font-bold">Kelola Kategori</h1>
            <a href="{{ route('kategori.create') }}" class="bg-lime-600 text-white px-4 py-3 rounded-xl hover:bg-lime-700">Tambah Kategori</a>
        </div>

        @include('partials.alerts')

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-left">
                        <th class="p-3">No</th>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Slug</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr class="border-b">
                            <td class="p-3">{{ $loop->iteration }}</td>
                            <td class="p-3">{{ $category->name }}</td>
                            <td class="p-3">{{ $category->slug }}</td>
                            <td class="p-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('kategori.edit', $category) }}" class="bg-yellow-400 text-white px-3 py-2 rounded-lg hover:bg-yellow-500">Edit</a>
                                    <form action="{{ route('kategori.destroy', $category) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-slate-500">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
