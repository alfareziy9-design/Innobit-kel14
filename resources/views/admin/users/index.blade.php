@extends('layouts.app')

@section('title', 'Kelola User - InnoBit')

@section('content')
@php
    $hasActiveFilters = request()->filled('search') || request()->filled('role_id') || request()->filled('account_status');
@endphp

<div class="min-h-[calc(100vh-140px)] bg-[#f5f6f3]">
    <main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8 lg:py-10">
        @include('admin._subnav')

        <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-lime-700">Pengguna dan akses</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950">Kelola akun</h1>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $users->total() }} akun ditemukan. Atur peran dan status akses dari halaman ini.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-bold text-slate-600 hover:text-lime-700">&larr; Kembali ke dashboard</a>
        </header>

        @include('partials.alerts')

        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_180px_auto] md:items-end">
                <div>
                    <label for="user-search" class="mb-2 block text-xs font-bold text-slate-600">Cari pengguna</label>
                    <input id="user-search" type="search" name="search" value="{{ request('search') }}" placeholder="Nama atau email" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                </div>
                <div>
                    <label for="user-role" class="mb-2 block text-xs font-bold text-slate-600">Peran</label>
                    <select id="user-role" name="role_id" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm font-semibold outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        <option value="">Semua peran</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="user-status" class="mb-2 block text-xs font-bold text-slate-600">Status akun</label>
                    <select id="user-status" name="account_status" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm font-semibold outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        <option value="">Semua status</option>
                        <option value="active" @selected(request('account_status') === 'active')>Aktif</option>
                        <option value="suspended" @selected(request('account_status') === 'suspended')>Ditangguhkan</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="h-11 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white transition hover:bg-lime-700">Terapkan</button>
                    @if ($hasActiveFilters)
                        <a href="{{ route('admin.users.index') }}" class="inline-flex h-11 items-center rounded-xl border border-slate-300 px-4 text-sm font-bold text-slate-600">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if ($users->isNotEmpty())
                <div class="hidden md:block">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] text-left text-sm">
                            <thead class="border-b border-slate-100 text-xs font-bold text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">Pengguna</th>
                                    <th class="px-4 py-4">Peran</th>
                                    <th class="px-4 py-4">Status</th>
                                    <th class="px-4 py-4">Bergabung</th>
                                    <th class="px-6 py-4 text-right">Pengaturan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($users as $user)
                                    <tr class="align-top transition hover:bg-slate-50/70">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-black uppercase text-slate-600">{{ Str::substr($user->name, 0, 1) }}</span>
                                                <div><p class="font-bold text-slate-900">{{ $user->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $user->email }}</p></div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-5"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $user->roleModel?->label ?? ucfirst($user->roleName()) }}</span></td>
                                        <td class="px-4 py-5">
                                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $user->isActive() ? 'border-lime-200 bg-lime-50 text-lime-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $user->isActive() ? 'bg-lime-500' : 'bg-rose-500' }}"></span>{{ $user->isActive() ? 'Aktif' : 'Ditangguhkan' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-5 text-xs text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                                        <td class="px-6 py-5">
                                            <div class="flex flex-wrap justify-end gap-2">
                                                <form action="{{ route('admin.users.role', $user) }}" method="POST" class="flex gap-2">
                                                    @csrf @method('PATCH')
                                                    <select name="role_id" class="h-9 rounded-lg border border-slate-300 px-2 text-xs font-semibold">
                                                        @foreach ($roles as $role)<option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->label }}</option>@endforeach
                                                    </select>
                                                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:border-lime-400">Simpan</button>
                                                </form>
                                                <form action="{{ route('admin.users.status', $user) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="account_status" value="{{ $user->isActive() ? 'suspended' : 'active' }}">
                                                    <button class="rounded-lg px-3 py-2 text-xs font-bold {{ $user->isActive() ? 'text-rose-600 hover:bg-rose-50' : 'bg-lime-50 text-lime-700' }}">{{ $user->isActive() ? 'Tangguhkan' : 'Aktifkan' }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach ($users as $user)
                        <article class="p-5">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-black uppercase text-slate-600">{{ Str::substr($user->name, 0, 1) }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-black text-slate-900">{{ $user->name }}</p>
                                    <p class="mt-1 break-all text-xs text-slate-500">{{ $user->email }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $user->roleModel?->label ?? ucfirst($user->roleName()) }}</span>
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $user->isActive() ? 'border-lime-200 bg-lime-50 text-lime-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">{{ $user->isActive() ? 'Aktif' : 'Ditangguhkan' }}</span>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-4 text-xs text-slate-400">Bergabung {{ $user->created_at->format('d M Y') }}</p>
                            <div class="mt-4 space-y-3 rounded-xl bg-slate-50 p-3">
                                <form action="{{ route('admin.users.role', $user) }}" method="POST" class="flex gap-2">
                                    @csrf @method('PATCH')
                                    <select name="role_id" class="h-10 min-w-0 flex-1 rounded-lg border border-slate-300 px-2 text-xs font-semibold">@foreach ($roles as $role)<option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->label }}</option>@endforeach</select>
                                    <button class="rounded-lg bg-slate-950 px-3 py-2 text-xs font-bold text-white">Simpan peran</button>
                                </form>
                                <form action="{{ route('admin.users.status', $user) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="account_status" value="{{ $user->isActive() ? 'suspended' : 'active' }}">
                                    <button class="w-full rounded-lg border px-3 py-2 text-xs font-bold {{ $user->isActive() ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-lime-200 bg-lime-50 text-lime-700' }}">{{ $user->isActive() ? 'Tangguhkan akun' : 'Aktifkan akun' }}</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-14 text-center">
                    <h2 class="text-lg font-black text-slate-950">Tidak ada pengguna yang cocok</h2>
                    <p class="mt-2 text-sm text-slate-500">Coba ubah kata kunci atau hapus filter yang aktif.</p>
                    <a href="{{ route('admin.users.index') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white">Hapus filter</a>
                </div>
            @endif
        </section>

        @if ($users->hasPages())<div class="mt-6">{{ $users->links() }}</div>@endif
    </main>
</div>
@endsection
