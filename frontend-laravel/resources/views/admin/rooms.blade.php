<x-layouts.app title="Kelola Ruangan">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Administrator</p>
                    <h1>Kelola Ruangan</h1>
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
                <h2>Tambah Ruangan</h2>
                <form method="POST" action="{{ route('admin.rooms.store') }}" class="form-grid two">
                    @csrf
                    <div class="field">
                        <label>Nama Ruangan</label>
                        <input class="input" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="field">
                        <label>Deskripsi</label>
                        <input class="input" name="description" value="{{ old('description') }}">
                    </div>
                    <button class="button-primary">Simpan Ruangan</button>
                </form>
            </section>

            <section class="data-panel section-gap">
                <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <h2>Daftar Ruangan</h2>
                    <input type="text" id="room-search" class="input" placeholder="Cari nama atau deskripsi..." style="max-width: 300px;">
                </div>
                @foreach ($rooms as $room)
                    <form id="room-update-{{ $room['_id'] }}" method="POST" action="{{ route('admin.rooms.update', $room['_id']) }}">
                        @csrf
                        @method('PATCH')
                    </form>
                @endforeach
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rooms as $room)
                                <tr class="room-row" data-search="{{ strtolower($room['name'] . ' ' . ($room['description'] ?? '')) }}">
                                    <td><input form="room-update-{{ $room['_id'] }}" class="input table-input" name="name" value="{{ $room['name'] }}" required></td>
                                    <td><input form="room-update-{{ $room['_id'] }}" class="input table-input" name="description" value="{{ $room['description'] ?? '' }}"></td>
                                    <td>
                                        <div class="action-cell">
                                            <button form="room-update-{{ $room['_id'] }}" class="button-secondary">Update</button>
                                            <form method="POST" action="{{ route('admin.rooms.destroy', $room['_id']) }}" data-confirm-delete="Hapus ruangan {{ $room['name'] }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button class="button-danger">Hapus</button>
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

    <script>
        const roomSearch = document.getElementById('room-search');
        if (roomSearch) {
            roomSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('.room-row').forEach(row => {
                    const text = row.dataset.search || '';
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    </script>
</x-layouts.app>
