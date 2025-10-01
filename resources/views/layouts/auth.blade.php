<!DOCTYPE html>
<html lang="hr" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title ?? config('app.name','Aplikacija') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        @media (prefers-reduced-motion: no-preference) {
            .fade-in { animation: fade .5s ease; }
            @keyframes fade { from { opacity:0; transform: translateY(6px);} to { opacity:1; transform: none;} }
        }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-amber-50 via-white to-rose-50 dark:from-[#0a0a0a] dark:via-[#121212] dark:to-[#1a1a1a] text-[#1b1b18] dark:text-[#ededec] flex items-center justify-center p-4 sm:p-6">
    <div class="absolute inset-0 pointer-events-none select-none overflow-hidden">
        <div class="absolute -top-24 -left-24 size-[420px] bg-gradient-to-br from-orange-300/30 to-pink-300/10 blur-3xl rounded-full dark:from-orange-600/20 dark:to-pink-600/10"></div>
        <div class="absolute -bottom-24 -right-24 size-[420px] bg-gradient-to-tr from-rose-300/20 to-amber-200/10 blur-3xl rounded-full dark:from-rose-800/30 dark:to-amber-700/10"></div>
    </div>

    <main class="relative w-full max-w-md fade-in">
        <div class="backdrop-blur-xl bg-white/70 dark:bg-[#161615]/70 shadow-[0_1px_2px_0_rgba(0,0,0,0.06),0_2px_6px_-1px_rgba(0,0,0,0.08)] dark:shadow-[0_1px_2px_0_rgba(255,255,255,0.04),0_2px_6px_-1px_rgba(255,255,255,0.06)] border border-[#19140035]/40 dark:border-[#3E3E3A]/40 rounded-2xl p-6 sm:p-8">
            <div class="flex items-center justify-between mb-6">
                <a href="/" class="inline-flex items-center gap-2 font-medium text-sm text-[#1b1b18] dark:text-[#ededec] hover:opacity-80 transition">
                    <span class="inline-flex items-center justify-center size-9 rounded-lg bg-gradient-to-tr from-orange-500 to-rose-500 text-white font-semibold shadow">
                        {{ substr(config('app.name','APP'),0,2) }}
                    </span>
                    <span>{{ config('app.name','Aplikacija') }}</span>
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-xs font-medium px-3 py-1.5 rounded-md bg-black text-white dark:bg-white dark:text-black hover:opacity-90">Dashboard</a>
                @endauth
            </div>

            @isset($heading)
                <h1 class="text-2xl font-semibold tracking-tight mb-1">{{ $heading }}</h1>
            @endisset
            @isset($subheading)
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-5 leading-relaxed">{!! $subheading !!}</p>
            @endisset

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-300/70 dark:border-red-600/50 bg-red-50 dark:bg-red-900/20 p-3 text-sm text-red-700 dark:text-red-300">
                    <ul class="list-disc ms-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </div>
        <div class="mt-6 text-center text-xs text-[#706f6c] dark:text-[#6f6e69]">
            © {{ date('Y') }} {{ config('app.name','Aplikacija') }} · <button id="themeToggle" type="button" class="underline underline-offset-4 hover:text-[#1b1b18] dark:hover:text-[#ededec]">Tema</button>
        </div>
    </main>

<script>
// Theme toggle (localStorage)
const btn = document.getElementById('themeToggle');
if(btn){
  btn.addEventListener('click', () => {
    const root = document.documentElement;
    const dark = root.classList.toggle('dark');
    localStorage.setItem('theme', dark ? 'dark':'light');
  });
  // initial
  if(localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
}
// Password visibility toggles
for(const wrapper of document.querySelectorAll('[data-password-wrapper]')){
  const input = wrapper.querySelector('input[type="password"], input[data-password]');
  const toggle = wrapper.querySelector('[data-toggle]');
  if(input && toggle){
    toggle.addEventListener('click', () => {
      input.type = input.type === 'password' ? 'text' : 'password';
      toggle.querySelector('[data-eye]')?.classList.toggle('hidden');
      toggle.querySelector('[data-eye-off]')?.classList.toggle('hidden');
    });
  }
}
</script>
</body>
</html>
