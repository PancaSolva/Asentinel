@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="max-w-2xl mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex items-center gap-4 mb-10">
        <a href="{{ route('admin.users.index') }}" class="p-2 text-zinc-500 hover:text-white hover:bg-zinc-800 rounded-xl transition-all duration-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight">Update Profile</h2>
            <p class="text-sm text-zinc-500 font-medium">Modifying identity for <span class="text-blue-400 font-bold">{{ $user->name }}</span>.</p>
        </div>
    </div>

    <div class="glass-card">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="p-10 space-y-8">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Name Field -->
                <div class="space-y-2">
                    <label for="name" class="text-xs font-bold text-zinc-500 uppercase tracking-widest ml-1">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full bg-zinc-900/50 border border-zinc-800 rounded-2xl px-5 py-4 text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/50 transition-all duration-300">
                </div>

                <!-- Email Field -->
                <div class="space-y-2">
                    <label for="email" class="text-xs font-bold text-zinc-500 uppercase tracking-widest ml-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full bg-zinc-900/50 border border-zinc-800 rounded-2xl px-5 py-4 text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/50 transition-all duration-300">
                </div>

                <!-- Role Selection -->
                <div class="space-y-2">
                    <label for="role" class="text-xs font-bold text-zinc-500 uppercase tracking-widest ml-1">System Role</label>
                    <select name="role" id="role" 
                            class="w-full bg-zinc-900/50 border border-zinc-800 rounded-2xl px-5 py-4 text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500/50 transition-all duration-300 appearance-none">
                        <option value="User" {{ (old('role', $user->role) === 'User') ? 'selected' : '' }}>Standard Participant</option>
                        <option value="Admin" {{ (old('role', $user->role) === 'Admin') ? 'selected' : '' }}>System Administrator</option>
                    </select>
                </div>
            </div>

            <div class="pt-6 border-t border-zinc-800/50 flex gap-4">
                <a href="{{ route('admin.users.index') }}" class="flex-1 text-center py-4 text-sm font-bold text-zinc-400 hover:text-white bg-zinc-900/50 border border-zinc-800 rounded-2xl transition-all duration-200">
                    Cancel Changes
                </a>
                <button type="submit" class="flex-[2] btn-primary py-4 text-lg">
                    Synchronize Profile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
