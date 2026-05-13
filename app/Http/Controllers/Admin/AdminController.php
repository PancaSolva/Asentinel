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
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('name', $request->username)->orWhere('email', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided credentials are incorrect.'
                ], 401);
            }
            return back()->withErrors(['username' => 'The provided credentials are incorrect.']);
        }

        // Create a single auth token for both web and API use
        $token = $user->createToken('auth_token')->plainTextToken;

        // Web Login
        session(['admin_logged_in' => true, 'admin_user' => $user]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token,
                'role' => $user->role,
            ]);
        }

        session(['spa_token' => $token]);

        // Redirect based on role
        return redirect($user->role === 'admin' ? '/' : '/user-dashboard');
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
            $user = session('admin_user') ?? Auth::user();
            if ($user && $user->role !== 'admin') {
                return redirect('/user-dashboard');
            }
            return redirect('/');
        }
        return view('admin.login');
    }
}
