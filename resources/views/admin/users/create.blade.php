
@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
<div class="max-w-2xl">
    <div class="flex justify-between items-center mb-8">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Create New User</h3>
        <a href="{{ route('admin.users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
            Back to Users
        </a>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-8 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Name
                </label>
                <input type="text" id="name" name="name" required value="{{ old('name') }}" 
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email
                </label>
                <input type="email" id="email" name="email" required value="{{ old('email') }}" 
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Password
            </label>
            <input type="password" id="password" name="password" required 
                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center">
            <input type="checkbox" id="has_sandwich_bar" name="has_sandwich_bar" value="1" {{ old('has_sandwich_bar') ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
            <label for="has_sandwich_bar" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Has Sandwich Bar
            </label>
        </div>

        <div class="flex space-x-4 pt-4">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-8 rounded-lg transition-colors focus:ring-2 focus:ring-green-500">
                Create User
            </button>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-8 rounded-lg transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection>

