<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyCompanyDeltaReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $start;
    public $end;
    public $report;

    public function __construct($start, $end, $report)
    {
        $this->start = $start;
        $this->end = $end;
        $this->report = $report;
    }

    public function build()
    {
        $periodLabel = $this->start->format('d.m.Y H:i')." - ".$this->end->format('d.m.Y H:i');
        return $this->subject('Sedmični izvještaj (Pet 23:30 -> Pon 07:00) - '.$periodLabel)
            ->view('emails.weekly_company_delta')
            ->with([
                'start' => $this->start,
                'end' => $this->end,
                'report' => $this->report,
            ]);
    }
}
