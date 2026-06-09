<?php

namespace App\Http\Controllers\StafLab;

use App\Http\Controllers\Concerns\InteractsWithBackend;
use App\Http\Controllers\Controller;
use App\Services\BackendApi;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    use InteractsWithBackend;

    public function index(BackendApi $api)
    {
        if ($redirect = $this->ensureRole('staf_lab')) {
            return $redirect;
        }

        $response = $this->api($api)->get('/staf-lab/maintenance');

        return view('staf-lab.maintenance', [
            'user' => session('auth_user'),
            'logs' => $response->json('logs', []),
            'inventories' => $response->json('inventories', []),
            'consumables' => $response->json('consumables', []),
            'error' => $response->successful() ? null : $response->json('message', 'Log maintenance belum bisa dimuat.'),
        ]);
    }

    public function show(BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('staf_lab')) {
            return $redirect;
        }

        $response = $this->api($api)->get("/staf-lab/maintenance/{$id}");

        return view('staf-lab.maintenance-show', [
            'user' => session('auth_user'),
            'log' => $response->json('log'),
            'usages' => $response->json('usages', []),
            'error' => $response->successful() ? null : $response->json('message', 'Log maintenance tidak ditemukan.'),
        ]);
    }

    public function store(Request $request, BackendApi $api)
    {
        if ($redirect = $this->ensureRole('staf_lab')) {
            return $redirect;
        }

        $payload = $request->validate([
            'inventory_item_id' => ['required', 'integer'],
            'maintenance_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'condition_before' => ['required', 'string'],
            'condition_after' => ['required', 'string'],
            'status_after' => ['nullable', 'in:active,maintenance,retired'],
            'consumable_usages' => ['nullable', 'array'],
            'consumable_usages.*.consumable_id' => ['required', 'integer'],
            'consumable_usages.*.quantity_used' => ['required', 'integer', 'min:1'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->post('/staf-lab/maintenance', $payload),
            'Log maintenance berhasil dicatat.',
        );
    }
}
