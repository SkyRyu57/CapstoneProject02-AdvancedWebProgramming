<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\BackendApi;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('api_token')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request, BackendApi $api)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $response = $api->post('/auth/login', $credentials);

        if (! $response->successful()) {
            return back()
                ->withErrors(['email' => $response->json('message', 'Login gagal.')])
                ->onlyInput('email');
        }

        session([
            'api_token' => $response->json('token'),
            'auth_user' => $response->json('user'),
        ]);

        return redirect()->route('dashboard');
    }

    public function showForgotAccount()
    {
        return view('auth.forgot-account');
    }

    public function forgotAccount(Request $request, BackendApi $api)
    {
        $payload = $request->validate([
            'email' => ['nullable', 'email'],
        ]);

        $response = $api->post('/auth/forgot-account', $payload);

        return back()->with([
            'status' => $response->json('message', 'Silakan hubungi administrator.'),
            'admin_contact' => $response->json('admin_contact'),
            'account_hint' => $response->json('account_hint'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['api_token', 'auth_user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
