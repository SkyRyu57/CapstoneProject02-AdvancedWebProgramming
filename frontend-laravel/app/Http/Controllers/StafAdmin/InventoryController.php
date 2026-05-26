<?php

namespace App\Http\Controllers\StafAdmin;

use App\Http\Controllers\Concerns\InteractsWithBackend;
use App\Http\Controllers\Controller;
use App\Services\BackendApi;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use InteractsWithBackend;

    public function index(BackendApi $api)
    {
        if ($redirect = $this->ensureRole('staf_admin')) {
            return $redirect;
        }

        $response = $this->api($api)->get('/staf-admin/inventories');

        return view('staf-admin.inventories', [
            'user' => session('auth_user'),
            'inventories' => $response->json('inventories', []),
            'rooms' => $response->json('rooms', []),
            'error' => $response->successful() ? null : $response->json('message', 'Inventaris belum bisa dimuat.'),
        ]);
    }

    public function update(Request $request, BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('staf_admin')) {
            return $redirect;
        }

        $payload = $request->validate([
            'label_code' => ['required', 'string'],
            'qr_code' => ['nullable', 'image', 'max:2048'],
            'existing_qr_code' => ['nullable', 'string'],
            'room_id' => ['required', 'integer'],
            'condition' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        $file = $payload['qr_code'] ?? null;
        unset($payload['qr_code']);

        return $this->backWithApiResult(
            $this->api($api)->patchMultipart(
                "/staf-admin/inventories/{$id}",
                $payload,
                $file ? ['qr_code' => $file] : [],
            ),
            'Data inventaris berhasil diperbarui.',
        );
    }

    public function destroy(BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('staf_admin')) {
            return $redirect;
        }

        return $this->backWithApiResult(
            $this->api($api)->delete("/staf-admin/inventories/{$id}"),
            'Inventaris berhasil dihapus.',
        );
    }
}
