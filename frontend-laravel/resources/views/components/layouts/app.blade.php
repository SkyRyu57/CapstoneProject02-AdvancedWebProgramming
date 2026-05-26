@props(['title' => 'Lab Asset'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @endif
</head>
<body class="{{ session()->has('auth_user') ? 'has-app-nav' : 'auth-body' }}">
    @if (session()->has('auth_user'))
        @include('components.app-nav')
    @endif

    {{ $slot }}

    @if (session()->has('auth_user'))
        <div class="modal-backdrop" data-delete-modal hidden>
            <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
                <div class="modal-icon">!</div>
                <div>
                    <p class="modal-kicker">Konfirmasi hapus</p>
                    <h2 id="delete-modal-title">Yakin ingin menghapus data ini?</h2>
                    <p class="modal-copy">Data yang sudah dihapus tidak bisa dikembalikan dari halaman ini.</p>
                </div>
                <div class="modal-actions">
                    <button type="button" class="button-secondary" data-delete-cancel>Batal</button>
                    <button type="button" class="button-danger" data-delete-confirm>Ya, hapus</button>
                </div>
            </div>
        </div>
        <script>
            (() => {
                const modal = document.querySelector('[data-delete-modal]');
                const title = document.getElementById('delete-modal-title');
                const cancel = document.querySelector('[data-delete-cancel]');
                const confirm = document.querySelector('[data-delete-confirm]');
                let activeForm = null;

                document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        activeForm = form;
                        title.textContent = form.dataset.confirmDelete || 'Yakin ingin menghapus data ini?';
                        modal.hidden = false;
                        document.body.classList.add('modal-open');
                    });
                });

                const closeModal = () => {
                    modal.hidden = true;
                    activeForm = null;
                    document.body.classList.remove('modal-open');
                };

                cancel?.addEventListener('click', closeModal);
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) closeModal();
                });
                confirm?.addEventListener('click', () => {
                    if (activeForm) {
                        activeForm.removeAttribute('data-confirm-delete');
                        activeForm.submit();
                    }
                });
            })();
        </script>
    @endif
</body>
</html>
