@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Sumanrni mjesečni izvještaj</h1>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-teal-600 hover:text-teal-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            <span class="ml-1">Nazad</span>
        </a>
    </div>

    <form id="filterForm" method="GET" action="{{ route('reports.summary') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6 mb-2 flex flex-row gap-6 items-end">
        <div class="flex flex-col min-w-[180px]">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="company_id">Firma</label>
            <select name="company_id" id="company_id" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-900 dark:border-gray-600">
                <option value="">-- Izaberite firmu --</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" @selected(optional($selectedCompany)->id === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col min-w-[120px]">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="year">Godina</label>
            <select name="year" id="year" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-900 dark:border-gray-600">
                <option value="">-- Godina --</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col min-w-[220px] ml-auto items-end">
           {{--   <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-right w-full" for="month">Mjesec</label>  --}}
            <div class="flex items-center gap-2 justify-end w-full">
                <select name="month" id="month" @if(!$year) disabled @endif class="px-3 py-2 border border-gray-300 rounded-md focus:ring-teal-500 focus:border-teal-500 bg-white dark:bg-gray-900 dark:border-gray-600 disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-800 dark:disabled:text-gray-500">
                    <option value="">-- Mjesec --</option>
                    @php $mnames=[1=>'Januar',2=>'Februar',3=>'Mart',4=>'April',5=>'Maj',6=>'Juni',7=>'Juli',8=>'Avgust',9=>'Septembar',10=>'Oktobar',11=>'Novembar',12=>'Decembar']; $selMonth=request('month'); @endphp
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" @selected($selMonth==$m)>{{ $mnames[$m] }}</option>
                    @endforeach
                </select>
                <button type="button" id="btnExportAllDocx" class="inline-flex items-center gap-2 px-3 py-2 rounded-md border border-sky-600 text-sky-700 dark:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/10 disabled:opacity-50" @if(!$year) disabled @endif>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
                    Word (sve firme)
                </button>
            </div>
        </div>

    </form>
    @if(!optional($selectedCompany)->id)
        <div class="mb-6 text-[11px] text-gray-400 dark:text-gray-500" id="yearHint">Prvo izaberite firmu.</div>
    @endif

    @if($selectedCompany && $year)
        @if($monthlyData->count())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-10">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <div class="flex items-start justify-between mb-5">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 tracking-tight">Tabela potrošnje ({{ $selectedCompany->name }}, {{ $year }})</h2>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Razlika (zadnja - prva vrijednost) po mjesecima; istaknuta je najveća vrijednost.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-md bg-teal-50 dark:bg-teal-900/30 px-2 py-1 text-[11px] font-medium text-teal-700 dark:text-teal-300">
                                <span class="h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span> Aktivno
                            </span>
                        </div>
                    </div>
                    @php $maxValRow = max(0, $monthlyData->max('total')); @endphp
                    <div class="relative rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 overflow-x-auto">
                        <table class="min-w-[520px] w-full text-sm">
                            <caption class="sr-only">Mjesečna potrošnja – {{ $selectedCompany->name }} ({{ $year }})</caption>
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-700/60 text-gray-700 dark:text-gray-200 text-xs uppercase tracking-wide sticky top-0 z-10">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold md:sticky md:left-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-700/60 z-10"></th>
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
                                        <td class="px-4 py-2 align-middle text-left md:sticky md:left-0 bg-white dark:bg-gray-800 group-hover:bg-teal-50/70 dark:group-hover:bg-gray-700/50">
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100 whitespace-nowrap">{{ $row['label'] }}</span>
                                        </td>
                                        <td class="pr-4 pl-2 py-2 font-medium text-right align-middle">
                                            <div class="relative">
                                                <div class="h-5 rounded bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                                    <div class="h-full
                                                        {{ $isMax ? 'bg-teal-600' : 'bg-teal-400/70 dark:bg-teal-500/80 group-hover:bg-teal-500' }}
                                                        transition-all duration-500" style="width: {{ number_format($pct, 2, '.', '') }}%"></div>
                                                </div>
                                                <div class="absolute inset-0 flex items-center justify-end pr-1">
                                                    <span class="text-[11px] font-bold tracking-wide
                                                        {{ $val < 0
                                                            ? 'text-rose-600 dark:text-rose-400'
                                                            : ($val == 0
                                                                ? 'text-gray-500 dark:text-gray-400'
                                                                : 'text-blue-700 dark:text-blue-400') }}">
                                                        {{ number_format($val, 2) }} m³
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700/60 text-sm">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-gray-100">Ukupno</th>
                                    <th class="pr-4 pl-2 py-3 text-right font-bold text-blue-700 dark:text-blue-400">{{ number_format($totals['overall'], 2) }} m³</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Grafički prikaz</h2>
                        <div class="inline-flex rounded-md shadow-sm isolate">
                            <button type="button" id="btnBar" class="px-3 py-1.5 text-xs font-medium bg-teal-600 text-white rounded-l-md border border-teal-600">Stubičasti</button>
                            <button type="button" id="btnGantt" class="px-3 py-1.5 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-r-md border border-gray-300 dark:border-gray-600">Lista</button>
                        </div>
                    </div>
                    <div class="relative h-64 sm:h-72" id="chartContainer">
                        <canvas id="summaryChart"></canvas>
                    </div>
                    @php $maxVal = max(1, $monthlyData->max('total')); @endphp
                    <div id="ganttContainer" class="hidden max-h-72 overflow-y-auto pr-2 space-y-3">
                        @foreach($monthlyData as $row)
                            @php $pct = $maxVal > 0 ? ($row['total'] / $maxVal * 100) : 0; @endphp
                            <div>
                                <div class="flex justify-between text-[11px] font-medium mb-1 text-gray-700 dark:text-gray-300">
                                    <span>{{ $row['label'] }}</span>
                                    <span>{{ number_format($row['total'], 2) }} m³</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded h-4 relative overflow-hidden">
                                    <div class="h-4 bg-teal-500 dark:bg-teal-400 rounded transition-all duration-500" style="width: {{ number_format($pct, 2) }}%;"></div>
                                    <span class="absolute inset-0 text-[10px] flex items-center justify-center text-white font-semibold mix-blend-luminosity">{{ $pct > 8 ? number_format($row['total'], 0) . ' m³' : '' }}</span>
                                </div>
                            </div>
                        @endforeach
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700 text-[11px] text-gray-600 dark:text-gray-400">
                            Skala bazirana na maksimalnoj mjesečnoj vrijednosti (100%).
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-sm text-gray-500 dark:text-gray-400">Nema podataka za odabranu kombinaciju.</div>
        @endif
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400">Odaberite firmu i godinu za prikaz.</div>
    @endif
</div>

@push('scripts')
<script>
// Auto-submit & dependent selects logic (uvek dostupno)
document.addEventListener('DOMContentLoaded', () => {
    const companySelect = document.getElementById('company_id');
    const yearSelect = document.getElementById('year');
    const monthSelect = document.getElementById('month');
    const form = document.getElementById('filterForm');
    const yearHint = document.getElementById('yearHint');
    const btnExportAll = document.getElementById('btnExportAllDocx');

    if (!companySelect || !yearSelect) return;

    function updateYearState(focus = false) {
        const hasCompany = companySelect.value.trim() !== '';
        // Godina više nije zaključana na izbor firme; samo hint ostaje
        if (!hasCompany) {
            yearHint && yearHint.classList.remove('hidden');
        } else {
            yearHint && yearHint.classList.add('hidden');
            if (focus) yearSelect.focus();
        }
        // Mjesec je aktivan kada je izabrana godina
        if (monthSelect) monthSelect.disabled = !(yearSelect && yearSelect.value);
    }

    updateYearState(false);

    companySelect.addEventListener('change', () => {
        const hasYear = yearSelect.value.trim() !== '';
        updateYearState(!hasYear); // fokusiraj godinu samo ako nije već izabrana
        if (hasYear) {
            // Automatski osvježi izvještaj za novu firmu sa postojećom godinom
            form.requestSubmit();
        }
    });

    yearSelect.addEventListener('change', () => {
        if (monthSelect) monthSelect.disabled = !(yearSelect && yearSelect.value);
        if (yearSelect.value) { form.requestSubmit(); }
    });

    if (btnExportAll) {
        btnExportAll.addEventListener('click', () => {
            if (!yearSelect.value) return;
            const params = new URLSearchParams({
                year: yearSelect.value,
                month: monthSelect && monthSelect.value ? monthSelect.value : (new Date().getMonth()+1)
            });
            const url = '{{ route('reports.summary.exportAll') }}' + '?' + params.toString();
            window.location.href = url;
        });
    }
});
</script>
@if($selectedCompany && $year && $monthlyData->count())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('summaryChart').getContext('2d');
    // Kumulativni niz generisan u kontroleru
    const cumulative = @json($cumulativeValues);

        const chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Mjesečno',
                        data: @json($chartValues),
                        backgroundColor: (function(){
                            const vals = @json($chartValues);
                            const maxV = Math.max(1, ...vals.map(v => v ?? 0));
                            return vals.map(v => {
                                const r = (v || 0) / maxV; // 0..1
                                const alpha = 0.35 + 0.45 * r; // 0.35 - 0.80
                                return `rgba(20,184,166,${alpha.toFixed(2)})`;
                            });
                        })(),
                        borderColor: (function(){
                            const vals = @json($chartValues);
                            const maxV = Math.max(1, ...vals.map(v => v ?? 0));
                            return vals.map(v => {
                                const r = (v || 0) / maxV;
                                const g = 150 + Math.round(34 * r); // 150..184
                                return `rgb(20,${g},166)`;
                            });
                        })(),
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Kumulativno',
                        data: cumulative,
                        borderColor: 'rgb(99,102,241)',
                        backgroundColor: 'rgba(99,102,241,0.20)',
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
                                    return ' Mjesečno: ' + (tt.parsed.y ?? 0).toFixed(2) + ' m³';
                                }
                                if (tt.dataset.type === 'line') {
                                    return ' Kumulativno: ' + (tt.parsed.y ?? 0).toFixed(2) + ' m³';
                                }
                                return ' ' + (tt.parsed.y ?? 0).toFixed(2) + ' m³';
                            }
                        }
                    },
                    valueLabels: {}
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Vrijednost (m³)' },
                        ticks: {
                            callback: v => v + ' m³'
                        }
                    },
                    x: {
                        title: { display: true, text: 'Mjeseci (Godina: {{$year}})' }
                    }
                }
            }
        });

        // Plugin za iscrtavanje vrijednosti iznad stubaca
        const valueLabelsPlugin = {
            id: 'valueLabels',
            afterDatasetsDraw(chart, args, opts) {
                const {ctx} = chart;
                const ds = chart.data.datasets[0];
                const meta = chart.getDatasetMeta(0);
                ctx.save();
                ctx.font = '11px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                meta.data.forEach((el, i) => {
                    const val = ds.data[i];
                    if (val === null || val === undefined) return;
                    if (Number(val) === 0) return; // preskoči nule radi preglednosti
                    const {x, y} = el.tooltipPosition();
                    ctx.fillStyle = '#1f2937'; // gray-800
                    ctx.fillText(Number(val).toFixed(2) + ' m³', x, y - 4);
                });
                ctx.restore();
            }
        };
        chartInstance.config.plugins.push(valueLabelsPlugin);
        chartInstance.update();

        // Toggle logic
        const btnBar = document.getElementById('btnBar');
        const btnGantt = document.getElementById('btnGantt');
        const chartContainer = document.getElementById('chartContainer');
        const ganttContainer = document.getElementById('ganttContainer');

        function activate(mode) {
            if (mode === 'bar') {
                chartContainer.classList.remove('hidden');
                ganttContainer.classList.add('hidden');
                btnBar.classList.add('bg-teal-600','text-white');
                btnBar.classList.remove('bg-gray-100','dark:bg-gray-700','text-gray-700','dark:text-gray-200');
                btnGantt.classList.remove('bg-teal-600','text-white');
                btnGantt.classList.add('bg-gray-100','dark:bg-gray-700','text-gray-700','dark:text-gray-200');
            } else {
                chartContainer.classList.add('hidden');
                ganttContainer.classList.remove('hidden');
                btnGantt.classList.add('bg-teal-600','text-white');
                btnGantt.classList.remove('bg-gray-100','dark:bg-gray-700','text-gray-700','dark:text-gray-200');
                btnBar.classList.remove('bg-teal-600','text-white');
                btnBar.classList.add('bg-gray-100','dark:bg-gray-700','text-gray-700','dark:text-gray-200');
            }
        }

        btnBar.addEventListener('click', () => activate('bar'));
        btnGantt.addEventListener('click', () => activate('gantt'));
        // Default state
        activate('bar');
    });
</script>
@endif
@endpush
@endsection
