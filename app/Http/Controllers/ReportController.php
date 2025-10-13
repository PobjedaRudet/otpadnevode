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
                        // Include measurement unit with superscript (m³)
                        'vrijednost' => number_format($record->vrijednost, 2) . ' m³'
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

    public function period(Request $request)
    {
        $companies = Company::orderBy('name')->get();
        $selectedCompany = null;
        $records = collect();
        $perPage = (int) $request->get('per_page', 50);
        $allowedPerPage = [25,50,100,200];
        if(!in_array($perPage, $allowedPerPage, true)) { $perPage = 50; }

        $companyId = $request->get('company_id');
        $dateFrom = $request->get('date_from');
        $timeFrom = $request->get('time_from');
        $dateTo = $request->get('date_to');
        $timeTo = $request->get('time_to');

        // Normalizacija vremena: prihvati HH:MM ili HH:MM:SS i pretvori u HH:MM:SS
        $normalizeTime = function($time) {
            if(!$time) return null;
            $orig = $time;
            $time = trim($time);
            // 1) HH:MM -> dodaj :00
            if(preg_match('/^(\d{2}):(\d{2})$/', $time, $m)) {
                [$full,$h,$i] = $m;
                if((int)$h < 24 && (int)$i < 60) return sprintf('%02d:%02d:00', $h, $i);
            }
            // 2) HH:MM:SS
            if(preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $time, $m)) {
                [$full,$h,$i,$s] = $m;
                if((int)$h < 24 && (int)$i < 60 && (int)$s < 60) return $time;
            }
            // 3) 12h format: H:MM AM/PM ili HH:MM AM/PM
            if(preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', strtoupper($time), $m)) {
                $h = (int)$m[1]; $i = (int)$m[2]; $ampm = strtoupper($m[3]);
                if($h >=1 && $h <=12 && $i < 60) {
                    if($ampm === 'AM') {
                        if($h == 12) $h = 0; // 12 AM -> 00
                    } else { // PM
                        if($h != 12) $h += 12; // 1 PM -> 13, 12 PM stays 12
                    }
                    return sprintf('%02d:%02d:00', $h, $i);
                }
            }
            return null; // nevalidno / ignorisano
        };
        $rawTimeFrom = $timeFrom; // čuvamo original za view ako treba
        $rawTimeTo = $timeTo;
    $normalizedTimeFrom = $normalizeTime($timeFrom);
    $normalizedTimeTo = $normalizeTime($timeTo);

        $hasFilter = $companyId && $dateFrom && $dateTo; // vrijeme je opcionalno
        $paginator = null;

        if ($companyId) {
            $selectedCompany = Company::find($companyId);
        }

        $driver = DB::getDriverName();
        $instrumentCount = 0;
        $appliedTimeFilter = false;
        $timeFilterMode = null; // between | from | to | none

        if ($selectedCompany && $hasFilter) {
            // Normalizacija datuma i vremena
            try {
                $fromDate = \Carbon\Carbon::parse($dateFrom)->format('Y-m-d');
                $toDate = \Carbon\Carbon::parse($dateTo)->format('Y-m-d');
            } catch(\Exception $e) {
                $fromDate = $toDate = null;
            }

            if ($fromDate && $toDate) {
                $instrumentIds = $selectedCompany->instruments()->pluck('id');
                $instrumentCount = $instrumentIds->count();
                if ($instrumentIds->count()) {
                    $query = Record::with(['instrument:id,name'])
                        ->whereIn('instrument_id', $instrumentIds)
                        ->whereBetween('datum', [$fromDate, $toDate]);

                    // Vrijeme filtriranje (ako oba unijeta)
                    if ($normalizedTimeFrom && $normalizedTimeTo) {
                        if ($normalizedTimeFrom > $normalizedTimeTo) {
                            [$normalizedTimeFrom, $normalizedTimeTo] = [$normalizedTimeTo, $normalizedTimeFrom];
                        }
                        if ($driver === 'sqlite') {
                            $query->whereBetween('vrijeme', [$normalizedTimeFrom, $normalizedTimeTo]);
                        } else {
                            $query->whereRaw('TIME(vrijeme) BETWEEN ? AND ?', [$normalizedTimeFrom, $normalizedTimeTo]);
                        }
                        $appliedTimeFilter = true; $timeFilterMode = 'between';
                    } elseif ($normalizedTimeFrom) {
                        if ($driver === 'sqlite') {
                            $query->where('vrijeme', '>=', $normalizedTimeFrom);
                        } else {
                            $query->whereRaw('TIME(vrijeme) >= ?', [$normalizedTimeFrom]);
                        }
                        $appliedTimeFilter = true; $timeFilterMode = 'from';
                    } elseif ($normalizedTimeTo) {
                        if ($driver === 'sqlite') {
                            $query->where('vrijeme', '<=', $normalizedTimeTo);
                        } else {
                            $query->whereRaw('TIME(vrijeme) <= ?', [$normalizedTimeTo]);
                        }
                        $appliedTimeFilter = true; $timeFilterMode = 'to';
                    } else {
                        $timeFilterMode = 'none';
                    }

                    // Sortiranje rastuće po datumu i vremenu
                    $query->orderBy('datum')->orderBy('vrijeme');

                    $paginator = $query->paginate($perPage)->appends($request->query());
                }
            }
        }

        return view('reports.period', [
            'companies' => $companies,
            'selectedCompany' => $selectedCompany,
            'paginator' => $paginator,
            'companyId' => $companyId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'timeFrom' => $rawTimeFrom,
            'timeTo' => $rawTimeTo,
            'normalizedTimeFrom' => $normalizedTimeFrom,
            'normalizedTimeTo' => $normalizedTimeTo,
            'perPage' => $perPage,
            'allowedPerPage' => $allowedPerPage,
            'hasFilter' => $hasFilter,
            'instrumentCount' => $instrumentCount,
            'appliedTimeFilter' => $appliedTimeFilter,
            'timeFilterMode' => $timeFilterMode,
            'driver' => $driver
        ]);
    }

    /**
     * Export monthly summary DOCX for selected company grouped by instruments using a Word template.
     * Request params: company_id (required), year (required), month (required: 1..12)
     */
    public function exportSummaryDocx(Request $request)
    {
        $companyId = (int)$request->query('company_id');
        $year = (int)$request->query('year');
        $month = (int)$request->query('month');

        if (!$companyId || !$year || $month < 1 || $month > 12) {
            return redirect()->back()->with('error', 'Nedostaju parametri: firma, godina i mjesec su obavezni.');
        }

        $company = Company::find($companyId);
        if (!$company) {
            return redirect()->back()->with('error', 'Firma nije pronađena.');
        }

        // Determine DB-specific expressions for filtering by year/month
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $yearWhere = "strftime('%Y', datum) = ?";
            $monthWhere = "CAST(strftime('%m', datum) AS INTEGER) = ?";
            $bindings = [$year, $month];
        } else {
            $yearWhere = 'YEAR(datum) = ?';
            $monthWhere = 'MONTH(datum) = ?';
            $bindings = [$year, $month];
        }

        // Collect data per instrument: (last - first) in the month, with optional extra details
        $instrumentIds = $company->instruments()->orderBy('name')->pluck('id', 'name');
        $rows = [];
        foreach ($company->instruments()->orderBy('name')->get(['id','name']) as $inst) {
            $records = Record::where('instrument_id', $inst->id)
                ->whereRaw($yearWhere, [$year])
                ->whereRaw($monthWhere, [$month])
                ->orderBy('datum')
                ->orderBy('vrijeme')
                ->get(['datum','vrijeme','vrijednost']);

            if ($records->isEmpty()) {
                $rows[] = [
                    'instrument' => $inst->name,
                    'first_date' => '-',
                    'first_value' => '-',
                    'last_date' => '-',
                    'last_value' => '-',
                    'diff' => 0.0,
                ];
                continue;
            }

            $first = $records->first();
            $last = $records->last();
            $diff = (float)$last->vrijednost - (float)$first->vrijednost;
            $rows[] = [
                'instrument' => $inst->name,
                'first_date' => $first->datum->format('d.m.Y') . ' ' . (is_string($first->vrijeme) ? \Carbon\Carbon::parse($first->vrijeme)->format('H:i:s') : $first->vrijeme->format('H:i:s')),
                'first_value' => number_format((float)$first->vrijednost, 2),
                'last_date' => $last->datum->format('d.m.Y') . ' ' . (is_string($last->vrijeme) ? \Carbon\Carbon::parse($last->vrijeme)->format('H:i:s') : $last->vrijeme->format('H:i:s')),
                'last_value' => number_format((float)$last->vrijednost, 2),
                'diff' => (float)round($diff, 2),
            ];
        }

        // Totals
        $total = collect($rows)->sum(fn($r) => (float)$r['diff']);

        // Prepare PHPWord document based on template if available
        // Try a few common variants (case/diacritics) to be resilient on different OSes
        $templatePath = null;
        foreach (['Izvjestaj.docx','izvjestaj.docx','Izvješaj.docx','izvješaj.docx'] as $tpl) {
            $p = public_path($tpl);
            if (file_exists($p)) { $templatePath = $p; break; }
        }
    $monthNames = [1=>'Januar',2=>'Februar',3=>'Mart',4=>'April',5=>'Maj',6=>'Juni',7=>'Juli',8=>'Avgust',9=>'Septembar',10=>'Oktobar',11=>'Novembar',12=>'Decembar'];
    $periodLabel = ($monthNames[$month] ?? $month) . ' ' . $year;
    // Compute first and last calendar day for selected month
    $firstDay = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfDay();
    $lastDay = (clone $firstDay)->endOfMonth()->startOfDay();
    $datumOd = $firstDay->format('d.m.Y');
    $datumDo = $lastDay->format('d.m.Y');
        $fileName = 'Izvjestaj_' . str_replace(' ', '_', $company->name) . '_' . $year . '_' . sprintf('%02d', $month) . '.docx';

        try {
            if (file_exists($templatePath)) {
                // If the template defines placeholders, replace them; also add a table for instruments
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
                $templateProcessor->setValue('company', htmlspecialchars($company->name));
                $templateProcessor->setValue('period', htmlspecialchars($periodLabel));
                $templateProcessor->setValue('total', number_format($total, 2) . ' m³');
                $templateProcessor->setValue('datum_od', $datumOd);
                $templateProcessor->setValue('datum_do', $datumDo);

                // If template contains a block/table placeholder for rows, clone
                // We'll support two strategies:
                // 1) If template has a row with placeholders like ${instrument}, ${first_date}, etc. within a table row marked as a block named 'rows'
                // 2) Otherwise, append a new table to the end
                $canClone = false;
                try {
                    $templateProcessor->cloneRow('instrument', max(1, count($rows)));
                    $canClone = true;
                } catch (\Throwable $e) {
                    $canClone = false;
                }
                if ($canClone) {
                    $i = 1;
                    foreach ($rows as $r) {
                        $templateProcessor->setValue("instrument#{$i}", htmlspecialchars($r['instrument']));
                        $templateProcessor->setValue("first_date#{$i}", $r['first_date']);
                        $templateProcessor->setValue("first_value#{$i}", $r['first_value'] . ' m³');
                        $templateProcessor->setValue("last_date#{$i}", $r['last_date']);
                        $templateProcessor->setValue("last_value#{$i}", $r['last_value'] . ' m³');
                        $templateProcessor->setValue("diff#{$i}", number_format($r['diff'], 2) . ' m³');
                        $i++;
                    }
                }

                $tmpPath = storage_path('app/tmp_' . uniqid('rep_', true) . '.docx');
                $templateProcessor->saveAs($tmpPath);
                return response()->download($tmpPath, $fileName)->deleteFileAfterSend(true);
            } else {
                // Build a simple DOCX from scratch if template missing
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $section = $phpWord->addSection();
                $section->addTitle('Mjesečni izvještaj', 1);
                $section->addText($company->name);
                $section->addText('Period: ' . $periodLabel);
                $section->addText('Ukupno: ' . number_format($total, 2) . ' m³');
                $section->addTextBreak(1);
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80]);
                $table->addRow();
                foreach (['Instrument','Prvi datum','Prva vrijednost','Zadnji datum','Zadnja vrijednost','Razlika'] as $hdr) {
                    $table->addCell(2200)->addText($hdr, ['bold' => true]);
                }
                foreach ($rows as $r) {
                    $table->addRow();
                    $table->addCell(2200)->addText($r['instrument']);
                    $table->addCell(2200)->addText($r['first_date']);
                    $table->addCell(2200)->addText($r['first_value'] . ' m³');
                    $table->addCell(2200)->addText($r['last_date']);
                    $table->addCell(2200)->addText($r['last_value'] . ' m³');
                    $table->addCell(2200)->addText(number_format($r['diff'], 2) . ' m³');
                }

                $tmpPath = storage_path('app/tmp_' . uniqid('rep_', true) . '.docx');
                $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                $writer->save($tmpPath);
                return response()->download($tmpPath, $fileName)->deleteFileAfterSend(true);
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Greška pri generisanju DOCX: ' . $e->getMessage());
        }
    }

    /**
     * Export a DOCX with instrument deltas for all companies for the given year/month.
     * Params: year (required), month (required)
     */
    public function exportAllSummaryDocx(Request $request)
    {
        $year = (int)$request->query('year');
        $month = (int)$request->query('month');
        if (!$year || $month < 1 || $month > 12) {
            return redirect()->back()->with('error', 'Godina i mjesec su obavezni.');
        }

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $yearWhere = "strftime('%Y', datum) = ?";
            $monthWhere = "CAST(strftime('%m', datum) AS INTEGER) = ?";
        } else {
            $yearWhere = 'YEAR(datum) = ?';
            $monthWhere = 'MONTH(datum) = ?';
        }

        $companies = Company::with(['instruments:id,company_id,name'])->orderBy('name')->get(['id','name']);
        $rows = [];
        foreach ($companies as $company) {
            foreach ($company->instruments as $inst) {
                $records = Record::where('instrument_id', $inst->id)
                    ->whereRaw($yearWhere, [$year])
                    ->whereRaw($monthWhere, [$month])
                    ->orderBy('datum')->orderBy('vrijeme')
                    ->get(['datum','vrijeme','vrijednost']);

                if ($records->isEmpty()) {
                    $rows[] = [
                        'company' => $company->name,
                        'instrument' => $inst->name,
                        'first_date' => '-', 'first_value' => '-',
                        'last_date' => '-', 'last_value' => '-',
                        'diff' => 0.0,
                    ];
                } else {
                    $first = $records->first();
                    $last = $records->last();
                    $diff = (float)$last->vrijednost - (float)$first->vrijednost;
                    $rows[] = [
                        'company' => $company->name,
                        'instrument' => $inst->name,
                        'first_date' => $first->datum->format('d.m.Y') . ' ' . (is_string($first->vrijeme) ? \Carbon\Carbon::parse($first->vrijeme)->format('H:i:s') : $first->vrijeme->format('H:i:s')),
                        'first_value' => number_format((float)$first->vrijednost, 2),
                        'last_date' => $last->datum->format('d.m.Y') . ' ' . (is_string($last->vrijeme) ? \Carbon\Carbon::parse($last->vrijeme)->format('H:i:s') : $last->vrijeme->format('H:i:s')),
                        'last_value' => number_format((float)$last->vrijednost, 2),
                        'diff' => (float)round($diff, 2),
                    ];
                }
            }
        }

        $total = collect($rows)->sum(fn($r) => (float)$r['diff']);
        $monthNames = [1=>'Januar',2=>'Februar',3=>'Mart',4=>'April',5=>'Maj',6=>'Juni',7=>'Juli',8=>'Avgust',9=>'Septembar',10=>'Oktobar',11=>'Novembar',12=>'Decembar'];
        $periodLabel = ($monthNames[$month] ?? $month) . ' ' . $year;
        $firstDay = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfDay();
        $lastDay = (clone $firstDay)->endOfMonth()->startOfDay();
        $datumOd = $firstDay->format('d.m.Y');
        $datumDo = $lastDay->format('d.m.Y');

        // Template path (reuse same detection)
        $templatePath = null;
        foreach (['Izvjestaj.docx','izvjestaj.docx','Izvješaj.docx','izvješaj.docx'] as $tpl) {
            $p = public_path($tpl);
            if (file_exists($p)) { $templatePath = $p; break; }
        }
        $fileName = 'Izvjestaj_SVE_FIRME_' . $year . '_' . sprintf('%02d', $month) . '.docx';

        try {
            if ($templatePath) {
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
                $templateProcessor->setValue('company', 'Sve firme');
                $templateProcessor->setValue('period', $periodLabel);
                $templateProcessor->setValue('total', number_format($total, 2) . ' m³');
                $templateProcessor->setValue('datum_od', $datumOd);
                $templateProcessor->setValue('datum_do', $datumDo);

                // Try to clone by 'instrument' as before; if template also contains ${company} in row, we will set company#i too
                $count = max(1, count($rows));
                $canClone = false;
                try { $templateProcessor->cloneRow('instrument', $count); $canClone = true; } catch (\Throwable $e) { $canClone = false; }
                if ($canClone) {
                    $i = 1;
                    foreach ($rows as $r) {
                        $templateProcessor->setValue("instrument#{$i}", htmlspecialchars($r['instrument']));
                        // per-row company placeholder if present in template row
                        try { $templateProcessor->setValue("company#{$i}", htmlspecialchars($r['company'])); } catch (\Throwable $e) {}
                        $templateProcessor->setValue("first_date#{$i}", $r['first_date']);
                        $templateProcessor->setValue("first_value#{$i}", $r['first_value'] . ' m³');
                        $templateProcessor->setValue("last_date#{$i}", $r['last_date']);
                        $templateProcessor->setValue("last_value#{$i}", $r['last_value'] . ' m³');
                        $templateProcessor->setValue("diff#{$i}", number_format($r['diff'], 2) . ' m³');
                        $i++;
                    }
                }

                // Additionally, support fixed template cells using reports_template config
                $map = config('reports_template.rows', []);
                if (is_array($map) && !empty($map)) {
                    foreach ($map as $key => $criteria) {
                        $companyContains = $criteria['company_contains'] ?? null;
                        $instrumentContains = $criteria['instrument_contains'] ?? null;
                        $alias = $criteria['alias'] ?? null;
                        $matched = collect($rows)->first(function($r) use ($companyContains, $instrumentContains) {
                            $ok = true;
                            if ($companyContains) {
                                $ok = $ok && (stripos($r['company'], $companyContains) !== false);
                            }
                            if ($instrumentContains) {
                                $ok = $ok && (stripos($r['instrument'], $instrumentContains) !== false);
                            }
                            return $ok;
                        });
                        if ($matched) {
                            // Fill ${<key>_first_value}, ${<key>_last_value}, ${<key>_diff}
                            try { $templateProcessor->setValue($key . '_first_value', $matched['first_value'] . ' m³'); } catch (\Throwable $e) {}
                            try { $templateProcessor->setValue($key . '_last_value', $matched['last_value'] . ' m³'); } catch (\Throwable $e) {}
                            try { $templateProcessor->setValue($key . '_diff', number_format($matched['diff'], 2) . ' m³'); } catch (\Throwable $e) {}
                            // Fill short alias ${<alias>_f}, ${<alias>_l}, ${<alias>_d}
                            if ($alias) {
                                try { $templateProcessor->setValue($alias . '_f', $matched['first_value'] . ' m³'); } catch (\Throwable $e) {}
                                try { $templateProcessor->setValue($alias . '_l', $matched['last_value'] . ' m³'); } catch (\Throwable $e) {}
                                try { $templateProcessor->setValue($alias . '_d', number_format($matched['diff'], 2) . ' m³'); } catch (\Throwable $e) {}
                            }
                        } else {
                            // Fill blanks if not matched
                            try { $templateProcessor->setValue($key . '_first_value', '-'); } catch (\Throwable $e) {}
                            try { $templateProcessor->setValue($key . '_last_value', '-'); } catch (\Throwable $e) {}
                            try { $templateProcessor->setValue($key . '_diff', '0.00 m³'); } catch (\Throwable $e) {}
                            if ($alias) {
                                try { $templateProcessor->setValue($alias . '_f', '-'); } catch (\Throwable $e) {}
                                try { $templateProcessor->setValue($alias . '_l', '-'); } catch (\Throwable $e) {}
                                try { $templateProcessor->setValue($alias . '_d', '0.00 m³'); } catch (\Throwable $e) {}
                            }
                        }
                    }
                }

                $tmpPath = storage_path('app/tmp_' . uniqid('rep_all_', true) . '.docx');
                $templateProcessor->saveAs($tmpPath);
                return response()->download($tmpPath, $fileName)->deleteFileAfterSend(true);
            } else {
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $section = $phpWord->addSection();
                $section->addTitle('Mjesečni izvještaj (sve firme)', 1);
                $section->addText('Period: ' . $periodLabel);
                $section->addText('Od: ' . $datumOd . '  Do: ' . $datumDo);
                $section->addText('Ukupno: ' . number_format($total, 2) . ' m³');
                $section->addTextBreak(1);
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80]);
                $table->addRow();
                foreach (['Firma','Instrument','Prvi datum','Prva vrijednost','Zadnji datum','Zadnja vrijednost','Razlika'] as $hdr) { $table->addCell(2000)->addText($hdr, ['bold'=>true]); }
                foreach ($rows as $r) {
                    $table->addRow();
                    $table->addCell(2000)->addText($r['company']);
                    $table->addCell(2000)->addText($r['instrument']);
                    $table->addCell(2000)->addText($r['first_date']);
                    $table->addCell(2000)->addText($r['first_value'] . ' m³');
                    $table->addCell(2000)->addText($r['last_date']);
                    $table->addCell(2000)->addText($r['last_value'] . ' m³');
                    $table->addCell(2000)->addText(number_format($r['diff'], 2) . ' m³');
                }
                $tmpPath = storage_path('app/tmp_' . uniqid('rep_all_', true) . '.docx');
                $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                $writer->save($tmpPath);
                return response()->download($tmpPath, $fileName)->deleteFileAfterSend(true);
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Greška pri generisanju DOCX (sve firme): ' . $e->getMessage());
        }
    }
}
