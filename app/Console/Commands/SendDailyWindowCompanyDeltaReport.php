<?php

namespace App\Console\Commands;

use App\Mail\DailyCompanyDeltaReportMail;
use App\Models\Company;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyWindowCompanyDeltaReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-daily {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily company delta report for window [yesterday 23:30 -> today 07:00] when there is a non-zero change';

    public function handle(): int
    {
        $base = $this->option('date') ? Carbon::parse($this->option('date'))->startOfDay() : now()->startOfDay();

        // Only run on weekdays (Monday=1 .. Friday=5)
        if ($base->isWeekend()) {
            $this->info('Daily report skipped on weekend.');
            return self::SUCCESS;
        }
    $start = $base->copy()->subDay()->setTime(23, 30, 0);
        $end = $base->copy()->setTime(7, 0, 0);

        $report = [];
        $hasChange = false;
        $sumAbs = 0.0;

        $companies = Company::with('instruments')->orderBy('name')->get();

        foreach ($companies as $company) {
            $companyData = [
                'company' => $company,
                'instruments' => [],
                'total' => 0.0,
            ];

            foreach ($company->instruments as $instrument) {
                $first = Record::where('instrument_id', $instrument->id)
                    ->whereBetween('vrijeme', [$start, $end])
                    ->orderBy('vrijeme', 'asc')
                    ->first();

                $last = Record::where('instrument_id', $instrument->id)
                    ->whereBetween('vrijeme', [$start, $end])
                    ->orderBy('vrijeme', 'desc')
                    ->first();

                $delta = null;
                if ($first && $last) {
                    $delta = (float) $last->vrijednost - (float) $first->vrijednost;
                    if (abs($delta) > 0) {
                        $hasChange = true;
                        $sumAbs += abs($delta);
                        $companyData['total'] += $delta;
                    }
                }

                $companyData['instruments'][] = [
                    'instrument' => $instrument,
                    'first' => $first,
                    'last' => $last,
                    'delta' => $delta,
                ];
            }

            $report[] = $companyData;
        }

        if (!$hasChange) {
            $this->info("No changes detected between {$start->format('d.m.Y H:i')} and {$end->format('d.m.Y H:i')}. Email will not be sent.");
            return self::SUCCESS;
        }

        $recipients = Config::get('reports.recipients', []);
        if (empty($recipients)) {
            $this->warn('No recipients configured in config/reports.php (key: recipients).');
        }

        if (!empty($recipients)) {
            try {
                Mail::to($recipients)->send(new DailyCompanyDeltaReportMail($start, $end, $report));
                $this->info('Daily report sent to: '.implode(', ', $recipients)." | sumAbs={$sumAbs}");
            } catch (\Throwable $e) {
                Log::error('Daily report email failed: '.$e->getMessage(), ['exception' => $e]);
                $this->error('Daily report email failed. Check storage/logs/laravel.log for details.');
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
