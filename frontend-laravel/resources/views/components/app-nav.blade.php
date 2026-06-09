@php
    $authUser = session('auth_user', []);
    $role = $authUser['role'] ?? null;
@endphp

<nav class="app-nav">
    <div class="container app-nav-inner">
        <a class="brand-mark" href="{{ route('dashboard') }}">
            <span class="brand-symbol">LA</span>
            <span>
                <strong>Lab Asset</strong>
                <small>{{ $authUser['role_label'] ?? 'Inventaris Lab' }}</small>
            </span>
        </a>

        <div class="nav-links">
            <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>Dashboard</a>

            @if ($role !== 'admin')
                <a href="{{ route('inventory-list.index') }}" @class(['active' => request()->routeIs('inventory-list.*')])>Daftar Barang</a>
            @endif

            @if ($role === 'admin')
                <a href="{{ route('admin.users.index') }}" @class(['active' => request()->routeIs('admin.users.*')])>Pengguna</a>
                <a href="{{ route('admin.rooms.index') }}" @class(['active' => request()->routeIs('admin.rooms.*')])>Ruangan</a>
            @else
                <a href="{{ route('inventories.list') }}" @class(['active' => request()->routeIs('inventories.list')])>Daftar Barang</a>
                
                @if ($role === 'kepala_lab')
                    <a href="{{ route('kepala-lab.drafts.index') }}" @class(['active' => request()->routeIs('kepala-lab.drafts.*')])>Draf Pengadaan</a>
                @elseif ($role === 'kaprodi')
                    <a href="{{ route('kaprodi.drafts.index') }}" @class(['active' => request()->routeIs('kaprodi.drafts.*')])>Review Draf</a>
                @elseif ($role === 'staf_admin')
                    <a href="{{ route('staf-admin.approved-drafts.index') }}" @class(['active' => request()->routeIs('staf-admin.approved-drafts.*')])>Draf Disetujui</a>
                    <a href="{{ route('staf-admin.inventories.index') }}" @class(['active' => request()->routeIs('staf-admin.inventories.*')])>Update Inventaris</a>
                @elseif ($role === 'staf_lab')
                    <a href="{{ route('staf-lab.consumables.index') }}" @class(['active' => request()->routeIs('staf-lab.consumables.*')])>Stok BHP</a>
                    <a href="{{ route('staf-lab.maintenance.index') }}" @class(['active' => request()->routeIs('staf-lab.maintenance.*')])>Maintenance</a>
                @endif
            @endif
        </div>

        <div class="nav-account">
            <div class="nav-user">
                <strong>{{ $authUser['name'] ?? 'User' }}</strong>
                <span>{{ $authUser['email'] ?? '' }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="nav-logout">Logout</button>
            </form>
        </div>
    </div>
</nav>
