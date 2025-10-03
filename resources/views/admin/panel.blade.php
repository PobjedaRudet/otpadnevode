@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-10">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100 flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-sky-600 text-white shadow-md text-xl font-semibold">A</span>
                <span>Administratorski panel</span>
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Upravljanje korisnicima, rolama i dozvolama sistema.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19l-7-7 7-7"/></svg>
                Nazad
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-start gap-3 shadow-sm">
            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-6 6-6m0 0h-4m4 0v4"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800/80 backdrop-blur rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/60 dark:bg-gray-900/40">
            <h2 class="text-sm font-semibold tracking-wide text-gray-600 dark:text-gray-300 uppercase">Korisnici sistema</h2>
            <span class="text-xs text-gray-400">Ukupno: {{ $users->count() }}</span>
        </div>

        {{-- Mobile (cards) view --}}
        <div class="md:hidden p-4 space-y-4">
            @foreach($users as $user)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 shadow-sm">
                    <div class="p-4 flex items-start gap-3">
                            <div class="h-12 w-12 shrink-0 rounded-lg flex items-center justify-center font-semibold shadow
                                {{ $user->role === 'admin'
                                    ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white'
                                    : 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-100' }}">{{ strtoupper(substr($user->name,0,1)) }}</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <h3 class="font-medium text-gray-800 dark:text-gray-100 text-sm">{{ $user->name }}</h3>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $user->role === 'admin' ? 'bg-amber-50 text-amber-700 ring-amber-300' : 'bg-slate-50 text-slate-700 ring-slate-300' }}">
                                    {{ $user->role === 'admin' ? 'Admin' : 'Korisnik' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 break-all">{{ $user->email }}</p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @php $perms = $user->permissions ?? []; @endphp
                                @forelse($availablePermissions as $permKey => $permLabel)
                                    @if(is_array($perms) && in_array($permKey, $perms))
                                        <span class="px-2 py-0.5 rounded-full bg-teal-100 text-teal-700 text-[10px] font-medium border border-teal-200">{{ $permLabel }}</span>
                                    @endif
                                @empty
                                    <span class="text-[10px] text-gray-400">Nema dozvola</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <details class="group border-t border-gray-100 dark:border-gray-700">
                        <summary class="flex items-center justify-between cursor-pointer px-4 py-2 text-xs font-medium text-teal-700 dark:text-teal-400 bg-teal-50/60 dark:bg-gray-800/60 hover:bg-teal-100 dark:hover:bg-gray-700">
                            <span>Uredi</span>
                            <svg class="h-4 w-4 transform transition group-open:rotate-90" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
                        </summary>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50">
                            <form method="POST" action="{{ route('admin.user.role', $user->id) }}" class="space-y-3">
                                @csrf
                                <div class="flex items-center gap-2">
                                    <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300">Rola</label>
                                    <select name="role" class="text-xs flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                                        <option value="user" @selected($user->role=='user')>Korisnik</option>
                                        <option value="admin" @selected($user->role=='admin')>Administrator</option>
                                    </select>
                                </div>
                                <fieldset class="space-y-1">
                                    <legend class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 tracking-wide uppercase">Dozvole</legend>
                                    <div class="grid grid-cols-2 gap-1">
                                        @foreach($availablePermissions as $permKey => $permLabel)
                                            <label class="flex items-center gap-1.5 text-[10px] text-gray-600 dark:text-gray-300">
                                                <input type="checkbox" name="permissions[]" value="{{ $permKey }}" class="rounded border-gray-300 dark:border-gray-600 text-teal-600 focus:ring-teal-500" @checked(is_array($user->permissions) && in_array($permKey, $user->permissions))>
                                                <span>{{ $permLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-teal-600 px-3 py-1.5 text-[11px] font-medium text-white shadow hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                        Spremi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </details>
                </div>
            @endforeach
        </div>

        {{-- Desktop (table) view --}}
        <div class="overflow-x-auto hidden md:block">
            <table class="min-w-full text-sm">
                <thead class="text-xs uppercase tracking-wider bg-gray-100 dark:bg-gray-900/60 text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Korisnik</th>
                        <th class="px-4 py-3 text-left font-semibold">Email</th>
                        <th class="px-4 py-3 text-left font-semibold">Rola</th>
                        <th class="px-4 py-3 text-left font-semibold">Dozvole</th>
                        <th class="px-4 py-3 text-left font-semibold">Ažuriranje</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($users as $user)
                        <tr class="hover:bg-teal-50/60 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-3 align-top">
                                <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg flex items-center justify-center font-semibold shadow-sm
                                            {{ $user->role === 'admin'
                                                ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white'
                                                : 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-100' }}">
                                            {{ strtoupper(substr($user->name,0,1)) }}
                                        </div>
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-gray-100">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $user->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">{{ $user->email }}</td>
                            <td class="px-4 py-3 align-top">
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $user->role === 'admin' ? 'bg-amber-50 text-amber-700 ring-amber-300' : 'bg-slate-50 text-slate-700 ring-slate-300' }}">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-3.866 0-7 2.239-7 5v1h14v-1c0-2.761-3.134-5-7-5z"/></svg>
                                    {{ $user->role === 'admin' ? 'Administrator' : 'Korisnik' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top w-72">
                                <div class="flex flex-wrap gap-1">
                                    @php $perms = $user->permissions ?? []; @endphp
                                    @forelse($availablePermissions as $permKey => $permLabel)
                                        @if(is_array($perms) && in_array($permKey, $perms))
                                            <span class="px-2 py-0.5 rounded-full bg-teal-100 text-teal-700 text-[11px] font-medium border border-teal-200">{{ $permLabel }}</span>
                                        @endif
                                    @empty
                                        <span class="text-xs text-gray-400">Nema dozvola</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <form method="POST" action="{{ route('admin.user.role', $user->id) }}" class="space-y-3 bg-gray-50 dark:bg-gray-900/40 p-3 rounded-lg border border-gray-200 dark:border-gray-700 shadow-inner">
                                    @csrf
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Rola</label>
                                        <select name="role" class="text-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                                            <option value="user" @selected($user->role=='user')>Korisnik</option>
                                            <option value="admin" @selected($user->role=='admin')>Administrator</option>
                                        </select>
                                    </div>
                                    <fieldset class="space-y-1">
                                        <legend class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 tracking-wide uppercase">Dozvole</legend>
                                        <div class="grid grid-cols-2 gap-1">
                                            @foreach($availablePermissions as $permKey => $permLabel)
                                                <label class="flex items-center gap-1.5 text-[11px] text-gray-600 dark:text-gray-300">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permKey }}" class="rounded border-gray-300 dark:border-gray-600 text-teal-600 focus:ring-teal-500" @checked(is_array($user->permissions) && in_array($permKey, $user->permissions))>
                                                    <span>{{ $permLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-teal-600 px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                            Spremi
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-10 grid gap-6 md:grid-cols-3">
        <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Statistika</p>
            <p class="text-3xl font-bold text-teal-600">{{ $users->where('role','admin')->count() }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Admin korisnika</p>
        </div>
        <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Ukupno korisnika</p>
            <p class="text-3xl font-bold text-sky-600">{{ $users->count() }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Registrovani nalozi</p>
        </div>
        <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Dozvole aktivne</p>
            <p class="text-3xl font-bold text-amber-600">{{ collect($users)->pluck('permissions')->filter()->flatten()->unique()->count() }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Različitih tipova</p>
        </div>
    </div>
</div>
@endsection
