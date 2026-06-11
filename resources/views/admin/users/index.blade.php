@extends('layouts.app')

@section('title', 'Kelola User - InnoBit')

@section('content')
<div class="min-h-[calc(100vh-140px)] bg-slate-50">
    <main class="mx-auto max-w-7xl px-4 py-8 lg:py-10">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-lime-700">Admin users</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Kelola User</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $users->total() }} akun ditemukan.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-lime-500 hover:text-lime-700">Kembali ke Dashboard</a>
        </div>

        @include('partials.alerts')

        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-5 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_180px_auto] md:items-end">
                <div>
                    <label for="user-search" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Search</label>
                    <input id="user-search" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                </div>
                <div>
                    <label for="user-role" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Role</label>
                    <select id="user-role" name="role_id" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm font-semibold outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) request('role_id') === (string) $role->id)>{{ $role->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="user-status" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-600">Status</label>
                    <select id="user-status" name="account_status" class="h-11 w-full rounded-lg border border-slate-300 px-3 text-sm font-semibold outline-none focus:border-lime-600 focus:ring-4 focus:ring-lime-100">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(request('account_status') === 'active')>Active</option>
                        <option value="suspended" @selected(request('account_status') === 'suspended')>Suspended</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="h-11 rounded-lg bg-slate-950 px-4 text-sm font-bold text-white transition hover:bg-lime-700">Filter</button>
                    <a href="{{ route('admin.users.index') }}" class="inline-flex h-11 items-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700">Reset</a>
                </div>
            </div>
        </form>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-4">User</th>
                            <th class="px-5 py-4">Role</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Bergabung</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($users as $user)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-black text-slate-900">{{ $user->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $user->roleModel?->label ?? ucfirst($user->roleName()) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $user->isActive() ? 'bg-lime-100 text-lime-800' : 'bg-red-100 text-red-700' }}">{{ ucfirst($user->account_status) }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <form action="{{ route('admin.users.role', $user) }}" method="POST" class="flex gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role_id" class="h-9 rounded-lg border border-slate-300 px-2 text-xs font-semibold">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->label }}</option>
                                                @endforeach
                                            </select>
                                            <button class="rounded-lg border border-lime-200 bg-lime-50 px-3 py-2 text-xs font-bold text-lime-800">Simpan Role</button>
                                        </form>
                                        <form action="{{ route('admin.users.status', $user) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="account_status" value="{{ $user->isActive() ? 'suspended' : 'active' }}">
                                            <button class="rounded-lg border {{ $user->isActive() ? 'border-red-200 bg-red-50 text-red-700' : 'border-lime-200 bg-lime-50 text-lime-800' }} px-3 py-2 text-xs font-bold">
                                                {{ $user->isActive() ? 'Suspend' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-slate-500">Tidak ada user yang cocok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($users->hasPages())
            <div class="mt-6">{{ $users->links() }}</div>
        @endif
    </main>
</div>
@endsection
