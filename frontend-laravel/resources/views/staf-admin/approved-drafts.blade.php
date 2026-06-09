<x-layouts.app title="Draf Pengadaan Disetujui">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Staf Administrasi</p>
                    <h1>Draf Pengadaan Disetujui</h1>
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
                <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <h2>Item yang Sudah Disetujui Kaprodi</h2>
                    <input type="text" id="draft-search" class="input" placeholder="Cari tahun, barang, atau tipe..." style="max-width: 300px;">
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tahun</th>
                                <th>Barang</th>
                                <th>Tipe</th>
                                <th>Jumlah Disetujui</th>
                                <th>Input Penerimaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($drafts as $draft)
                                @foreach (($draft['items'] ?? []) as $item)
                                    <tr class="draft-row" data-search="{{ strtolower($draft['fiscal_year'] . ' ' . $item['name'] . ' ' . $item['item_type']) }}">
                                        <td>{{ $draft['fiscal_year'] }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ $item['item_type'] }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('staf-admin.receipts.store') }}" class="receipt-form">
                                                @csrf
                                                <input type="hidden" name="draft_item_id" value="{{ $item['_id'] }}">
                                                <input class="input table-input" type="date" name="received_date" required>
                                                <input class="input table-input small" type="number" min="1" name="quantity" placeholder="Jumlah" required>
                                                <input class="input table-input" name="notes" placeholder="Catatan">
                                                <button class="button-primary">Catat</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-cell">Belum ada draf final dengan item disetujui.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="data-panel section-gap">
                <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <h2>Riwayat Penerimaan</h2>
                    <input type="text" id="receipt-search" class="input" placeholder="Cari tanggal atau catatan..." style="max-width: 300px;">
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($receipts as $receipt)
                                <tr class="receipt-row" data-search="{{ strtolower((isset($receipt['received_date']) ? substr($receipt['received_date'], 0, 10) : '') . ' ' . ($receipt['notes'] ?? '')) }}">
                                    <td>{{ $receipt['draft_item_id'] }}</td>
                                    <td>{{ isset($receipt['received_date']) ? substr($receipt['received_date'], 0, 10) : '-' }}</td>
                                    <td>{{ $receipt['quantity'] }}</td>
                                    <td>{{ $receipt['notes'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-cell">Belum ada penerimaan barang.</td>
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

        const receiptSearch = document.getElementById('receipt-search');
        if (receiptSearch) {
            receiptSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.receipt-row').forEach(row => {
                    const text = row.dataset.search || '';
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    </script>
</x-layouts.app>
