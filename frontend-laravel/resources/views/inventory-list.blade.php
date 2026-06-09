<x-layouts.app title="Daftar Inventaris Laboratorium">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Lab Asset</p>
                    <h1>Daftar Inventaris Laboratorium</h1>
                </div>
                <div class="page-meta">Daftar semua aset yang ada di laboratorium</div>
            </div>
        </header>

        <main class="container content">
            <div class="topbar-actions" style="margin-bottom: 20px;">
                <input type="text" id="inv-search" class="input" placeholder="Cari nama barang, label, atau ruangan..." style="max-width: 300px;">
            </div>

            <div class="table-wrap">
                <table id="inv-list-table">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Label</th>
                            <th>Ruangan</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th>QR / Barcode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inventories as $inv)
                            @php
                                $qrCode = $inv['qr_code'] ?? '';
                                $isUploaded = str_starts_with($qrCode, '/uploads/');
                                $generatedQrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=INV-" . $inv['_id'];
                                $condClass = match($inv['condition'] ?? '') {
                                    'baik'             => 'status-pill--success',
                                    'perlu maintenance'=> 'status-pill--warning',
                                    'rusak'            => 'status-pill--danger',
                                    default            => '',
                                };
                                $statusClass = match($inv['status'] ?? '') {
                                    'active'      => 'status-pill--success',
                                    'maintenance' => 'status-pill--warning',
                                    'retired'     => 'status-pill--locked',
                                    default       => '',
                                };
                            @endphp
                            <tr class="inv-list-row"
                                data-search="{{ strtolower(($inv['name'] ?? '') . ' ' . ($inv['label_code'] ?? '') . ' ' . ($inv['room_name'] ?? '') . ' ' . ($inv['condition'] ?? '') . ' ' . ($inv['status'] ?? '')) }}">
                                <td>
                                    <strong>{{ $inv['name'] }}</strong>
                                    @if (!empty($inv['description']))
                                        <div class="muted-link">{{ $inv['description'] }}</div>
                                    @endif
                                </td>
                                <td>{{ $inv['label_code'] ?? '-' }}</td>
                                <td>{{ $inv['room_name'] ?? '-' }}</td>
                                <td><span class="status-pill {{ $condClass }}">{{ ucfirst($inv['condition'] ?? '-') }}</span></td>
                                <td><span class="status-pill {{ $statusClass }}">{{ ucfirst($inv['status'] ?? '-') }}</span></td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <img class="qr-mini" src="{{ $generatedQrUrl }}" alt="System QR {{ $inv['name'] }}" title="System QR (INV-{{ $inv['_id'] }})">
                                        @if ($isUploaded)
                                            <img class="qr-mini" src="{{ config('services.backend_api.asset_url', 'http://localhost:5000').$qrCode }}" alt="Uploaded QR {{ $inv['name'] }}" title="Uploaded QR">
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-cell">Belum ada data inventaris.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 24px;">
                <a href="{{ route('dashboard') }}" class="button-secondary">Kembali ke Dashboard</a>
            </div>
        </main>
    </div>

    <script>
        const invSearch = document.getElementById('inv-search');
        if (invSearch) {
            invSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.inv-list-row').forEach(row => {
                    const text = row.dataset.search || '';
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    </script>
</x-layouts.app>
