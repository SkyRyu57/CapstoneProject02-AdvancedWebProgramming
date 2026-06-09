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
            <section class="data-panel">
                <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <h2>Draf dari Kepala Laboratorium</h2>
                    <input type="text" id="draft-search" class="input" placeholder="Cari tahun, status, atau catatan..." style="max-width: 300px;">
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
                                <tr class="draft-row" data-search="{{ strtolower($draft['fiscal_year'] . ' ' . $draft['status'] . ' ' . ($draft['notes'] ?? '')) }}">
                                    <td>{{ $draft['fiscal_year'] }}</td>
                                    @php
                                        $statusLabels = [
                                            'draft' => 'Draf',
                                            'submitted' => 'Diajukan',
                                            'finalized' => 'Difinalisasi'
                                        ];
                                        $statusLabel = $statusLabels[$draft['status']] ?? ucfirst($draft['status']);
                                    @endphp
                                    <td><span class="status-pill">{{ $statusLabel }}</span></td>
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

    <script>
        const draftSearch = document.getElementById('draft-search');
        if (draftSearch) {
            draftSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.draft-row').forEach(row => {
                    const text = row.dataset.search || '';
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    </script>
</x-layouts.app>
