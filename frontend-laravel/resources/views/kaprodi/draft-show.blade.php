<x-layouts.app title="Detail Review Draf">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Ketua Program Studi</p>
                    <h1>Detail Review Draf</h1>
                </div>
                <a href="{{ route('kaprodi.drafts.index') }}" class="button-secondary">Kembali</a>
            </div>
        </header>

        <main class="container content">
            @include('components.status')

            @if ($error)
                <div class="notice danger">{{ $error }}</div>
            @elseif ($draft)
                <section class="dashboard-intro">
                    @php
                        $statusLabels = [
                            'draft' => 'Draf',
                            'submitted' => 'Diajukan',
                            'finalized' => 'Difinalisasi'
                        ];
                        $statusLabel = $statusLabels[$draft['status']] ?? ucfirst($draft['status']);
                    @endphp
                    <p>Tahun {{ $draft['fiscal_year'] }} · status <strong>{{ $statusLabel }}</strong>{{ $draft['locked'] ? ' · locked' : '' }}</p>
                    @if (! $draft['locked'])
                        <form method="POST" action="{{ route('kaprodi.drafts.finalize', $draft['_id']) }}" class="inline-form">
                            @csrf
                            @method('PATCH')
                            <button class="button-primary">Finalisasi Draf</button>
                        </form>
                    @endif
                </section>

                <section class="data-panel">
                    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <h2>Item Pengadaan</h2>
                        <input type="text" id="item-search" class="input" placeholder="Cari barang, tipe, link..." style="max-width: 300px;">
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Tipe</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Status Review</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($draft['items'] as $item)
                                    <tr class="item-row" data-search="{{ strtolower($item['name'] . ' ' . $item['item_type'] . ' ' . ($item['purchase_link'] ?? '') . ' ' . $item['approval_status']) }}">
                                        <td>
                                            <strong>{{ $item['name'] }}</strong>
                                            <div class="muted-link">{{ $item['purchase_link'] ?? '-' }}</div>
                                        </td>
                                        <td>{{ $item['item_type'] }}</td>
                                        <td>Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td>{{ $item['quantity'] }}</td>
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
                                            @if ($draft['locked'])
                                                <span class="muted-link">Final</span>
                                            @else
                                                <form method="POST" action="{{ route('kaprodi.drafts.items.review', [$draft['_id'], $item['_id']]) }}" class="button-row">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button name="approval_status" value="approved" class="button-primary">Setujui</button>
                                                    <button name="approval_status" value="rejected" class="button-secondary">Tolak</button>
                                                    <button name="approval_status" value="pending" class="button-secondary">Pending</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
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
