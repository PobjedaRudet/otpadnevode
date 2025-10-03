@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6 lg:px-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Mjerni instrumenti</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Grupisano po firmama • Ukupno instrumenata: <span class="font-semibold text-teal-600 dark:text-teal-400">{{ $totalInstruments }}</span></p>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-teal-600 hover:text-teal-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            <span class="ml-1">Nazad</span>
        </a>
    </div>

    @forelse($companies as $company)
        <div class="mb-8 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between bg-gray-50 dark:bg-gray-700/40">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-md bg-gradient-to-br from-teal-500 to-sky-600 flex items-center justify-center text-white font-semibold text-sm shadow">{{ mb_substr($company->name,0,2) }}</div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $company->name }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Instrumenata: {{ $company->instruments->count() }}</p>
                    </div>
                </div>
                @if($company->instruments->count() > 8)
                    <button type="button" class="toggle-section text-xs text-teal-600 hover:text-teal-700 font-medium" data-target="company-{{ $company->id }}">Sakrij / Prikaži</button>
                @endif
            </div>
            @if($company->instruments->count())
            <div id="company-{{ $company->id }}" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm text-center">
                    <thead class="bg-white dark:bg-gray-800">
                        <tr class="text-gray-600 dark:text-gray-300">
                            <th class="px-4 py-2 font-medium">#</th>
                            <th class="px-4 py-2 font-medium">Instrument</th>
                            <th class="px-4 py-2 font-medium">Akcije</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($company->instruments as $inst)
                        <tr class="hover:bg-teal-50/60 dark:hover:bg-gray-700/40 transition">
                            <td class="px-4 py-2 align-middle text-gray-700 dark:text-gray-300">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $inst->name }}</td>
                            <td class="px-4 py-2 align-middle">
                                <a href="{{ route('reports.index', ['company_id' => $company->id, 'instrument_id' => $inst->id]) }}" class="inline-flex items-center justify-center text-xs px-3 py-1 rounded-md bg-teal-600 hover:bg-teal-700 text-black shadow">Izvještaj</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">Nema instrumenata za ovu firmu.</div>
            @endif
        </div>
    @empty
        <div class="text-sm text-gray-500 dark:text-gray-400">Nema firmi ili instrumenata.</div>
    @endforelse
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.toggle-section').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.getElementById(btn.dataset.target);
                if (!target) return;
                target.classList.toggle('hidden');
            });
        });
    });
</script>
@endpush
@endsection
