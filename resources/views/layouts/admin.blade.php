<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - @yield('title', 'Asentinel Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Fallback styles to prevent "white flash" or broken layout if CSS fails to load */
        html.dark { background-color: #09090b; color: #fafafa; }
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 h-full font-sans antialiased">
    <div class="flex h-screen w-full overflow-hidden bg-zinc-950">
        
        <!-- Sidebar: Visible on desktop, robust layout -->
        <aside class="hidden lg:flex flex-col w-72 bg-zinc-900 border-r border-zinc-800 shadow-2xl z-30 flex-shrink-0">
            <div class="p-8 flex items-center gap-4">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white">Asentinel</h1>
            </div>

            <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto custom-scrollbar">
                <div class="px-4 py-2">
                    <span class="text-[10px] font-black text-zinc-500 uppercase tracking-widest">Main Navigation</span>
                </div>
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : 'nav-link-inactive' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard Overview
                </a>

                <a href="{{ route('admin.users.index') }}" 
                   class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    User Management
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-6 border-t border-zinc-800 bg-zinc-900/50">
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-zinc-950 border border-zinc-800">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/10 flex items-center justify-center font-bold text-blue-500">A</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate text-zinc-100 uppercase">Admin</p>
                        <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-tighter">System Access</p>
                    </div>
                </div>
                <a href="{{ route('logout') }}" class="mt-4 flex items-center justify-center gap-2 py-3 text-sm font-bold text-zinc-500 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-all duration-200 uppercase tracking-widest">
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-zinc-950 relative overflow-hidden">
            <!-- Header/Top Bar -->
            <header class="h-20 flex items-center justify-between px-8 border-b border-zinc-800 bg-zinc-950/80 backdrop-blur-xl z-20 flex-shrink-0">
                <div class="flex items-center gap-4 lg:hidden">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="h-8 w-1 bg-blue-600 rounded-full hidden sm:block"></div>
                    <h2 class="text-lg font-bold text-zinc-100 uppercase tracking-tight">@yield('title', 'Admin Panel')</h2>
                </div>

                <div class="flex items-center gap-6">
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-[10px] font-black text-zinc-500 uppercase tracking-[0.2em]">Session Active</span>
                        <span class="text-xs font-bold text-zinc-300">{{ now()->format('l, d F Y') }}</span>
                    </div>
                    <div class="h-10 w-px bg-zinc-800"></div>
                    <button class="p-2.5 text-zinc-400 hover:text-white bg-zinc-900 border border-zinc-800 rounded-xl transition-all relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-blue-500 rounded-full border-2 border-zinc-900"></span>
                    </button>
                </div>
            </header>

            <!-- Scrollable Content -->
            <main class="flex-1 overflow-y-auto p-8 md:p-12 custom-scrollbar">
                
                @if (session('success'))
                    <div class="max-w-5xl mx-auto mb-10 animate-in fade-in slide-in-from-top-4 duration-500">
                        <div class="bg-emerald-500/10 border border-emerald-500/20 p-5 rounded-2xl flex items-center gap-4 shadow-2xl shadow-emerald-500/5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400 flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <p class="text-sm font-bold text-emerald-50 text-zinc-100">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <div class="max-w-6xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
