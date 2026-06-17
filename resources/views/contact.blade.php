@extends('layouts.app')

@section('title', 'Kontak Kami - Innobit')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid md:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl shadow-sm border p-6 md:p-8">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">Hubungi Kami</h1>
            <p class="text-slate-600 mb-6">Ada pertanyaan atau saran? Mulai percakapan dengan admin InnoBit.</p>

            @include('partials.alerts')

            @auth
                @if (auth()->user()->isAdmin())
                    <div class="rounded-xl border border-lime-200 bg-lime-50 p-5">
                        <p class="text-sm leading-6 text-slate-700">Pesan pengguna dikelola melalui inbox pada dashboard admin.</p>
                        <a href="{{ route('admin.messages.index') }}" class="mt-4 inline-flex bg-slate-950 px-5 py-3 text-sm font-bold text-white hover:bg-lime-700">Buka Inbox Admin</a>
                    </div>
                @else
                <form method="POST" action="{{ route('contact.send') }}">
                    @csrf
                    <div class="absolute left-[-9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input id="contact-website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="mb-4">
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" placeholder="Nama Lengkap" required maxlength="100" class="w-full border rounded-none px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500">
                    </div>
                    <div class="mb-4">
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" placeholder="Alamat Email" required maxlength="100" class="w-full border rounded-none px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500">
                    </div>
                    <div class="mb-6">
                        <textarea name="message" rows="5" placeholder="Pesan Anda..." required maxlength="5000" class="w-full border rounded-none px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500">{{ old('message') }}</textarea>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="bg-lime-500 text-white rounded-none px-6 py-3 hover:bg-lime-600 transition w-full md:w-auto">Mulai Percakapan</button>
                        <a href="{{ route('messages.index') }}" class="text-sm font-bold text-slate-600 hover:text-lime-700">Lihat Pesan Saya</a>
                    </div>
                </form>
                @endif
            @else
                <div class="rounded-xl border border-lime-200 bg-lime-50 p-5">
                    <p class="text-sm leading-6 text-slate-700">Masuk terlebih dahulu agar balasan admin dapat dibaca langsung melalui web.</p>
                    <a href="{{ route('login') }}" class="mt-4 inline-flex bg-slate-950 px-5 py-3 text-sm font-bold text-white hover:bg-lime-700">Login untuk Mengirim Pesan</a>
                </div>
            @endauth
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-6 md:p-8">
            <h2 class="text-xl font-bold text-slate-800 mb-4">Informasi Kontak</h2>
            <div class="space-y-4 text-slate-600">
                <div>
                    <p class="font-semibold">Alamat</p>
                    <p>Jl. Rungkut Madya, Gn. Anyar, Kec. Gunung Anyar, Surabaya, Jawa Timur 60294</p>
                </div>
                <div>
                    <p class="font-semibold">Email</p>
                    <p>innobit@outlook.com</p>
                </div>
                <div>
                    <p class="font-semibold">Telepon</p>
                    <p>+62 812 3456 7890</p>
                </div>
                <div>
                    <p class="font-semibold">Jam Operasional</p>
                    <p>Senin - Jumat, 09.00 - 17.00 WIB</p>
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <h2 class="text-xl font-bold text-slate-800 mb-3">Ikuti Kami</h2>
            <div class="flex gap-4">
                <a href="#" class="text-slate-500 hover:text-lime-600 transition">Instagram</a>
                <a href="#" class="text-slate-500 hover:text-lime-600 transition">LinkedIn</a>
                <a href="#" class="text-slate-500 hover:text-lime-600 transition">YouTube</a>
                <a href="#" class="text-slate-500 hover:text-lime-600 transition">TikTok</a>
            </div>
        </div>
    </div>
</div>
@endsection
