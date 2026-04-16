@extends('layouts.admin')

@section('title', 'System Overview')

@section('content')
<div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-700">
    
    <!-- Welcome Header -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                <span class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.3em]">Live Analytics</span>
            </div>
            <h2 class="text-4xl lg:text-5xl font-black tracking-tight text-white mb-3">Operational Intelligence</h2>
            <p class="text-zinc-500 font-bold text-lg max-w-2xl">Monitor system integrity, user engagement, and infrastructure performance in real-time.</p>
        </div>
        <div class="flex items-center gap-4">
            <button class="px-6 py-3 text-xs font-black text-zinc-400 bg-zinc-900 border border-zinc-800 rounded-2xl hover:text-white hover:border-zinc-700 transition-all duration-300 uppercase tracking-widest">
                Export Logs
            </button>
            <a href="{{ route('admin.users.create') }}" class="btn-action">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Provision User
            </a>
        </div>
    </div>
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Total Users -->
        <div class="glass-card p-10 group hover:bg-blue-600/[0.03] hover:border-blue-500/30 transition-all duration-500">
            <div class="flex items-start justify-between mb-8">
                <div class="w-14 h-14 bg-blue-600/10 rounded-2xl flex items-center justify-center text-blue-500 border border-blue-500/20 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="text-emerald-400 text-[10px] font-black bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 rounded-full tracking-widest">+12.5%</div>
            </div>
            <div>
                <p class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.2em] mb-2">Authenticated Users</p>
                <h3 class="text-5xl font-black text-white tracking-tighter">{{ \App\Models\User::count() }}</h3>
            </div>
            <div class="mt-10 pt-6 border-t border-zinc-800/50 flex items-center justify-between">
                <span class="text-[10px] font-black text-zinc-600 uppercase tracking-widest">Active Pool</span>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-black text-blue-500 hover:text-blue-400 transition-colors uppercase tracking-widest flex items-center gap-2">
                    Directory <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>

        <!-- System Health -->
        <div class="glass-card p-10 group hover:bg-emerald-600/[0.03] hover:border-emerald-500/30 transition-all duration-500">
            <div class="flex items-start justify-between mb-8">
                <div class="w-14 h-14 bg-emerald-600/10 rounded-2xl flex items-center justify-center text-emerald-500 border border-emerald-500/20 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040L3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622l-0.382-3.016z"></path></svg>
                </div>
                <div class="flex gap-1.5">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="w-2 h-2 bg-emerald-500/30 rounded-full"></span>
                </div>
            </div>
            <div>
                <p class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.2em] mb-2">Core Integrity</p>
                <h3 class="text-5xl font-black text-white tracking-tighter uppercase">Stable</h3>
            </div>
            <div class="mt-10 pt-6 border-t border-zinc-800/50 flex items-center justify-between">
                <span class="text-[10px] font-black text-zinc-600 uppercase tracking-widest">Global Latency</span>
                <span class="text-xs font-black text-emerald-500 uppercase tracking-widest">14ms Average</span>
            </div>
        </div>

        <!-- Database Load -->
        <div class="glass-card p-10 group hover:bg-purple-600/[0.03] hover:border-purple-500/30 transition-all duration-500">
            <div class="flex items-start justify-between mb-8">
                <div class="w-14 h-14 bg-purple-600/10 rounded-2xl flex items-center justify-center text-purple-500 border border-purple-500/20 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                </div>
                <div class="text-purple-400 text-[10px] font-black bg-purple-500/10 border border-purple-500/20 px-3 py-1.5 rounded-full tracking-widest">Normal</div>
            </div>
            <div>
                <p class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.2em] mb-2">Volume Traffic</p>
                <h3 class="text-5xl font-black text-white tracking-tighter">8.4<span class="text-2xl text-zinc-600 ml-1">GB</span></h3>
            </div>
            <div class="mt-10 pt-6 border-t border-zinc-800/50">
                <div class="w-full bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 h-full w-[35%] rounded-full"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="glass-card overflow-hidden">
        <div class="p-10 border-b border-zinc-800/50 flex items-center justify-between bg-zinc-900/10">
            <div>
                <h3 class="text-2xl font-black text-white tracking-tight">Security Audit Trail</h3>
                <p class="text-sm text-zinc-500 font-bold mt-1">Real-time log of the latest identity provisions.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-[10px] font-black text-zinc-500 hover:text-white uppercase tracking-[0.2em] border border-zinc-800 px-5 py-2.5 rounded-xl transition-all duration-300">
                Audit Full Directory
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black text-zinc-600 uppercase tracking-[0.25em] border-b border-zinc-800/50">
                        <th class="px-10 py-5">Identity Profile</th>
                        <th class="px-10 py-5">System Role</th>
                        <th class="px-10 py-5">Provisioned</th>
                        <th class="px-10 py-5 text-right">Verification</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/50">
                    @foreach (\App\Models\User::latest()->take(5)->get() as $user)
                        <tr class="group hover:bg-white/[0.01] transition-all duration-300">
                            <td class="px-10 py-6">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center font-black text-blue-500 group-hover:border-blue-500/30 group-hover:scale-105 transition-all duration-500">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-zinc-100 group-hover:text-blue-400 transition-colors">{{ $user->name }}</p>
                                        <p class="text-xs text-zinc-600 font-bold tracking-tight">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-10 py-6">
                                <span class="px-3 py-1.5 text-[9px] font-black rounded-lg uppercase tracking-widest bg-zinc-950 text-zinc-500 border border-zinc-800 group-hover:border-zinc-700 transition-all duration-300">
                                    {{ $user->role ?? 'Standard' }}
                                </span>
                            </td>
                            <td class="px-10 py-6">
                                <p class="text-xs text-zinc-500 font-black uppercase tracking-tighter">{{ $user->created_at->format('M d, Y') }}</p>
                                <p class="text-[10px] text-zinc-700 font-bold uppercase tracking-widest mt-0.5">{{ $user->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-10 py-6 text-right">
                                <a href="{{ route('admin.users.show', $user) }}" class="inline-flex p-2.5 text-zinc-600 hover:text-white bg-zinc-900/50 border border-zinc-800 rounded-xl hover:border-zinc-700 transition-all duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
