<x-layouts.app title="Kelola Stok BHP">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Staf Laboratorium</p>
                    <h1>Manajemen Stok BHP</h1>
                </div>
                <div class="topbar-actions">
                    <a href="{{ route('staf-lab.maintenance.index') }}" class="button-secondary">Log Maintenance</a>
                    <a href="{{ route('dashboard') }}" class="button-secondary">Dashboard</a>
                </div>
            </div>
        </header>

        <main class="container content">
            @include('components.status')

            @if ($error)
                <div class="notice danger">{{ $error }}</div>
            @endif

            <section class="data-panel">
                <div class="panel-header">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <div>
                            <h2>Daftar Barang Habis Pakai (BHP)</h2>
                            <p class="panel-subtitle">Gunakan tombol (+) atau (−) untuk menyesuaikan stok.</p>
                        </div>
                        <input type="text" id="consumable-search" class="input" placeholder="Cari nama atau deskripsi..." style="max-width: 300px;">
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama BHP</th>
                                <th>Satuan</th>
                                <th>Stok</th>
                                <th>Min. Stok</th>
                                <th>Kondisi</th>
                                <th>Adjust Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($consumables as $bhp)
                                @php
                                    $isLow = $bhp['stock'] <= $bhp['min_stock'];
                                @endphp
                                <tr class="consumable-row" data-search="{{ strtolower($bhp['name'] . ' ' . ($bhp['description'] ?? '')) }}">
                                    <td>
                                        <strong>{{ $bhp['name'] }}</strong>
                                        <div class="muted-link">{{ $bhp['description'] ?? '' }}</div>
                                    </td>
                                    <td>{{ $bhp['unit'] }}</td>
                                    <td>
                                        <span class="status-pill {{ $isLow ? 'status-pill--danger' : 'status-pill--success' }}">
                                            {{ $bhp['stock'] }}
                                        </span>
                                    </td>
                                    <td>{{ $bhp['min_stock'] }}</td>
                                    <td>
                                        @if ($isLow)
                                            <span class="status-pill status-pill--danger">Stok Menipis</span>
                                        @else
                                            <span class="status-pill status-pill--success">Cukup</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST"
                                            action="{{ route('staf-lab.consumables.adjust', $bhp['_id']) }}"
                                            class="adjust-form">
                                            @csrf
                                            <input type="number" class="input table-input small"
                                                name="quantity_change"
                                                placeholder="±jumlah"
                                                required>
                                            <input type="hidden" name="reference_type" value="manual">
                                            <input class="input table-input" name="reason"
                                                placeholder="Alasan (opsional)">
                                            <button class="button-primary">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-cell">Belum ada data BHP.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        const consumableSearch = document.getElementById('consumable-search');
        if (consumableSearch) {
            consumableSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.consumable-row').forEach(row => {
                    const text = row.dataset.search || '';
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    </script>
</x-layouts.app>
