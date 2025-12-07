<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $role = optional(Auth::user()->role)->value ?? Auth::user()->role;
            $target = match ($role) {
                'superadmin', 'admin' => route('cms'),
                'couple' => route('cms.couple'),
                default => '/',
            };

            return redirect($target);
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas ou permissão insuficiente.',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
