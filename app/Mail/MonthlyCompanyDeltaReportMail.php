<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class MonthlyCompanyDeltaReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $start;
    public $end;
    public $report;

    /**
     * Create a new message instance.
     */
    public function __construct($start, $end, $report)
    {
        $this->start = $start;
        $this->end = $end;
        $this->report = $report;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $periodLabel = $this->start->format('d.m.Y')." - ".$this->end->format('d.m.Y');

        return $this->subject('Mjesečni izvještaj (delta vrijednosti) - '.$periodLabel)
            ->view('emails.monthly_company_delta')
            ->with([
                'start' => $this->start,
                'end' => $this->end,
                'report' => $this->report,
            ]);
    }
}
