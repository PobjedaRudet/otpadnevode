@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Mjesečni izvještaj po instrumentu</h1>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-teal-600 hover:text-teal-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            <span class="ml-1">Nazad</span>
        </a>
    </div>

    <form id="filterFormInstrument" method="GET" action="{{ route('reports.instrumentSummary') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6 mb-8 flex flex-wrap gap-6 items-end">
        <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="company_id">Firma</label>
            <select name="company_id" id="company_id" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-900 dark:border-gray-600">
                <option value="">-- Izaberite firmu --</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" @selected(optional($selectedCompany)->id === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="instrument_id">Instrument</label>
            <select name="instrument_id" id="instrument_id" @disabled(!$selectedCompany) class="px-3 py-2 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-900 dark:border-gray-600 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed dark:disabled:bg-gray-800 dark:disabled:text-gray-500">
                <option value="">-- Instrument --</option>
                @foreach($instruments as $inst)
                    <option value="{{ $inst->id }}" @selected(optional($selectedInstrument)->id === $inst->id)>{{ $inst->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="year">Godina</label>
            <select name="year" id="year" @disabled(!($selectedCompany && $selectedInstrument)) class="px-3 py-2 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-900 dark:border-gray-600 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed dark:disabled:bg-gray-800 dark:disabled:text-gray-500">
                <option value="">-- Godina --</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </form>

    @if($selectedCompany && $selectedInstrument && $year)
        @if($monthlyData->count())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <div class="flex items-start justify-between mb-5">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 tracking-tight">{{ $selectedInstrument->name }} – mjesečna potrošnja ({{ $year }})</h2>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Razlika (zadnja - prva vrijednost) po mjesecima; kumulativno uključeno u grafiku.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-md bg-teal-50 dark:bg-teal-900/30 px-2 py-1 text-[11px] font-medium text-teal-700 dark:text-teal-300">
                                <span class="h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span> Aktivno
                            </span>
                        </div>
                    </div>
                    @php $maxValRow = max(0, $monthlyData->max('total')); @endphp
                    <div class="relative overflow-hidden rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                        <table class="min-w-full text-sm">
                            <caption class="sr-only">Mjesečna potrošnja – {{ $selectedInstrument->name }} ({{ $year }})</caption>
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-700/60 text-gray-700 dark:text-gray-200 text-xs uppercase tracking-wide sticky top-0 z-10">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold sticky left-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-700/60 z-10">Mjesec</th>
                                    <th class="pr-4 pl-2 py-2 text-right font-semibold">Ukupno</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 bg-white dark:bg-gray-800">
                                @foreach($monthlyData as $row)
                                    @php
                                        $val = $row['total'];
                                        $pct = $maxValRow > 0 ? ($val / $maxValRow * 100) : 0;
                                        $isMax = $maxValRow > 0 && $val == $maxValRow;
                                    @endphp
                                    <tr class="group transition-colors hover:bg-teal-50/70 dark:hover:bg-gray-700/50">
                                        <td class="px-4 py-2 align-middle text-left sticky left-0 bg-white dark:bg-gray-800 group-hover:bg-teal-50/70 dark:group-hover:bg-gray-700/50">
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100 whitespace-nowrap">{{ $row['label'] }}</span>
                                        </td>
                                        <td class="pr-4 pl-2 py-2 font-medium text-right align-middle">
                                            <div class="relative">
                                                <div class="h-5 rounded bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                                    <div class="h-full {{ $isMax ? 'bg-teal-600' : 'bg-teal-400/70 dark:bg-teal-500/80 group-hover:bg-teal-500' }} transition-all duration-500" style="width: {{ number_format($pct, 2, '.', '') }}%"></div>
                                                </div>
                                                <div class="absolute inset-0 flex items-center justify-end pr-1">
                                                    <span class="text-[11px] font-bold tracking-wide {{ $val < 0 ? 'text-rose-600 dark:text-rose-400' : ($val == 0 ? 'text-gray-500 dark:text-gray-400' : 'text-emerald-600 dark:text-emerald-400') }}">{{ number_format($val, 2) }}</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700/60 text-sm">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">Ukupno</th>
                                    <th class="pr-4 pl-2 py-3 text-right font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($totals['overall'], 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Grafički prikaz</h2>
                    </div>
                    <div class="relative h-72" id="chartContainer">
                        <canvas id="instrumentSummaryChart"></canvas>
                    </div>
                </div>
            </div>
        @else
            <div class="text-sm text-gray-500 dark:text-gray-400">Nema podataka za odabrani instrument i godinu.</div>
        @endif
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400">Odaberite firmu, instrument i godinu za prikaz.</div>
    @endif
</div>

@push('scripts')
<script>
// Auto logic
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('filterFormInstrument');
    const companySelect = document.getElementById('company_id');
    const instrumentSelect = document.getElementById('instrument_id');
    const yearSelect = document.getElementById('year');

    function toggle(disable, el){ el.disabled = disable; }

    function handleCompanyChange(){
        if(!companySelect.value){
            instrumentSelect.value='';
            yearSelect.value='';
            toggle(true, instrumentSelect);
            toggle(true, yearSelect);
            form.requestSubmit();
            return;
        }
        toggle(false, instrumentSelect);
        yearSelect.value='';
        toggle(true, yearSelect);
        form.requestSubmit();
    }
    function handleInstrumentChange(){
        if(!instrumentSelect.value){
            yearSelect.value='';
            toggle(true, yearSelect);
            form.requestSubmit();
            return;
        }
        toggle(false, yearSelect);
        // Ako je godina već izabrana – odmah osvježi izvještaj
        if (yearSelect.value.trim() !== '') {
            form.requestSubmit();
        }
    }
    companySelect.addEventListener('change', handleCompanyChange);
    instrumentSelect.addEventListener('change', handleInstrumentChange);
    yearSelect.addEventListener('change', ()=> { if(yearSelect.value) form.requestSubmit(); });
});
</script>
@if($selectedCompany && $selectedInstrument && $year && $monthlyData->count())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('instrumentSummaryChart').getContext('2d');
        const cumulative = @json($cumulativeValues);
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Mjesečno',
                        data: @json($chartValues),
                        backgroundColor: 'rgba(20,184,166,0.55)',
                        borderColor: 'rgb(20,184,166)',
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Kumulativno',
                        data: cumulative,
                        borderColor: 'rgb(99,102,241)',
                        backgroundColor: 'rgba(99,102,241,0.15)',
                        pointBackgroundColor: 'rgb(99,102,241)',
                        pointRadius: 4,
                        tension: 0.25,
                        fill: true,
                        yAxisID: 'y',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(tt){
                                if (tt.dataset.label === 'Mjesečno') {
                                    return ' Mjesečno: ' + (tt.parsed.y ?? 0).toFixed(2);
                                }
                                if (tt.dataset.type === 'line') {
                                    return ' Kumulativno: ' + (tt.parsed.y ?? 0).toFixed(2);
                                }
                                return ' ' + (tt.parsed.y ?? 0).toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Vrijednost' }
                    },
                    x: {
                        title: { display: true, text: 'Mjeseci' }
                    }
                }
            }
        });
    });
</script>
@endif
@endpush
@endsection
