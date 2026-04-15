@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-extrabold text-white tracking-tight">User Directory</h2>
            <p class="text-sm text-zinc-500 font-medium mt-1">Manage, monitor and audit all platform participants.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Add New User
        </a>
    </div>

    <!-- Table Container -->
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-zinc-500 uppercase tracking-[0.2em] bg-zinc-900/30 border-b border-zinc-800/50">
                        <th class="px-8 py-5">Profile Details</th>
                        <th class="px-8 py-5">System Role</th>
                        <th class="px-8 py-5">Registration Date</th>
                        <th class="px-8 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/50">
                    @forelse ($users as $user)
                        <tr class="group hover:bg-white/[0.02] transition-all duration-200">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center font-bold text-blue-400 group-hover:border-blue-500/30 group-hover:scale-105 transition-all duration-300">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="text-sm font-bold text-zinc-100 group-hover:text-white transition-colors">{{ $user->name }}</p>
                                        <p class="text-xs text-zinc-500 font-medium">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1.5 text-[10px] font-bold rounded-lg uppercase tracking-wider 
                                    {{ ($user->role ?? 'User') === 'Admin' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-zinc-800 text-zinc-400 border border-zinc-700/50' }}">
                                    {{ $user->role ?? 'User' }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-sm text-zinc-400 font-medium">{{ $user->created_at->format('M d, Y') }}</p>
                                <p class="text-[10px] text-zinc-600 font-bold uppercase tracking-tighter">{{ $user->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="p-2 text-zinc-500 hover:text-white hover:bg-zinc-800 rounded-xl transition-all duration-200" title="View Profile">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="p-2 text-zinc-500 hover:text-amber-400 hover:bg-amber-400/10 rounded-xl transition-all duration-200" title="Edit User">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Archive this user? This will restrict their access permanently.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-zinc-500 hover:text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all duration-200" title="Delete User">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center justify-center space-y-4">
                                    <div class="w-16 h-16 bg-zinc-900 rounded-3xl flex items-center justify-center text-zinc-700">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">No users found</h3>
                                        <p class="text-sm text-zinc-500">The directory is currently empty. Start by adding a new user.</p>
                                    </div>
                                    <a href="{{ route('admin.users.create') }}" class="btn-primary mt-4">
                                        Initialize Directory
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center mt-10">
        <div class="glass-card px-4 py-2">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
