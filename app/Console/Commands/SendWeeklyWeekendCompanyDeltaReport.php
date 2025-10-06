<?php

namespace App\Console\Commands;

use App\Mail\WeeklyCompanyDeltaReportMail;
use App\Models\Company;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyWeekendCompanyDeltaReport extends Command
{
    /** @var string */
    protected $signature = 'reports:send-weekly-weekend {--date= : Base date to determine the Monday (YYYY-MM-DD)}';

    /** @var string */
    protected $description = 'Send Monday 07:06 report if there is a non-zero change between Friday 23:30 and Monday 07:00';

    public function handle(): int
    {
        $base = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        // Determine the Monday of the week for the given base date
        $mondayStart = $base->copy()->startOfDay();
        if (!$mondayStart->isMonday()) {
            $mondayStart = $base->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        }

        $end = $mondayStart->copy()->setTime(7, 0, 0);           // Monday 07:00
        $start = $mondayStart->copy()->subDays(3)->setTime(23, 30, 0); // Friday 23:30

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
                // First and last record in the [Fri 23:30 -> Mon 07:00] window
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
                Mail::to($recipients)->send(new WeeklyCompanyDeltaReportMail($start, $end, $report));
                $this->info('Weekly weekend report sent to: '.implode(', ', $recipients)." | sumAbs={$sumAbs}");
            } catch (\Throwable $e) {
                Log::error('Weekly weekend report email failed: '.$e->getMessage(), ['exception' => $e]);
                $this->error('Weekly weekend report email failed. Check storage/logs/laravel.log for details.');
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
