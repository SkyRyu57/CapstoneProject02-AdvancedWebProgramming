<x-layouts.app title="Log Maintenance Inventaris">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Staf Laboratorium</p>
                    <h1>Log Maintenance Inventaris</h1>
                </div>
                <div class="topbar-actions">
                    <a href="{{ route('staf-lab.consumables.index') }}" class="button-secondary">Stok BHP</a>
                    <a href="{{ route('dashboard') }}" class="button-secondary">Dashboard</a>
                </div>
            </div>
        </header>

        <main class="container content">
            @include('components.status')

            @if ($error)
                <div class="notice danger">{{ $error }}</div>
            @endif

            {{-- Form buat log baru --}}
            <section class="form-card page-form">
                <h2>Catat Log Maintenance Baru</h2>
                <form method="POST" action="{{ route('staf-lab.maintenance.store') }}" id="maintenance-form">
                    @csrf

                    <div class="form-grid">
                        <div class="field">
                            <label>Inventaris</label>
                            <select class="input" name="inventory_item_id" id="inventory-select" required>
                                <option value="">– Pilih inventaris –</option>
                                @foreach ($inventories as $inv)
                                    <option value="{{ $inv['_id'] }}"
                                        data-condition="{{ $inv['condition'] ?? 'baik' }}"
                                        @selected(old('inventory_item_id') == $inv['_id'])>
                                        {{ $inv['name'] }} ({{ $inv['label_code'] ?? 'belum berlabel' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label>Tanggal Maintenance</label>
                            <input class="input" name="maintenance_date" type="date"
                                value="{{ old('maintenance_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="field">
                            <label>Kondisi Sebelum</label>
                            <select class="input" name="condition_before" id="condition-before" required>
                                @foreach (['baik', 'perlu maintenance', 'rusak'] as $c)
                                    <option value="{{ $c }}" @selected(old('condition_before') === $c)>
                                        {{ ucfirst($c) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label>Kondisi Setelah</label>
                            <select class="input" name="condition_after" required>
                                @foreach (['baik', 'perlu maintenance', 'rusak'] as $c)
                                    <option value="{{ $c }}" @selected(old('condition_after', 'baik') === $c)>
                                        {{ ucfirst($c) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label>Status Inventaris Setelah Maintenance</label>
                            <select class="input" name="status_after">
                                <option value="">– Tetap sama –</option>
                                <option value="active" @selected(old('status_after') === 'active')>Active</option>
                                <option value="maintenance" @selected(old('status_after') === 'maintenance')>Maintenance</option>
                                <option value="retired" @selected(old('status_after') === 'retired')>Retired</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Deskripsi Pekerjaan</label>
                            <input class="input" name="description"
                                value="{{ old('description') }}"
                                placeholder="Contoh: Pembersihan kipas dan penggantian thermal paste">
                        </div>
                    </div>

                    {{-- BHP yang digunakan --}}
                    <div class="panel-header" style="margin-top:1.5rem">
                        <h3>BHP yang Digunakan (opsional)</h3>
                        <button type="button" class="button-secondary" id="add-bhp-btn">+ Tambah BHP</button>
                    </div>
                    <div id="bhp-rows">
                        {{-- Baris BHP diisi via JS --}}
                    </div>

                    <button class="button-primary" style="margin-top:1.5rem">Catat Log Maintenance</button>
                </form>
            </section>

            {{-- Riwayat log --}}
            <section class="data-panel section-gap">
                <div class="panel-header">
                    <div>
                        <h2>Riwayat Log Maintenance</h2>
                    </div>
                    <div class="search-bar-wrap">
                        <input id="maint-search" class="input search-input" type="search"
                            placeholder="🔍 Cari inventaris, kondisi, deskripsi…" autocomplete="off">
                    </div>
                </div>
                <div class="table-wrap">
                    <table id="maintenance-table">
                        <thead>
                            <tr>
                                <th>Inventaris</th>
                                <th>Tanggal</th>
                                <th>Kondisi Sebelum</th>
                                <th>Kondisi Sesudah</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                @php
                                    $invName = collect($inventories)
                                        ->firstWhere('_id', $log['inventory_item_id'] ?? null)['name']
                                        ?? 'Inventaris #' . ($log['inventory_item_id'] ?? '-');
                                    $condBefore = $log['condition_before'] ?? '-';
                                    $condAfter  = $log['condition_after'] ?? '-';
                                    $condBeforeClass = $condBefore === 'baik' ? 'status-pill--success' : ($condBefore === 'rusak' ? 'status-pill--danger' : 'status-pill--warning');
                                    $condAfterClass  = $condAfter  === 'baik' ? 'status-pill--success' : ($condAfter  === 'rusak' ? 'status-pill--danger' : 'status-pill--warning');
                                @endphp
                                <tr class="maint-row"
                                    data-search="{{ strtolower($invName . ' ' . $condBefore . ' ' . $condAfter . ' ' . ($log['description'] ?? '')) }}"
                                    data-log-id="{{ $log['_id'] }}">
                                    <td><strong>{{ $invName }}</strong></td>
                                    <td>{{ isset($log['maintenance_date']) ? date('d M Y', strtotime($log['maintenance_date'])) : '-' }}</td>
                                    <td><span class="status-pill {{ $condBeforeClass }}">{{ ucfirst($condBefore) }}</span></td>
                                    <td><span class="status-pill {{ $condAfterClass }}">{{ ucfirst($condAfter) }}</span></td>
                                    <td>{{ Str::limit($log['description'] ?? '-', 50) }}</td>
                                    <td>
                                        <div class="action-cell">
                                            <a class="button-secondary"
                                                href="{{ route('staf-lab.maintenance.show', $log['_id']) }}">
                                                Detail
                                            </a>
                                            <button type="button" class="button-secondary btn-expand-log"
                                                data-target="expand-log-{{ $log['_id'] }}">
                                                ▼
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="expand-row maint-row"
                                    id="expand-log-{{ $log['_id'] }}"
                                    data-search="{{ strtolower($invName . ' ' . $condBefore . ' ' . $condAfter . ' ' . ($log['description'] ?? '')) }}"
                                    hidden>
                                    <td colspan="6" class="expand-cell">
                                        <div class="expand-inner">
                                            <p class="expand-label">Deskripsi Lengkap</p>
                                            <p>{{ $log['description'] ?? 'Tidak ada deskripsi.' }}</p>
                                            <p class="expand-label" style="margin-top:.5rem">Dicatat:</p>
                                            <p>{{ isset($log['created_at']) ? date('d M Y H:i', strtotime($log['created_at'])) : '-' }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-cell">Belum ada log maintenance.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="maint-pagination" class="pagination-bar"></div>
            </section>
        </main>
    </div>

    <script>
        (() => {
            const consumables = @json($consumables);
            let bhpIndex = 0;

            const inventorySelect = document.getElementById('inventory-select');
            const conditionBefore = document.getElementById('condition-before');

            // Auto-isi kondisi sebelum saat pilih inventaris
            inventorySelect.addEventListener('change', () => {
                const selected = inventorySelect.options[inventorySelect.selectedIndex];
                const condition = selected.dataset.condition || 'baik';
                Array.from(conditionBefore.options).forEach((opt) => {
                    opt.selected = opt.value === condition;
                });
            });

            document.getElementById('add-bhp-btn').addEventListener('click', () => {
                const container = document.getElementById('bhp-rows');
                const row = document.createElement('div');
                row.className = 'bhp-usage-row';
                row.innerHTML = `
                    <select class="input" name="consumable_usages[${bhpIndex}][consumable_id]" required>
                        <option value="">– Pilih BHP –</option>
                        ${consumables.map(c => `<option value="${c._id}">${c.name} (stok: ${c.stock} ${c.unit})</option>`).join('')}
                    </select>
                    <input class="input table-input small" type="number" min="1"
                        name="consumable_usages[${bhpIndex}][quantity_used]"
                        placeholder="Jumlah pakai" required>
                    <button type="button" class="button-danger btn-remove-bhp">Hapus</button>
                `;
                row.querySelector('.btn-remove-bhp').addEventListener('click', () => row.remove());
                container.appendChild(row);
                bhpIndex++;
            });

            // ── Expand toggle ──
            document.querySelectorAll('.btn-expand-log').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const target = document.getElementById(btn.dataset.target);
                    if (!target) return;
                    target.hidden = !target.hidden;
                    btn.textContent = target.hidden ? '▼' : '▲';
                });
            });

            // ── Search & pagination for maintenance history ──
            const PAGE_SIZE = 5;
            const allMainRows = Array.from(document.querySelectorAll('#maintenance-table tbody .maint-row:not(.expand-row)'));
            let visibleMaintRows = allMainRows;
            let maintPage = 1;
            const maintPagBar = document.getElementById('maint-pagination');
            const maintSearch = document.getElementById('maint-search');

            function renderMaintPage() {
                const start = (maintPage - 1) * PAGE_SIZE;
                const end = start + PAGE_SIZE;
                allMainRows.forEach(r => r.hidden = true);
                document.querySelectorAll('#maintenance-table .expand-row').forEach(r => r.hidden = true);
                visibleMaintRows.slice(start, end).forEach(r => r.hidden = false);
                renderMaintPagination();
            }

            function renderMaintPagination() {
                const pages = Math.ceil(visibleMaintRows.length / PAGE_SIZE);
                if (pages <= 1) { maintPagBar.innerHTML = ''; return; }
                let html = `<span class="page-info">Halaman ${maintPage} dari ${pages}</span>`;
                html += `<button class="button-secondary btn-page" ${maintPage === 1 ? 'disabled' : ''} data-page="${maintPage - 1}">← Sebelumnya</button>`;
                html += `<button class="button-secondary btn-page" ${maintPage === pages ? 'disabled' : ''} data-page="${maintPage + 1}">Berikutnya →</button>`;
                maintPagBar.innerHTML = html;
                maintPagBar.querySelectorAll('.btn-page').forEach(btn => {
                    btn.addEventListener('click', () => {
                        maintPage = Number(btn.dataset.page);
                        renderMaintPage();
                    });
                });
            }

            if (maintSearch) {
                maintSearch.addEventListener('input', () => {
                    const q = maintSearch.value.toLowerCase().trim();
                    visibleMaintRows = q ? allMainRows.filter(r => r.dataset.search.includes(q)) : allMainRows;
                    maintPage = 1;
                    renderMaintPage();
                });
            }

            renderMaintPage();
        })();
    </script>
</x-layouts.app>

