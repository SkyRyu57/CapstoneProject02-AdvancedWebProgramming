<x-layouts.app title="Dashboard Lab Asset">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Lab Asset</p>
                    <h1>Dashboard {{ $dashboard['role_label'] ?? ($user['role_label'] ?? '') }}</h1>
                </div>

                <div class="page-meta">Ringkasan operasional laboratorium</div>
            </div>
        </header>

        <main class="container content">
            @if ($error)
                <div class="notice danger">{{ $error }}</div>
            @else
                <section class="dashboard-intro">
                    <p>{{ $dashboard['description'] }}</p>
                    <div class="action-tags">
                        @foreach ($dashboard['actions'] as $action)
                            <span>{{ $action }}</span>
                        @endforeach
                    </div>
                    <div class="feature-links">
                        @if (($user['role'] ?? null) === 'admin')
                            <a href="{{ route('admin.users.index') }}">Kelola pengguna</a>
                            <a href="{{ route('admin.rooms.index') }}">Kelola ruangan</a>
                        @elseif (($user['role'] ?? null) === 'kepala_lab')
                            <a href="{{ route('kepala-lab.drafts.index') }}">Draf pengadaan saya</a>
                            <a href="{{ route('inventory-list.index') }}">Daftar Inventaris Laboratorium</a>
                        @elseif (($user['role'] ?? null) === 'kaprodi')
                            <a href="{{ route('kaprodi.drafts.index') }}">Review draf pengadaan</a>
                            <a href="{{ route('inventory-list.index') }}">Daftar Inventaris Laboratorium</a>
                        @elseif (($user['role'] ?? null) === 'staf_admin')
                            <a href="{{ route('staf-admin.approved-drafts.index') }}">Draf disetujui</a>
                            <a href="{{ route('staf-admin.inventories.index') }}">Update inventaris</a>
                            <a href="{{ route('inventory-list.index') }}">Daftar Inventaris Laboratorium</a>
                        @elseif (($user['role'] ?? null) === 'staf_lab')
                            <a href="{{ route('staf-lab.consumables.index') }}">Kelola stok BHP</a>
                            <a href="{{ route('staf-lab.maintenance.index') }}">Catat maintenance</a>
                            <a href="{{ route('inventory-list.index') }}">Daftar Inventaris Laboratorium</a>
                        @endif
                    </div>
                </section>

                <section class="stats-grid">
                    @foreach ($dashboard['stats'] as $stat)
                        <div class="stat-card">
                            <p>{{ $stat['label'] }}</p>
                            <strong>{{ $stat['value'] }}</strong>
                        </div>
                    @endforeach
                </section>

                <section class="panel-grid">
                    @foreach ($dashboard['sections'] as $section)
                        <div class="data-panel">
                            <div class="panel-header">
                                <h2>{{ $section['title'] }}</h2>
                            </div>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            @foreach ($section['columns'] as $column)
                                                <th>{{ $column }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($section['rows'] as $row)
                                            <tr>
                                                @foreach ($row as $cell)
                                                    <td>{{ $cell ?: '-' }}</td>
                                                @endforeach
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ count($section['columns']) }}" class="empty-cell">Belum ada data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </section>
            @endif
        </main>
    </div>
</x-layouts.app>
