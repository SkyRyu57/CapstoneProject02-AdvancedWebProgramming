<?php

namespace App\Http\Controllers\StafLab;

use App\Http\Controllers\Concerns\InteractsWithBackend;
use App\Http\Controllers\Controller;
use App\Services\BackendApi;
use Illuminate\Http\Request;

class ConsumableController extends Controller
{
    use InteractsWithBackend;

    public function index(BackendApi $api)
    {
        if ($redirect = $this->ensureRole('staf_lab')) {
            return $redirect;
        }

        $response = $this->api($api)->get('/staf-lab/consumables');

        return view('staf-lab.consumables', [
            'user' => session('auth_user'),
            'consumables' => $response->json('consumables', []),
            'error' => $response->successful() ? null : $response->json('message', 'Data BHP belum bisa dimuat.'),
        ]);
    }

    public function adjust(Request $request, BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('staf_lab')) {
            return $redirect;
        }

        $payload = $request->validate([
            'quantity_change' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string'],
            'reference_type' => ['nullable', 'in:manual,procurement_receipt,maintenance'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->post("/staf-lab/consumables/{$id}/adjust", $payload),
            'Stok BHP berhasil diperbarui.',
        );
    }
}
