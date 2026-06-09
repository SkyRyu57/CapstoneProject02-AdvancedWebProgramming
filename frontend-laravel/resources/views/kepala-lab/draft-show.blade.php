<x-layouts.app title="Detail Draf Pengadaan">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Kepala Laboratorium</p>
                    <h1>Detail Draf Pengadaan</h1>
                </div>
                <a href="{{ route('kepala-lab.drafts.index') }}" class="button-secondary">Kembali</a>
            </div>
        </header>

        <main class="container content">
            @include('components.status')

            @if ($error)
                <div class="notice danger">{{ $error }}</div>
            @elseif ($draft)
                {{-- Info draf & form edit --}}
                <section class="form-card">
                    <div class="panel-header">
                        <div>
                            <h2>Informasi Draf</h2>
                            <p class="panel-subtitle">
                                @php
                                    $statusLabels = [
                                        'draft' => 'Draf',
                                        'submitted' => 'Diajukan',
                                        'finalized' => 'Difinalisasi'
                                    ];
                                    $statusLabel = $statusLabels[$draft['status']] ?? ucfirst($draft['status']);
                                @endphp
                                Tahun {{ $draft['fiscal_year'] }} &middot;
                                Status <strong>{{ $statusLabel }}</strong>
                                @if ($draft['locked'])
                                    &middot; <span class="status-pill status-pill--locked">🔒 Terkunci</span>
                                @endif
                            </p>
                        </div>
                        @if ($draft['status'] === 'draft')
                            <form method="POST" action="{{ route('kepala-lab.drafts.submit', $draft['_id']) }}" onsubmit="return confirm('Apakah Anda yakin ingin men-submit draf ini? Setelah di-submit, draf tidak dapat diubah lagi.');">
                                @csrf
                                @method('PATCH')
                                <button class="button-primary">Submit Draf</button>
                            </form>
                        @endif
                    </div>

                    @if (!$draft['locked'])
                        <form method="POST" action="{{ route('kepala-lab.drafts.update', $draft['_id']) }}"
                            class="form-grid">
                            @csrf
                            @method('PATCH')
                            <div class="field">
                                <label>Tahun Anggaran</label>
                                <input class="input" name="fiscal_year" type="number" min="2000" max="2100"
                                    value="{{ old('fiscal_year', $draft['fiscal_year']) }}" required>
                            </div>
                            <div class="field">
                                <label>Catatan</label>
                                <input class="input" name="notes"
                                    value="{{ old('notes', $draft['notes'] ?? '') }}"
                                    placeholder="Catatan pengadaan">
                            </div>
                            <button class="button-secondary">Perbarui Draf</button>
                        </form>
                    @endif
                </section>

                {{-- Tabel item --}}
                <section class="data-panel section-gap">
                    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <h2>Daftar Item Pengadaan</h2>
                        <input type="text" id="item-search" class="input" placeholder="Cari barang, tipe, link..." style="max-width: 300px;">
                    </div>

                    @if (!$draft['locked'])
                        @foreach ($draft['items'] as $item)
                            <form id="item-update-{{ $item['_id'] }}"
                                method="POST"
                                action="{{ route('kepala-lab.drafts.items.update', [$draft['_id'], $item['_id']]) }}">
                                @csrf
                                @method('PATCH')
                            </form>
                        @endforeach
                    @endif

                    <div class="table-wrap">
                        <table class="compact-table">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Tipe</th>
                                    <th>Harga Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Link Pembelian</th>
                                    <th>Barang Digantikan</th>
                                    <th>Status</th>
                                    @if (!$draft['locked'])
                                        <th>Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($draft['items'] as $item)
                                    @php
                                        $replacedName = collect($inventories)
                                            ->firstWhere('_id', $item['replacement_inventory_id'] ?? null)['name'] ?? '-';
                                    @endphp
                                    <tr class="item-row" data-search="{{ strtolower($item['name'] . ' ' . $item['item_type'] . ' ' . ($item['purchase_link'] ?? '') . ' ' . $replacedName . ' ' . $item['approval_status']) }}">
                                        @if ($draft['locked'])
                                            <td><strong>{{ $item['name'] }}</strong></td>
                                            <td><span class="status-pill">{{ $item['item_type'] }}</span></td>
                                            <td>Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                            <td>{{ $item['quantity'] }}</td>
                                            <td>
                                                @if (!empty($item['purchase_link']))
                                                    <a class="muted-link" href="{{ $item['purchase_link'] }}" target="_blank" rel="noopener">Link</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $replacedName }}</td>
                                            @php
                                                $approvalLabels = [
                                                    'pending' => 'Pending',
                                                    'approved' => 'Disetujui',
                                                    'rejected' => 'Ditolak'
                                                ];
                                                $approvalLabel = $approvalLabels[$item['approval_status']] ?? ucfirst($item['approval_status']);
                                            @endphp
                                            <td><span class="status-pill">{{ $approvalLabel }}</span></td>
                                        @else
                                            <td>
                                                <input form="item-update-{{ $item['_id'] }}"
                                                    class="input table-input"
                                                    name="name"
                                                    value="{{ $item['name'] }}"
                                                    required>
                                            </td>
                                            <td>
                                                <select form="item-update-{{ $item['_id'] }}"
                                                    class="input table-input"
                                                    name="item_type"
                                                    required>
                                                    <option value="inventory" @selected($item['item_type'] === 'inventory')>Inventaris</option>
                                                    <option value="consumable" @selected($item['item_type'] === 'consumable')>BHP</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input form="item-update-{{ $item['_id'] }}"
                                                    class="input table-input small"
                                                    name="price"
                                                    type="number"
                                                    min="0"
                                                    value="{{ $item['price'] }}"
                                                    required>
                                            </td>
                                            <td>
                                                <input form="item-update-{{ $item['_id'] }}"
                                                    class="input table-input small"
                                                    name="quantity"
                                                    type="number"
                                                    min="1"
                                                    value="{{ $item['quantity'] }}"
                                                    required>
                                            </td>
                                            <td>
                                                <input form="item-update-{{ $item['_id'] }}"
                                                    class="input table-input"
                                                    name="purchase_link"
                                                    value="{{ $item['purchase_link'] ?? '' }}"
                                                    placeholder="https://...">
                                            </td>
                                            <td>
                                                <select form="item-update-{{ $item['_id'] }}"
                                                    class="input table-input"
                                                    name="replacement_inventory_id">
                                                    <option value="">– Tidak ada –</option>
                                                    @foreach ($inventories as $inv)
                                                        <option value="{{ $inv['_id'] }}"
                                                            @selected(($item['replacement_inventory_id'] ?? null) == $inv['_id'])>
                                                            {{ $inv['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            @php
                                                $approvalLabels = [
                                                    'pending' => 'Pending',
                                                    'approved' => 'Disetujui',
                                                    'rejected' => 'Ditolak'
                                                ];
                                                $approvalLabel = $approvalLabels[$item['approval_status']] ?? ucfirst($item['approval_status']);
                                            @endphp
                                            <td><span class="status-pill">{{ $approvalLabel }}</span></td>
                                            <td>
                                                <div class="action-cell">
                                                    <button form="item-update-{{ $item['_id'] }}"
                                                        class="button-secondary">
                                                        Simpan
                                                    </button>
                                                    <form method="POST"
                                                        action="{{ route('kepala-lab.drafts.items.destroy', [$draft['_id'], $item['_id']]) }}"
                                                        data-confirm-delete="Hapus item {{ $item['name'] }}?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="button-danger">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $draft['locked'] ? 7 : 8 }}" class="empty-cell">
                                            Belum ada item. Tambahkan item di bawah.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Form tambah item (hanya jika tidak locked) --}}
                @if (!$draft['locked'])
                    <section class="form-card section-gap">
                        <h2>Tambah Item Baru</h2>
                        <form method="POST"
                            action="{{ route('kepala-lab.drafts.items.store', $draft['_id']) }}"
                            class="form-grid">
                            @csrf
                            <div class="field">
                                <label>Tipe Barang</label>
                                <select class="input" name="item_type" required>
                                    <option value="inventory">Inventaris</option>
                                    <option value="consumable">BHP (Barang Habis Pakai)</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Nama Barang</label>
                                <input class="input" name="name" value="{{ old('name') }}"
                                    placeholder="Contoh: Router MikroTik CCR2004" required>
                            </div>
                            <div class="field">
                                <label>Harga Satuan (Rp)</label>
                                <input class="input" name="price" type="number" min="0"
                                    value="{{ old('price', 0) }}" required>
                            </div>
                            <div class="field">
                                <label>Jumlah</label>
                                <input class="input" name="quantity" type="number" min="1"
                                    value="{{ old('quantity', 1) }}" required>
                            </div>
                            <div class="field">
                                <label>Link Pembelian</label>
                                <input class="input" name="purchase_link"
                                    value="{{ old('purchase_link') }}"
                                    placeholder="https://tokopedia.com/...">
                            </div>
                            <div class="field">
                                <label>Menggantikan Inventaris (opsional)</label>
                                <select class="input" name="replacement_inventory_id">
                                    <option value="">– Tidak ada –</option>
                                    @foreach ($inventories as $inv)
                                        <option value="{{ $inv['_id'] }}"
                                            @selected(old('replacement_inventory_id') == $inv['_id'])>
                                            {{ $inv['name'] }}
                                            ({{ $inv['label_code'] ?? 'belum berlabel' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="button-primary">Tambah Item</button>
                        </form>
                    </section>
                @endif
            @endif
        </main>
    </div>

    <script>
        const itemSearch = document.getElementById('item-search');
        if (itemSearch) {
            itemSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.item-row').forEach(row => {
                    const text = row.dataset.search || '';
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    </script>
</x-layouts.app>
