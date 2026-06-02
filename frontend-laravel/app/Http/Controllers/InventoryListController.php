<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithBackend;
use App\Services\BackendApi;

class InventoryListController extends Controller
{
    use InteractsWithBackend;

    public function index(BackendApi $api)
    {
        if (! session()->has('api_token')) {
            return redirect()->route('login');
        }

        $role = session('auth_user.role');

        if ($role === 'admin') {
            return redirect()->route('dashboard')->with('status', 'Fitur ini tidak tersedia untuk Administrator.');
        }

        $response = $this->api($api)->get('/inventory-list');

        return view('inventory-list', [
            'user'        => session('auth_user'),
            'inventories' => $response->json('inventories', []),
            'consumables' => $response->json('consumables', []),
            'error'       => $response->successful() ? null : $response->json('message', 'Daftar barang belum bisa dimuat.'),
        ]);
    }
}
