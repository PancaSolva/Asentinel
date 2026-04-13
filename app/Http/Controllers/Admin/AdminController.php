<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    public function showLogin()
    {
        if (Session::get('admin_logged_in')) {
            return redirect('/admin');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($request->email === 'admin@example.com' && $request->password === 'admin123') {
            Session::put('admin_logged_in', true);
            return redirect('/admin')->with('success', 'Login berhasil!');
        }

        return back()->withErrors(['email' => 'Login gagal!']);
    }

    public function index()
    {
        return view('admin.dashboard');
    }

    public function logout()
    {
        Session::forget('admin_logged_in');
        return redirect('/login');
    }
}

