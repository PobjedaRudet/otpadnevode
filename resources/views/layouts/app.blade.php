<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Otpadne Vode') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .sidebar-collapsed .sidebar-label { display: none; }
        .sidebar-collapsed .sidebar-wrapper { width: 4rem; }
        .sidebar-transition { transition: width .25s ease; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-gray-100 dark:bg-gray-900 flex">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar-wrapper w-64 sidebar-transition bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
        <div class="h-16 flex items-center px-4 border-b border-gray-200 dark:border-gray-700">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-teal-500 to-sky-600 flex items-center justify-center text-white font-semibold shadow-md">
                    OV
                </div>
                <span class="ml-3 text-lg font-semibold text-gray-900 dark:text-gray-50 sidebar-label">Otpadne Vode</span>
            </a>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
            @php
                $user = Auth::user();
                $nav = [
                    ['label' => 'Dashboard', 'route' => 'dashboard', 'permission' => 'dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['label' => 'Firme', 'route' => 'companies.index', 'permission' => 'companies', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v12H4V6zm6 0a2 2 0 012-2h2a2 2 0 012 2v12h-6V6zm8 0a2 2 0 012-2h2a2 2 0 012 2v12h-6V6'],
                    ['label' => 'Instrumenti', 'route' => 'instruments.index', 'permission' => 'instruments', 'icon' => 'M11 3a1 1 0 00-1 1v7.586l-1.707 1.707a1 1 0 001.414 1.414L12 13.414l2.293 2.293a1 1 0 001.414-1.414L14 11.586V4a1 1 0 00-1-1h-2z M5 20h14a2 2 0 002-2v-1H3v1a2 2 0 002 2z'],
                    ['label' => 'Detaljni izvještaj', 'route' => 'reports.index', 'permission' => 'reports', 'icon' => 'M9 17v-6h2v6H9zm4 0V7h2v10h-2zM5 17h2v-2H5v2zm8-12V4a1 1 0 00-1-1H6a1 1 0 00-1 1v14h14V7h-2a1 1 0 01-1-1h-3z'],
                    ['label' => 'Sumarni izvještaj', 'route' => 'reports.summary', 'permission' => 'summary', 'icon' => 'M3 3h18v4H3V3zm0 6h18v4H3V9zm0 6h18v4H3v-4z'],
                    ['label' => 'Izvještaj po instrumentu', 'route' => 'reports.instrumentSummary', 'permission' => 'instrumentSummary', 'icon' => 'M9 17v-6h2v6H9zm4 0V7h2v10h-2zM5 17h2v-2H5v2zm8-12V4a1 1 0 00-1-1H6a1 1 0 00-1 1v14h14V7h-2a1 1 0 01-1-1h-3z'],
                    ['label' => 'Izvještaj po periodu', 'route' => 'reports.period', 'permission' => 'reports', 'icon' => 'M8 7V3m8 4V3M3 9h18M5 9v10a2 2 0 002 2h10a2 2 0 002-2V9H5z'],
                ];
            @endphp
            @foreach($nav as $item)
                @php
                    $show = $user && $user->hasPermission($item['permission']);
                    $active = $show && $item['route'] && request()->routeIs($item['route']);
                @endphp
                @if($show)
                    <a href="{{ route($item['route']) }}"
                       class="group flex items-center px-3 py-2 rounded-md text-sm font-medium tracking-wide
                        {{ $active ? 'bg-teal-600 text-white shadow' : 'text-gray-700 dark:text-gray-300 hover:bg-teal-50 dark:hover:bg-gray-700 hover:text-teal-700 dark:hover:text-teal-300' }}">
                        <svg class="h-5 w-5 flex-shrink-0 {{ $active ? 'text-white' : 'text-teal-500 group-hover:text-teal-600 dark:group-hover:text-teal-400' }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="{{ $item['icon'] }}" />
                        </svg>
                        <span class="ml-3 sidebar-label">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach

            @if(Auth::check() && Auth::user()->role === 'admin')
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="px-3 mb-2 text-xs font-semibold tracking-wider text-gray-500 dark:text-gray-400 uppercase">Admin</p>
                    @php
                        $adminNav = [
                            ['label' => 'Admin Panel', 'route' => 'admin.panel', 'icon' => 'M4 4h16v4H4V4zm0 6h10v10H4V10zm12 0h4v10h-4V10z'],
                        ];
                    @endphp
                    @foreach($adminNav as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="group flex items-center px-3 py-2 rounded-md text-sm font-medium tracking-wide
                            {{ $active ? 'bg-amber-600 text-white shadow' : 'text-gray-700 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 hover:text-amber-700 dark:hover:text-amber-300' }}">
                            <svg class="h-5 w-5 flex-shrink-0 {{ $active ? 'text-white' : 'text-amber-500 group-hover:text-amber-600 dark:group-hover:text-amber-400' }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                <path d="{{ $item['icon'] }}" />
                            </svg>
                            <span class="ml-3 sidebar-label">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </nav>
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium rounded-md bg-red-600 hover:bg-red-700 text-white shadow">
                    Odjava
                </button>
            </form>
        </div>
    </aside>

    <!-- Main area -->
    <div class="flex-1 flex flex-col min-h-screen">
        <!-- Top bar -->
        <header class="h-16 bg-white/70 dark:bg-gray-800/70 backdrop-blur border-b border-gray-200 dark:border-gray-700 flex items-center px-4 justify-between">
            <div class="flex items-center space-x-3">
                <button id="sidebarToggle" class="inline-flex items-center justify-center h-9 w-9 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <svg id="sidebarToggleIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $title ?? 'Pregled' }}</h1>
            </div>
            <div class="hidden md:flex items-center text-sm text-gray-500 dark:text-gray-400">
                <span>{{ Auth::user()->name ?? '' }}</span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-8">
            {{-- Flash poruke --}}
            @if(session('error'))
                <div class="mb-4 relative rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 shadow" role="alert" data-flash-alert data-alert-type="error">
                    <span class="font-medium">Greška:</span> {{ session('error') }}
                    <button type="button" class="absolute top-1.5 right-2 text-red-500 hover:text-red-700" data-alert-close aria-label="Zatvori">&times;</button>
                </div>
            @endif
            @if(session('status'))
                <div class="mb-4 relative rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-700 shadow" role="alert" data-flash-alert data-alert-type="status">
                    <span class="font-medium">Info:</span> {{ session('status') }}
                    <button type="button" class="absolute top-1.5 right-2 text-green-600 hover:text-green-800" data-alert-close aria-label="Zatvori">&times;</button>
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="px-6 py-4 text-xs text-gray-500 dark:text-gray-500 border-t border-gray-200 dark:border-gray-700">
            © {{ date('Y') }} Otpadne Vode. Sva prava zadržana.
        </footer>
    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            let collapsed = false;
            toggle.addEventListener('click', () => {
                collapsed = !collapsed;
                if (collapsed) {
                    body.classList.add('sidebar-collapsed');
                } else {
                    body.classList.remove('sidebar-collapsed');
                }
            });

            // Flash alert auto-hide & close
            const alerts = document.querySelectorAll('[data-flash-alert]');
            alerts.forEach(alert => {
                const closeBtn = alert.querySelector('[data-alert-close]');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => alert.remove());
                }
                // Auto-hide nakon 6 sekundi osim error poruka (njih ostavi 10s)
                const type = alert.getAttribute('data-alert-type');
                const timeout = type === 'error' ? 10000 : 6000;
                setTimeout(() => {
                    alert.classList.add('opacity-0', 'transition', 'duration-500');
                    setTimeout(() => alert.remove(), 520);
                }, timeout);
            });
        });
    </script>
</body>
</html>
