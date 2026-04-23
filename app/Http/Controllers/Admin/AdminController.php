<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LogAnomali;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials are incorrect.'
                ], 401);
            }
            return back()->withErrors(['email' => 'The provided credentials are incorrect.']);
        }


        session(['admin_logged_in' => true, 'admin_user' => $user]);

        if ($request->expectsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ]);
        }


        $token = $user->createToken('auth_token')->plainTextToken;
        session(['spa_token' => $token]);

        return redirect('/');
    }

    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            $user = $request->user();
            if ($user && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
        }

        session()->forget(['admin_logged_in', 'admin_user']);
        Auth::logout();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        }

        return redirect('/login');
    }
    public function ShowLogin()
    {
        if (session('admin_logged_in') || Auth::check()) {
            return redirect('/');
        }
        return view('admin.login');
    }
}
