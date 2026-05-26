<?php

namespace App\Http\Controllers\KepalaLab;

use App\Http\Controllers\Concerns\InteractsWithBackend;
use App\Http\Controllers\Controller;
use App\Services\BackendApi;
use Illuminate\Http\Request;

class ProcurementDraftController extends Controller
{
    use InteractsWithBackend;

    public function index(BackendApi $api)
    {
        if ($redirect = $this->ensureRole('kepala_lab')) {
            return $redirect;
        }

        $response = $this->api($api)->get('/kepala-lab/procurement-drafts');
        $invResponse = $this->api($api)->get('/kepala-lab/inventories');

        return view('kepala-lab.drafts', [
            'user' => session('auth_user'),
            'drafts' => $response->json('drafts', []),
            'inventories' => $invResponse->json('inventories', []),
            'error' => $response->successful() ? null : $response->json('message', 'Draf pengadaan belum bisa dimuat.'),
        ]);
    }

    public function store(Request $request, BackendApi $api)
    {
        if ($redirect = $this->ensureRole('kepala_lab')) {
            return $redirect;
        }

        $payload = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->post('/kepala-lab/procurement-drafts', $payload),
            'Draf pengadaan berhasil dibuat.',
        );
    }

    public function show(BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('kepala_lab')) {
            return $redirect;
        }

        $response = $this->api($api)->get("/kepala-lab/procurement-drafts/{$id}");
        $invResponse = $this->api($api)->get('/kepala-lab/inventories');

        return view('kepala-lab.draft-show', [
            'user' => session('auth_user'),
            'draft' => $response->json('draft'),
            'inventories' => $invResponse->json('inventories', []),
            'error' => $response->successful() ? null : $response->json('message', 'Draf tidak ditemukan.'),
        ]);
    }

    public function update(Request $request, BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('kepala_lab')) {
            return $redirect;
        }

        $payload = $request->validate([
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->patch("/kepala-lab/procurement-drafts/{$id}", $payload),
            'Draf berhasil diperbarui.',
        );
    }

    public function destroy(BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('kepala_lab')) {
            return $redirect;
        }

        return $this->backWithApiResult(
            $this->api($api)->delete("/kepala-lab/procurement-drafts/{$id}"),
            'Draf berhasil dihapus.',
        );
    }

    public function storeItem(Request $request, BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('kepala_lab')) {
            return $redirect;
        }

        $payload = $request->validate([
            'item_type' => ['required', 'in:inventory,consumable'],
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'purchase_link' => ['nullable', 'string'],
            'replacement_inventory_id' => ['nullable', 'integer'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->post("/kepala-lab/procurement-drafts/{$id}/items", $payload),
            'Item berhasil ditambahkan.',
        );
    }

    public function updateItem(Request $request, BackendApi $api, int $id, int $itemId)
    {
        if ($redirect = $this->ensureRole('kepala_lab')) {
            return $redirect;
        }

        $payload = $request->validate([
            'item_type' => ['required', 'in:inventory,consumable'],
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'purchase_link' => ['nullable', 'string'],
            'replacement_inventory_id' => ['nullable', 'integer'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->patch("/kepala-lab/procurement-drafts/{$id}/items/{$itemId}", $payload),
            'Item berhasil diperbarui.',
        );
    }

    public function destroyItem(BackendApi $api, int $id, int $itemId)
    {
        if ($redirect = $this->ensureRole('kepala_lab')) {
            return $redirect;
        }

        return $this->backWithApiResult(
            $this->api($api)->delete("/kepala-lab/procurement-drafts/{$id}/items/{$itemId}"),
            'Item berhasil dihapus.',
        );
    }
}
