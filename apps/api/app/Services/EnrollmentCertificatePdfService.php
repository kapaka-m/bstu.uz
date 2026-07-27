<?php

namespace App\Services;

use App\Models\Enrollment;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EnrollmentCertificatePdfService
{
    public function generate(Enrollment $enrollment): string
    {
        $enrollment->loadMissing([
            'application.admission',
            'studentProfile.user',
            'program.translations',
        ]);

        $application = $enrollment->application;
        $student = $enrollment->studentProfile;
        $program = $enrollment->program;
        $programName = $program?->translate('name', 'en') ?: $program?->slug ?: 'Selected program';

        $data = [
            'number' => $enrollment->student_number,
            'date' => $this->uzbekDate($enrollment->issue_date ?: now()),
            'name' => strtoupper((string) ($student?->full_name_english ?: $student?->user?->name ?: '')),
            'birth_date' => $this->formatDate($student?->birth_date, 'd/m/Y'),
            'passport' => strtoupper((string) $student?->passport_number),
            'nationality' => strtoupper((string) $student?->nationality),
            'degree' => $this->label($application?->degree_level),
            'program' => $programName,
            'academic_year' => $enrollment->academic_year,
            'study_language' => strtolower($this->label($application?->language_of_study)),
            'admission_number' => $application?->admission?->admission_number,
        ];

        $pdfPath = storage_path('app/private/generated/enrollments');
        if (! is_dir($pdfPath)) {
            mkdir($pdfPath, 0755, true);
        }

        $relativePath = 'generated/enrollments/enrollment-'.$enrollment->id.'-'.Str::slug($enrollment->student_number).'.pdf';
        $absolutePath = storage_path('app/private/'.$relativePath);

        $this->writePdf($absolutePath, $data);
        $enrollment->forceFill(['document_path' => $relativePath])->save();

        return $relativePath;
    }

    public function ensureDocument(Enrollment $enrollment): string
    {
        if ($enrollment->document_path && Storage::disk('local')->exists($enrollment->document_path)) {
            return $enrollment->document_path;
        }

        return $this->generate($enrollment);
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
        $pdf->SetTitle('Enrollment Certificate '.$data['number']);
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
            [$this->pdfLabel('pdf.enrollment.labels', 0, 'Student Name'), $data['name']],
            [$this->pdfLabel('pdf.enrollment.labels', 1, 'Date of Birth'), $data['birth_date']],
            [$this->pdfLabel('pdf.enrollment.labels', 2, 'Nationality'), $data['nationality']],
            [$this->pdfLabel('pdf.enrollment.labels', 3, 'Passport No / National ID'), $data['passport']],
            [$this->pdfLabel('pdf.enrollment.labels', 4, 'Degree'), $data['degree']],
            [$this->pdfLabel('pdf.enrollment.labels', 5, 'Program'), $data['program']],
            [$this->pdfLabel('pdf.enrollment.labels', 6, 'Study Language'), $data['study_language']],
            [$this->pdfLabel('pdf.enrollment.labels', 7, 'Academic Year'), $data['academic_year']],
            [$this->pdfLabel('pdf.enrollment.labels', 8, 'Admission Number'), $data['admission_number']],
        ])->map(fn ($row) => '<tr><td class="label">'.$this->e($row[0]).':</td><td class="value">'.$this->e($row[1]).'</td></tr>')->implode('');
        $settings = $this->settings();
        $ministry = $this->e($settings->text('pdf.shared.ministry', ''));
        $university = $this->e($settings->text('pdf.shared.university', ''));
        $address = $this->e($settings->text('pdf.shared.address', ''));
        $title = $this->e($settings->text('pdf.enrollment.title', ''));
        $subtitle = $this->e($settings->text('pdf.enrollment.subtitle', ''));
        $body = $settings->render('pdf.enrollment.body', [
            'name' => $this->e($data['name']),
            'academic_year' => $this->e($data['academic_year']),
            'program' => $this->e($data['program']),
        ]);
        $signature = $this->e($settings->text('pdf.enrollment.signature', ''));
        $stamp = $this->e($settings->text('pdf.shared.stamp', ''));
        $footer = $this->e($settings->text('pdf.enrollment.footer', ''));

        return <<<HTML
<style>
  body { color: #111827; font-family: dejavusans; }
  .header { text-align: center; line-height: 1.35; }
  .ministry { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; }
  .university { font-size: 13pt; font-weight: bold; color: #143260; text-transform: uppercase; }
  .address { font-size: 8.5pt; color: #374151; }
  .rule { border-bottom: 1.2px solid #143260; height: 8px; }
  .meta { font-size: 10pt; }
  .title { margin-top: 12px; text-align: center; font-size: 16pt; font-weight: bold; color: #143260; text-transform: uppercase; }
  .subtitle { text-align: center; font-size: 10pt; font-weight: bold; color: #374151; }
  table.info { margin-top: 12px; width: 100%; border-collapse: collapse; }
  table.info td { border: 1px solid #cbd5e1; padding: 7px 8px; font-size: 10pt; }
  table.info td.label { width: 38%; background-color: #f3f6fb; font-weight: bold; color: #143260; }
  table.info td.value { width: 62%; font-weight: bold; }
  .body { margin-top: 16px; font-size: 10.5pt; line-height: 1.75; text-align: justify; }
  .signature { margin-top: 30px; font-size: 10pt; }
  .stamp { border: 1px solid #cbd5e1; color: #94a3b8; font-size: 9pt; text-align: center; padding: 12px; }
  .footer { margin-top: 22px; padding-top: 8px; border-top: 1px solid #cbd5e1; font-size: 7.8pt; color: #64748b; text-align: center; }
</style>
<div class="header">
  <div class="ministry">{$ministry}</div>
  <div class="university">{$university}</div>
  <div class="address">{$address}</div>
</div>
<div class="rule"></div>
<br>
<table class="meta">
  <tr>
    <td width="50%"><b>{$this->e($data['date'])}</b></td>
    <td width="50%" align="right"><b>{$this->e($data['number'])}</b></td>
  </tr>
</table>
<div class="title">{$title}</div>
<div class="subtitle">{$subtitle}</div>
<table class="info">{$rows}</table>
<div class="body">{$body}</div>
<table class="signature">
  <tr>
    <td width="58%">
      <b>{$signature}</b><br>
      {$university}
    </td>
    <td width="42%" class="stamp">{$stamp}</td>
  </tr>
</table>
<div class="footer">{$footer}</div>
HTML;
    }

    private function uzbekDate(DateTimeInterface|string|null $date): string
    {
        $date = $date ?: now();
        if (! $date instanceof DateTimeInterface) {
            $date = Carbon::parse($date);
        }

        $months = $this->settings()->list('pdf.shared.uzbek_months', [
            'yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun',
            'iyul', 'avgust', 'sentabr', 'oktabr', 'noyabr', 'dekabr',
        ]);

        return ((int) $date->format('j')).' '.($months[(int) $date->format('n') - 1] ?? '').' '.$date->format('Y').' yil.';
    }

    private function formatDate(DateTimeInterface|string|null $date, string $format): string
    {
        if (! $date) {
            return '';
        }

        if (! $date instanceof DateTimeInterface) {
            $date = Carbon::parse($date);
        }

        return $date->format($format);
    }

    private function label(?string $value): string
    {
        return Str::of((string) $value)->replace(['_', '-'], ' ')->title()->toString();
    }

    private function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function pdfLabel(string $key, int $index, string $default): string
    {
        return $this->settings()->list($key)[$index] ?? $default;
    }

    private function settings(): CmsSettingService
    {
        return app(CmsSettingService::class);
    }
}
