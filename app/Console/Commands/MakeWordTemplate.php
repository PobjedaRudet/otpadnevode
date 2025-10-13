<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeWordTemplate extends Command
{
    protected $signature = 'reports:make-word-template {--force : Overwrite if exists}';
    protected $description = 'Generate a Word template (public/Izvjestaj.docx) with required placeholders and a table row for cloning.';

    public function handle(): int
    {
        $path = public_path('Izvjestaj.docx');
        if (file_exists($path) && !$this->option('force')) {
            $this->warn('Template already exists at ' . $path . ' (use --force to overwrite).');
            return self::SUCCESS;
        }

        // Build a minimal DOCX that includes the placeholders we support.
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addTitle('Mjesečni izvještaj', 1);
        $section->addText('Firma: ${company}');
    $section->addText('Period: ${period}');
    $section->addText('Datum od: ${datum_od}');
    $section->addText('Datum do: ${datum_do}');
        $section->addText('Ukupno: ${total}');
        $section->addTextBreak(1);

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80]);
        $table->addRow();
        foreach (['Firma','Instrument','Prvi datum','Prva vrijednost','Zadnji datum','Zadnja vrijednost','Razlika'] as $hdr) {
            $table->addCell(2200)->addText($hdr, ['bold' => true]);
        }
        // Template row with placeholders (one row only; export will clone this by placeholder name "instrument")
        $table->addRow();
        $table->addCell(2200)->addText('${company}');
        $table->addCell(2200)->addText('${instrument}');
        $table->addCell(2200)->addText('${first_date}');
        $table->addCell(2200)->addText('${first_value}');
        $table->addCell(2200)->addText('${last_date}');
        $table->addCell(2200)->addText('${last_value}');
        $table->addCell(2200)->addText('${diff}');

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        $this->info('Template created at ' . $path);
        return self::SUCCESS;
    }
}
