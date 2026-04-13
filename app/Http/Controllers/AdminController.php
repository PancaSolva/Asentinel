<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class AdminController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($credentials['email'] === 'admin@example.com' && $credentials['password'] === 'admin123') {
            Session::put('admin_logged_in', true);
            return redirect('/admin');
        }

        return back()->withErrors([
            'email' => 'Wrong credentials.',
        ]);
    }

    public function logout()
    {
        Session::forget('admin_logged_in');
        return redirect('/login');
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create($request->all());

        return redirect()->route('admin.users.index')
                        ->with('success','User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->update($request->all());

        return redirect()->route('admin.users.index')
                        ->with('success','User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success','User deleted successfully.');
    }
}

