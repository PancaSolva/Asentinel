@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div>
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Dashboard Admin</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-xl font-semibold mb-2">Total Users</h3>
            <p class="text-3xl font-bold text-blue-600">{{ \App\Models\User::count() }}</p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-xl font-semibold mb-2">Users with Sandwich Bar</h3>
            <p class="text-3xl font-bold text-green-600">{{ \App\Models\User::where('has_sandwich_bar', true)->count() }}</p>
        </div>
    </div>
    
    <div class="mt-8">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            Kelola Users
        </a>
    </div>
</div>
@endsection

