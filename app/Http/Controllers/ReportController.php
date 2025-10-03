<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Instrument;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Allowed per-page values for pagination control
        $allowedPerPage = [10, 20, 50];
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }
        $companies = Company::orderBy('name')->get();
        $selectedCompany = null;
        $instruments = collect();
        $selectedInstrument = null;
        $records = collect();
        $chartLabels = [];
        $chartValues = [];
        $chartMinValues = [];
        $chartMaxValues = [];
        $dateFrom = null;
        $dateTo = null;
        $periodText = '';
        $firstValue = 0;
        $lastValue = 0;
        $valueDifference = 0;

        if ($request->has('company_id') && $request->company_id) {
            $selectedCompany = Company::find($request->company_id);
            if ($selectedCompany) {
                $instruments = $selectedCompany->instruments()->orderBy('name')->get();
            }
        }

        if ($request->has('instrument_id') && $request->instrument_id) {
            $selectedInstrument = Instrument::find($request->instrument_id);
            if ($selectedInstrument) {
                // Get date range from request or default to last 30 days
                $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
                $dateTo = $request->get('date_to', now()->format('Y-m-d'));

                // Base query for reuse (date constraints)
                $baseQuery = $selectedInstrument->records()
                    ->whereBetween('datum', [$dateFrom, $dateTo]);

                // Clone for main pagination (page param auto handled by paginate)
                $records = (clone $baseQuery)
                    ->orderByDesc('datum')
                    ->orderByDesc('vrijeme')
                    ->paginate($perPage);

                // Prepare chart data - independent ordered copy
                $chartData = (clone $baseQuery)
                    ->orderBy('datum')
                    ->orderBy('vrijeme')
                    ->get();

                // Format chart data for JavaScript - show individual timestamps with date
                $chartLabels = $chartData->map(function ($record) {
                    return $record->datum->format('d.m.Y') . ' ' . \Carbon\Carbon::parse($record->vrijeme)->format('H:i:s');
                })->toArray();

                $chartValues = $chartData->map(function ($record) {
                    return round($record->vrijednost, 2);
                })->toArray();

                // For individual records, min and max are the same as values
                $chartMinValues = $chartValues;
                $chartMaxValues = $chartValues;

                // Calculate difference between first and last value
                $firstValue = $chartData->first()?->vrijednost ?? 0;
                $lastValue = $chartData->last()?->vrijednost ?? 0;
                $valueDifference = $lastValue - $firstValue;

                // Format period for chart title
                $periodText = \Carbon\Carbon::parse($dateFrom)->format('d.m.Y') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('d.m.Y');
            }
        }

        // Handle AJAX requests for lazy loading (must re-run query if page param present but instrument not processed)
        if ($request->ajax() || $request->has('ajax')) {
            if ($selectedInstrument) {
                if (!$records instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                    // Safety: ensure paginator if initial condition skipped
                    $dateFrom = $dateFrom ?? now()->subDays(30)->format('Y-m-d');
                    $dateTo = $dateTo ?? now()->format('Y-m-d');
                    $records = $selectedInstrument->records()
                        ->whereBetween('datum', [$dateFrom, $dateTo])
                        ->orderByDesc('datum')
                        ->orderByDesc('vrijeme')
                        ->paginate($perPage);
                }

                $recordsData = $records->map(function ($record) {
                    return [
                        'datum' => $record->datum->format('d.m.Y'),
                        'vrijeme' => $record->vrijeme->format('H:i:s'),
                        'vrijednost' => number_format($record->vrijednost, 2)
                    ];
                });

                return response()->json([
                    'success' => true,
                    'records' => $recordsData,
                    'hasMore' => $records->hasMorePages(),
                    'currentPage' => $records->currentPage(),
                    'nextPageUrl' => $records->nextPageUrl()
                ]);
            }

            return response()->json([
                'success' => false,
                'records' => [],
                'hasMore' => false
            ]);
        }

        return view('reports.index', compact(
            'companies',
            'selectedCompany',
            'instruments',
            'selectedInstrument',
            'records',
            'chartLabels',
            'chartValues',
            'chartMinValues',
            'chartMaxValues',
            'dateFrom',
            'dateTo',
            'periodText',
            'firstValue',
            'lastValue',
            'valueDifference',
            'perPage'
        ));
    }

    public function summary(Request $request)
    {
        $companies = Company::orderBy('name')->get();
        $selectedCompany = null;
        $year = $request->get('year');
        // Choose SQL snippets depending on driver (SQLite vs MySQL/PostgreSQL)
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $yearExpr = "strftime('%Y', datum)";      // returns string '2025'
            $monthExpr = "CAST(strftime('%m', datum) AS INTEGER)"; // 1..12
            $yearWhere = "strftime('%Y', datum) = ?";
        } else { // mysql, pgsql, mariadb etc.
            $yearExpr = "YEAR(datum)";
            $monthExpr = "MONTH(datum)";
            $yearWhere = "YEAR(datum) = ?";
        }

        // Distinct years present in records (limit to realistic ones)
        $years = Record::selectRaw("DISTINCT $yearExpr as y")
            ->orderBy('y', 'desc')
            ->pluck('y')
            ->map(fn($y) => (int)$y)
            ->filter(fn($y)=> $y >= 2020 && $y <= (int)date('Y'))
            ->values();

        $monthlyData = collect();
        $totals = [
            'overall' => 0,
        ];
        $monthNames = [1=>'Januar',2=>'Februar',3=>'Mart',4=>'April',5=>'Maj',6=>'Juni',7=>'Juli',8=>'Avgust',9=>'Septembar',10=>'Oktobar',11=>'Novembar',12=>'Decembar'];
        $cumulativeValues = [];

        if ($request->filled('company_id') && $year) {
            $selectedCompany = Company::find($request->get('company_id'));
            if ($selectedCompany) {
                $instrumentIds = $selectedCompany->instruments()->pluck('id');
                if ($instrumentIds->count()) {
                    // Fetch all records for the selected instruments & year ordered chronologically
                    $recordsQuery = Record::whereIn('instrument_id', $instrumentIds)
                        ->whereRaw($yearWhere, [$year])
                        ->orderBy('instrument_id')
                        ->orderBy('datum')
                        ->orderBy('vrijeme');

                    $all = $recordsQuery->get(['instrument_id', 'datum', 'vrijeme', 'vrijednost']);

                    // Prepare structure: month => total difference across instruments
                    $monthlyTotals = array_fill(1, 12, 0.0);

                    // Group by instrument first to compute (last - first) per instrument per month then sum
                    $all->groupBy('instrument_id')->each(function($instrumentRecords) use (&$monthlyTotals) {
                        // Group by month
                        $instrumentRecords->groupBy(fn($r) => (int)$r->datum->format('n'))
                            ->each(function($monthRecords, $monthNum) use (&$monthlyTotals) {
                                // Already ordered (datum, vrijeme) asc
                                $first = $monthRecords->first();
                                $last = $monthRecords->last();
                                if ($first && $last) {
                                    $diff = (float)$last->vrijednost - (float)$first->vrijednost;
                                    $monthlyTotals[(int)$monthNum] += $diff;
                                }
                            });
                    });

                    // Build full 12-month structure
                    $monthlyData = collect(range(1,12))->map(function($m) use ($monthlyTotals, $monthNames) {
                        return [
                            'month' => $m,
                            'label' => $monthNames[$m] ?? $m,
                            'total' => round($monthlyTotals[$m], 2),
                        ];
                    });

                    $totals['overall'] = $monthlyData->sum('total');
                    // Build cumulative values for chart (progressive sum)
                    $running = 0;
                    foreach ($monthlyData as $row) {
                        $running += $row['total'];
                        $cumulativeValues[] = round($running, 2);
                    }
                }
            }
        }

        // Prepare data for chart (labels + values)
        $chartLabels = $monthlyData->pluck('label');
        $chartValues = $monthlyData->pluck('total');
        if (!$cumulativeValues && $monthlyData->count()) { // fallback if not populated
            $running = 0;
            foreach ($monthlyData as $row) {
                $running += $row['total'];
                $cumulativeValues[] = round($running, 2);
            }
        }

        return view('reports.summary', compact(
            'companies',
            'selectedCompany',
            'year',
            'years',
            'monthlyData',
            'totals',
            'chartLabels',
            'chartValues'
            ,'cumulativeValues'
        ));
    }

    public function instrumentSummary(Request $request)
    {
        $companies = Company::orderBy('name')->get();
        $selectedCompany = null;
        $selectedInstrument = null;
        $instrumentId = $request->get('instrument_id');
        $companyId = $request->get('company_id');
        $year = $request->get('year');
        $instruments = collect();

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $yearExpr = "strftime('%Y', datum)";
            $monthExpr = "CAST(strftime('%m', datum) AS INTEGER)";
            $yearWhere = "strftime('%Y', datum) = ?";
        } else {
            $yearExpr = "YEAR(datum)";
            $monthExpr = "MONTH(datum)";
            $yearWhere = "YEAR(datum) = ?";
        }

        $years = Record::selectRaw("DISTINCT $yearExpr as y")
            ->orderBy('y', 'desc')
            ->pluck('y')
            ->map(fn($y) => (int)$y)
            ->filter(fn($y)=> $y >= 2020 && $y <= (int)date('Y'))
            ->values();

        $monthNames = [1=>'Januar',2=>'Februar',3=>'Mart',4=>'April',5=>'Maj',6=>'Juni',7=>'Juli',8=>'Avgust',9=>'Septembar',10=>'Oktobar',11=>'Novembar',12=>'Decembar'];
        $monthlyData = collect();
        $totals = ['overall' => 0];
        $cumulativeValues = [];

        if ($companyId) {
            $selectedCompany = Company::find($companyId);
            if ($selectedCompany) {
                $instruments = $selectedCompany->instruments()->orderBy('name')->get();
            }
        }

        if ($selectedCompany && $instrumentId && $year) {
            $selectedInstrument = Instrument::where('company_id', $selectedCompany->id)->find($instrumentId);
            if ($selectedInstrument) {
                // Fetch ordered records for that instrument & year
                $records = Record::where('instrument_id', $selectedInstrument->id)
                    ->whereRaw($yearWhere, [$year])
                    ->orderBy('datum')
                    ->orderBy('vrijeme')
                    ->get(['datum','vrijeme','vrijednost']);

                // Group by month and compute (last - first)
                $monthlyTotals = array_fill(1, 12, 0.0);
                $records->groupBy(fn($r)=> (int)$r->datum->format('n'))->each(function($monthRecords, $m) use (&$monthlyTotals) {
                    $first = $monthRecords->first();
                    $last = $monthRecords->last();
                    if ($first && $last) {
                        $monthlyTotals[$m] = (float)$last->vrijednost - (float)$first->vrijednost;
                    }
                });

                $monthlyData = collect(range(1,12))->map(function($m) use ($monthlyTotals, $monthNames) {
                    return [
                        'month' => $m,
                        'label' => $monthNames[$m] ?? $m,
                        'total' => round($monthlyTotals[$m], 2),
                    ];
                });

                $totals['overall'] = $monthlyData->sum('total');
                $running = 0;
                foreach ($monthlyData as $row) {
                    $running += $row['total'];
                    $cumulativeValues[] = round($running, 2);
                }
            }
        }

        $chartLabels = $monthlyData->pluck('label');
        $chartValues = $monthlyData->pluck('total');
        if (!$cumulativeValues && $monthlyData->count()) {
            $running = 0;
            foreach ($monthlyData as $row) {
                $running += $row['total'];
                $cumulativeValues[] = round($running, 2);
            }
        }

        return view('reports.instrument_summary', compact(
            'companies',
            'selectedCompany',
            'instruments',
            'selectedInstrument',
            'instrumentId',
            'year',
            'years',
            'monthlyData',
            'totals',
            'chartLabels',
            'chartValues',
            'cumulativeValues'
        ));
    }
}
