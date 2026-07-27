<?php

namespace App\Services;

use App\Models\Prikaz;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PrikazPdfService
{
    public function generate(Prikaz $prikaz): string
    {
        $prikaz->loadMissing([
            'application.admission',
            'enrollment',
            'studentProfile.user',
            'program.translations',
        ]);

        $application = $prikaz->application;
        $student = $prikaz->studentProfile;
        $programName = $prikaz->program?->translate('name', 'en') ?: $prikaz->program?->slug ?: 'Selected program';

        $data = [
            'number' => $prikaz->prikaz_number,
            'date' => $this->uzbekDate($prikaz->issue_date ?: now()),
            'student' => strtoupper((string) ($student?->full_name_english ?: $student?->user?->name ?: '')),
            'passport' => strtoupper((string) $student?->passport_number),
            'nationality' => strtoupper((string) $student?->nationality),
            'degree' => $this->label($application?->degree_level),
            'program' => $programName,
            'academic_year' => $prikaz->academic_year ?: $prikaz->enrollment?->academic_year,
            'admission_number' => $application?->admission?->admission_number,
            'student_number' => $prikaz->enrollment?->student_number,
        ];

        $directory = storage_path('app/private/prikazes');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $relativePath = 'prikazes/prikaz-'.$prikaz->id.'-'.Str::slug($prikaz->prikaz_number).'.pdf';
        $absolutePath = storage_path('app/private/'.$relativePath);
        $this->writePdf($absolutePath, $data);
        $prikaz->forceFill(['document_path' => $relativePath])->save();

        return $relativePath;
    }

    public function ensureDocument(Prikaz $prikaz): string
    {
        if ($prikaz->document_path && Storage::disk('local')->exists($prikaz->document_path)) {
            return $prikaz->document_path;
        }

        return $this->generate($prikaz);
    }

    private function writePdf(string $absolutePath, array $data): void
    {
        $tcpdfPath = base_path('vendor/tecnickcom/tcpdf/tcpdf.php');
        if (! class_exists('TCPDF') && file_exists($tcpdfPath)) {
            require_once $tcpdfPath;
        }
        if (! class_exists('TCPDF')) {
            throw new \RuntimeException('PDF engine is not available.');
        }

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Bukhara State Technical University');
        $pdf->SetAuthor('Bukhara State Technical University');
        $pdf->SetTitle('Prikaz '.$data['number']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(18, 14, 18);
        $pdf->SetAutoPageBreak(true, 16);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->writeHTML($this->html($data), true, false, true, false, '');
        $pdf->Output($absolutePath, 'F');
    }

    private function html(array $data): string
    {
        $rows = collect([
            ['Student Name', $data['student']],
            ['Passport No / National ID', $data['passport']],
            ['Nationality', $data['nationality']],
            ['Degree', $data['degree']],
            ['Program', $data['program']],
            ['Academic Year', $data['academic_year']],
            ['Admission Number', $data['admission_number']],
            ['Enrollment Number', $data['student_number']],
        ])->map(fn ($row) => '<tr><td class="label">'.$this->e($row[0]).':</td><td class="value">'.$this->e($row[1]).'</td></tr>')->implode('');

        return <<<HTML
<style>
  body { color: #111827; font-family: dejavusans; }
  .header { text-align: center; line-height: 1.35; }
  .ministry { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; }
  .university { font-size: 13pt; font-weight: bold; color: #143260; text-transform: uppercase; }
  .address { font-size: 8.5pt; color: #374151; }
  .rule { border-bottom: 1.2px solid #143260; height: 8px; }
  .title { margin-top: 12px; text-align: center; font-size: 16pt; font-weight: bold; color: #143260; text-transform: uppercase; }
  table.info { margin-top: 12px; width: 100%; border-collapse: collapse; }
  table.info td { border: 1px solid #cbd5e1; padding: 7px 8px; font-size: 10pt; }
  table.info td.label { width: 38%; background-color: #f3f6fb; font-weight: bold; color: #143260; }
  table.info td.value { width: 62%; font-weight: bold; }
  .body { margin-top: 16px; font-size: 10.5pt; line-height: 1.75; text-align: justify; }
  .signature { margin-top: 30px; font-size: 10pt; }
  .stamp { border: 1px solid #cbd5e1; color: #94a3b8; font-size: 9pt; text-align: center; padding: 12px; }
</style>
<div class="header">
  <div class="ministry">MINISTRY OF HIGHER EDUCATION, SCIENCE AND INNOVATIONS OF THE REPUBLIC OF UZBEKISTAN</div>
  <div class="university">BUKHARA STATE TECHNICAL UNIVERSITY</div>
  <div class="address">15 K. Murtazoyev Street, Bukhara city, Republic of Uzbekistan</div>
</div>
<div class="rule"></div>
<br>
<table><tr><td width="50%"><b>{$this->e($data['date'])}</b></td><td width="50%" align="right"><b>{$this->e($data['number'])}</b></td></tr></table>
<div class="title">ORDER OF ENROLLMENT</div>
<table class="info">{$rows}</table>
<div class="body">
  Based on the submitted documents, issued admission letter, completed contract advance
  payment, and enrollment certificate, the student named above is included in the university
  enrollment order for international students of Bukhara State Technical University.
  <br><br>
  This document is issued for internal university registration and for the student's subsequent
  telex, visa, housing, and residence permit procedures where applicable.
</div>
<table class="signature">
  <tr>
    <td width="58%"><b>Rector / Authorized Representative</b><br>Bukhara State Technical University</td>
    <td width="42%" class="stamp">Official stamp and signature</td>
  </tr>
</table>
HTML;
    }

    private function uzbekDate(DateTimeInterface|string|null $date): string
    {
        $date = $date ?: now();
        if (! $date instanceof DateTimeInterface) {
            $date = Carbon::parse($date);
        }

        $months = [1 => 'yanvar', 2 => 'fevral', 3 => 'mart', 4 => 'aprel', 5 => 'may', 6 => 'iyun', 7 => 'iyul', 8 => 'avgust', 9 => 'sentabr', 10 => 'oktabr', 11 => 'noyabr', 12 => 'dekabr'];
        return ((int) $date->format('j')).' '.$months[(int) $date->format('n')].' '.$date->format('Y').' yil.';
    }

    private function label(?string $value): string
    {
        return Str::of((string) $value)->replace(['_', '-'], ' ')->title()->toString();
    }

    private function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
