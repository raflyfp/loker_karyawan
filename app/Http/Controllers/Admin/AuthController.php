<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login admin.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Proses autentikasi admin.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        $username = trim($credentials['username']);
        $password = $credentials['password'];

        // Dukung login via username atau email
        $loginType = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$loginType => $username, 'password' => $password], $remember)) {
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')
                ->with('success', 'Selamat datang kembali, ' . (Auth::user()->name ?? 'Admin') . '!');
        }

        return back()
            ->withInput($request->only('username', 'remember'))
            ->withErrors([
                'username' => 'Username atau password yang dimasukkan salah.',
            ]);
    }

    /**
     * Keluar dari sesi admin.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('loker.index')
            ->with('info', 'Anda telah keluar dari sesi Admin.');
    }
}
