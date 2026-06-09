<x-layouts.app title="Daftar Barang Inventaris & BHP">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Daftar Barang</p>
                    <h1>Inventaris &amp; BHP Laboratorium</h1>
                </div>
                <a href="{{ route('dashboard') }}" class="button-secondary">Dashboard</a>
            </div>
        </header>

        <main class="container content">
            @include('components.status')

            @if ($error)
                <div class="notice danger">{{ $error }}</div>
            @endif

            {{-- Search & Filter --}}
            <section class="data-panel">
                <div class="panel-header" style="flex-wrap:wrap;gap:1rem">
                    <div>
                        <h2>Daftar Barang</h2>
                        <p class="panel-subtitle">
                            {{ count($inventories) }} inventaris &middot; {{ count($consumables) }} BHP
                        </p>
                    </div>
                    <div class="search-bar-wrap">
                        <input id="item-search" class="input search-input" type="search"
                            placeholder="🔍 Cari nama, label, ruangan, status…" autocomplete="off">
                    </div>
                </div>

                {{-- Filter tabs --}}
                <div class="filter-tabs">
                    <button class="filter-tab active" data-filter="all">Semua</button>
                    <button class="filter-tab" data-filter="inventory">🖥 Inventaris</button>
                    <button class="filter-tab" data-filter="consumable">🧪 BHP</button>
                </div>

                {{-- Inventaris Table --}}
                <div id="section-inventory" class="item-section">
                    <h3 class="section-subtitle">Inventaris</h3>
                    <div class="table-wrap">
                        <table id="inv-list-table">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Label</th>
                                    <th>Ruangan</th>
                                    <th>Kondisi</th>
                                    <th>Status</th>
                                    <th>Barcode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventories as $inv)
                                    @php
                                        $qrCode = $inv['qr_code'] ?? '';
                                        $isUploaded = str_starts_with($qrCode, '/uploads/');
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
                                        data-type="inventory"
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
                                                @php
                                                    $generatedQrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=INV-" . $inv['_id'];
                                                @endphp
                                                <img class="qr-mini" src="{{ $generatedQrUrl }}" alt="System QR {{ $inv['name'] }}" title="System QR (INV-{{ $inv['_id'] }})">
                                                
                                                @if ($isUploaded)
                                                    <img class="qr-mini" src="{{ config('services.backend_api.asset_url', 'http://localhost:5000').$qrCode }}" alt="Uploaded QR {{ $inv['name'] }}" title="Uploaded QR">
                                                @elseif (!empty($qrCode) && !$isUploaded)
                                                    {{-- Some other string saved as QR --}}
                                                    <span class="status-pill" style="align-self: center;">{{ $qrCode }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="empty-cell">Belum ada data inventaris.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div id="inv-list-pagination" class="pagination-bar"></div>
                </div>

                {{-- BHP Table --}}
                <div id="section-consumable" class="item-section section-gap">
                    <h3 class="section-subtitle">Barang Habis Pakai (BHP)</h3>
                    <div class="table-wrap">
                        <table id="bhp-list-table">
                            <thead>
                                <tr>
                                    <th>Nama BHP</th>
                                    <th>Satuan</th>
                                    <th>Stok</th>
                                    <th>Min. Stok</th>
                                    <th>Kondisi Stok</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($consumables as $bhp)
                                    <tr class="bhp-list-row"
                                        data-type="consumable"
                                        data-search="{{ strtolower(($bhp['name'] ?? '') . ' ' . ($bhp['unit'] ?? '') . ' ' . ($bhp['description'] ?? '')) }}">
                                        <td><strong>{{ $bhp['name'] }}</strong></td>
                                        <td>{{ $bhp['unit'] }}</td>
                                        <td>
                                            <span class="status-pill {{ $bhp['is_low'] ? 'status-pill--danger' : 'status-pill--success' }}">
                                                {{ $bhp['stock'] }}
                                            </span>
                                        </td>
                                        <td>{{ $bhp['min_stock'] }}</td>
                                        <td>
                                            @if ($bhp['is_low'])
                                                <span class="status-pill status-pill--danger">Stok Menipis</span>
                                            @else
                                                <span class="status-pill status-pill--success">Cukup</span>
                                            @endif
                                        </td>
                                        <td class="muted-link">{{ $bhp['description'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="empty-cell">Belum ada data BHP.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div id="bhp-list-pagination" class="pagination-bar"></div>
                </div>
            </section>
        </main>
    </div>

    <script>
        (() => {
            const PAGE_SIZE = 5;

            // ── Filter tabs ──
            const tabs = document.querySelectorAll('.filter-tab');
            const secInv = document.getElementById('section-inventory');
            const secBhp = document.getElementById('section-consumable');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    const filter = tab.dataset.filter;
                    secInv.hidden = filter === 'consumable';
                    secBhp.hidden = filter === 'inventory';
                });
            });

            // ── Generic search + pagination factory ──
            function setupSearchPagination(tableId, rowSelector, searchInputId, paginationId) {
                const allRows = Array.from(document.querySelectorAll(`#${tableId} tbody ${rowSelector}`));
                let visibleRows = allRows;
                let page = 1;
                const pagBar = document.getElementById(paginationId);
                const searchInput = document.getElementById(searchInputId);

                function renderPage() {
                    const start = (page - 1) * PAGE_SIZE;
                    const end = start + PAGE_SIZE;
                    allRows.forEach(r => r.hidden = true);
                    visibleRows.slice(start, end).forEach(r => r.hidden = false);
                    renderPagination();
                }

                function renderPagination() {
                    const pages = Math.ceil(visibleRows.length / PAGE_SIZE);
                    if (pages <= 1) { pagBar.innerHTML = ''; return; }
                    let html = `<span class="page-info">Halaman ${page} dari ${pages}</span>`;
                    html += `<button class="button-secondary btn-page" ${page === 1 ? 'disabled' : ''} data-page="${page - 1}">← Sebelumnya</button>`;
                    html += `<button class="button-secondary btn-page" ${page === pages ? 'disabled' : ''} data-page="${page + 1}">Berikutnya →</button>`;
                    pagBar.innerHTML = html;
                    pagBar.querySelectorAll('.btn-page').forEach(btn => {
                        btn.addEventListener('click', () => {
                            page = Number(btn.dataset.page);
                            renderPage();
                        });
                    });
                }

                if (searchInput) {
                    searchInput.addEventListener('input', () => {
                        const q = searchInput.value.toLowerCase().trim();
                        visibleRows = q ? allRows.filter(r => r.dataset.search.includes(q)) : allRows;
                        page = 1;
                        renderPage();
                    });
                }

                renderPage();
                return { refresh: () => { visibleRows = allRows; page = 1; renderPage(); } };
            }

            const invPager = setupSearchPagination('inv-list-table', '.inv-list-row', 'item-search', 'inv-list-pagination');
            const bhpPager = setupSearchPagination('bhp-list-table', '.bhp-list-row', 'item-search', 'bhp-list-pagination');

            // When shared search input changes, refresh both
            const sharedSearch = document.getElementById('item-search');
            if (sharedSearch) {
                sharedSearch.addEventListener('input', () => {
                    invPager.refresh();
                    bhpPager.refresh();
                });
            }
        })();
    </script>
</x-layouts.app>
