<nav class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <img src="{{ asset('assets/img/logo_Innobit.png') }}" alt="logo" class="w-10 h-10">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-lime-600">InnoBit</a>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-sm">
            <a href="{{ route('home') }}" class="hover:underline hover:text-lime-600">Home</a>
            <a href="{{ route('about') }}" class="hover:underline hover:text-lime-600">About</a>
            <a href="{{ route('contact') }}" class="hover:underline hover:text-lime-600">Contact</a>

            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="hover:underline hover:text-lime-600">Dashboard Admin</a>
                @endif

                <span class="text-slate-600">Halo, <strong>{{ auth()->user()->name }}</strong></span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hover:underline hover:text-lime-600">Login</a>
            @endauth
        </div>
    </div>
</nav>
