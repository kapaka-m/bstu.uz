class AppConstants {
  const AppConstants._();

  static const supportedLocales = ['en', 'uz', 'ru', 'ar'];
  static const rtlLocales = ['ar'];

  static const applicationStatuses = [
    'draft',
    'submitted',
    'under_review',
    'missing_documents',
    'accepted',
    'rejected',
    'contract_pending',
    'payment_pending',
    'enrolled',
    'active_student',
    'graduated',
  ];

  static const documentTypes = [
    'passport',
    'photo',
    'education_certificate',
    'transcript',
    'medical_certificate',
    'language_certificate',
    'payment_receipt',
    'other',
  ];
}
