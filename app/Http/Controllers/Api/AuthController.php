<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);

        return ['user' => $user];
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $request->session()->regenerate();

        return ['user' => Auth::user()];
    }

    public function logout(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ['ok' => true];
    }

    public function me(Request $request)
    {
        return ['user' => Auth::user()];
    }
}

