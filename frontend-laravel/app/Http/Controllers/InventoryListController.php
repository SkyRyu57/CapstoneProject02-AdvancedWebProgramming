<?php

namespace App\Http\Controllers;

use App\Services\BackendApi;
use Illuminate\Http\Request;

class InventoryListController extends Controller
{
    public function __invoke(BackendApi $api)
    {
        if (! session()->has('api_token')) {
            return redirect()->route('login');
        }

        $response = $api->withToken(session('api_token'))->get('/inventories');

        if (! $response->successful()) {
            return back()->withErrors(['message' => 'Gagal memuat daftar inventaris.']);
        }

        return view('inventory-list', [
            'user' => session('auth_user'),
            'inventories' => $response->json('inventories', []),
        ]);
    }
}
