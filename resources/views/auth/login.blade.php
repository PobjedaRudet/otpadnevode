@php($title = 'Prijava')
@php($heading = 'Dobrodošao natrag')
@php($subheading = 'Prijavi se u svoj račun i nastavi gdje si stao.')

<x-auth-layout :title="$title" :heading="$heading" :subheading="$subheading">
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <div class="space-y-1.5">
            <label class="block text-xs font-medium tracking-wide uppercase text-[#706f6c] dark:text-[#A1A09A]" for="email">Email</label>
            <div class="relative group">
                <input id="email" name="email" type="email" inputmode="email" autocomplete="username" value="{{ old('email') }}" required autofocus placeholder="ime@domena.com" class="peer w-full rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white/70 dark:bg-[#1b1b18]/60 backdrop-blur px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/60 focus:border-orange-500 dark:focus:ring-orange-400/50 transition" />
                <div class="pointer-events-none opacity-0 peer-focus:opacity-100 transition absolute inset-y-0 right-2 flex items-center text-orange-500">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
            </div>
            @error('email') <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5" data-password-wrapper>
            <label class="block text-xs font-medium tracking-wide uppercase text-[#706f6c] dark:text-[#A1A09A]" for="password">Lozinka</label>
            <div class="relative">
                <input id="password" data-password name="password" type="password" autocomplete="current-password" required placeholder="••••••••" class="peer w-full rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white/70 dark:bg-[#1b1b18]/60 backdrop-blur px-3.5 py-2.5 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/60 focus:border-orange-500 dark:focus:ring-orange-400/50 transition" />
                <button type="button" data-toggle class="absolute inset-y-0 right-0 px-3 flex items-center text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#ededec]">
                    <svg data-eye class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg data-eye-off class="size-4 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="m3 3 18 18M10.6 10.65A3 3 0 0 0 9 13a3 3 0 0 0 5.06 2.12M6.1 6.16C3.89 7.78 2 12 2 12s3.6 7 10 7c2.11 0 3.96-.5 5.53-1.27M13.35 10.63A3 3 0 0 1 15 13c0 .23-.03.45-.08.66M9.5 5.21A9 9 0 0 1 12 5c6.4 0 10 7 10 7a18.9 18.9 0 0 1-2.24 3.56"/></svg>
                </button>
            </div>
            <div class="flex items-center justify-between text-xs mt-1">
                <label class="inline-flex items-center gap-2 select-none cursor-pointer">
                    <input type="checkbox" name="remember" class="size-4 rounded border-[#c9c9c6] dark:border-[#3E3E3A] text-orange-600 focus:ring-orange-500/40" />
                    <span class="text-[#706f6c] dark:text-[#A1A09A]">Zapamti me</span>
                </label>
                <a href="{{ url('/') }}" class="text-orange-600 dark:text-orange-400 hover:underline font-medium">Početna</a>
            </div>
            @error('password') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="pt-2">
            <button class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-orange-600 to-rose-600 text-white font-medium py-2.5 text-sm shadow hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-60">
                <span>Prijava</span>
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </div>
        <p class="text-xs text-center text-[#706f6c] dark:text-[#A1A09A]">Nemaš račun? <a href="{{ route('register') }}" class="font-medium text-orange-600 dark:text-orange-400 hover:underline">Registruj se</a></p>
    </form>
</x-auth-layout>
