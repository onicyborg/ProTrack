<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials)) {
            return back()->with('error', 'Username atau password salah.')->withInput();
        }

        $request->session()->regenerate();

        $role = Auth::user()->role;

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'pm'    => redirect()->route('pm.dashboard'),
            default => redirect('/'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
