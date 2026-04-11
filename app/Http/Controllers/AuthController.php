<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Guru;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'academic_year' => 'nullable|string',
            'semester' => 'nullable|in:Ganjil,Genap',
        ]);

        $email = $validated['email'];
        $password = $validated['password'];

        $authenticated = Auth::attempt(['email' => $email, 'password' => $password]);

        // Allow Guru login by email
        if (!$authenticated) {
            $guru = Guru::where('email', $email)->first();
            if ($guru && Hash::check($password, $guru->password)) {
                Auth::login($guru);
                $authenticated = true;
            }
        }

        if (!$authenticated) {
            return response()->json(['message' => 'Username atau password salah.'], 401);
        }

        $request->session()->regenerate();

        if (!empty($validated['academic_year'])) {
            $request->session()->put('academic_year', $validated['academic_year']);
        }
        if (!empty($validated['semester'])) {
            $request->session()->put('semester', $validated['semester']);
        }

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $request->user(),
            'context' => [
                'academic_year' => $request->session()->get('academic_year'),
                'semester' => $request->session()->get('semester'),
            ],
            'csrf_token' => csrf_token(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'context' => [
                'academic_year' => $request->session()->get('academic_year'),
                'semester' => $request->session()->get('semester'),
            ],
        ]);
    }
}
