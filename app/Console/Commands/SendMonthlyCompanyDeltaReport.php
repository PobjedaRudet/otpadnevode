<?php

namespace App\Console\Commands;

use App\Mail\MonthlyCompanyDeltaReportMail;
use App\Models\Company;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SendMonthlyCompanyDeltaReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-monthly {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly company delta report (difference between first and last record of previous month)';

    public function handle(): int
    {
        $runDate = $this->option('date') ? Carbon::parse($this->option('date')) : now();

        // Determine previous month date range
        $start = $runDate->copy()->subMonthNoOverflow()->startOfMonth();
        $end = $runDate->copy()->subMonthNoOverflow()->endOfMonth();

        // Build report per company
        $report = [];

        $companies = Company::with('instruments')->orderBy('name')->get();

        foreach ($companies as $company) {
            $companyData = [
                'company' => $company,
                'instruments' => [],
                'total' => 0.0,
            ];

            foreach ($company->instruments as $instrument) {
                // First and last record by date within previous month
                $first = Record::where('instrument_id', $instrument->id)
                    ->whereBetween('datum', [$start->toDateString(), $end->toDateString()])
                    ->orderBy('datum', 'asc')
                    ->orderBy('vrijeme', 'asc')
                    ->first();

                $last = Record::where('instrument_id', $instrument->id)
                    ->whereBetween('datum', [$start->toDateString(), $end->toDateString()])
                    ->orderBy('datum', 'desc')
                    ->orderBy('vrijeme', 'desc')
                    ->first();

                if ($first && $last) {
                    $delta = (float) $last->vrijednost - (float) $first->vrijednost;
                } else {
                    $delta = null;
                }

                $companyData['instruments'][] = [
                    'instrument' => $instrument,
                    'first' => $first,
                    'last' => $last,
                    'delta' => $delta,
                ];
                if (!is_null($delta)) {
                    $companyData['total'] += $delta;
                }
            }

            $report[] = $companyData;
        }

        $recipients = Config::get('reports.recipients', []);
        if (empty($recipients)) {
            $this->warn('No recipients configured in config/reports.php (key: recipients).');
        }

        // Send email
        if (!empty($recipients)) {
            try {
                Mail::to($recipients)->send(new MonthlyCompanyDeltaReportMail($start, $end, $report));
                $this->info('Monthly report sent to: '.implode(', ', $recipients));
            } catch (\Throwable $e) {
                Log::error('Monthly report email failed: '.$e->getMessage(), [
                    'exception' => $e,
                ]);
                $this->error('Monthly report email failed. Check storage/logs/laravel.log for details.');
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
