@php
    $adminNavigation = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => request()->routeIs('admin.dashboard')],
        ['label' => 'User', 'route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*')],
        ['label' => 'Kategori', 'route' => 'kategori.index', 'active' => request()->routeIs('kategori.*')],
        ['label' => 'Pesan', 'route' => 'admin.messages.index', 'active' => request()->routeIs('admin.messages.*')],
        ['label' => 'Aktivitas', 'route' => 'admin.activity.index', 'active' => request()->routeIs('admin.activity.*')],
    ];
@endphp

<nav aria-label="Navigasi admin" class="mb-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm">
    <div class="flex min-w-max items-center gap-1">
        @foreach ($adminNavigation as $item)
            <a href="{{ route($item['route']) }}" class="rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $item['active'] ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
