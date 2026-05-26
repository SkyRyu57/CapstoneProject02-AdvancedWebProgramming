<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\InteractsWithBackend;
use App\Http\Controllers\Controller;
use App\Services\BackendApi;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use InteractsWithBackend;

    public function index(BackendApi $api)
    {
        if ($redirect = $this->ensureRole('admin')) {
            return $redirect;
        }

        $response = $this->api($api)->get('/admin/users');

        return view('admin.users', [
            'user' => session('auth_user'),
            'users' => $response->json('users', []),
            'roles' => $response->json('roles', []),
            'error' => $response->successful() ? null : $response->json('message', 'Data pengguna belum bisa dimuat.'),
        ]);
    }

    public function store(Request $request, BackendApi $api)
    {
        if ($redirect = $this->ensureRole('admin')) {
            return $redirect;
        }

        $payload = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->post('/admin/users', $payload),
            'Pengguna berhasil dibuat.',
        );
    }

    public function update(Request $request, BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('admin')) {
            return $redirect;
        }

        $payload = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->patch("/admin/users/{$id}", $payload),
            'Pengguna berhasil diperbarui.',
        );
    }

    public function destroy(BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('admin')) {
            return $redirect;
        }

        return $this->backWithApiResult(
            $this->api($api)->delete("/admin/users/{$id}"),
            'Pengguna berhasil dihapus.',
        );
    }
}
