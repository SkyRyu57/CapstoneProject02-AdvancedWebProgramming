<x-layouts.app title="Review Draf Pengadaan">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Ketua Program Studi</p>
                    <h1>Review Draf Pengadaan</h1>
                </div>
                <a href="{{ route('dashboard') }}" class="button-secondary">Dashboard</a>
            </div>
        </header>

        <main class="container content">
            @include('components.status')

            @if ($error)
                <div class="notice danger">{{ $error }}</div>
            @endif

            <section class="data-panel">
                <div class="panel-header">
                    <h2>Draf dari Kepala Laboratorium</h2>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tahun</th>
                                <th>Status</th>
                                <th>Item</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($drafts as $draft)
                                <tr>
                                    <td>{{ $draft['fiscal_year'] }}</td>
                                    <td><span class="status-pill">{{ $draft['status'] }}</span></td>
                                    <td>{{ count($draft['items'] ?? []) }}</td>
                                    <td>{{ $draft['notes'] ?? '-' }}</td>
                                    <td><a class="button-secondary" href="{{ route('kaprodi.drafts.show', $draft['_id']) }}">Review</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-cell">Belum ada draf untuk direview.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</x-layouts.app>
