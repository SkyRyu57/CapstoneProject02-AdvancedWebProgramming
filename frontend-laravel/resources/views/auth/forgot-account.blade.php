<x-layouts.app title="Lupa Akun">
    <main class="center-shell">
        <div class="auth-card-wrap">
            <a href="{{ route('login') }}" class="text-link">Kembali ke login</a>
            <div class="page-heading compact">
                <h1>Lupa akun</h1>
                <p>Tidak ada registrasi mandiri. Jika lupa akses, sistem akan mengarahkan Anda untuk menghubungi administrator.</p>
            </div>

            @if (session('status'))
                <div class="notice success">
                    <p>{{ session('status') }}</p>
                    @if (session('admin_contact'))
                        <p><strong>Kontak admin:</strong> {{ session('admin_contact') }}</p>
                    @endif
                    @if (session('account_hint'))
                        <p class="mt-2">Akun: {{ session('account_hint.name') }} - {{ session('account_hint.role') }}</p>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('forgot-account.store') }}" class="form-card">
                @csrf
                <div class="field">
                    <label for="email">Email kampus</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                        class="input">
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="button-primary full">Cek bantuan akun</button>
            </form>
        </div>
    </main>
</x-layouts.app>
