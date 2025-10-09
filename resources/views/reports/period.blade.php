@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 flex items-center gap-2">Izvještaj po periodu
                <span class="text-xs font-medium px-2 py-0.5 rounded bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300">Beta</span>
            </h1>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Filtriranje zapisa po firmi, datumu i (opciono) vremenu uz pregled i statistiku.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                Nazad
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.period') }}" class="sticky top-16 z-10 bg-white/90 dark:bg-gray-900/85 backdrop-blur rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-8 flex flex-col gap-5 md:gap-6 md:flex-wrap md:flex-row">
        <div class="flex flex-col w-full md:w-64 md:order-1">
            <label for="company_id" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Firma</label>
            <select name="company_id" id="company_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 focus:border-teal-500 focus:ring-teal-500">
                <option value="">-- Izaberite firmu --</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col w-full md:w-40 md:order-2">
            <label for="date_from" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Datum od</label>
            <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 focus:border-teal-500 focus:ring-teal-500" />
        </div>
        <div class="flex flex-col w-full md:w-40 md:order-3">
            <label for="date_to" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Datum do</label>
            <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 focus:border-teal-500 focus:ring-teal-500" />
        </div>
        <div class="flex flex-col w-full md:w-32 md:order-4">
            <label for="per_page" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Po stranici</label>
            <select name="per_page" id="per_page" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 focus:border-teal-500 focus:ring-teal-500">
                @foreach($allowedPerPage as $pp)
                    <option value="{{ $pp }}" @selected($perPage==$pp)>{{ $pp }}</option>
                @endforeach
            </select>
        </div>
        <!-- Collapsible vrijeme sekcija full width da ne preklapa -->
        <div x-data="{open:false}" class="w-full flex flex-col md:order-5" x-cloak>
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vrijeme (opciono)</span>
                <button type="button" @click="open=!open" x-bind:aria-expanded="open.toString()" class="text-[11px] font-medium text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300">
                    <span x-text="open ? 'Sakrij' : 'Prikaži'"></span>
                </button>
            </div>
            <div x-show="open" x-transition.origin.top.left class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-md border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col">
                    <label for="time_from" class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Vrijeme od</label>
                    <input type="time" id="time_from" name="time_from" value="{{ $timeFrom }}" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 focus:border-teal-500 focus:ring-teal-500" />
                </div>
                <div class="flex flex-col">
                    <label for="time_to" class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Vrijeme do</label>
                    <input type="time" id="time_to" name="time_to" value="{{ $timeTo }}" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 focus:border-teal-500 focus:ring-teal-500" />
                </div>
            </div>
        </div>
        <!-- Quick ranges -->
        <div class="flex flex-col w-full md:w-60 md:order-6">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Brzi datumski raspon</label>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 md:grid-cols-2">
                <button type="button" data-range="today" class="quick-range px-2 py-1.5 text-[11px] rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400">Danas</button>
                <button type="button" data-range="7d" class="quick-range px-2 py-1.5 text-[11px] rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400">7 dana</button>
                <button type="button" data-range="month" class="quick-range px-2 py-1.5 text-[11px] rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400">Ovaj mjes.</button>
                <button type="button" data-range="prev-month" class="quick-range px-2 py-1.5 text-[11px] rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400">Prošli mjes.</button>
            </div>
        </div>
        <div class="flex items-end w-full md:w-auto gap-3 self-end md:order-7">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-md bg-teal-600 text-white text-sm font-medium shadow hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4h2m-1 0v16m7-9h-6m6 0a8 8 0 11-16 0 8 8 0 0116 0z" /></svg>
                Primijeni
            </button>
            <a href="{{ route('reports.period') }}" class="inline-flex items-center px-4 py-2.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Reset</a>
        </div>
    </form>

    @if(!$hasFilter)
        <div class="text-sm text-gray-500 dark:text-gray-400">Odaberite firmu i obavezno raspon datuma za prikaz podataka (vrijeme je opcionalno).</div>
    @else
        @if(!$paginator)
            <div class="text-sm text-gray-500 dark:text-gray-400">Nema zapisa za zadati kriterij.</div>
        @else
            @php
                $values = collect($paginator->items())->map(fn($r) => (float)$r->vrijednost);
                $minV = $values->min();
                $maxV = $values->max();
                $avgV = $values->avg();
            @endphp
            <div class="mb-5 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ukupno (filtrirano)</span>
                    <span class="text-xl font-semibold text-teal-600 dark:text-teal-400">{{ $paginator->total() }}</span>
                    <span class="text-[11px] text-gray-400">Str. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
                </div>
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Min (stranica)</span>
                    <span class="text-lg font-semibold text-sky-600 dark:text-sky-400">{{ $minV !== null ? number_format($minV,2) . ' m³' : '—' }}</span>
                    <span class="text-[11px] text-gray-400">Najmanja vrijednost</span>
                </div>
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Max (stranica)</span>
                    <span class="text-lg font-semibold text-fuchsia-600 dark:text-fuchsia-400">{{ $maxV !== null ? number_format($maxV,2) . ' m³' : '—' }}</span>
                    <span class="text-[11px] text-gray-400">Najveća vrijednost</span>
                </div>
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Prosjek (stranica)</span>
                    <span class="text-lg font-semibold text-amber-600 dark:text-amber-400">{{ $avgV !== null ? number_format($avgV,2) . ' m³' : '—' }}</span>
                    <span class="text-[11px] text-gray-400">Aritmetička sredina</span>
                </div>
            </div>
            <div class="mb-4 flex flex-wrap gap-2 text-[11px]">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 font-medium">Datum: {{ $dateFrom }} – {{ $dateTo }}</span>
                @if($timeFrom || $timeTo)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium">Vrijeme (unos): {{ $timeFrom ?? '—' }} – {{ $timeTo ?? '—' }}</span>
                @endif
                @if($normalizedTimeFrom || $normalizedTimeTo)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 font-medium">Vrijeme (efektivno): {{ $normalizedTimeFrom ?? '—' }} – {{ $normalizedTimeTo ?? '—' }}</span>
                @endif
                @if($instrumentCount)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">Instrumenata: {{ $instrumentCount }}</span>
                @endif
                @if($appliedTimeFilter && $paginator->total() === 0)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 font-medium">Nema rezultata u vremenu</span>
                @endif
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm align-top">
                        <thead class="bg-gray-100 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold">Datum</th>
                                <th class="px-4 py-2 text-left font-semibold">Vrijeme</th>
                                <th class="px-4 py-2 text-left font-semibold">Mjerno mjesto</th>
                                <th class="px-4 py-2 text-left font-semibold">Vrijednost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach($paginator as $rec)
                                @php $neg = $rec->vrijednost < 0; @endphp
                                <tr class="hover:bg-teal-50/70 dark:hover:bg-gray-700/50 transition {{ $neg ? 'bg-rose-50/40 dark:bg-rose-900/10' : '' }}">
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-800 dark:text-gray-100">{{ $rec->datum->format('d.m.Y') }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($rec->vrijeme)->format('H:i:s') }}</td>
                                    <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $rec->instrument?->name ?? '—' }}</td>
                                    <td class="px-4 py-2 font-medium {{ $neg ? 'text-rose-600 dark:text-rose-400' : 'text-teal-700 dark:text-teal-300' }}">{{ number_format($rec->vrijednost, 2) }} m³</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    {{ $paginator->links() }}
                </div>
            </div>
            {{-- Mobile cards --}}
            <div class="mt-6 space-y-3 md:hidden">
                @foreach($paginator as $rec)
                    @php $neg = $rec->vrijednost < 0; @endphp
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900/40 relative overflow-hidden">
                        <div class="absolute inset-0 pointer-events-none opacity-5 {{ $neg ? 'bg-gradient-to-br from-rose-400 to-rose-600' : 'bg-gradient-to-br from-teal-400 to-teal-600' }}"></div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $rec->datum->format('d.m.Y') }}</span>
                            <span class="text-xs font-semibold text-teal-600 dark:text-teal-400">{{ \Carbon\Carbon::parse($rec->vrijeme)->format('H:i') }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Mjerno mjesto: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $rec->instrument?->name ?? '—' }}</span></p>
                        <p class="text-sm font-semibold {{ $neg ? 'text-rose-600 dark:text-rose-400' : 'text-teal-700 dark:text-teal-300' }}">Vrijednost: {{ number_format($rec->vrijednost, 2) }} m³</p>
                    </div>
                @endforeach
                <div class="pt-2 text-center">{{ $paginator->links() }}</div>
            </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rangeButtons = document.querySelectorAll('.quick-range');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    const form = dateFrom?.form;
    function fmt(d){ return d.toISOString().slice(0,10); }
    function firstDayOfMonth(d){ return new Date(d.getFullYear(), d.getMonth(), 1); }
    function lastDayOfMonth(d){ return new Date(d.getFullYear(), d.getMonth()+1, 0); }
    rangeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            if(!dateFrom || !dateTo) return;
            const now = new Date();
            let from, to;
            switch(btn.dataset.range){
                case 'today':
                    from = to = now; break;
                case '7d':
                    to = now; from = new Date(now); from.setDate(now.getDate()-6); break;
                case 'month':
                    from = firstDayOfMonth(now); to = lastDayOfMonth(now); break;
                case 'prev-month':
                    const prev = new Date(now.getFullYear(), now.getMonth()-1, 1);
                    from = firstDayOfMonth(prev); to = lastDayOfMonth(prev); break;
                default: return;
            }
            dateFrom.value = fmt(from); dateTo.value = fmt(to);
            btn.classList.add('ring-2','ring-teal-500','border-teal-500');
            setTimeout(()=> btn.classList.remove('ring-2','ring-teal-500','border-teal-500'), 1200);
            form && form.requestSubmit();
        });
    });

    const scrollBtn = document.createElement('button');
    scrollBtn.type='button';
    scrollBtn.setAttribute('aria-label','Nazad na vrh');
    scrollBtn.className='hidden fixed bottom-6 right-6 z-40 rounded-full bg-teal-600 hover:bg-teal-700 text-white shadow-lg h-11 w-11 flex items-center justify-center transition';
    scrollBtn.innerHTML='<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>';
    document.body.appendChild(scrollBtn);
    window.addEventListener('scroll', () => {
        if(window.scrollY > 600) scrollBtn.classList.remove('hidden'); else scrollBtn.classList.add('hidden');
    });
    scrollBtn.addEventListener('click', () => window.scrollTo({top:0, behavior:'smooth'}));
});
</script>
@endpush
