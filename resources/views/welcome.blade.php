<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asentinel - Monitoring & Inventory System</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/main.jsx'])
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    @if(session('spa_token'))
        @php
            $sessionUser = session('admin_user');
            $userData = [
                'id' => $sessionUser->id ?? null,
                'name' => $sessionUser->name ?? '',
                'email' => $sessionUser->email ?? '',
                'role' => $sessionUser->role ?? 'user',
            ];
        @endphp
        <script>
            localStorage.setItem('token', "{{ session('spa_token') }}");
            localStorage.setItem('user', JSON.stringify(@json($userData)));
        </script>
        @php session()->forget(['spa_token', 'admin_user']); @endphp
    @endif
    <div id="root"></div>
</body>
</html>
