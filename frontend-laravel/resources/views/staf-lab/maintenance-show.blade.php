<x-layouts.app title="Detail Log Maintenance">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Staf Laboratorium</p>
                    <h1>Detail Log Maintenance</h1>
                </div>
                <a href="{{ route('staf-lab.maintenance.index') }}" class="button-secondary">← Kembali</a>
            </div>
        </header>

        <main class="container content">
            @include('components.status')

            @if ($error)
                <div class="notice danger">{{ $error }}</div>
            @elseif ($log)
                {{-- Info utama --}}
                <section class="form-card">
                    <div class="panel-header">
                        <div>
                            <h2>{{ $log['inventory_name'] ?? 'Inventaris' }}</h2>
                            @if (!empty($log['inventory_label']))
                                <p class="panel-subtitle">Label: <strong>{{ $log['inventory_label'] }}</strong></p>
                            @endif
                        </div>
                        <span class="status-pill status-pill--info">Log #{{ $log['_id'] }}</span>
                    </div>

                    <dl class="detail-list">
                        <div>
                            <dt>Tanggal Maintenance</dt>
                            <dd>{{ isset($log['maintenance_date']) ? date('d M Y', strtotime($log['maintenance_date'])) : '-' }}</dd>
                        </div>
                        <div>
                            <dt>Kondisi Sebelum</dt>
                            <dd>
                                <span class="status-pill {{ $log['condition_before'] === 'baik' ? 'status-pill--success' : ($log['condition_before'] === 'rusak' ? 'status-pill--danger' : 'status-pill--warning') }}">
                                    {{ ucfirst($log['condition_before'] ?? '-') }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt>Kondisi Sesudah</dt>
                            <dd>
                                <span class="status-pill {{ $log['condition_after'] === 'baik' ? 'status-pill--success' : ($log['condition_after'] === 'rusak' ? 'status-pill--danger' : 'status-pill--warning') }}">
                                    {{ ucfirst($log['condition_after'] ?? '-') }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt>Deskripsi Pekerjaan</dt>
                            <dd>{{ $log['description'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt>Dicatat Pada</dt>
                            <dd>{{ isset($log['created_at']) ? date('d M Y H:i', strtotime($log['created_at'])) : '-' }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- BHP yang digunakan --}}
                <section class="data-panel section-gap">
                    <div class="panel-header">
                        <h2>BHP yang Digunakan</h2>
                        <span class="status-pill">{{ count($usages) }} item</span>
                    </div>

                    @if (count($usages) > 0)
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nama BHP</th>
                                        <th>Satuan</th>
                                        <th>Jumlah Dipakai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($usages as $usage)
                                        <tr>
                                            <td><strong>{{ $usage['consumable_name'] }}</strong></td>
                                            <td>{{ $usage['consumable_unit'] }}</td>
                                            <td>
                                                <span class="status-pill status-pill--warning">
                                                    {{ $usage['quantity_used'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="empty-cell" style="padding:1rem">Tidak ada BHP yang digunakan pada maintenance ini.</p>
                    @endif
                </section>
            @endif
        </main>
    </div>
</x-layouts.app>
