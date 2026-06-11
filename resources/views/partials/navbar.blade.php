<nav class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto grid max-w-7xl grid-cols-2 items-center gap-y-3 px-4 py-4 md:grid-cols-[1fr_auto_1fr]">
        <div class="flex items-center gap-3 justify-self-start">
            <img src="{{ asset('assets/img/logo_Innobit.png') }}" alt="logo" class="h-9 w-9">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight text-slate-950">Inno<span class="text-lime-600">Bit</span></a>
        </div>

        <div class="order-3 col-span-2 flex flex-wrap items-center justify-center gap-2 text-sm font-medium text-slate-600 md:order-none md:col-span-1">
            <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-lime-700 hover:bg-lime-50">Home</a>
            <a href="{{ route('about') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 hover:text-slate-950">About</a>
            <a href="{{ route('contact') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 hover:text-slate-950">Contact</a>

            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 hover:text-slate-950">Dashboard Admin</a>
                @elseif (auth()->user()->isAuthor())
                    <a href="{{ route('author.dashboard') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 hover:text-slate-950">Dashboard Penulis</a>
                @endif
            @endauth
        </div>

        <div class="flex items-center justify-self-end gap-2 text-sm font-medium text-slate-600">
            @auth
                <details class="group relative">
                    <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg border border-lime-200 bg-lime-50 text-lime-800 transition hover:border-lime-400 hover:bg-lime-100 focus:outline-none focus:ring-4 focus:ring-lime-100 [&::-webkit-details-marker]:hidden" aria-label="Lihat streak belajar">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M13 2L5 13h6l-1 9 9-13h-6l1-7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                    </summary>

                    <div class="absolute right-0 top-12 z-50 w-72 rounded-lg border border-slate-200 bg-white p-4 text-slate-700 shadow-lg">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-lime-700">Streak harian</p>
                                <p class="mt-2 text-3xl font-black text-slate-950">7 hari</p>
                                <p class="mt-1 text-sm leading-6 text-slate-500">Pertahankan ritme belajar singkat setiap hari.</p>
                            </div>
                            <span class="rounded-full bg-lime-100 px-3 py-1 text-sm font-black text-lime-800">5/7</span>
                        </div>

                        <div class="mt-4">
                            <div class="mb-2 flex items-center justify-between text-xs font-bold text-slate-500">
                                <span>Progress mingguan</span>
                                <span>5 selesai</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-200">
                                <div class="h-full w-[72%] rounded-full bg-lime-600"></div>
                            </div>
                        </div>
                    </div>
                </details>

                <details class="group relative">
                    <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-950 text-sm font-black uppercase text-white transition hover:border-lime-400 hover:bg-lime-700 focus:outline-none focus:ring-4 focus:ring-lime-100 [&::-webkit-details-marker]:hidden" aria-label="Buka menu profil">
                        @if (auth()->user()->photo_url)
                            <img src="{{ auth()->user()->photo_url }}" alt="" class="h-full w-full object-cover">
                        @else
                            {{ Str::of(auth()->user()->name)->substr(0, 1) }}
                        @endif
                    </summary>

                    <div class="absolute right-0 top-12 z-50 w-64 rounded-lg border border-slate-200 bg-white p-3 text-slate-700 shadow-lg">
                        <div class="border-b border-slate-200 px-3 py-3">
                            <p class="font-black text-slate-950">{{ auth()->user()->name }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.12em] text-slate-400">{{ auth()->user()->roleName() }}</p>
                        </div>

                        <div class="py-2">
                            <a href="{{ route('profile.show') }}" class="block rounded-lg px-3 py-2 text-sm font-bold text-slate-700 hover:bg-lime-50 hover:text-lime-800">Profil Saya</a>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-bold text-slate-700 hover:bg-lime-50 hover:text-lime-800">Dashboard Admin</a>
                                <a href="{{ route('admin.users.index') }}" class="block rounded-lg px-3 py-2 text-sm font-bold text-slate-700 hover:bg-lime-50 hover:text-lime-800">Kelola User</a>
                            @elseif (auth()->user()->isAuthor())
                                <a href="{{ route('author.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-bold text-slate-700 hover:bg-lime-50 hover:text-lime-800">Dashboard Penulis</a>
                            @else
                                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-sm font-bold text-slate-700 hover:bg-lime-50 hover:text-lime-800">Beranda Belajar</a>
                            @endif
                        </div>

                        <form action="{{ route('logout') }}" method="POST" class="border-t border-slate-200 pt-2">
                            @csrf
                            <button class="w-full rounded-lg px-3 py-2 text-left text-sm font-bold text-red-600 hover:bg-red-50">Keluar</button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-slate-900 hover:border-lime-400 hover:text-lime-700">Login</a>
            @endauth
        </div>
    </div>
</nav>
