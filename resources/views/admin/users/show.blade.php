@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="max-w-4xl mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Breadcrumb/Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="p-2 text-zinc-500 hover:text-white hover:bg-zinc-800 rounded-xl transition-all duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Identity Overview</h2>
                <p class="text-sm text-zinc-500 font-medium mt-1">Detailed system profile and access logs.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Modify Identity
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Card -->
        <div class="lg:col-span-1 space-y-8">
            <div class="glass-card p-8 text-center">
                <div class="relative inline-block mb-6">
                    <div class="w-24 h-24 rounded-3xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-3xl font-extrabold text-white shadow-xl shadow-blue-500/20 mx-auto">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-zinc-900 border-4 border-background rounded-full flex items-center justify-center">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-white mb-1">{{ $user->name }}</h3>
                <p class="text-sm text-zinc-500 mb-6">{{ $user->email }}</p>
                
                <span class="inline-flex px-4 py-1.5 text-[10px] font-extrabold rounded-full uppercase tracking-widest 
                    {{ ($user->role ?? 'User') === 'Admin' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-zinc-800 text-zinc-400 border border-zinc-700/50' }}">
                    {{ $user->role ?? 'User' }}
                </span>
            </div>

            <!-- Quick Info -->
            <div class="glass-card p-6 space-y-4">
                <h4 class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-2">Account Metadata</h4>
                <div class="space-y-1">
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-white/[0.02] transition-colors">
                        <span class="text-sm text-zinc-400 font-medium">Joined Date</span>
                        <span class="text-sm text-zinc-100 font-bold">{{ $user->created_at->format('M Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-white/[0.02] transition-colors">
                        <span class="text-sm text-zinc-400 font-medium">Status</span>
                        <span class="text-sm text-emerald-400 font-bold uppercase tracking-tighter">Verified</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Info & Timeline -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Information Grid -->
            <div class="glass-card overflow-hidden">
                <div class="p-6 border-b border-zinc-800/50 bg-zinc-900/20">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">Detailed Information</h4>
                </div>
                <div class="p-8 grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Display Name</p>
                        <p class="text-base text-zinc-100 font-medium">{{ $user->name }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Email Address</p>
                        <p class="text-base text-zinc-100 font-medium">{{ $user->email }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Registered At</p>
                        <p class="text-base text-zinc-100 font-medium">{{ $user->created_at->format('F d, Y @ H:i') }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Last Update</p>
                        <p class="text-base text-zinc-100 font-medium">{{ $user->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-rose-500/5 border border-rose-500/10 rounded-2xl p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div>
                    <h4 class="text-lg font-bold text-rose-100">Restrict Account Access</h4>
                    <p class="text-sm text-rose-300/60 font-medium">Permanently archive this identity and revoke all permissions.</p>
                </div>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Archive this user? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-3 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-xl shadow-lg shadow-rose-500/20 transition-all duration-200">
                        Archive Identity
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
