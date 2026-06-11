@extends('layouts.app')

@section('title', 'Login - Innobit')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-[700px] bg-white rounded-[28px] shadow-[0_20px_40px_rgba(15,23,42,0.12)] p-8 sm:p-[56px_60px]">
        <h1 class="text-center text-4xl sm:text-[44px] font-bold mb-2.5 text-slate-800">Login</h1>

        <div class="text-center text-base sm:text-lg mb-9 text-slate-600">
            Belum punya akun? <a href="{{ route('register') }}" class="text-lime-600 font-bold hover:underline">Daftar</a>
        </div>

        @include('partials.alerts')

        <form action="{{ route('login.store') }}" method="POST">
            @csrf
            <div>
                <input type="email" name="email" placeholder="Alamat Email" value="{{ old('email') }}" class="w-full h-[60px] sm:h-[68px] border border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-lime-500 border-b-0" required>
            </div>

            <div>
                <input type="password" name="password" placeholder="Password" class="w-full h-[60px] sm:h-[68px] border border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-lime-500" required>
            </div>

            <div class="mt-5 mb-7 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <label class="flex items-center gap-3 text-base sm:text-lg text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-6 h-6" style="accent-color:#65a30d">
                    <span>Ingat saya</span>
                </label>

                <a href="#" class="text-lime-600 font-semibold text-base sm:text-lg hover:underline">Lupa password?</a>
            </div>

            <button type="submit" class="w-full bg-lime-500 hover:bg-lime-600 text-white py-4 sm:py-[18px] rounded-xl text-lg sm:text-xl font-bold transition-colors duration-200">Masuk</button>
        </form>
    </div>
</div>
@endsection
