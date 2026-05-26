<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\InteractsWithBackend;
use App\Http\Controllers\Controller;
use App\Services\BackendApi;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    use InteractsWithBackend;

    public function index(BackendApi $api)
    {
        if ($redirect = $this->ensureRole('admin')) {
            return $redirect;
        }

        $response = $this->api($api)->get('/admin/rooms');

        return view('admin.rooms', [
            'user' => session('auth_user'),
            'rooms' => $response->json('rooms', []),
            'error' => $response->successful() ? null : $response->json('message', 'Data ruangan belum bisa dimuat.'),
        ]);
    }

    public function store(Request $request, BackendApi $api)
    {
        if ($redirect = $this->ensureRole('admin')) {
            return $redirect;
        }

        $payload = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->post('/admin/rooms', $payload),
            'Ruangan berhasil dibuat.',
        );
    }

    public function update(Request $request, BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('admin')) {
            return $redirect;
        }

        $payload = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->patch("/admin/rooms/{$id}", $payload),
            'Ruangan berhasil diperbarui.',
        );
    }

    public function destroy(BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('admin')) {
            return $redirect;
        }

        return $this->backWithApiResult(
            $this->api($api)->delete("/admin/rooms/{$id}"),
            'Ruangan berhasil dihapus.',
        );
    }
}
