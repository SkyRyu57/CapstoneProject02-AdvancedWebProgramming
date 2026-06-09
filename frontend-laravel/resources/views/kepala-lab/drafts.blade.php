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
                <div class="panel-header">
                    <h2>Draf yang Pernah Diajukan</h2>
                    <div class="search-bar-wrap">
                        <input id="draft-search" class="input search-input" type="search"
                            placeholder="🔍 Cari tahun, status, catatan…" autocomplete="off">
                    </div>
                </div>

                <div class="table-wrap">
                    <table id="drafts-table">
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
                                @php
                                    $statusClass = match($draft['status']) {
                                        'draft'     => 'status-pill--warning',
                                        'submitted' => 'status-pill--info',
                                        'finalized' => 'status-pill--locked',
                                        default     => '',
                                    };
                                    $statusLabel = match($draft['status']) {
                                        'draft'     => '✏️ Draft',
                                        'submitted' => '📤 Terkirim',
                                        'finalized' => '✅ Difinalisasi',
                                        default     => $draft['status'],
                                    };
                                    $itemCount = count($draft['items'] ?? []);
                                @endphp
                                <tr class="draft-row"
                                    data-search="{{ strtolower($draft['fiscal_year'] . ' ' . $draft['status'] . ' ' . ($draft['notes'] ?? '')) }}">
                                    <td><strong>{{ $draft['fiscal_year'] }}</strong></td>
                                    <td>
                                        <span class="status-pill {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td>{{ $itemCount }} item</td>
                                    <td>{{ $draft['notes'] ?? '-' }}</td>
                                    <td>
                                        <div class="action-cell">
                                            <a class="button-secondary"
                                                href="{{ route('kepala-lab.drafts.show', $draft['_id']) }}">
                                                {{ $draft['status'] === 'draft' ? 'Edit' : 'Lihat' }}
                                            </a>
                                            @if ($draft['status'] === 'draft')
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
                                {{-- Expand row: show items --}}
                                @if ($itemCount > 0)
                                    <tr class="expand-row draft-row"
                                        data-search="{{ strtolower($draft['fiscal_year'] . ' ' . $draft['status'] . ' ' . ($draft['notes'] ?? '')) }}"
                                        id="expand-draft-{{ $draft['_id'] }}" hidden>
                                        <td colspan="5" class="expand-cell">
                                            <div class="expand-inner">
                                                <p class="expand-label">Item Pengadaan ({{ $itemCount }})</p>
                                                <ul class="item-mini-list">
                                                    @foreach ($draft['items'] as $item)
                                                        <li>
                                                            <span class="status-pill">{{ $item['item_type'] }}</span>
                                                            {{ $item['name'] }}
                                                            &middot; {{ $item['quantity'] }} unit
                                                            &middot; <em>{{ $item['approval_status'] }}</em>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-cell">Belum ada draf pengadaan. Buat draf baru di atas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination controls --}}
                <div id="drafts-pagination" class="pagination-bar"></div>
            </section>
        </main>
    </div>

    <script>
        (() => {
            const PAGE_SIZE = 5;
            const allRows = Array.from(document.querySelectorAll('#drafts-table tbody .draft-row:not(.expand-row)'));
            let visibleRows = allRows;
            let currentPage = 1;

            const paginationBar = document.getElementById('drafts-pagination');
            const searchInput = document.getElementById('draft-search');

            function renderPage() {
                const start = (currentPage - 1) * PAGE_SIZE;
                const end = start + PAGE_SIZE;

                allRows.forEach(row => { row.hidden = true; });
                document.querySelectorAll('#drafts-table .expand-row').forEach(r => { r.hidden = true; });

                visibleRows.slice(start, end).forEach(row => {
                    row.hidden = false;
                });

                renderPagination();
            }

            function renderPagination() {
                const total = visibleRows.length;
                const pages = Math.ceil(total / PAGE_SIZE);
                if (pages <= 1) { paginationBar.innerHTML = ''; return; }

                let html = `<span class="page-info">Halaman ${currentPage} dari ${pages}</span>`;
                html += `<button class="button-secondary btn-page" ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">← Sebelumnya</button>`;
                html += `<button class="button-secondary btn-page" ${currentPage === pages ? 'disabled' : ''} data-page="${currentPage + 1}">Berikutnya →</button>`;
                paginationBar.innerHTML = html;

                paginationBar.querySelectorAll('.btn-page').forEach(btn => {
                    btn.addEventListener('click', () => {
                        currentPage = Number(btn.dataset.page);
                        renderPage();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                });
            }

            searchInput.addEventListener('input', () => {
                const q = searchInput.value.toLowerCase().trim();
                visibleRows = q
                    ? allRows.filter(r => r.dataset.search.includes(q))
                    : allRows;
                currentPage = 1;
                renderPage();
            });

            // Expand/collapse on row click
            document.querySelectorAll('#drafts-table tbody .draft-row:not(.expand-row)').forEach(row => {
                const draftId = row.querySelector('[href*="/procurement-drafts/"]')?.href?.match(/\/(\d+)$/)?.[1];
                if (!draftId) return;
                const expandRow = document.getElementById(`expand-draft-${draftId}`);
                if (!expandRow) return;

                row.style.cursor = 'pointer';
                row.title = 'Klik untuk lihat item';
                row.addEventListener('click', (e) => {
                    if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') return;
                    expandRow.hidden = !expandRow.hidden;
                });
            });

            renderPage();
        })();
    </script>
</x-layouts.app>
