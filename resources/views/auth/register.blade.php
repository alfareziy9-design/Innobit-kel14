@extends('layouts.app')

@section('title', 'Register - Innobit')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-[700px] bg-white rounded-[28px] shadow-[0_20px_40px_rgba(15,23,42,0.12)] p-8 sm:p-[56px_60px]">
        <h1 class="text-center text-4xl sm:text-[44px] font-bold mb-2.5 text-slate-800">Daftar</h1>

        <div class="text-center text-base sm:text-lg mb-9 text-slate-600">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-lime-600 font-bold hover:underline">Masuk</a>
        </div>

        @include('partials.alerts')

        <form action="{{ route('register.store') }}" method="POST">
            @csrf
            <div>
                <input type="text" name="name" placeholder="Nama Lengkap" value="{{ old('name') }}" class="w-full h-[60px] sm:h-[68px] border border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-lime-500 rounded-t-xl" required>
            </div>

            <div>
                <input type="email" name="email" placeholder="Alamat Email" value="{{ old('email') }}" class="w-full h-[60px] sm:h-[68px] border-x border-b border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-lime-500" required>
            </div>

            <div>
                <input type="password" name="password" placeholder="Password" class="w-full h-[60px] sm:h-[68px] border-x border-b border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-lime-500" required>
            </div>

            <div class="mb-8">
                <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" class="w-full h-[60px] sm:h-[68px] border-x border-b border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-lime-500 rounded-b-xl" required>
            </div>

            <button type="submit" class="w-full bg-lime-600 hover:bg-lime-700 text-white py-4 sm:py-[18px] rounded-xl text-lg sm:text-xl font-bold transition-all duration-200 transform active:scale-[0.98]">Daftar Sekarang</button>
        </form>
    </div>
</div>
@endsection
