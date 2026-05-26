<x-layouts.app title="Kelola Pengguna">
    <div class="app-shell">
        <header class="topbar">
            <div class="container topbar-inner">
                <div>
                    <p class="page-kicker">Administrator</p>
                    <h1>Kelola Pengguna</h1>
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
                <h2>Tambah Akun</h2>
                <form method="POST" action="{{ route('admin.users.store') }}" class="form-grid">
                    @csrf
                    <div class="field">
                        <label>Nama</label>
                        <input class="input" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input class="input" name="email" type="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <input class="input" name="password" type="password" required>
                    </div>
                    <div class="field">
                        <label>Role</label>
                        <select class="input" name="role" required>
                            @foreach ($roles as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="button-primary">Simpan Akun</button>
                </form>
            </section>

            <section class="data-panel section-gap">
                <div class="panel-header">
                    <h2>Daftar Pengguna</h2>
                </div>
                @foreach ($users as $account)
                    @php($accountId = $account['id'] ?? $account['_id'])
                    <form id="user-update-{{ $accountId }}" method="POST" action="{{ route('admin.users.update', $accountId) }}">
                        @csrf
                        @method('PATCH')
                    </form>
                @endforeach
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Password Baru</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $account)
                                @php($accountId = $account['id'] ?? $account['_id'])
                                <tr>
                                    <td><input form="user-update-{{ $accountId }}" class="input table-input" name="name" value="{{ $account['name'] }}" required></td>
                                    <td><input form="user-update-{{ $accountId }}" class="input table-input" name="email" type="email" value="{{ $account['email'] }}" required></td>
                                    <td>
                                        <select form="user-update-{{ $accountId }}" class="input table-input" name="role" required>
                                            @foreach ($roles as $key => $label)
                                                <option value="{{ $key }}" @selected($account['role'] === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input form="user-update-{{ $accountId }}" class="input table-input" name="password" type="password" placeholder="Kosongkan jika tetap"></td>
                                    <td>
                                        <div class="action-cell">
                                            <button form="user-update-{{ $accountId }}" class="button-secondary">Update</button>
                                            <form method="POST" action="{{ route('admin.users.destroy', $accountId) }}" data-confirm-delete="Hapus pengguna {{ $account['name'] }}?">
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
</x-layouts.app>
