<?php

namespace App\Services;

use App\Models\Contract;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudyContractPdfService
{
    public function generate(Contract $contract): string
    {
        $contract->loadMissing([
            'application.studentProfile.user',
            'application.program.translations',
            'application.faculty.translations',
            'application.admission',
        ]);

        $application = $contract->application;
        $student = $application?->studentProfile;
        $program = $application?->program;
        $programName = $program?->translate('name', 'en') ?: $program?->slug ?: 'Selected program';
        $facultyName = $application?->faculty?->translate('name', 'en') ?: $application?->faculty?->slug ?: 'Selected faculty';

        $data = [
            'number' => $contract->contract_number,
            'date' => $this->uzbekDate($contract->issued_at ?: now()),
            'student' => strtoupper((string) ($student?->full_name_english ?: $student?->user?->name ?: '')),
            'passport' => strtoupper((string) $student?->passport_number),
            'nationality' => strtoupper((string) $student?->nationality),
            'application_number' => $application?->application_number,
            'admission_number' => $application?->admission?->admission_number,
            'degree' => $this->label($application?->degree_level),
            'faculty' => $facultyName,
            'program' => $programName,
            'study_language' => $this->label($application?->language_of_study),
            'education_type' => $this->label($application?->study_mode),
            'academic_year' => $this->academicYear($application?->intended_intake),
            'amount' => $this->money($contract->amount, $contract->currency),
            'advance' => $this->money($contract->advance_amount, $contract->currency),
            'advance_percentage' => $contract->advance_percentage ?: 30,
        ];

        $directory = storage_path('app/private/contracts');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $relativePath = 'contracts/contract-'.$contract->id.'-'.Str::slug($contract->contract_number).'.pdf';
        $absolutePath = storage_path('app/private/'.$relativePath);
        $this->writePdf($absolutePath, $data);

        $contract->forceFill([
            'document_path' => $relativePath,
            'issued_at' => $contract->issued_at ?: now(),
        ])->save();

        return $relativePath;
    }

    public function ensureDocument(Contract $contract): string
    {
        if ($contract->document_path && Storage::disk('local')->exists($contract->document_path)) {
            return $contract->document_path;
        }

        return $this->generate($contract);
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
        $pdf->SetTitle('Study Contract '.$data['number']);
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
            ['Application Number', $data['application_number']],
            ['Admission Number', $data['admission_number']],
            ['Degree', $data['degree']],
            ['Faculty', $data['faculty']],
            ['Program', $data['program']],
            ['Study Language', $data['study_language']],
            ['Education Type', $data['education_type']],
            ['Academic Year', $data['academic_year']],
            ['Total Contract Amount', $data['amount']],
            ['Required Advance Payment', $data['advance'].' ('.$data['advance_percentage'].'%)'],
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
  .body { margin-top: 16px; font-size: 10.4pt; line-height: 1.7; text-align: justify; }
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
<div class="title">STUDY CONTRACT</div>
<table class="info">{$rows}</table>
<div class="body">
  This study contract confirms the financial terms for the international student listed above.
  The student must pay the required advance payment before the university issues the enrollment
  certificate and proceeds with the following registration stages. This contract is not a visa,
  residence permit, or final government registration document.
  <br><br>
  The remaining contract balance, service fees, residence, housing, telex, and visa-related
  procedures are processed according to the university rules and the official requirements of
  the Republic of Uzbekistan.
</div>
<table class="signature">
  <tr>
    <td width="58%"><b>Authorized Representative</b><br>Bukhara State Technical University</td>
    <td width="42%" class="stamp">Official stamp and signature</td>
  </tr>
</table>
HTML;
    }

    private function academicYear(?string $intake): string
    {
        if (preg_match('/(fall|autumn)[-_ ]?(20\d{2})/i', (string) $intake, $match)) {
            $year = (int) $match[2];
            return $year.'-'.($year + 1);
        }
        $year = (int) now()->format('Y');
        return $year.'-'.($year + 1);
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

    private function money(float|int|string|null $amount, ?string $currency): string
    {
        return $amount !== null ? number_format((float) $amount, 2).' '.($currency ?: 'USD') : 'To be calculated';
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
