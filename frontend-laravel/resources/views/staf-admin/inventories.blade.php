<x-layouts.app title="Update Inventaris">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Staf Administrasi</p>
                    <h1>Update Inventaris</h1>
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
                <div class="panel-header">
                    <div>
                        <h2>Label dan QR/Barcode</h2>
                        <p class="panel-subtitle">Data ringkas inventaris. Gunakan tombol detail untuk edit label, ruangan, kondisi, status, dan gambar QR/Barcode.</p>
                    </div>
                </div>

                @foreach ($inventories as $inventory)
                    <form id="inventory-update-{{ $inventory['_id'] }}" method="POST" enctype="multipart/form-data" action="{{ route('staf-admin.inventories.update', $inventory['_id']) }}">
                        @csrf
                        @method('PATCH')
                    </form>
                @endforeach

                <div class="table-wrap">
                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Label</th>
                                <th>QR/Barcode</th>
                                <th>Ruangan</th>
                                <th>Kondisi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inventories as $inventory)
                                @php
                                    $qrCode = $inventory['qr_code'] ?? '';
                                    $qrIsUploadedImage = str_starts_with($qrCode, '/uploads/');
                                    $roomName = collect($rooms)->firstWhere('_id', $inventory['room_id'] ?? null)['name'] ?? '-';
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $inventory['name'] }}</strong>
                                        <div class="muted-link">{{ $inventory['description'] ?? '-' }}</div>
                                    </td>
                                    <td>{{ $inventory['label_code'] ?? '-' }}</td>
                                    <td>
                                        @if ($qrIsUploadedImage)
                                            <div class="qr-chip">
                                                <img src="{{ config('services.backend_api.asset_url', 'http://localhost:5000').$qrCode }}" alt="QR/Barcode {{ $inventory['name'] }}">
                                                <span>Ada gambar</span>
                                            </div>
                                        @elseif (!empty($qrCode))
                                            <span class="status-pill">{{ $qrCode }}</span>
                                        @else
                                            <span class="muted-link">Belum ada</span>
                                        @endif
                                    </td>
                                    <td>{{ $roomName }}</td>
                                    <td><span class="status-pill">{{ $inventory['condition'] ?? '-' }}</span></td>
                                    <td><span class="status-pill">{{ $inventory['status'] ?? '-' }}</span></td>
                                    <td>
                                        <div class="icon-actions">
                                            <button type="button" class="icon-button" title="Detail" aria-label="Detail" data-open-inventory-modal="inventory-detail-{{ $inventory['_id'] }}">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
                                            <button type="button" class="icon-button" title="Edit" aria-label="Edit" data-open-inventory-modal="inventory-edit-{{ $inventory['_id'] }}">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg>
                                            </button>
                                            <form method="POST" action="{{ route('staf-admin.inventories.destroy', $inventory['_id']) }}" data-confirm-delete="Hapus inventaris {{ $inventory['name'] }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button class="icon-button danger" title="Hapus" aria-label="Hapus">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    @foreach ($inventories as $inventory)
        @php
            $qrCode = $inventory['qr_code'] ?? '';
            $qrIsUploadedImage = str_starts_with($qrCode, '/uploads/');
            $roomName = collect($rooms)->firstWhere('_id', $inventory['room_id'] ?? null)['name'] ?? '-';
        @endphp
        <div class="modal-backdrop inventory-modal-backdrop" id="inventory-detail-{{ $inventory['_id'] }}" hidden>
            <div class="inventory-modal" role="dialog" aria-modal="true" aria-labelledby="inventory-detail-title-{{ $inventory['_id'] }}">
                <div class="modal-heading-row">
                    <div>
                        <p class="modal-kicker">Detail inventaris</p>
                        <h2 id="inventory-detail-title-{{ $inventory['_id'] }}">{{ $inventory['name'] }}</h2>
                        <p class="modal-copy">{{ $inventory['description'] ?? 'Tidak ada deskripsi.' }}</p>
                    </div>
                    <button type="button" class="modal-close" data-close-inventory-modal>&times;</button>
                </div>

                <dl class="detail-list">
                    <div><dt>Nomor Label</dt><dd>{{ $inventory['label_code'] ?? '-' }}</dd></div>
                    <div><dt>Ruangan</dt><dd>{{ $roomName }}</dd></div>
                    <div><dt>Kondisi</dt><dd>{{ $inventory['condition'] ?? '-' }}</dd></div>
                    <div><dt>Status</dt><dd>{{ $inventory['status'] ?? '-' }}</dd></div>
                    <div><dt>Harga</dt><dd>Rp {{ number_format($inventory['price'] ?? 0, 0, ',', '.') }}</dd></div>
                    <div><dt>QR/Barcode</dt><dd>{{ $qrCode ?: '-' }}</dd></div>
                </dl>

                @if ($qrIsUploadedImage)
                    <img class="qr-large-preview" src="{{ config('services.backend_api.asset_url', 'http://localhost:5000').$qrCode }}" alt="QR/Barcode {{ $inventory['name'] }}">
                @endif

                <div class="modal-actions">
                    <button type="button" class="button-secondary" data-close-inventory-modal>Tutup</button>
                </div>
            </div>
        </div>

        <div class="modal-backdrop inventory-modal-backdrop" id="inventory-edit-{{ $inventory['_id'] }}" hidden>
            <div class="inventory-modal" role="dialog" aria-modal="true" aria-labelledby="inventory-title-{{ $inventory['_id'] }}">
                <div class="modal-heading-row">
                    <div>
                        <p class="modal-kicker">Detail inventaris</p>
                        <h2 id="inventory-title-{{ $inventory['_id'] }}">{{ $inventory['name'] }}</h2>
                        <p class="modal-copy">{{ $inventory['description'] ?? 'Tidak ada deskripsi.' }}</p>
                    </div>
                    <button type="button" class="modal-close" data-close-inventory-modal>&times;</button>
                </div>

                <div class="detail-grid">
                    <div class="field">
                        <label>Nomor Label</label>
                        <input form="inventory-update-{{ $inventory['_id'] }}" class="input" name="label_code" value="{{ $inventory['label_code'] }}" required>
                    </div>

                    <div class="field">
                        <label>Ruangan</label>
                        <select form="inventory-update-{{ $inventory['_id'] }}" class="input" name="room_id" required>
                            @foreach ($rooms as $room)
                                <option value="{{ $room['_id'] }}" @selected(($inventory['room_id'] ?? null) === $room['_id'])>{{ $room['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Kondisi</label>
                        <select form="inventory-update-{{ $inventory['_id'] }}" class="input" name="condition" required>
                            @foreach (['baik', 'perlu maintenance', 'rusak'] as $condition)
                                <option value="{{ $condition }}" @selected(($inventory['condition'] ?? '') === $condition)>{{ $condition }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Status</label>
                        <select form="inventory-update-{{ $inventory['_id'] }}" class="input" name="status" required>
                            @foreach (['active', 'maintenance', 'retired'] as $status)
                                <option value="{{ $status }}" @selected(($inventory['status'] ?? '') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="qr-upload-panel">
                    <input form="inventory-update-{{ $inventory['_id'] }}" type="hidden" name="existing_qr_code" value="{{ $inventory['qr_code'] ?? '' }}">
                    <div>
                        <label class="field-label">Gambar QR/Barcode</label>
                        <p class="modal-copy">Upload gambar QR atau barcode aset. Jika kosong, gambar lama tetap dipakai.</p>
                    </div>
                    @if ($qrIsUploadedImage)
                        <img class="qr-large-preview" src="{{ config('services.backend_api.asset_url', 'http://localhost:5000').$qrCode }}" alt="QR/Barcode {{ $inventory['name'] }}">
                    @elseif (!empty($qrCode))
                        <div class="notice">File lama: {{ $qrCode }}</div>
                    @endif
                    <input form="inventory-update-{{ $inventory['_id'] }}" class="input file-input" name="qr_code" type="file" accept="image/*">
                </div>

                <div class="modal-actions">
                    <button type="button" class="button-secondary" data-close-inventory-modal>Batal</button>
                    <button form="inventory-update-{{ $inventory['_id'] }}" class="button-primary">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        (() => {
            document.querySelectorAll('[data-open-inventory-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const modal = document.getElementById(button.dataset.openInventoryModal);
                    if (!modal) return;
                    modal.hidden = false;
                    document.body.classList.add('modal-open');
                });
            });

            const closeModal = (modal) => {
                modal.hidden = true;
                document.body.classList.remove('modal-open');
            };

            document.querySelectorAll('.inventory-modal-backdrop').forEach((modal) => {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) closeModal(modal);
                });
                modal.querySelectorAll('[data-close-inventory-modal]').forEach((button) => {
                    button.addEventListener('click', () => closeModal(modal));
                });
            });
        })();
    </script>
</x-layouts.app>
