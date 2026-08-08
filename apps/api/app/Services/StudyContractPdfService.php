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

        $directory = storage_path('app/private/generated/contracts');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $relativePath = 'generated/contracts/contract-'.$contract->id.'-'.Str::slug($contract->contract_number).'.pdf';
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
        $pdf->SetCreator($this->settings()->text('pdf.shared.university', ''));
        $pdf->SetAuthor($this->settings()->text('pdf.shared.university', ''));
        $pdf->SetTitle($this->settings()->render('pdf.study_contract.document_title', ['number' => $data['number']], $this->missing('pdf.study_contract.document_title')));
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
            [$this->pdfLabel('pdf.study_contract.labels', 0), $data['student']],
            [$this->pdfLabel('pdf.study_contract.labels', 1), $data['passport']],
            [$this->pdfLabel('pdf.study_contract.labels', 2), $data['nationality']],
            [$this->pdfLabel('pdf.study_contract.labels', 3), $data['application_number']],
            [$this->pdfLabel('pdf.study_contract.labels', 4), $data['admission_number']],
            [$this->pdfLabel('pdf.study_contract.labels', 5), $data['degree']],
            [$this->pdfLabel('pdf.study_contract.labels', 6), $data['faculty']],
            [$this->pdfLabel('pdf.study_contract.labels', 7), $data['program']],
            [$this->pdfLabel('pdf.study_contract.labels', 8), $data['study_language']],
            [$this->pdfLabel('pdf.study_contract.labels', 9), $data['education_type']],
            [$this->pdfLabel('pdf.study_contract.labels', 10), $data['academic_year']],
            [$this->pdfLabel('pdf.study_contract.labels', 11), $data['amount']],
            [$this->pdfLabel('pdf.study_contract.labels', 12), $data['advance'].' ('.$data['advance_percentage'].'%)'],
        ])->map(fn ($row) => '<tr><td class="label">'.$this->e($row[0]).':</td><td class="value">'.$this->e($row[1]).'</td></tr>')->implode('');
        $settings = $this->settings();
        $ministry = $this->e($settings->text('pdf.shared.ministry', ''));
        $university = $this->e($settings->text('pdf.shared.university', ''));
        $address = $this->e($settings->text('pdf.shared.address', ''));
        $title = $this->e($settings->text('pdf.study_contract.title', ''));
        $body = $settings->render('pdf.study_contract.body');
        $signature = $this->e($settings->text('pdf.study_contract.signature', ''));
        $stamp = $this->e($settings->text('pdf.shared.stamp', ''));

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
  <div class="ministry">{$ministry}</div>
  <div class="university">{$university}</div>
  <div class="address">{$address}</div>
</div>
<div class="rule"></div>
<br>
<table><tr><td width="50%"><b>{$this->e($data['date'])}</b></td><td width="50%" align="right"><b>{$this->e($data['number'])}</b></td></tr></table>
<div class="title">{$title}</div>
<table class="info">{$rows}</table>
<div class="body">{$body}</div>
<table class="signature">
  <tr>
    <td width="58%"><b>{$signature}</b><br>{$university}</td>
    <td width="42%" class="stamp">{$stamp}</td>
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

        $months = $this->settings()->list('pdf.shared.uzbek_months');

        return ((int) $date->format('j')).' '.($months[(int) $date->format('n') - 1] ?? '').' '.$date->format('Y').' yil.';
    }

    private function money(float|int|string|null $amount, ?string $currency): string
    {
        return $amount !== null
            ? number_format((float) $amount, 2).' '.($currency ?: $this->settings()->text('workflow.currency', ''))
            : $this->settings()->text('pdf.study_contract.amount_to_be_calculated', $this->missing('pdf.study_contract.amount_to_be_calculated'));
    }

    private function label(?string $value): string
    {
        return Str::of((string) $value)->replace(['_', '-'], ' ')->title()->toString();
    }

    private function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function pdfLabel(string $key, int $index): string
    {
        return $this->settings()->list($key)[$index] ?? $this->missing("{$key}.{$index}");
    }

    private function missing(string $key): string
    {
        return "[missing:{$key}]";
    }

    private function settings(): CmsSettingService
    {
        return app(CmsSettingService::class);
    }
}
