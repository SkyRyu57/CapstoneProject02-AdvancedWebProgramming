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
                    <p>Tahun {{ $draft['fiscal_year'] }} · status <strong>{{ $draft['status'] }}</strong>{{ $draft['locked'] ? ' · locked' : '' }}</p>
                    @if (! $draft['locked'])
                        <form method="POST" action="{{ route('kaprodi.drafts.finalize', $draft['_id']) }}" class="inline-form">
                            @csrf
                            @method('PATCH')
                            <button class="button-primary">Finalisasi Draf</button>
                        </form>
                    @endif
                </section>

                <section class="data-panel">
                    <div class="panel-header">
                        <h2>Item Pengadaan</h2>
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
                                    <tr>
                                        <td>
                                            <strong>{{ $item['name'] }}</strong>
                                            <div class="muted-link">{{ $item['purchase_link'] ?? '-' }}</div>
                                        </td>
                                        <td>{{ $item['item_type'] }}</td>
                                        <td>Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td><span class="status-pill">{{ $item['approval_status'] }}</span></td>
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
</x-layouts.app>
