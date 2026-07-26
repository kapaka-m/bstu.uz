import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { motion, AnimatePresence } from "framer-motion";
import {
  User, Mail, Phone, Globe, Calendar, FileText,
  GraduationCap, BookOpen, CheckCircle2, ChevronRight,
  ChevronLeft, ArrowLeft, Loader2, Shield, Star
} from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { programService } from "../services/programService";

const STEPS = [1, 2, 3];

const stepVariants = {
  enter: (dir) => ({ opacity: 0, x: dir > 0 ? 60 : -60 }),
  center: { opacity: 1, x: 0 },
  exit: (dir) => ({ opacity: 0, x: dir > 0 ? -60 : 60 }),
};

// ── Translations ──────────────────────────────────────────────────────────────
const applyT = {
  en: {
    pageTitle: "Apply to BSTU",
    pageSubtitle: "Begin your academic journey — Complete your application in 3 easy steps",
    step1Label: "Personal Info",
    step2Label: "Academic Info",
    step3Label: "Review & Submit",
    firstName: "First Name",
    lastName: "Last Name",
    dateOfBirth: "Date of Birth",
    nationality: "Nationality",
    email: "Email Address",
    emailPlaceholder: "you@example.com",
    phone: "Phone Number",
    gender: "Gender",
    male: "Male",
    female: "Female",
    passport: "Passport / National ID No.",
    chooseProgram: "Choose Your Program",
    degreeLevel: "Degree Level",
    bachelor: "Bachelor",
    master: "Master",
    phd: "PhD",
    prevInstitution: "Previous Educational Institution",
    gradYear: "Graduation Year",
    gpa: "GPA / Final Grade Average",
    studyLanguage: "Preferred Language of Study",
    uzbek: "Uzbek",
    russian: "Russian",
    english: "English",
    motivation: "Motivation Letter (Optional)",
    motivationPlaceholder: "Briefly describe why you want to study at BSTU and what drives your interest in the chosen program...",
    reviewTitle: "Review Your Application",
    reviewSubtitle: "Please verify all details before submitting.",
    personalSection: "Personal Information",
    academicSection: "Academic Information",
    editBtn: "Edit",
    agree: "I confirm that all information provided is accurate and I agree to the",
    termsLink: "Terms & Conditions",
    submitBtn: "Submit Application",
    submitting: "Submitting...",
    next: "Next Step",
    back: "Back",
    successTitle: "Application Submitted!",
    successMsg: "Thank you for applying to Bukhara State Technical University. We have received your application and will contact you at the email address you provided within 3-5 business days.",
    successRef: "Your reference number:",
    backHome: "Back to Home",
    viewPrograms: "View Programs",
    required: "This field is required",
    invalidEmail: "Please enter a valid email address",
    invalidPhone: "Please enter a valid phone number",
    stepOf: "Step {current} of {total}",
  },
  uz: {
    pageTitle: "BSTUga hujjat topshirish",
    pageSubtitle: "Akademik sayohatingizni boshlang — 3 oddiy qadamda ariza to'ldiring",
    step1Label: "Shaxsiy ma'lumot",
    step2Label: "Ta'lim ma'lumoti",
    step3Label: "Ko'rib chiqish va yuborish",
    firstName: "Ism",
    lastName: "Familiya",
    dateOfBirth: "Tug'ilgan sana",
    nationality: "Fuqaroligi",
    email: "Elektron pochta",
    emailPlaceholder: "siz@example.com",
    phone: "Telefon raqami",
    gender: "Jinsi",
    male: "Erkak",
    female: "Ayol",
    passport: "Pasport / Milliy ID raqami",
    chooseProgram: "Dasturni tanlang",
    degreeLevel: "Ta'lim darajasi",
    bachelor: "Bakalavr",
    master: "Magistr",
    phd: "PhD",
    prevInstitution: "Avvalgi ta'lim muassasasi",
    gradYear: "Bitirish yili",
    gpa: "O'rtacha baho (GPA)",
    studyLanguage: "O'qish tili",
    uzbek: "O'zbek",
    russian: "Rus",
    english: "Ingliz",
    motivation: "Motivatsiya xati (ixtiyoriy)",
    motivationPlaceholder: "BSTUda o'qishni nima uchun xohlashingiz va tanlagan dasturga qiziqishingiz sababini qisqacha tasvirlab bering...",
    reviewTitle: "Arizangizni ko'rib chiqing",
    reviewSubtitle: "Yuborishdan oldin barcha ma'lumotlarni tekshiring.",
    personalSection: "Shaxsiy ma'lumotlar",
    academicSection: "Ta'lim ma'lumotlari",
    editBtn: "Tahrirlash",
    agree: "Barcha taqdim etilgan ma'lumotlar to'g'ri ekanligini tasdiqlaymen va",
    termsLink: "Shartlar va qoidalar",
    submitBtn: "Arizani yuborish",
    submitting: "Yuborilmoqda...",
    next: "Keyingi qadam",
    back: "Orqaga",
    successTitle: "Ariza yuborildi!",
    successMsg: "Buxoro Davlat Texnik Universitetiga arizangiz uchun rahmat. Arizangizni qabul qildik va 3-5 ish kuni ichida siz ko'rsatgan elektron pochta manzilingizga murojaat qilamiz.",
    successRef: "Sizning ma'lumotnoma raqamingiz:",
    backHome: "Bosh sahifaga qaytish",
    viewPrograms: "Dasturlarni ko'rish",
    required: "Bu maydon majburiy",
    invalidEmail: "Iltimos, to'g'ri elektron pochta manzilini kiriting",
    invalidPhone: "Iltimos, to'g'ri telefon raqamini kiriting",
    stepOf: "{current}/{total}-qadam",
  },
  ru: {
    pageTitle: "Подать заявку в БГТУ",
    pageSubtitle: "Начните свой академический путь — заполните заявку в 3 простых шага",
    step1Label: "Личная информация",
    step2Label: "Учебная информация",
    step3Label: "Проверка и отправка",
    firstName: "Имя",
    lastName: "Фамилия",
    dateOfBirth: "Дата рождения",
    nationality: "Гражданство",
    email: "Электронная почта",
    emailPlaceholder: "you@example.com",
    phone: "Номер телефона",
    gender: "Пол",
    male: "Мужской",
    female: "Женский",
    passport: "Паспорт / Номер удостоверения личности",
    chooseProgram: "Выберите программу",
    degreeLevel: "Уровень образования",
    bachelor: "Бакалавриат",
    master: "Магистратура",
    phd: "Докторантура",
    prevInstitution: "Предыдущее учебное заведение",
    gradYear: "Год окончания",
    gpa: "Средний балл (GPA)",
    studyLanguage: "Язык обучения",
    uzbek: "Узбекский",
    russian: "Русский",
    english: "Английский",
    motivation: "Мотивационное письмо (по желанию)",
    motivationPlaceholder: "Кратко опишите, почему вы хотите учиться в БГТУ и что побуждает вас к выбранной программе...",
    reviewTitle: "Проверьте свою заявку",
    reviewSubtitle: "Пожалуйста, проверьте все данные перед отправкой.",
    personalSection: "Личная информация",
    academicSection: "Учебная информация",
    editBtn: "Изменить",
    agree: "Я подтверждаю, что все предоставленные данные достоверны, и соглашаюсь с",
    termsLink: "Правилами и условиями",
    submitBtn: "Отправить заявку",
    submitting: "Отправка...",
    next: "Следующий шаг",
    back: "Назад",
    successTitle: "Заявка подана!",
    successMsg: "Спасибо за вашу заявку в Бухарский государственный технический университет. Мы получили вашу заявку и свяжемся с вами по указанному адресу электронной почты в течение 3-5 рабочих дней.",
    successRef: "Ваш регистрационный номер:",
    backHome: "На главную",
    viewPrograms: "Просмотр программ",
    required: "Это поле обязательно",
    invalidEmail: "Пожалуйста, введите действительный адрес электронной почты",
    invalidPhone: "Пожалуйста, введите действительный номер телефона",
    stepOf: "Шаг {current} из {total}",
  },
  ar: {
    pageTitle: "التقديم في BSTU",
    pageSubtitle: "ابدأ رحلتك الأكاديمية — أكمل طلبك في 3 خطوات بسيطة",
    step1Label: "المعلومات الشخصية",
    step2Label: "المعلومات الأكاديمية",
    step3Label: "المراجعة والإرسال",
    firstName: "الاسم الأول",
    lastName: "الاسم الأخير",
    dateOfBirth: "تاريخ الميلاد",
    nationality: "الجنسية",
    email: "البريد الإلكتروني",
    emailPlaceholder: "you@example.com",
    phone: "رقم الهاتف",
    gender: "الجنس",
    male: "ذكر",
    female: "أنثى",
    passport: "رقم جواز السفر / الهوية الوطنية",
    chooseProgram: "اختر برنامجك",
    degreeLevel: "مستوى الدراسة",
    bachelor: "بكالوريوس",
    master: "ماجستير",
    phd: "دكتوراه",
    prevInstitution: "المؤسسة التعليمية السابقة",
    gradYear: "سنة التخرج",
    gpa: "المعدل التراكمي / متوسط الدرجات",
    studyLanguage: "لغة الدراسة المفضلة",
    uzbek: "الأوزبكية",
    russian: "الروسية",
    english: "الإنجليزية",
    motivation: "خطاب الدوافع (اختياري)",
    motivationPlaceholder: "صف باختصار سبب رغبتك في الدراسة في BSTU وما الذي يحفزك نحو البرنامج المختار...",
    reviewTitle: "راجع طلبك",
    reviewSubtitle: "يرجى التحقق من جميع التفاصيل قبل الإرسال.",
    personalSection: "المعلومات الشخصية",
    academicSection: "المعلومات الأكاديمية",
    editBtn: "تعديل",
    agree: "أؤكد أن جميع المعلومات المقدمة دقيقة وأوافق على",
    termsLink: "الشروط والأحكام",
    submitBtn: "إرسال الطلب",
    submitting: "جارٍ الإرسال...",
    next: "الخطوة التالية",
    back: "رجوع",
    successTitle: "تم إرسال الطلب!",
    successMsg: "شكراً لتقديم طلبك في جامعة بخارى التقنية الحكومية. لقد استلمنا طلبك وسنتواصل معك على عنوان البريد الإلكتروني المقدم خلال 3-5 أيام عمل.",
    successRef: "رقم مرجعك:",
    backHome: "العودة للرئيسية",
    viewPrograms: "عرض البرامج",
    required: "هذا الحقل مطلوب",
    invalidEmail: "يرجى إدخال عنوان بريد إلكتروني صحيح",
    invalidPhone: "يرجى إدخال رقم هاتف صحيح",
    stepOf: "الخطوة {current} من {total}",
  }
};

function generateRefNumber() {
  return "BSTU-" + new Date().getFullYear() + "-" + Math.random().toString(36).toUpperCase().slice(2, 8);
}

// ── Shared form sub-components (defined OUTSIDE render) ───────────────────────
function InputField({ label, id, type = "text", value, onChange, placeholder, required, error, icon: Icon }) {
  return (
    <div className="flex flex-col gap-1.5">
      <label htmlFor={id} className="text-xs font-bold text-navy">
        {label}{required && <span className="text-red-500 ms-1">*</span>}
      </label>
      <div className="relative">
        {Icon && (
          <span className="absolute inset-s-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <Icon className="w-4 h-4" />
          </span>
        )}
        <input
          id={id}
          type={type}
          value={value}
          onChange={onChange}
          placeholder={placeholder}
          className={`w-full bg-white border ${error ? "border-red-400 focus:ring-red-200" : "border-gray-200 focus:ring-primary/20"} rounded-xl px-4 py-2.5 text-sm text-navy placeholder:text-gray-300 outline-none focus:border-primary focus:ring-2 transition-all duration-200 ${Icon ? "ps-10" : ""}`}
        />
      </div>
      {error && <span className="text-xs text-red-500 font-semibold">{error}</span>}
    </div>
  );
}

function SelectField({ label, id, value, onChange, options, required, error, icon: Icon }) {
  return (
    <div className="flex flex-col gap-1.5">
      <label htmlFor={id} className="text-xs font-bold text-navy">
        {label}{required && <span className="text-red-500 ms-1">*</span>}
      </label>
      <div className="relative">
        {Icon && (
          <span className="absolute inset-s-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <Icon className="w-4 h-4" />
          </span>
        )}
        <select
          id={id}
          value={value}
          onChange={onChange}
          className={`w-full bg-white border ${error ? "border-red-400 focus:ring-red-200" : "border-gray-200 focus:ring-primary/20"} rounded-xl px-4 py-2.5 text-sm text-navy outline-none focus:border-primary focus:ring-2 transition-all duration-200 ${Icon ? "ps-10" : ""} appearance-none`}
        >
          {options.map(opt => (
            <option key={opt.value} value={opt.value} disabled={opt.value === ""}>{opt.label}</option>
          ))}
        </select>
      </div>
      {error && <span className="text-xs text-red-500 font-semibold">{error}</span>}
    </div>
  );
}

// ReviewRow is a simple presentational component — defined at module level
function ReviewRow({ label, value }) {
  return (
    <div className="flex justify-between items-start gap-4 py-2.5 border-b border-gray-50 last:border-0">
      <span className="text-xs text-gray-400 font-semibold w-40 shrink-0">{label}</span>
      <span className="text-xs font-bold text-navy text-end break-all">{value || "—"}</span>
    </div>
  );
}

// ── Main Page Component ───────────────────────────────────────────────────────
export default function ApplyPage() {
  const { language } = useLanguage();
  const T = applyT[language] || applyT.en;
  const isRtl = language === "ar";

  const [step, setStep] = useState(1);
  const [dir, setDir] = useState(1);
  const [submitted, setSubmitted] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [refNumber, setRefNumber] = useState("");
  const [agreed, setAgreed] = useState(false);
  const [errors, setErrors] = useState({});
  const [programs, setPrograms] = useState([]);
  const [programsLoading, setProgramsLoading] = useState(true);

  const [form, setForm] = useState({
    firstName: "", lastName: "", dateOfBirth: "", nationality: "",
    email: "", phone: "", gender: "", passport: "",
    program: "", degreeLevel: "bachelor", prevInstitution: "",
    gradYear: "", gpa: "", studyLanguage: "uzbek", motivation: ""
  });

  useEffect(() => { window.scrollTo(0, 0); }, []);

  useEffect(() => {
    let active = true;
    setProgramsLoading(true);

    programService
      .getPrograms()
      .then((items) => {
        if (active) setPrograms(items || []);
      })
      .catch(() => {
        if (active) setPrograms([]);
      })
      .finally(() => {
        if (active) setProgramsLoading(false);
      });

    return () => {
      active = false;
    };
  }, [language]);

  const handleChange = (field) => (e) => {
    setForm(f => ({ ...f, [field]: e.target.value }));
    setErrors(err => ({ ...err, [field]: "" }));
  };

  const validateStep1 = () => {
    const e = {};
    if (!form.firstName.trim()) e.firstName = T.required;
    if (!form.lastName.trim()) e.lastName = T.required;
    if (!form.dateOfBirth) e.dateOfBirth = T.required;
    if (!form.nationality.trim()) e.nationality = T.required;
    if (!form.email.trim()) e.email = T.required;
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) e.email = T.invalidEmail;
    if (!form.phone.trim()) e.phone = T.required;
    else if (!/^[\d\s+\-()]{7,}$/.test(form.phone)) e.phone = T.invalidPhone;
    if (!form.gender) e.gender = T.required;
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const validateStep2 = () => {
    const e = {};
    if (!form.program) e.program = T.required;
    if (!form.prevInstitution.trim()) e.prevInstitution = T.required;
    if (!form.gradYear) e.gradYear = T.required;
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const goNext = () => {
    let valid = false;
    if (step === 1) valid = validateStep1();
    if (step === 2) valid = validateStep2();
    if (step === 3) valid = true;
    if (valid) { setDir(1); setStep(s => s + 1); window.scrollTo(0, 0); }
  };

  const goBack = () => {
    setDir(-1);
    setStep(s => s - 1);
    window.scrollTo(0, 0);
  };

  const handleSubmit = async () => {
    if (!agreed) { setErrors(e => ({ ...e, agreed: T.required })); return; }
    setSubmitting(true);
    await new Promise(r => setTimeout(r, 2000));
    setRefNumber(generateRefNumber());
    setSubmitted(true);
    setSubmitting(false);
    window.scrollTo(0, 0);
  };

  const programOptions = [
    {
      value: "",
      label: programsLoading ? T.submitting : isRtl ? "— اختر برنامجًا —"
        : language === "uz" ? "— Dastur tanlang —"
        : language === "ru" ? "— Выберите программу —"
        : "— Select a Program —"
    },
    ...programs.map(p => ({
      value: p.slug || p.id,
      label: [p.display_code || p.official_code, p.name].filter(Boolean).join(" - ")
    }))
  ];

  const selectedProgram = programs.find(p => (p.slug || p.id) === form.program);
  const selectedProgramName = selectedProgram?.name || "—";

  // ── Success screen ──────────────────────────────────────────────────────────
  if (submitted) {
    return (
      <div className="min-h-screen pt-20 bg-primary-light/30 flex items-center justify-center p-6">
        <motion.div
          initial={{ opacity: 0, scale: 0.92 }}
          animate={{ opacity: 1, scale: 1 }}
          className="bg-white border border-gray-100 rounded-4xl shadow-xl max-w-lg w-full p-10 text-center"
        >
          <div className="w-20 h-20 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center mx-auto mb-6">
            <CheckCircle2 className="w-10 h-10" />
          </div>
          <h1 className="text-2xl font-extrabold text-navy mb-3">{T.successTitle}</h1>
          <p className="text-gray-500 text-sm leading-relaxed mb-6">{T.successMsg}</p>
          <div className="bg-primary/5 border border-primary/20 rounded-2xl px-6 py-4 mb-8">
            <p className="text-xs text-gray-400 font-semibold mb-1">{T.successRef}</p>
            <p className="text-xl font-black text-primary tracking-widest">{refNumber}</p>
          </div>
          <div className="flex flex-col sm:flex-row gap-3 justify-center">
            <Link to="/" className="inline-flex items-center justify-center gap-2 bg-navy text-white px-6 py-3 rounded-xl text-sm font-bold hover:bg-primary transition-colors">
              {T.backHome}
            </Link>
            <Link to="/programs" className="inline-flex items-center justify-center gap-2 border border-gray-200 text-navy px-6 py-3 rounded-xl text-sm font-bold hover:border-primary hover:text-primary transition-colors">
              {T.viewPrograms}
            </Link>
          </div>
        </motion.div>
      </div>
    );
  }

  // ── Main form ───────────────────────────────────────────────────────────────
  return (
    <div className="min-h-screen pt-20 bg-primary-light/30" dir={isRtl ? "rtl" : "ltr"}>
      {/* Hero Header */}
      <div className="bg-navy py-12 px-4">
        <div className="container mx-auto max-w-4xl text-center">
          <div className="inline-flex items-center gap-2 bg-white/10 text-white text-[11px] font-extrabold uppercase tracking-widest px-4 py-1.5 rounded-full mb-4">
            <GraduationCap className="w-4 h-4" />
            {T.pageTitle}
          </div>
          <h1 className="text-2xl md:text-3xl font-extrabold text-white mb-2">{T.pageTitle}</h1>
          <p className="text-white/70 text-sm font-semibold">{T.pageSubtitle}</p>
        </div>
      </div>

      <div className="container mx-auto max-w-3xl px-4 py-12">

        {/* ── Stepper ── */}
        <div className="flex items-center justify-center gap-0 mb-12">
          {STEPS.map((s, i) => (
            <React.Fragment key={s}>
              <div className="flex flex-col items-center">
                <div className={`w-10 h-10 rounded-full flex items-center justify-center font-black text-sm border-2 transition-all duration-300 ${
                  step > s ? "bg-emerald-500 border-emerald-500 text-white" :
                  step === s ? "bg-primary border-primary text-white shadow-lg shadow-primary/30" :
                  "bg-white border-gray-200 text-gray-400"
                }`}>
                  {step > s ? <CheckCircle2 className="w-5 h-5" /> : s}
                </div>
                <span className={`text-[10px] font-extrabold mt-2 max-w-20 text-center ${step === s ? "text-primary" : step > s ? "text-emerald-500" : "text-gray-400"}`}>
                  {s === 1 ? T.step1Label : s === 2 ? T.step2Label : T.step3Label}
                </span>
              </div>
              {i < STEPS.length - 1 && (
                <div className={`h-0.5 flex-1 mx-3 -mt-4.5 transition-all duration-500 ${step > s ? "bg-emerald-400" : "bg-gray-200"}`} />
              )}
            </React.Fragment>
          ))}
        </div>

        {/* ── Step Content ── */}
        <div className="relative overflow-hidden">
          <AnimatePresence mode="wait" custom={dir}>
            <motion.div
              key={step}
              custom={dir}
              variants={stepVariants}
              initial="enter"
              animate="center"
              exit="exit"
              transition={{ duration: 0.28, ease: "easeInOut" }}
            >

              {/* ── STEP 1: Personal Info ── */}
              {step === 1 && (
                <div className="bg-white border border-gray-100 rounded-3xl shadow-sm p-8">
                  <div className="flex items-center gap-3 mb-8">
                    <div className="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                      <User className="w-5 h-5" />
                    </div>
                    <div>
                      <h2 className="text-lg font-extrabold text-navy">{T.step1Label}</h2>
                      <p className="text-xs text-gray-400 font-semibold">{T.stepOf.replace("{current}", "1").replace("{total}", "3")}</p>
                    </div>
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <InputField label={T.firstName} id="firstName" value={form.firstName} onChange={handleChange("firstName")} icon={User} required error={errors.firstName} />
                    <InputField label={T.lastName} id="lastName" value={form.lastName} onChange={handleChange("lastName")} icon={User} required error={errors.lastName} />
                    <InputField label={T.dateOfBirth} id="dateOfBirth" type="date" value={form.dateOfBirth} onChange={handleChange("dateOfBirth")} icon={Calendar} required error={errors.dateOfBirth} />
                    <InputField label={T.nationality} id="nationality" value={form.nationality} onChange={handleChange("nationality")} icon={Globe} required error={errors.nationality}
                      placeholder={language === "ar" ? "مثلاً: أوزبكستاني" : language === "uz" ? "Masalan: O'zbekiston" : language === "ru" ? "Например: Узбекистан" : "e.g. Uzbekistan"} />
                    <InputField label={T.email} id="email" type="email" value={form.email} onChange={handleChange("email")} icon={Mail} required error={errors.email} placeholder={T.emailPlaceholder} />
                    <InputField label={T.phone} id="phone" type="tel" value={form.phone} onChange={handleChange("phone")} icon={Phone} required error={errors.phone} placeholder="+998 __ ___ __ __" />
                    <SelectField
                      label={T.gender} id="gender" value={form.gender} onChange={handleChange("gender")} required error={errors.gender}
                      options={[{ value: "", label: "—" }, { value: "male", label: T.male }, { value: "female", label: T.female }]}
                    />
                    <InputField label={T.passport} id="passport" value={form.passport} onChange={handleChange("passport")} icon={FileText} />
                  </div>
                </div>
              )}

              {/* ── STEP 2: Academic Info ── */}
              {step === 2 && (
                <div className="bg-white border border-gray-100 rounded-3xl shadow-sm p-8">
                  <div className="flex items-center gap-3 mb-8">
                    <div className="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                      <BookOpen className="w-5 h-5" />
                    </div>
                    <div>
                      <h2 className="text-lg font-extrabold text-navy">{T.step2Label}</h2>
                      <p className="text-xs text-gray-400 font-semibold">{T.stepOf.replace("{current}", "2").replace("{total}", "3")}</p>
                    </div>
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div className="sm:col-span-2">
                      <SelectField label={T.chooseProgram} id="program" value={form.program} onChange={handleChange("program")} required error={errors.program}
                        icon={GraduationCap} options={programOptions} />
                    </div>
                    <SelectField label={T.degreeLevel} id="degreeLevel" value={form.degreeLevel} onChange={handleChange("degreeLevel")}
                      options={[
                        { value: "bachelor", label: T.bachelor },
                        { value: "master", label: T.master },
                        { value: "phd", label: T.phd }
                      ]}
                    />
                    <SelectField label={T.studyLanguage} id="studyLanguage" value={form.studyLanguage} onChange={handleChange("studyLanguage")}
                      options={[
                        { value: "uzbek", label: T.uzbek },
                        { value: "russian", label: T.russian },
                        { value: "english", label: T.english }
                      ]}
                    />
                    <InputField label={T.prevInstitution} id="prevInstitution" value={form.prevInstitution} onChange={handleChange("prevInstitution")} icon={BookOpen} required error={errors.prevInstitution} />
                    <InputField label={T.gradYear} id="gradYear" type="number" value={form.gradYear} onChange={handleChange("gradYear")} icon={Calendar} required error={errors.gradYear} placeholder="2024" />
                    <div className="sm:col-span-2">
                      <InputField label={T.gpa} id="gpa" value={form.gpa} onChange={handleChange("gpa")} placeholder="4.5 / 5.0" />
                    </div>
                    <div className="sm:col-span-2 flex flex-col gap-1.5">
                      <label htmlFor="motivation" className="text-xs font-bold text-navy">{T.motivation}</label>
                      <textarea
                        id="motivation"
                        value={form.motivation}
                        onChange={handleChange("motivation")}
                        placeholder={T.motivationPlaceholder}
                        rows={5}
                        className="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm text-navy placeholder:text-gray-300 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all resize-none"
                      />
                    </div>
                  </div>
                </div>
              )}

              {/* ── STEP 3: Review & Submit ── */}
              {step === 3 && (
                <div className="bg-white border border-gray-100 rounded-3xl shadow-sm p-8">
                  <div className="flex items-center gap-3 mb-8">
                    <div className="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                      <Star className="w-5 h-5" />
                    </div>
                    <div>
                      <h2 className="text-lg font-extrabold text-navy">{T.reviewTitle}</h2>
                      <p className="text-xs text-gray-400 font-semibold">{T.reviewSubtitle}</p>
                    </div>
                  </div>

                  <div className="space-y-6">
                    {/* Personal review */}
                    <div className="bg-gray-50/60 border border-gray-100 rounded-2xl p-6">
                      <div className="flex items-center justify-between mb-4">
                        <h3 className="text-sm font-extrabold text-navy flex items-center gap-2">
                          <User className="w-4 h-4 text-primary" /> {T.personalSection}
                        </h3>
                        <button onClick={() => { setDir(-1); setStep(1); }} className="text-xs text-primary font-bold hover:underline">{T.editBtn}</button>
                      </div>
                      <ReviewRow label={T.firstName} value={form.firstName} />
                      <ReviewRow label={T.lastName} value={form.lastName} />
                      <ReviewRow label={T.dateOfBirth} value={form.dateOfBirth} />
                      <ReviewRow label={T.nationality} value={form.nationality} />
                      <ReviewRow label={T.email} value={form.email} />
                      <ReviewRow label={T.phone} value={form.phone} />
                      <ReviewRow label={T.gender} value={form.gender === "male" ? T.male : form.gender === "female" ? T.female : "—"} />
                      {form.passport && <ReviewRow label={T.passport} value={form.passport} />}
                    </div>

                    {/* Academic review */}
                    <div className="bg-gray-50/60 border border-gray-100 rounded-2xl p-6">
                      <div className="flex items-center justify-between mb-4">
                        <h3 className="text-sm font-extrabold text-navy flex items-center gap-2">
                          <GraduationCap className="w-4 h-4 text-primary" /> {T.academicSection}
                        </h3>
                        <button onClick={() => { setDir(-1); setStep(2); }} className="text-xs text-primary font-bold hover:underline">{T.editBtn}</button>
                      </div>
                      <ReviewRow label={T.chooseProgram} value={selectedProgramName} />
                      <ReviewRow label={T.degreeLevel} value={form.degreeLevel === "bachelor" ? T.bachelor : form.degreeLevel === "master" ? T.master : T.phd} />
                      <ReviewRow label={T.studyLanguage} value={form.studyLanguage === "uzbek" ? T.uzbek : form.studyLanguage === "russian" ? T.russian : T.english} />
                      <ReviewRow label={T.prevInstitution} value={form.prevInstitution} />
                      <ReviewRow label={T.gradYear} value={form.gradYear} />
                      {form.gpa && <ReviewRow label={T.gpa} value={form.gpa} />}
                      {form.motivation && (
                        <ReviewRow
                          label={T.motivation}
                          value={form.motivation.slice(0, 120) + (form.motivation.length > 120 ? "..." : "")}
                        />
                      )}
                    </div>

                    {/* Agreement */}
                    <div className={`flex items-start gap-3 p-4 border rounded-2xl ${errors.agreed ? "border-red-300 bg-red-50/50" : "border-gray-200 bg-gray-50/40"}`}>
                      <input
                        id="agreed"
                        type="checkbox"
                        checked={agreed}
                        onChange={e => { setAgreed(e.target.checked); setErrors(er => ({ ...er, agreed: "" })); }}
                        className="mt-0.5 w-4 h-4 accent-primary cursor-pointer"
                      />
                      <label htmlFor="agreed" className="text-xs text-gray-500 leading-relaxed font-semibold cursor-pointer">
                        {T.agree}{" "}
                        <Link to="/contact" className="text-primary underline font-bold">{T.termsLink}</Link>.
                      </label>
                    </div>
                    {errors.agreed && <p className="text-xs text-red-500 font-semibold -mt-3">{T.required}</p>}
                  </div>
                </div>
              )}

            </motion.div>
          </AnimatePresence>
        </div>

        {/* ── Navigation Buttons ── */}
        <div className={`flex justify-between items-center mt-8 ${isRtl ? "flex-row-reverse" : ""}`}>
          {step > 1 ? (
            <button
              onClick={goBack}
              className="inline-flex items-center gap-2 border border-gray-200 text-navy px-5 py-2.5 rounded-xl text-sm font-bold hover:border-primary hover:text-primary transition-all"
            >
              <ChevronLeft className={`w-4 h-4 ${isRtl ? "rotate-180" : ""}`} />
              {T.back}
            </button>
          ) : (
            <Link to="/programs" className="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-primary font-bold transition-colors">
              <ArrowLeft className={`w-4 h-4 ${isRtl ? "rotate-180" : ""}`} />
              {language === "ar" ? "العودة" : language === "uz" ? "Orqaga" : language === "ru" ? "Назад" : "Back"}
            </Link>
          )}

          {step < 3 ? (
            <button
              onClick={goNext}
              className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-7 py-2.5 rounded-xl text-sm font-extrabold shadow-md shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 transition-all"
            >
              {T.next}
              <ChevronRight className={`w-4 h-4 ${isRtl ? "rotate-180" : ""}`} />
            </button>
          ) : (
            <button
              onClick={handleSubmit}
              disabled={submitting}
              className="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-7 py-2.5 rounded-xl text-sm font-extrabold shadow-md shadow-emerald-500/20 hover:-translate-y-0.5 transition-all disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none"
            >
              {submitting ? <Loader2 className="w-4 h-4 animate-spin" /> : <Shield className="w-4 h-4" />}
              {submitting ? T.submitting : T.submitBtn}
            </button>
          )}
        </div>

        {/* Security note */}
        <p className="text-center text-[11px] text-gray-400 font-semibold mt-6 flex items-center justify-center gap-1.5">
          <Shield className="w-3.5 h-3.5" />
          {language === "ar" ? "معلوماتك آمنة ومحمية بالتشفير الكامل"
            : language === "uz" ? "Ma'lumotlaringiz xavfsiz va to'liq shifrlangan"
            : language === "ru" ? "Ваши данные защищены и полностью зашифрованы"
            : "Your information is secure and fully encrypted"}
        </p>
      </div>
    </div>
  );
}
