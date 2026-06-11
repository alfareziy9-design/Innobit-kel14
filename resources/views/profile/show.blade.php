@extends('layouts.app')

@section('title', 'Profil Saya - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-slate-50">
    <div class="mx-auto max-w-6xl px-4 py-8 lg:py-10">
        <section class="rounded-lg border border-slate-200 bg-slate-950 p-6 text-white shadow-sm md:p-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                @if ($user->photo_url)
                    <img src="{{ $user->photo_url }}" alt="Foto profil {{ $user->name }}" class="h-24 w-24 rounded-full border-4 border-white/15 object-cover">
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-white/15 bg-lime-500 text-3xl font-black uppercase text-slate-950">
                        {{ Str::of($user->name)->substr(0, 1) }}
                    </div>
                @endif

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-300">Profil akun</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight md:text-4xl">{{ $user->name }}</h1>
                    <p class="mt-2 text-white/65">{{ $user->email }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                        <span class="rounded-full bg-white/10 px-3 py-1">{{ $user->roleModel?->label ?? ucfirst($user->roleName()) }}</span>
                        <span class="rounded-full bg-white/10 px-3 py-1">Bergabung {{ $user->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-6">
            @include('partials.alerts')
        </div>

        <section class="mt-6">
            <div class="mb-4">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-700">Aktivitas belajar</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950">Ringkasan Aktivitas</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Artikel Dibaca</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $learningStats['articles_read'] }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Favorit</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $learningStats['favorites'] }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Koleksi</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $learningStats['collections'] }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Percobaan Quiz</p>
                    <p class="mt-2 text-3xl font-black text-slate-950">{{ $learningStats['quiz_attempts'] }}</p>
                </div>
            </div>
        </section>

        @if ($articleStats)
            <section class="mt-8 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-700">Aktivitas menulis</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">Status Artikel</h2>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach ([
                        'total' => 'Total',
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'published' => 'Published',
                        'rejected' => 'Rejected',
                    ] as $key => $label)
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
                            <p class="mt-1 text-2xl font-black text-slate-950">{{ $articleStats[$key] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Informasi Akun</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Perbarui nama dan alamat email akunmu.</p>

                <form action="{{ route('profile.update') }}" method="POST" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="profile-name" class="mb-2 block text-sm font-bold text-slate-700">Nama</label>
                        <input id="profile-name" type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="100" class="h-12 w-full rounded-lg border border-slate-300 px-4 outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                    </div>

                    <div>
                        <label for="profile-email" class="mb-2 block text-sm font-bold text-slate-700">Email</label>
                        <input id="profile-email" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="100" class="h-12 w-full rounded-lg border border-slate-300 px-4 outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                    </div>

                    <div>
                        <label for="identity-current-password" class="mb-2 block text-sm font-bold text-slate-700">Password Saat Ini</label>
                        <input id="identity-current-password" type="password" name="current_password" autocomplete="current-password" class="h-12 w-full rounded-lg border border-slate-300 px-4 outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        <p class="mt-2 text-xs leading-5 text-slate-500">Wajib diisi hanya saat mengubah email.</p>
                    </div>

                    <button class="rounded-lg bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-lime-700">Simpan Informasi</button>
                </form>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">Foto Profil</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Gunakan JPG, PNG, atau WEBP maksimal 2MB.</p>

                <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')
                    <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" required class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm">
                    <button class="rounded-lg bg-lime-600 px-5 py-3 text-sm font-black text-white transition hover:bg-lime-700">Unggah Foto</button>
                </form>

                @if ($user->photo)
                    <form action="{{ route('profile.photo.destroy') }}" method="POST" class="mt-4 border-t border-slate-200 pt-4" onsubmit="return confirm('Hapus foto profil saat ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-lg border border-red-200 bg-red-50 px-5 py-3 text-sm font-black text-red-700 transition hover:bg-red-100">Hapus Foto</button>
                    </form>
                @endif
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-xl font-black text-slate-950">Ubah Password</h2>
                <p class="mt-1 text-sm leading-6 text-slate-500">Gunakan password baru minimal 8 karakter.</p>

                <form action="{{ route('profile.password.update') }}" method="POST" class="mt-6 grid gap-5 md:grid-cols-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="password-current" class="mb-2 block text-sm font-bold text-slate-700">Password Saat Ini</label>
                        <input id="password-current" type="password" name="current_password" required autocomplete="current-password" class="h-12 w-full rounded-lg border border-slate-300 px-4 outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                    </div>

                    <div>
                        <label for="password-new" class="mb-2 block text-sm font-bold text-slate-700">Password Baru</label>
                        <input id="password-new" type="password" name="password" required minlength="8" autocomplete="new-password" class="h-12 w-full rounded-lg border border-slate-300 px-4 outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                    </div>

                    <div>
                        <label for="password-confirmation" class="mb-2 block text-sm font-bold text-slate-700">Konfirmasi Password</label>
                        <input id="password-confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="h-12 w-full rounded-lg border border-slate-300 px-4 outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                    </div>

                    <div class="md:col-span-3">
                        <button class="rounded-lg bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-lime-700">Perbarui Password</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
