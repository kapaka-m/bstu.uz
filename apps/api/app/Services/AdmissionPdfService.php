<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Application;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdmissionPdfService
{
    public function generate(Admission $admission): string
    {
        $admission->loadMissing([
            'application.studentProfile.user',
            'application.program.translations',
            'application.equivalency',
        ]);

        $application = $admission->application;
        $student = $application?->studentProfile;
        $program = $application?->program;
        $programName = $program?->translate('name', 'en') ?: $program?->slug ?: 'Selected program';
        $degree = $this->label($application?->degree_level);
        $studyLanguage = strtolower($this->label($admission->study_language ?: $application?->language_of_study));
        $academicYear = $this->academicYear($application?->intended_intake, $admission->issue_date?->year);
        $duration = $this->duration($admission, $application);

        $data = [
            'number' => $this->admissionNumberLabel($admission->admission_number),
            'date' => $this->uzbekDate($admission->issue_date),
            'name' => strtoupper((string) ($student?->full_name_english ?: $student?->user?->name ?: '')),
            'gender' => strtoupper((string) $this->label($student?->gender)),
            'birth_date' => $this->formatDate($student?->birth_date, 'd/m/Y'),
            'nationality' => strtoupper((string) $student?->nationality),
            'passport' => strtoupper((string) $student?->passport_number),
            'degree' => $degree,
            'program' => $programName,
            'duration' => $duration,
            'academic_year' => $academicYear,
            'study_language' => $studyLanguage ?: 'selected',
        ];

        $pdfPath = storage_path('app/private/admissions');
        if (! is_dir($pdfPath)) {
            mkdir($pdfPath, 0755, true);
        }

        $relativePath = 'admissions/admission-'.$admission->id.'-'.Str::slug($admission->admission_number).'.pdf';
        $absolutePath = storage_path('app/private/'.$relativePath);

        $this->writePdf($absolutePath, $data);

        $admission->forceFill(['document_path' => $relativePath])->save();

        return $relativePath;
    }

    public function ensureDocument(Admission $admission): string
    {
        if ($admission->document_path && Storage::disk('local')->exists($admission->document_path)) {
            return $admission->document_path;
        }

        return $this->generate($admission);
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
        $pdf->SetTitle('Admission Letter '.$data['number']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(18, 14, 18);
        $pdf->SetAutoPageBreak(true, 16);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);

        $html = $this->html($data);
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($absolutePath, 'F');
    }

    private function html(array $data): string
    {
        $tableRows = [
            ['Student Name', $data['name']],
            ['Gender', $data['gender']],
            ['Date of Birth', $data['birth_date']],
            ['Nationality', $data['nationality']],
            ['Passport No / National ID', $data['passport']],
            ['Degree', $data['degree']],
            ['Program', $data['program']],
            ['Duration of Study', $data['duration']],
        ];

        $rows = collect($tableRows)->map(fn ($row) => '<tr><td class="label">'.$this->e($row[0]).':</td><td class="value">'.$this->e($row[1]).'</td></tr>')->implode('');
        $program = $this->e($data['program']);
        $year = $this->e($data['academic_year']);
        $language = $this->e($data['study_language']);

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
  .body { margin-top: 15px; font-size: 10.4pt; line-height: 1.75; text-align: justify; }
  .signature { margin-top: 28px; font-size: 10pt; }
  .stamp { border: 1px solid #cbd5e1; color: #94a3b8; font-size: 9pt; text-align: center; padding: 12px; }
  .footer { margin-top: 22px; padding-top: 8px; border-top: 1px solid #cbd5e1; font-size: 7.8pt; color: #64748b; text-align: center; }
</style>
<div class="header">
  <div class="ministry">MINISTRY OF HIGHER EDUCATION, SCIENCE AND INNOVATIONS OF THE REPUBLIC OF UZBEKISTAN</div>
  <div class="university">BUKHARA STATE TECHNICAL UNIVERSITY</div>
  <div class="address">15 K. Murtazoyev Street, Bukhara city, Republic of Uzbekistan</div>
</div>
<div class="rule"></div>
<br>
<table class="meta">
  <tr>
    <td width="50%"><b>{$this->e($data['date'])}</b></td>
    <td width="50%" align="right"><b>{$this->e($data['number'])}</b></td>
  </tr>
</table>
<div class="title">ADMISSION LETTER</div>
<div class="subtitle">Offer of Enrollment for International Student</div>
<table class="info">{$rows}</table>
<div class="body">
  The student has submitted documents for studying at Bukhara State Technical University
  (Buxoro davlat texnika universiteti) in the Republic of Uzbekistan and has received an offer
  of enrollment in <b>{$program}</b> program. The student will begin studies in the
  <b>{$year}</b> academic year at the first level <b>{$language}-medium</b> program.
  The student must submit his/her original documents after arriving in the country.
  <br><br>
  The Ministry of Higher Education of the Republic of Uzbekistan recognizes Bukhara State
  Technical University (Buxoro davlat texnika universiteti). The university is listed in the
  Times Higher Education, QS World Rankings Asia 2025, UI GreenMetric World University Rankings.
</div>
<table class="signature">
  <tr>
    <td width="58%">
      <b>Rector / Authorized Representative</b><br>
      Bukhara State Technical University
    </td>
    <td width="42%" class="stamp">Official stamp and signature</td>
  </tr>
</table>
<div class="footer">
  This admission letter is generated by the university international admissions system.
  It is valid together with the applicant passport and original education documents.
</div>
HTML;
    }

    private function duration(Admission $admission, ?Application $application): string
    {
        $equivalency = $application?->equivalency;
        if ($equivalency?->estimated_remaining_duration) {
            $duration = strtoupper((string) $equivalency->estimated_remaining_duration);
            if ($equivalency->proposed_entry_year) {
                return $duration.' (the student will join the '.$this->ordinal((int) $equivalency->proposed_entry_year).' level of the program)';
            }

            return $duration;
        }

        if ($admission->estimated_study_duration) {
            return strtoupper($admission->estimated_study_duration);
        }

        if ($application?->program?->duration_years) {
            return strtoupper($application->program->duration_years.' years');
        }

        return 'TO BE DETERMINED';
    }

    private function academicYear(?string $intake, ?int $fallbackYear): string
    {
        if (preg_match('/(fall|autumn)[-_ ]?(20\d{2})/i', (string) $intake, $match)) {
            $year = (int) $match[2];
            return $year.'–'.($year + 1);
        }

        if (preg_match('/spring[-_ ]?(20\d{2})/i', (string) $intake, $match)) {
            $year = (int) $match[1] - 1;
            return $year.'–'.($year + 1);
        }

        $year = $fallbackYear ?: (int) now()->format('Y');
        return $year.'–'.($year + 1);
    }

    private function uzbekDate(DateTimeInterface|string|null $date): string
    {
        $date = $date ?: now();
        if (! $date instanceof DateTimeInterface) {
            $date = Carbon::parse($date);
        }

        $months = [
            1 => 'yanvar',
            2 => 'fevral',
            3 => 'mart',
            4 => 'aprel',
            5 => 'may',
            6 => 'iyun',
            7 => 'iyul',
            8 => 'avgust',
            9 => 'sentabr',
            10 => 'oktabr',
            11 => 'noyabr',
            12 => 'dekabr',
        ];

        return ((int) $date->format('j')).' '.$months[(int) $date->format('n')].' '.$date->format('Y').' yil.';
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

    private function admissionNumberLabel(string $number): string
    {
        return Str::endsWith($number, '-son') ? $number : $number.'-son';
    }

    private function ordinal(int $number): string
    {
        $suffix = in_array($number % 100, [11, 12, 13], true) ? 'th' : match ($number % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };

        return $number.$suffix;
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
