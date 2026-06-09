<x-layouts.app title="Draf Pengadaan Saya">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Kepala Laboratorium</p>
                    <h1>Draf Pengadaan Tahunan</h1>
                </div>
                <a href="{{ route('dashboard') }}" class="button-secondary">Dashboard</a>
            </div>
        </header>

        <main class="container content">
            @include('components.status')

            @if ($error)
                <div class="notice danger">{{ $error }}</div>
            @endif

            <section class="form-card page-form">
                <h2>Buat Draf Baru</h2>
                <form method="POST" action="{{ route('kepala-lab.drafts.store') }}" class="form-grid">
                    @csrf
                    <div class="field">
                        <label>Tahun Anggaran</label>
                        <input class="input" name="fiscal_year" type="number" min="2000" max="2100"
                            value="{{ old('fiscal_year', date('Y')) }}" required>
                    </div>
                    <div class="field">
                        <label>Catatan</label>
                        <input class="input" name="notes" value="{{ old('notes') }}"
                            placeholder="Contoh: Pengadaan perangkat jaringan lab 2026">
                    </div>
                    <button class="button-primary">Buat Draf</button>
                </form>
            </section>

            <section class="data-panel section-gap">
                <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <h2>Draf yang Pernah Diajukan</h2>
                    <input type="text" id="draft-search" class="input" placeholder="Cari tahun, status, atau catatan..." style="max-width: 300px;">
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tahun</th>
                                <th>Status</th>
                                <th>Jumlah Item</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($drafts as $draft)
                                <tr class="draft-row" data-search="{{ strtolower($draft['fiscal_year'] . ' ' . $draft['status'] . ' ' . ($draft['notes'] ?? '')) }}">
                                    <td>{{ $draft['fiscal_year'] }}</td>
                                    <td>
                                        @php
                                            $statusLabels = [
                                                'draft' => 'Draf',
                                                'submitted' => 'Diajukan',
                                                'finalized' => 'Difinalisasi'
                                            ];
                                            $statusLabel = $statusLabels[$draft['status']] ?? ucfirst($draft['status']);
                                        @endphp
                                        <span class="status-pill {{ $draft['locked'] ? 'status-pill--locked' : '' }}">
                                            {{ $statusLabel }}
                                            @if ($draft['locked'])
                                                🔒
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ count($draft['items'] ?? []) }} item</td>
                                    <td>{{ $draft['notes'] ?? '-' }}</td>
                                    <td>
                                        <div class="action-cell">
                                            <a class="button-secondary"
                                                href="{{ route('kepala-lab.drafts.show', $draft['_id']) }}">
                                                {{ $draft['locked'] ? 'Lihat' : 'Edit' }}
                                            </a>
                                            @if (!$draft['locked'])
                                                <form method="POST"
                                                    action="{{ route('kepala-lab.drafts.destroy', $draft['_id']) }}"
                                                    data-confirm-delete="Hapus draf tahun {{ $draft['fiscal_year'] }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="button-danger">Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-cell">Belum ada draf pengadaan. Buat draf baru di atas.</td>
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
