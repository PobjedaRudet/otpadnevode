@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">Izvještaji o otpadnim vodama</h1>

                <!-- Filter Form -->
                <form method="GET" action="{{ route('reports.index') }}" id="reportForm" class="flex flex-wrap items-end gap-4 mb-8">

                    <!-- Company Selection -->
                    <div class="flex flex-col">
                        <label for="company_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Izaberite firmu:
                        </label>
                        <select name="company_id" id="company_id" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none
                       focus:ring-teal-500 focus:border-teal-500" onchange="this.form.submit()">
                            <option value="">-- Izaberite firmu --</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" @if($selectedCompany && $selectedCompany->id == $company->id) selected @endif>
                                {{ $company->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Instrument Selection -->
                    <div class="flex flex-col">
                        <label for="instrument_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Izaberite mjerni instrument:
                        </label>
                        <select name="instrument_id" id="instrument_id" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none
                       focus:ring-teal-500 focus:border-teal-500" onchange="this.form.submit()" @if(!$selectedCompany) disabled @endif>
                            <option value="">-- Izaberite instrument --</option>
                            @foreach($instruments as $instrument)
                            <option value="{{ $instrument->id }}" @if($selectedInstrument && $selectedInstrument->id == $instrument->id) selected @endif>
                                {{ $instrument->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="flex flex-col">
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">
                            Datum od:
                        </label>
                        <input type="date" name="date_from" id="date_from" value="{{ $dateFrom ?? '' }}" onchange="autoSubmitIfReady()" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none
                      focus:ring-teal-500 focus:border-teal-500">
                    </div>

                    <!-- Date To -->
                    <div class="flex flex-col">
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">
                            Datum do:
                        </label>
                        <input type="date" name="date_to" id="date_to" value="{{ $dateTo ?? '' }}" onchange="autoSubmitIfReady()" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none
                      focus:ring-teal-500 focus:border-teal-500">
                    </div>

                    <!-- Per Page Selection -->
                    <div class="flex flex-col">
                        <label for="per_page" class="block text-sm font-medium text-gray-700 mb-2">Zapisa po stranici:</label>
                        <select name="per_page" id="per_page" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500" onchange="autoSubmitIfReady()">
                            @foreach([10,20,50] as $pp)
                            <option value="{{ $pp }}" @if($perPage==$pp) selected @endif>{{ $pp }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedInstrument)
                    <div class="flex">
                        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            Primijeni filter
                        </button>
                    </div>
                    @endif
                </form>


                <!-- Records Display -->
                @if($selectedInstrument && $records->count() > 0)
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        Podaci za {{ $selectedCompany->name }} - {{ $selectedInstrument->name }}
                        @if($periodText)
                        <span class="text-sm font-normal text-gray-600">({{ $periodText }})</span>
                        @endif
                    </h2>

                    <!-- Chart Section -->
                    @if(!empty($chartLabels) && count($chartLabels) > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Grafički prikaz vrijednosti</h3>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <canvas id="valuesChart" width="400" height="250"></canvas>
                        </div>
                    </div>
                    @endif

                    <!-- Statistics Summary -->
                    <div class="bg-gradient-to-r from-teal-50 to-sky-50 p-6 rounded-lg border border-teal-200 mb-6">
                        <div class="flex flex-wrap justify-between items-center gap-6">
                            <div class="text-center">
                                <div class="text-teal-800 text-sm font-medium mb-1">Ukupno zapisa</div>
                                <div class="text-2xl font-bold text-teal-900">{{ $records->total() }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-sky-800 text-sm font-medium mb-1">Početna vrijednost</div>
                                <div class="text-2xl font-bold text-sky-900">{{ number_format($firstValue, 2) }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-emerald-800 text-sm font-medium mb-1">Krajnja vrijednost</div>
                                <div class="text-2xl font-bold text-emerald-900">{{ number_format($lastValue, 2) }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-amber-800 text-sm font-medium mb-1">Razlika</div>
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ $valueDifference >= 0 ? '+' : '' }}{{ number_format($valueDifference, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Records Table with Scroller and Lazy Loading -->
                    <div class="rounded-lg shadow bg-white">
                        <div class="overflow-x-auto">
                            <div class="h-80 overflow-y-scroll border border-gray-200 rounded-lg" id="table-container" style="scrollbar-width: thin; scrollbar-color: #14b8a6 #f1f5f9; min-height: 320px;">

                                <table class="min-w-full text-center relative">
                                    <!-- Table Header -->
                                    <thead class="bg-gray-100 border-b border-gray-300 sticky top-0 z-10">
                                        <tr>
                                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 uppercase tracking-wider bg-gray-100">
                                                Datum
                                            </th>
                                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 uppercase tracking-wider bg-gray-100">
                                                Vrijeme
                                            </th>
                                            <th class="px-6 py-3 text-sm font-semibold text-gray-700 uppercase tracking-wider bg-gray-100">
                                                Vrijednost
                                            </th>
                                        </tr>
                                    </thead>

                                    <!-- Table Body -->
                                    <tbody class="bg-white divide-y divide-gray-200" id="table-body">
                                        @foreach($records as $record)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 text-sm text-gray-900 border-b border-gray-100">
                                                {{ $record->datum->format('d.m.Y') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 border-b border-gray-100">
                                                {{ $record->vrijeme->format('H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 border-b border-gray-100">
                                                {{ number_format($record->vrijednost, 2) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <!-- Loading indicator -->
                                <div id="loading-indicator" class="hidden flex justify-center items-center py-4 bg-white border-t">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
                                    <span class="ml-2 text-gray-600">Učitavanje...</span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Info text for lazy loading -->
                    <div class="mt-4 text-center text-sm text-gray-600">
                        Prikazano: <span id="loaded-count">{{ $records->count() }}</span> od ukupno {{ $records->total() }} zapisa
                        <div id="lazy-hint" class="mt-1">
                            @if($records->hasMorePages())
                            <span class="text-green-700">Kliknite na dugme Proširi za učitavanje svih preostalih zapisa.</span>
                            @else
                            <span class="text-gray-500" id="no-more-msg">Svi podaci su prikazani.</span>
                            @endif
                        </div>
                        @if($records->hasMorePages())
                        <button type="button" id="load-more-btn" class="mt-2 inline-flex items-center px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-md shadow transition">Proširi</button>
                        @endif
                    </div>
                </div>
                @elseif($selectedInstrument && $records->count() == 0)
                <div class="text-center py-12">
                    <div class="text-gray-500 text-lg">
                        Nema podataka za odabrani instrument.
                    </div>
                </div>
                @elseif(!$selectedCompany)
                <div class="text-center py-12">
                    <div class="text-gray-500 text-lg">
                        Izaberite firmu da biste vidjeli dostupne instrumente.
                    </div>
                </div>
                @elseif(!$selectedInstrument)
                <div class="text-center py-12">
                    <div class="text-gray-500 text-lg">
                        Izaberite mjerni instrument da biste vidjeli podatke.
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    /* Custom scrollbar styles */
    #table-container {
        scrollbar-width: thin;
        scrollbar-color: #14b8a6 #f1f5f9;
    }

    #table-container::-webkit-scrollbar {
        width: 8px;
    }

    #table-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    #table-container::-webkit-scrollbar-thumb {
        background: #14b8a6;
        border-radius: 4px;
    }

    #table-container::-webkit-scrollbar-thumb:hover {
        background: #0f766e;
    }

    /* Uklonjen min-height koji je rastezao malo redova kada ih je bilo malo */
    #table-container table {
        /* min-height removed to keep natural row height */
    }

    /* Konzistentna visina redova bez rastezanja */
    #table-container table th,
    #table-container table td {
        padding-top: 0.5rem;      /* stabilna visina (py-2) */
        padding-bottom: 0.5rem;
        line-height: 1.25rem;     /* sprječava vertikalno raztezanje teksta */
    }

    /* Ako želiš još kompaktnije: smanji padding-top/bottom na 0.25rem */

</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if($selectedInstrument && !empty($chartLabels))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('valuesChart').getContext('2d');

        const chart = new Chart(ctx, {
            type: 'line'
            , data: {
                labels: @json($chartLabels)
                , datasets: [{
                    label: 'Vrijednost'
                    , data: @json($chartValues)
                    , borderColor: 'rgb(20, 184, 166)'
                    , backgroundColor: 'rgba(20, 184, 166, 0.1)'
                    , borderWidth: 2
                    , fill: false
                    , tension: 0.1
                    , pointRadius: 4
                    , pointHoverRadius: 6
                }]
            },
            // Uklonjen custom plugin za iscrtavanje datuma/vremena ispod ose
            plugins: []
            , options: {
                responsive: true
                , maintainAspectRatio: false
                , layout: {
                    padding: {
                        bottom: 10
                    }
                }
                , plugins: {
                    title: {
                        display: true
                        , text: '{{ $selectedInstrument->name ?? "" }} ({{ $selectedCompany->name ?? "" }}) - Period: {{ $periodText }}'
                    }
                    , legend: {
                        display: true
                        , position: 'top'
                    }
                    , tooltip: {
                        callbacks: {
                            // Po želji se još može ukloniti ili prilagoditi tooltip
                            title: function(context) {
                                return context[0].label;
                            }
                        }
                    }
                }
                , scales: {
                    y: {
                        beginAtZero: false
                        , title: {
                            display: true
                            , text: 'Vrijednost'
                        }
                    }
                    , x: {
                        display: false // Sakrivamo kompletnu x osu (etikete i liniju)
                    }
                }
                , interaction: {
                    intersect: false
                    , mode: 'index'
                }
            }
        });
    });

</script>
@endif

<script>
    function autoSubmitIfReady() {
        const form = document.getElementById('reportForm');
        const companyId = document.getElementById('company_id').value;
        const instrumentId = document.getElementById('instrument_id').value;
        const dateFrom = document.getElementById('date_from').value;
        const dateTo = document.getElementById('date_to').value;
        const perPage = document.getElementById('per_page') ? document.getElementById('per_page').value : '';

        // Only auto-submit if we have company, instrument, and at least one date or per-page change
        if (companyId && instrumentId && (dateFrom || dateTo || perPage)) {
            setTimeout(() => {
                const lc = document.getElementById('loaded-count');
                if (lc) lc.textContent = '0';
                form.submit();
            }, 100);
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const container = document.getElementById('table-container');
        const tableBody = document.getElementById('table-body');
        const loadingIndicator = document.getElementById('loading-indicator');

        if (container && tableBody) {
            let page = 1;
            let loading = false;
            let hasMore = true;
            const loadMoreBtn = document.getElementById('load-more-btn');
            const lazyHint = document.getElementById('lazy-hint');

            async function loadPage(next = false) {
                if (loading || (!hasMore && next)) return;
                loading = true;
                loadingIndicator.classList.remove('hidden');

                try {
                    const instrumentId = '{{ $selectedInstrument?->id }}';
                    if (!instrumentId) {
                        hasMore = false;
                        return;
                    }
                    const params = new URLSearchParams({
                        instrument_id: instrumentId
                        , page: page + (next ? 1 : 0)
                        , per_page: document.getElementById('per_page') ? document.getElementById('per_page').value : 10
                        , date_from: '{{ $dateFrom ?? '' }}'
                        , date_to: '{{ $dateTo ?? '' }}'
                    });
                    const url = `{{ route('records.lazy') }}?${params.toString()}`;
                    const resp = await fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                            , 'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await resp.json();
                    if (!data.success) {
                        hasMore = false;
                        return;
                    }

                    // Ako je ovo sljedeća stranica, uvećaj lokalni page tek sada
                    if (next) page = data.currentPage;

                    if (!data.records.length) {
                        hasMore = false;
                    } else {
                        const frag = document.createDocumentFragment();
                        data.records.forEach(r => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-gray-50 transition-colors';
                            tr.innerHTML = `
                            <td class="px-6 py-4 text-sm text-gray-900 border-b border-gray-100">${r.datum || ''}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 border-b border-gray-100">${r.vrijeme || ''}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 border-b border-gray-100">${r.vrijednost || ''}</td>`;
                            frag.appendChild(tr);
                        });
                        tableBody.appendChild(frag);

                        // Ažuriraj brojač prikazanih
                        const loadedCountElement = document.getElementById('loaded-count');
                        if (loadedCountElement) {
                            const currentCount = parseInt(loadedCountElement.textContent) || 0;
                            loadedCountElement.textContent = currentCount + data.records.length;
                        }

                        hasMore = data.hasMore;
                        if (!hasMore) {
                            if (loadMoreBtn) loadMoreBtn.remove();
                            if (lazyHint) lazyHint.innerHTML = '<span class="text-gray-500" id="no-more-msg">Nema više podataka.</span>';
                        }
                    }
                } catch (e) {
                    console.error('Lazy load greška:', e);
                    hasMore = false;
                    if (loadMoreBtn) loadMoreBtn.remove();
                    if (lazyHint) lazyHint.innerHTML = '<span class="text-red-500">Greška pri učitavanju.</span>';
                } finally {
                    loading = false;
                    loadingIndicator.classList.add('hidden');
                }
            }

            // Funkcija za učitavanje svih preostalih stranica na jedan klik
            async function expandAll() {
                if (loading || !hasMore) return;
                if (loadMoreBtn) {
                    loadMoreBtn.disabled = true;
                    loadMoreBtn.classList.add('opacity-70', 'cursor-not-allowed');
                    loadMoreBtn.textContent = 'Učitavam...';
                }
                while (hasMore) {
                    await loadPage(true);
                }
                if (loadMoreBtn) {
                    loadMoreBtn.textContent = 'Prošireno';
                    loadMoreBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    loadMoreBtn.classList.add('bg-green-500');
                }
            }

            // Klik na "Proširi" učitava sve do kraja
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', expandAll);
            }
        }
    });

</script>
