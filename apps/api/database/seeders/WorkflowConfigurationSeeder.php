<?php

namespace Database\Seeders;

use App\Models\ApplicationCountry;
use App\Models\ApplicationNationality;
use App\Models\DocumentRequirement;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class WorkflowConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedApplicationOptions();
        $this->seedDocumentRequirements();
        $this->seedWorkflowSettings();
        $this->seedPdfSettings();
    }

    private function seedApplicationOptions(): void
    {
        $pairs = [
            ['Afghanistan', 'Afghan'], ['Albania', 'Albanian'], ['Algeria', 'Algerian'], ['Andorra', 'Andorran'],
            ['Angola', 'Angolan'], ['Argentina', 'Argentinian'], ['Armenia', 'Armenian'], ['Australia', 'Australian'],
            ['Austria', 'Austrian'], ['Azerbaijan', 'Azerbaijani'], ['Bahrain', 'Bahraini'], ['Bangladesh', 'Bangladeshi'],
            ['Belarus', 'Belarusian'], ['Belgium', 'Belgian'], ['Brazil', 'Brazilian'], ['Bulgaria', 'Bulgarian'],
            ['Canada', 'Canadian'], ['China', 'Chinese'], ['Egypt', 'Egyptian'], ['France', 'French'],
            ['Georgia', 'Georgian'], ['Germany', 'German'], ['India', 'Indian'], ['Indonesia', 'Indonesian'],
            ['Iran', 'Iranian'], ['Iraq', 'Iraqi'], ['Italy', 'Italian'], ['Japan', 'Japanese'],
            ['Jordan', 'Jordanian'], ['Kazakhstan', 'Kazakh'], ['Kuwait', 'Kuwaiti'], ['Kyrgyzstan', 'Kyrgyz'],
            ['Malaysia', 'Malaysian'], ['Morocco', 'Moroccan'], ['Pakistan', 'Pakistani'], ['Qatar', 'Qatari'],
            ['Russia', 'Russian'], ['Saudi Arabia', 'Saudi'], ['South Korea', 'South Korean'], ['Tajikistan', 'Tajik'],
            ['Turkey', 'Turkish'], ['Turkmenistan', 'Turkmen'], ['United Arab Emirates', 'Emirati'],
            ['United Kingdom', 'British'], ['United States', 'American'], ['Uzbekistan', 'Uzbek'],
        ];

        foreach ($pairs as $index => [$country, $nationality]) {
            ApplicationCountry::firstOrCreate(
                ['name' => $country],
                ['is_active' => true, 'sort_order' => $index + 1]
            );

            ApplicationNationality::firstOrCreate(
                ['name' => $nationality],
                ['country_name' => $country, 'is_active' => true, 'sort_order' => $index + 1]
            );
        }

        $this->setting('application.genders', "male\nfemale", 'list', 'application', true);
        $this->setting('application.messengers', "whatsapp\ntelegram\nboth", 'list', 'application', true);
        $this->setting('application.passport_types', "ordinary\ndiplomatic\nservice", 'list', 'application', true);
        $this->setting('application.student_types', "new\ntransfer", 'list', 'application', true);
        $this->setting('application.password_min_length', '8', 'integer', 'application', false);
        $this->setting('application.intake_terms', "fall\nspring", 'list', 'application', true);
    }

    private function seedDocumentRequirements(): void
    {
        $requirements = [
            ['passport', 'Passport Copy', 'Main passport information page.', null, null, true],
            ['photo', 'Personal Photo', 'Recent personal photo.', null, null, true],
            ['secondary_certificate', 'Secondary School Certificate', 'Completed secondary education certificate.', null, null, true],
            ['secondary_transcript', 'Secondary School Transcript', 'Secondary grades transcript if separate from certificate.', 'bachelor', null, true],
            ['bachelor_degree', 'Bachelor Degree / Diploma', 'Certified bachelor diploma.', 'master', null, true],
            ['bachelor_transcript', 'Bachelor Transcript', 'Bachelor degree transcript.', 'master', null, true],
            ['bachelor_degree', 'Bachelor Degree / Diploma', 'Certified bachelor diploma.', 'phd', null, true],
            ['bachelor_transcript', 'Bachelor Transcript', 'Bachelor degree transcript.', 'phd', null, true],
            ['master_degree', 'Master Degree / Diploma', 'Certified master diploma.', 'phd', null, true],
            ['master_transcript', 'Master Transcript', 'Master degree transcript.', 'phd', null, true],
            ['university_transcript', 'University Transcript', 'Transcript from previous university.', null, 'transfer', true],
            ['proof_of_enrollment', 'Proof of Enrollment', 'Student status certificate from previous university.', null, 'transfer', true],
            ['course_descriptions', 'Course Descriptions / Syllabus', 'Course descriptions for academic equivalency.', null, 'transfer', false],
        ];

        foreach ($requirements as [$type, $name, $description, $degree, $studentType, $required]) {
            DocumentRequirement::firstOrCreate(
                [
                    'application_id' => null,
                    'program_id' => null,
                    'degree_level' => $degree,
                    'student_type' => $studentType,
                    'document_type' => $type,
                ],
                [
                    'name' => $name,
                    'description' => $description,
                    'is_required' => $required,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedWorkflowSettings(): void
    {
        $this->setting('workflow.application_fee_amount', '50', 'decimal', 'workflow', true);
        $this->setting('workflow.service_fee_amount', '300', 'decimal', 'workflow', true);
        $this->setting('workflow.currency', 'USD', 'text', 'workflow', true);
        $this->setting('workflow.contract_advance_percentage', '30', 'integer', 'workflow', true);

        $messages = [
            'workflow.next.upload_documents' => 'Upload all required documents.',
            'workflow.next.wait_document_review' => 'Wait for document review or correct rejected documents.',
            'workflow.next.wait_equivalency' => 'Wait for academic equivalency result and accept it.',
            'workflow.next.equivalency_not_required' => 'Academic equivalency is not required.',
            'workflow.next.pay_application_fee' => 'Pay the {{amount}} {{currency}} application and admission fee and upload the receipt.',
            'workflow.next.wait_final_review' => 'Wait for final university review.',
            'workflow.next.wait_admission' => 'Wait for admission issuance.',
            'workflow.next.review_contract' => 'Download and review the study contract.',
            'workflow.next.upload_contract_advance' => 'Upload the {{percentage}}% contract payment receipt.',
            'workflow.next.wait_enrollment' => 'Wait for enrollment certificate issuance.',
            'workflow.next.wait_prikaz' => 'Wait for prikaz issuance.',
            'workflow.next.upload_service_fee' => 'Upload the {{amount}} {{currency}} service fee receipt.',
            'workflow.next.wait_telex' => 'Wait for telex processing.',
            'workflow.next.wait_visa' => 'Wait for visa processing.',
            'workflow.next.wait_housing' => 'Wait for housing request review.',
            'workflow.next.wait_residence' => 'Wait for residence permit processing.',
            'workflow.next.completed' => 'All current admission workflow steps are completed.',
            'workflow.notification.admission_issued.title' => 'Admission issued',
            'workflow.notification.admission_issued.message' => 'Your final admission has been issued.',
            'workflow.history.admission_issued' => 'Final admission issued.',
            'workflow.timeline.account' => 'Account Created',
            'workflow.timeline.documents_required' => 'Documents Required',
            'workflow.timeline.documents_review' => 'Documents Under Review',
            'workflow.timeline.academic_review' => 'Academic Review',
            'workflow.timeline.fee' => 'Application Fee Required',
            'workflow.timeline.payment_review' => 'Payment Under Review',
            'workflow.timeline.final_review' => 'Final Application Review',
            'workflow.timeline.admission' => 'Admission Issued',
            'workflow.timeline.study_contract' => 'Study Contract',
            'workflow.timeline.contract_advance' => '30% Contract Payment',
            'workflow.timeline.enrollment' => 'Enrollment Certificate',
            'workflow.timeline.prikaz' => 'Prikaz',
            'workflow.timeline.service_fee' => 'Service Fee',
            'workflow.timeline.telex' => 'Telex',
            'workflow.timeline.visa' => 'Visa',
            'workflow.timeline.housing' => 'Housing',
            'workflow.timeline.residence' => 'Residence Permit',
            'workflow.status.completed' => 'Completed',
            'workflow.status.action_required' => 'Action Required',
            'workflow.status.approved' => 'Approved',
            'workflow.status.in_progress' => 'In Progress',
            'workflow.status.under_review' => 'Under Review',
            'workflow.status.not_started' => 'Not Started',
            'workflow.status.issued' => 'Issued',
            'workflow.status.ready' => 'Ready',
        ];

        foreach ($messages as $key => $value) {
            $this->setting($key, $value, 'text', 'workflow', true);
        }
    }

    private function seedPdfSettings(): void
    {
        $shared = [
            'pdf.shared.ministry' => 'MINISTRY OF HIGHER EDUCATION, SCIENCE AND INNOVATIONS OF THE REPUBLIC OF UZBEKISTAN',
            'pdf.shared.university' => 'BUKHARA STATE TECHNICAL UNIVERSITY',
            'pdf.shared.address' => '15 K. Murtazoyev Street, Bukhara city, Republic of Uzbekistan',
            'pdf.shared.stamp' => 'Official stamp and signature',
            'pdf.shared.generated_footer' => 'This document is generated by the university international student system.',
            'pdf.shared.uzbek_months' => "yanvar\nfevral\nmart\naprel\nmay\niyun\niyul\navgust\nsentabr\noktabr\nnoyabr\ndekabr",
        ];

        foreach ($shared as $key => $value) {
            $this->setting($key, $value, 'text', 'pdf', false);
        }

        $documents = [
            'admission' => [
                'title' => 'ADMISSION LETTER',
                'document_title' => 'Admission Letter {{number}}',
                'subtitle' => 'Offer of Enrollment for International Student',
                'labels' => "Student Name\nGender\nDate of Birth\nNationality\nPassport No / National ID\nDegree\nProgram\nDuration of Study",
                'body' => 'The student has submitted documents for studying at Bukhara State Technical University (Buxoro davlat texnika universiteti) in the Republic of Uzbekistan and has received an offer of enrollment in <b>{{program}}</b> program. The student will begin studies in the <b>{{academic_year}}</b> academic year at the first level <b>{{study_language}}-medium</b> program. The student must submit his/her original documents after arriving in the country.<br><br>The Ministry of Higher Education of the Republic of Uzbekistan recognizes Bukhara State Technical University (Buxoro davlat texnika universiteti). The university is listed in the Times Higher Education, QS World Rankings Asia 2025, UI GreenMetric World University Rankings.',
                'duration_transfer_level' => '{{duration}} (the student will join the {{level}} level of the program)',
                'duration_to_be_determined' => 'TO BE DETERMINED',
                'signature' => 'Rector / Authorized Representative',
                'footer' => 'This admission letter is generated by the university international admissions system. It is valid together with the applicant passport and original education documents.',
            ],
            'enrollment' => [
                'title' => 'ENROLLMENT CERTIFICATE',
                'document_title' => 'Enrollment Certificate {{number}}',
                'subtitle' => 'Student Registration Confirmation',
                'labels' => "Student Name\nDate of Birth\nNationality\nPassport No / National ID\nDegree\nProgram\nStudy Language\nAcademic Year\nAdmission Number",
                'body' => 'This is to certify that <b>{{name}}</b> has been enrolled as an international student of Bukhara State Technical University (Buxoro davlat texnika universiteti) for the <b>{{academic_year}}</b> academic year after receiving admission and completing the required initial contract payment. The student is registered in the <b>{{program}}</b> program.<br><br>This certificate is issued for submission to the relevant authorities and confirms the student registration status at the university as of the issue date above.',
                'signature' => 'Registrar / Authorized Representative',
                'footer' => 'This document is generated by the university international student system.',
            ],
            'prikaz' => [
                'title' => 'ORDER OF ENROLLMENT',
                'document_title' => 'Prikaz {{number}}',
                'subtitle' => '',
                'labels' => "Student Name\nPassport No / National ID\nNationality\nDegree\nProgram\nAcademic Year\nAdmission Number\nEnrollment Number",
                'body' => 'Based on the submitted documents, issued admission letter, completed contract advance payment, and enrollment certificate, the student named above is included in the university enrollment order for international students of Bukhara State Technical University.<br><br>This document is issued for internal university registration and for the student subsequent telex, visa, housing, and residence permit procedures where applicable.',
                'signature' => 'Rector / Authorized Representative',
                'footer' => '',
            ],
            'study_contract' => [
                'title' => 'STUDY CONTRACT',
                'document_title' => 'Study Contract {{number}}',
                'subtitle' => '',
                'labels' => "Student Name\nPassport No / National ID\nNationality\nApplication Number\nAdmission Number\nDegree\nFaculty\nProgram\nStudy Language\nEducation Type\nAcademic Year\nTotal Contract Amount\nRequired Advance Payment",
                'body' => 'This study contract confirms the financial terms for the international student listed above. The student must pay the required advance payment before the university issues the enrollment certificate and proceeds with the following registration stages. This contract is not a visa, residence permit, or final government registration document.<br><br>The remaining contract balance, service fees, residence, housing, telex, and visa-related procedures are processed according to the university rules and the official requirements of the Republic of Uzbekistan.',
                'amount_to_be_calculated' => 'To be calculated',
                'signature' => 'Authorized Representative',
                'footer' => '',
            ],
        ];

        foreach ($documents as $document => $fields) {
            foreach ($fields as $field => $value) {
                $this->setting("pdf.{$document}.{$field}", $value, 'text', 'pdf', false);
            }
        }
    }

    private function setting(string $key, string $value, string $type, string $group, bool $public): void
    {
        Setting::firstOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'is_public' => $public,
            ]
        );
    }
}
