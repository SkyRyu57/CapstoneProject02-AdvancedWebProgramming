<x-layouts.app title="Login Lab Asset">
    <main class="auth-shell">
        <section class="auth-main">
            <div class="auth-card-wrap">
                <div class="page-kicker">Inventaris Laboratorium</div>
                <div class="page-heading">
                    <h1>Masuk ke sistem aset dan BHP</h1>
                    <p>Akun dibuat oleh administrator. Gunakan kredensial yang sudah diberikan sesuai role Anda.</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="form-card">
                    @csrf

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                            class="input">
                        @error('email')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" required
                            class="input">
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('forgot-account') }}">Lupa akun?</a>
                        <button type="submit" class="button-primary">Login</button>
                    </div>
                </form>

                <div class="notice">
                    Demo: <strong>nadia@kampus.ac.id</strong> sampai <strong>bagas@kampus.ac.id</strong>, password <strong>password_demo</strong>.
                </div>
            </div>
        </section>

        <section class="auth-side">
            <div>
                <div class="side-badge">Capstone 2</div>
                <h2>Digitalisasi aset, pengadaan, maintenance, dan stok habis pakai.</h2>
            </div>

            <div class="side-list">
                <div>Administrator mengelola pengguna dan ruangan.</div>
                <div>Kaprodi melakukan review sampai finalisasi pengadaan.</div>
                <div>Staf lab mencatat maintenance dan pemakaian BHP.</div>
            </div>
        </section>
    </main>
</x-layouts.app>
