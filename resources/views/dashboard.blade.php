@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6 lg:px-8">
    <h1 class="text-3xl font-semibold mb-8 text-gray-900">Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-10 items-stretch justify-center">
        @php $user = Auth::user(); @endphp
        @if($user && $user->hasPermission('companies'))
        <!-- Kartica: Firme -->
        <a href="{{ route('companies.index') }}" class="group h-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-teal-200 dark:border-teal-700 p-5 hover:shadow-lg hover:border-teal-400 dark:hover:border-teal-500 transition relative">
            <div>
                <div class="flex items-start justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 tracking-wide">FIRME</h3>
                    <span class="text-xs px-2 py-1 rounded-md bg-teal-50 text-teal-700 group-hover:bg-teal-100 dark:bg-teal-900/40 dark:text-teal-300">{{ isset($companies) ? $companies->count() : 0 }}</span>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-gray-600 dark:text-gray-400">Pregled registrovanih firmi u sistemu.</p>
            </div>
            <div class="mt-auto pt-4 text-teal-600 group-hover:text-teal-700 dark:text-teal-400 dark:group-hover:text-teal-300 text-sm font-medium flex items-center gap-1">
                Otvori
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </div>
            <div class="absolute inset-0 rounded-xl pointer-events-none opacity-0 group-hover:opacity-10 bg-gradient-to-br from-teal-400 to-teal-600 transition"></div>
        </a>
        @endif

        @if($user && $user->hasPermission('instruments'))
        <!-- Kartica: Instrumenti -->
        <a href="{{ route('instruments.index') }}" class="group h-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-sky-200 dark:border-sky-700 p-5 hover:shadow-lg hover:border-sky-400 dark:hover:border-sky-500 transition relative">
            <div>
                <div class="flex items-start justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 tracking-wide">INSTRUMENTI</h3>
                    <span class="text-xs px-2 py-1 rounded-md bg-sky-50 text-sky-700 group-hover:bg-sky-100 dark:bg-sky-900/40 dark:text-sky-300">{{ isset($instruments) ? $instruments->count() : 0 }}</span>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-gray-600 dark:text-gray-400">Lista mjernih instrumenata po firmama.</p>
            </div>
            <div class="mt-auto pt-4 text-sky-600 group-hover:text-sky-700 dark:text-sky-400 dark:group-hover:text-sky-300 text-sm font-medium flex items-center gap-1">
                Otvori
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </div>
            <div class="absolute inset-0 rounded-xl pointer-events-none opacity-0 group-hover:opacity-10 bg-gradient-to-br from-sky-400 to-sky-600 transition"></div>
        </a>
        @endif

        @if($user && $user->hasPermission('reports'))
        <!-- Kartica: Izvještaji -->
        <a href="{{ route('reports.index') }}" class="group h-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-emerald-200 dark:border-emerald-700 p-5 hover:shadow-lg hover:border-emerald-400 dark:hover:border-emerald-500 transition relative">
            <div>
                <div class="flex items-start justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 tracking-wide">DETALJNI IZVJEŠTAJ</h3>
                    <span class="text-xs px-2 py-1 rounded-md bg-emerald-50 text-emerald-700 group-hover:bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300">→</span>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-gray-600 dark:text-gray-400">Grafikoni, vrijednosti i filtriranje.</p>
            </div>
            <div class="mt-auto pt-4 text-emerald-600 group-hover:text-emerald-700 dark:text-emerald-400 dark:group-hover:text-emerald-300 text-sm font-medium flex items-center gap-1">
                Otvori
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </div>
            <div class="absolute inset-0 rounded-xl pointer-events-none opacity-0 group-hover:opacity-10 bg-gradient-to-br from-emerald-400 to-emerald-600 transition"></div>
        </a>
        @endif

        @if($user && $user->hasPermission('summary'))
        <!-- Kartica: Sumarni -->
        <a href="{{ route('reports.summary') }}" class="group h-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-fuchsia-200 dark:border-fuchsia-700 p-5 hover:shadow-lg hover:border-fuchsia-400 dark:hover:border-fuchsia-500 transition relative">
            <div>
                <div class="flex items-start justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 tracking-wide">SUMARNI</h3>
                    <span class="text-xs px-2 py-1 rounded-md bg-fuchsia-50 text-fuchsia-700 group-hover:bg-fuchsia-100 dark:bg-fuchsia-900/40 dark:text-fuchsia-300">12M</span>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-gray-600 dark:text-gray-400">Mjesečni pregled ukupne potrošnje.</p>
            </div>
            <div class="mt-auto pt-4 text-fuchsia-600 group-hover:text-fuchsia-700 dark:text-fuchsia-400 dark:group-hover:text-fuchsia-300 text-sm font-medium flex items-center gap-1">
                Otvori
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </div>
            <div class="absolute inset-0 rounded-xl pointer-events-none opacity-0 group-hover:opacity-10 bg-gradient-to-br from-fuchsia-400 to-fuchsia-600 transition"></div>
        </a>
        @endif

        @if($user && $user->hasPermission('instrumentSummary'))
        <!-- Kartica: Instrument (mjesečno) -->
        <a href="{{ route('reports.instrumentSummary') }}" class="group h-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border-2 border-indigo-200 dark:border-indigo-700 p-5 hover:shadow-lg hover:border-indigo-400 dark:hover:border-indigo-500 transition relative">
            <div>
                <div class="flex items-start justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 tracking-wide">IZVJEŠTAJ PO INSTRUMENT-U</h3>
                    <span class="text-xs px-2 py-1 rounded-md bg-indigo-50 text-indigo-700 group-hover:bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-300">Δ</span>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-gray-600 dark:text-gray-400">Mjesečna potrošnja po instrumentu.</p>
            </div>
            <div class="mt-auto pt-4 text-indigo-600 group-hover:text-indigo-700 dark:text-indigo-400 dark:group-hover:text-indigo-300 text-sm font-medium flex items-center gap-1">
                Otvori
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </div>
            <div class="absolute inset-0 rounded-xl pointer-events-none opacity-0 group-hover:opacity-10 bg-gradient-to-br from-indigo-400 to-indigo-600 transition"></div>
        </a>
        @endif
    </div>

    @if($user && !$user->hasPermission('companies') && !$user->hasPermission('instruments') && !$user->hasPermission('reports') && !$user->hasPermission('summary') && !$user->hasPermission('instrumentSummary'))
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 text-center text-sm text-gray-600 dark:text-gray-300">Nemate dodijeljenih modula za prikaz. Obratite se administratoru.</div>
    @endif


</div>
@endsection
