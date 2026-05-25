<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\BackendApi;

class DashboardController extends Controller
{
    public function __invoke(BackendApi $api)
    {
        if (! session()->has('api_token')) {
            return redirect()->route('login');
        }

        $response = $api->withToken(session('api_token'))->get('/dashboard');

        if ($response->status() === 401) {
            session()->forget(['api_token', 'auth_user']);

            return redirect()->route('login')->withErrors([
                'email' => 'Sesi sudah berakhir. Silakan login ulang.',
            ]);
        }

        if (! $response->successful()) {
            return view('dashboard.index', [
                'user' => session('auth_user'),
                'dashboard' => null,
                'error' => $response->json('message', 'Dashboard belum bisa dimuat.'),
            ]);
        }

        return view('dashboard.index', [
            'user' => session('auth_user'),
            'dashboard' => $response->json(),
            'error' => null,
        ]);
    }
}
