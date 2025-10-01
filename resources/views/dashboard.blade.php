<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 dark:bg-[#0a0a0a] text-gray-900 dark:text-gray-100">
    <div class="max-w-3xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold">Dashboard</h1>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-500">Odjava</button>
            </form>
        </div>
        <p>Ulogovani ste kao <strong>{{ Auth::user()->name }}</strong>.</p>
        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">Ovdje možete dodati sadržaj aplikacije.</p>
    </div>
</body>
</html>
