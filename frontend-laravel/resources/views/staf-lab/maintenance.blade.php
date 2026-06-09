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
                <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <h2>Riwayat Log Maintenance</h2>
                    <input type="text" id="log-search" class="input" placeholder="Cari inventaris, tanggal, atau deskripsi..." style="max-width: 300px;">
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Inventaris ID</th>
                                <th>Tanggal</th>
                                <th>Kondisi Sebelum</th>
                                <th>Kondisi Sesudah</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                @php
                                    $invName = collect($inventories)
                                        ->firstWhere('_id', $log['inventory_item_id'] ?? null)['name']
                                        ?? 'Inventaris #' . ($log['inventory_item_id'] ?? '-');
                                @endphp
                                <tr class="log-row" data-search="{{ strtolower($invName . ' ' . (isset($log['maintenance_date']) ? substr($log['maintenance_date'], 0, 10) : '') . ' ' . ($log['description'] ?? '')) }}">
                                    <td>{{ $invName }}</td>
                                    <td>{{ isset($log['maintenance_date']) ? substr($log['maintenance_date'], 0, 10) : '-' }}</td>
                                    <td><span class="status-pill">{{ $log['condition_before'] ?? '-' }}</span></td>
                                    <td><span class="status-pill">{{ $log['condition_after'] ?? '-' }}</span></td>
                                    <td>{{ $log['description'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-cell">Belum ada log maintenance.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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

            const logSearch = document.getElementById('log-search');
            if (logSearch) {
                logSearch.addEventListener('input', (e) => {
                    const query = e.target.value.toLowerCase();
                    document.querySelectorAll('.log-row').forEach(row => {
                        const text = row.dataset.search || '';
                        row.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }
        })();
    </script>
</x-layouts.app>
