
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - {{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white">
    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-gray-800 shadow-lg h-screen fixed">
        <div class="p-6">
            <h1 class="text-2xl font-bold">Admin Panel</h1>
        </div>
        <nav class="mt-8">
            <a href="/admin" class="flex items-center px-6 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                    <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                </svg>
                Dashboard
            </a>

            <a href="/admin/users" class="flex items-center px-6 py-3 bg-blue-500 text-white">

                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
                Users
            </a>

            <a href="/logout" class="flex items-center px-6 py-3 text-red-500 hover:bg-red-50 mt-8">

                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm11.354 7.646l-2 2a.5.5 0 00.708.708l2-2a.5.5 0 000-.708l-2-2a.5.5 0 10.708-.708l2 2zM17 11a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                </svg>
                Logout
            </a>
        </nav>
    </aside>

    <div class="ml-64">
        <!-- Top bar -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b">
            <div class="px-6 py-4">
                <h2 class="text-xl font-semibold">@yield('title', 'Dashboard')</h2>
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>

