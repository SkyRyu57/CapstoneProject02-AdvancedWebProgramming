<?php

namespace App\Http\Controllers\Concerns;

use App\Services\BackendApi;
use Illuminate\Http\RedirectResponse;

trait InteractsWithBackend
{
    protected function api(BackendApi $api): BackendApi|RedirectResponse
    {
        if (! session()->has('api_token')) {
            return redirect()->route('login');
        }

        return $api->withToken(session('api_token'));
    }

    protected function ensureRole(string $role): ?RedirectResponse
    {
        if (! session()->has('api_token')) {
            return redirect()->route('login');
        }

        if (session('auth_user.role') !== $role) {
            return redirect()->route('dashboard')->with('status', 'Akses fitur tidak sesuai role akun.');
        }

        return null;
    }

    protected function backWithApiResult($response, string $fallbackSuccess): RedirectResponse
    {
        if ($response->successful()) {
            return back()->with('status', $response->json('message', $fallbackSuccess));
        }

        return back()
            ->withInput()
            ->withErrors(['api' => $response->json('message', 'Permintaan gagal diproses.')]);
    }
}
