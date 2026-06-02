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

            {{-- Pending Items (belum terpenuhi) --}}
            <section class="data-panel">
                <div class="panel-header">
                    <div>
                        <h2>Item yang Belum Terpenuhi</h2>
                        <p class="panel-subtitle">Item yang sudah disetujui Kaprodi dan belum sepenuhnya diterima.</p>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tahun</th>
                                <th>Barang</th>
                                <th>Tipe</th>
                                <th>Disetujui</th>
                                <th>Diterima</th>
                                <th>Sisa</th>
                                <th>Input Penerimaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasPending = false; @endphp
                            @foreach ($drafts as $draft)
                                @foreach (($draft['items'] ?? []) as $item)
                                    @php $hasPending = true; @endphp
                                    <tr>
                                        <td>{{ $draft['fiscal_year'] }}</td>
                                        <td><strong>{{ $item['name'] }}</strong></td>
                                        <td><span class="status-pill">{{ $item['item_type'] }}</span></td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td>
                                            <span class="status-pill status-pill--info">
                                                {{ $item['total_received'] ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-pill status-pill--warning">
                                                {{ $item['remaining'] ?? $item['quantity'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('staf-admin.receipts.store') }}" class="receipt-form">
                                                @csrf
                                                <input type="hidden" name="draft_item_id" value="{{ $item['_id'] }}">
                                                <input class="input table-input" type="date" name="received_date" required>
                                                <input class="input table-input small" type="number" min="1"
                                                    max="{{ $item['remaining'] ?? $item['quantity'] }}"
                                                    name="quantity" placeholder="Jumlah" required>
                                                <input class="input table-input" name="notes" placeholder="Catatan">
                                                <button class="button-primary">Catat</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @unless ($hasPending)
                                <tr>
                                    <td colspan="7" class="empty-cell">Semua item sudah terpenuhi.</td>
                                </tr>
                            @endunless
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Fulfilled items (hidden/collapsed) --}}
            @php
                $fulfilledItems = [];
                foreach ($drafts as $draft) {
                    foreach (($draft['fulfilled_items'] ?? []) as $item) {
                        $fulfilledItems[] = array_merge($item, ['fiscal_year' => $draft['fiscal_year']]);
                    }
                }
            @endphp
            @if (count($fulfilledItems) > 0)
                <section class="data-panel section-gap">
                    <div class="panel-header">
                        <h2>Item Sudah Terpenuhi <span class="status-pill status-pill--success">{{ count($fulfilledItems) }}</span></h2>
                        <button type="button" class="button-secondary" id="toggle-fulfilled">Tampilkan</button>
                    </div>
                    <div id="fulfilled-table-wrap" hidden>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tahun</th>
                                        <th>Barang</th>
                                        <th>Tipe</th>
                                        <th>Disetujui</th>
                                        <th>Diterima</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fulfilledItems as $item)
                                        <tr>
                                            <td>{{ $item['fiscal_year'] }}</td>
                                            <td><strong>{{ $item['name'] }}</strong></td>
                                            <td><span class="status-pill">{{ $item['item_type'] }}</span></td>
                                            <td>{{ $item['quantity'] }}</td>
                                            <td><span class="status-pill status-pill--success">✓ {{ $item['total_received'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Riwayat Penerimaan --}}
            <section class="data-panel section-gap">
                <div class="panel-header">
                    <div>
                        <h2>Riwayat Penerimaan</h2>
                    </div>
                    <div class="search-bar-wrap">
                        <input id="receipt-search" class="input search-input" type="search"
                            placeholder="🔍 Cari catatan, tanggal…" autocomplete="off">
                    </div>
                </div>
                <div class="table-wrap">
                    <table id="receipts-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($receipts as $receipt)
                                @php
                                    // Find item name across all drafts
                                    $itemName = '-';
                                    foreach ($drafts as $d) {
                                        foreach (array_merge($d['items'] ?? [], $d['fulfilled_items'] ?? []) as $itm) {
                                            if ($itm['_id'] == $receipt['draft_item_id']) {
                                                $itemName = $itm['name'];
                                                break 2;
                                            }
                                        }
                                    }
                                @endphp
                                <tr class="receipt-row"
                                    data-search="{{ strtolower($itemName . ' ' . ($receipt['notes'] ?? '') . ' ' . substr($receipt['received_date'] ?? '', 0, 10)) }}">
                                    <td>{{ $itemName }}</td>
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
                <div id="receipts-pagination" class="pagination-bar"></div>
            </section>
        </main>
    </div>

    <script>
        (() => {
            // Toggle fulfilled
            const toggleBtn = document.getElementById('toggle-fulfilled');
            const fulfilledWrap = document.getElementById('fulfilled-table-wrap');
            if (toggleBtn && fulfilledWrap) {
                toggleBtn.addEventListener('click', () => {
                    fulfilledWrap.hidden = !fulfilledWrap.hidden;
                    toggleBtn.textContent = fulfilledWrap.hidden ? 'Tampilkan' : 'Sembunyikan';
                });
            }

            // Pagination + search for receipts
            const PAGE_SIZE = 5;
            const allRows = Array.from(document.querySelectorAll('#receipts-table tbody .receipt-row'));
            let visibleRows = allRows;
            let currentPage = 1;
            const paginationBar = document.getElementById('receipts-pagination');
            const searchInput = document.getElementById('receipt-search');

            function renderPage() {
                const start = (currentPage - 1) * PAGE_SIZE;
                const end = start + PAGE_SIZE;
                allRows.forEach(r => r.hidden = true);
                visibleRows.slice(start, end).forEach(r => r.hidden = false);
                renderPagination();
            }

            function renderPagination() {
                const pages = Math.ceil(visibleRows.length / PAGE_SIZE);
                if (pages <= 1) { paginationBar.innerHTML = ''; return; }
                let html = `<span class="page-info">Halaman ${currentPage} dari ${pages}</span>`;
                html += `<button class="button-secondary btn-page" ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">← Sebelumnya</button>`;
                html += `<button class="button-secondary btn-page" ${currentPage === pages ? 'disabled' : ''} data-page="${currentPage + 1}">Berikutnya →</button>`;
                paginationBar.innerHTML = html;
                paginationBar.querySelectorAll('.btn-page').forEach(btn => {
                    btn.addEventListener('click', () => {
                        currentPage = Number(btn.dataset.page);
                        renderPage();
                    });
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    const q = searchInput.value.toLowerCase().trim();
                    visibleRows = q ? allRows.filter(r => r.dataset.search.includes(q)) : allRows;
                    currentPage = 1;
                    renderPage();
                });
            }

            renderPage();
        })();
    </script>
</x-layouts.app>
