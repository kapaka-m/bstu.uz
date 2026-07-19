import React, { useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useLanguage } from "../context/LanguageContext";
import { Mail, Phone, Clock, Award, Briefcase, FileText, CheckCircle, GraduationCap } from "lucide-react";
import { administrationData } from "../data/universityData";
import { facultiesData } from "../data/mockData";
import { departmentsData, convertToSlug } from "../data/departmentsData";

export default function ProfileDetails() {
  const { id } = useParams();
  const { t } = useLanguage();
  const navigate = useNavigate();

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [id]);

  // Find the person
  let person = null;
  let category = "";

  // 1. Search in Administration (Rector, Vice Rectors)
  const adminEntry = Object.entries(administrationData).find(([key, val]) => {
    return key === id || convertToSlug(val.name) === id;
  });
  if (adminEntry) {
    const [adminKey, val] = adminEntry;
    const adminContent = t(`administration.${adminKey}`, null);
    person = {
      name: val.name,
      title: adminContent?.title || val.title,
      degree: adminContent?.degree || val.degree,
      image: val.image,
      email: val.email,
      phone: val.phone,
      officeHours: adminContent?.officeHours || val.officeHours,
      about: adminContent?.about || val.about,
      details: adminContent?.details || val.details,
      achievements: adminContent?.achievements || val.achievements,
      slug: convertToSlug(val.name)
    };
    category = t("common.administration", t("nav.administration", "Administration"));
  }

  // 2. Search in Faculty Deans / Leadership
  if (!person) {
    for (const faculty of facultiesData) {
      if (faculty.leadership) {
        const leadIndex = faculty.leadership.findIndex(l => convertToSlug(l.name) === id);
        const lead = leadIndex >= 0 ? faculty.leadership[leadIndex] : null;
        if (lead) {
          const localizedRole = t(`faculties.${faculty.id}.leadership.${leadIndex}.role`, lead.role);
          person = {
            name: lead.name,
            title: localizedRole,
            degree: localizedRole,
            image: lead.image,
            email: lead.email,
            phone: lead.phone,
            officeHours: t(`faculties.${faculty.id}.leadership.${leadIndex}.officeHours`, lead.officeHours),
            about: t("common.profileFacultyLeadershipAbout", "A member of the faculty leadership team at Bukhara State Technical University, responsible for academic, student, and administrative coordination."),
            slug: convertToSlug(lead.name)
          };
          category = t("common.facultyLeadership", "Faculty Leadership");
          break;
        }
      }
    }
  }

  // 3. Search in Department Heads
  if (!person) {
    for (const [deptSlug, dept] of Object.entries(departmentsData)) {
      if (dept.head && convertToSlug(dept.head) === id) {
        person = {
          name: dept.head,
          title: t(`departments.${deptSlug}.headTitle`, dept.headTitle),
          degree: t("common.headOfDepartment", "Head of Department"),
          image: dept.headImage || dept.image,
          email: dept.headEmail,
          phone: dept.headPhone,
          officeHours: t(`departments.${deptSlug}.headOfficeHours`, dept.headOfficeHours),
            about: t("common.profileDepartmentHeadAbout", "Directly leads the department, organizes the academic staff workflow, and is responsible for implementing the department's assigned tasks according to university regulations."),
          slug: convertToSlug(dept.head)
        };
        category = t("common.headOfDepartment", "Head of Department");
        break;
      }
    }
  }

  // 4. Search in Department Staff
  if (!person) {
    for (const [deptSlug, dept] of Object.entries(departmentsData)) {
      if (dept.staff) {
        const memberIndex = dept.staff.findIndex(s => convertToSlug(s.name) === id);
        const member = memberIndex >= 0 ? dept.staff[memberIndex] : null;
        if (member) {
          person = {
            name: member.name,
            title: t(`departments.${deptSlug}.staff.${memberIndex}.title`, member.title),
            degree: t(`departments.${deptSlug}.staff.${memberIndex}.title`, member.title),
            image: member.image,
            email: `${convertToSlug(member.name)}@bstu.uz`,
            phone: dept.headPhone,
            officeHours: t(`departments.${deptSlug}.headOfficeHours`, dept.headOfficeHours),
            about: t("common.profileAcademicStaffAbout", "A member of the academic staff at Bukhara State Technical University, actively involved in teaching, student mentorship, and scientific research."),
            slug: convertToSlug(member.name)
          };
          category = t("common.academicStaff", "Academic Staff");
          break;
        }
      }
    }
  }

  if (!person) {
    return (
      <div className="pt-24 min-h-[70vh] bg-white flex flex-col items-center justify-center p-6 text-center">
        <h2 className="text-2xl font-extrabold text-navy mb-4">{t("common.notFound")}</h2>
        <button onClick={() => navigate(-1)} className="bg-primary text-white px-6 py-2.5 rounded-xl font-bold text-sm">
          {t("common.goBack", "Go Back")}
        </button>
      </div>
    );
  }

  const getInitials = (name) => {
    const parts = name.replace(/^(Dr\.|Prof\.)\s+/i, "").trim().split(/\s+/);
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[1][0]).toUpperCase();
  };

  return (
    <div className="pt-24 bg-white min-h-screen text-start">
      <div className="container mx-auto px-4 md:px-8 max-w-6xl py-12">

        {/* Main Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          {/* Left Column: Portrait Card */}
          <div className="lg:col-span-4 bg-gray-50 border border-gray-100 rounded-3xl p-6 text-center shadow-sm flex flex-col items-center gap-6">
            {person.image ? (
              <img
                src={person.image}
                alt={person.name}
                className="w-48 h-48 rounded-2xl object-cover border-4 border-white shadow-md"
                onError={(e) => {
                  e.target.onerror = null;
                  e.target.style.display = "none";
                  e.target.nextSibling.style.display = "flex";
                }}
              />
            ) : null}
            <div
              className="w-48 h-48 rounded-2xl bg-linear-to-br from-primary to-primary-hover text-white shadow-md flex items-center justify-center font-extrabold text-4xl border-4 border-white"
              style={{ display: person.image ? "none" : "flex" }}
            >
              {getInitials(person.name)}
            </div>

            <div className="flex flex-col gap-2">
              <span className="inline-block text-[10px] font-extrabold uppercase tracking-wider text-primary bg-primary/10 px-3 py-1 rounded-full w-fit mx-auto">
                {category}
              </span>
              <h1 className="text-xl font-extrabold text-navy leading-snug mt-2">{person.name}</h1>
              <p className="text-xs font-semibold text-gray-500">{person.title}</p>
            </div>

            {/* Quick Contact Info */}
            <div className="w-full border-t border-gray-200/65 pt-6 flex flex-col gap-4 text-xs font-semibold text-gray-600 text-start">
              <div className="flex items-center gap-3">
                <Mail className="w-5 h-5 text-primary shrink-0" />
                <div>
                  <p className="text-[10px] text-gray-400 font-extrabold uppercase tracking-wider">{t("common.email", "Email")}</p>
                  <p className="text-navy break-all font-bold">{person.email}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <Phone className="w-5 h-5 text-primary shrink-0" />
                <div>
                  <p className="text-[10px] text-gray-400 font-extrabold uppercase tracking-wider">{t("common.phone", "Phone")}</p>
                  <p className="text-navy font-bold">{person.phone}</p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <Clock className="w-5 h-5 text-primary shrink-0" />
                <div>
                  <p className="text-[10px] text-gray-400 font-extrabold uppercase tracking-wider">{t("common.officeHours", "Office Hours")}</p>
                  <p className="text-navy font-bold">{person.officeHours}</p>
                </div>
              </div>
            </div>
          </div>

          {/* Right Column: Bio & Professional details */}
          <div className="lg:col-span-8 flex flex-col gap-8">
            {/* Degree / Academic Rank banner */}
            {person.degree && (
              <div className="bg-primary/5 border border-primary/10 rounded-2xl p-4 flex items-center gap-3">
                <GraduationCap className="w-6 h-6 text-primary shrink-0" />
                <div>
                  <h4 className="text-xs font-extrabold text-navy uppercase tracking-wider">{t("common.academicRank", "Academic Rank & Position")}</h4>
                  <p className="text-sm font-bold text-primary leading-snug">{person.degree}</p>
                </div>
              </div>
            )}

            {/* About / Bio */}
            <section className="flex flex-col gap-4">
              <h3 className="text-lg font-extrabold text-navy flex items-center gap-2 border-b border-gray-100 pb-2">
                <Briefcase className="w-5 h-5 text-primary shrink-0" />
                {t("common.biography", "Professional Biography")}
              </h3>
              <p className="text-gray-500 text-sm md:text-base leading-relaxed whitespace-pre-line">
                {person.about}
              </p>
            </section>

            {/* Detailed Duties / Responsibilities (Rectorate only) */}
            {person.details && (
              <section className="flex flex-col gap-4">
                <h3 className="text-lg font-extrabold text-navy flex items-center gap-2 border-b border-gray-100 pb-2">
                  <FileText className="w-5 h-5 text-primary shrink-0" />
                  {t("common.dutiesResponsibilities", "Duties & Responsibilities")}
                </h3>
                <div className="grid grid-cols-1 gap-3">
                  {person.details.split(/[;؛]/).map((duty, idx) => {
                    const cleanDuty = duty.trim();
                    if (!cleanDuty) return null;
                    return (
                      <div key={idx} className="flex gap-3 items-start bg-gray-50 p-4 border border-gray-100 rounded-2xl">
                        <CheckCircle className="w-5 h-5 text-primary shrink-0 mt-0.5" />
                        <p className="text-xs text-navy font-semibold leading-relaxed">{cleanDuty}</p>
                      </div>
                    );
                  })}
                </div>
              </section>
            )}

            {/* Achievements (Rectorate only) */}
            {person.achievements && person.achievements.length > 0 && (
              <section className="flex flex-col gap-4">
                <h3 className="text-lg font-extrabold text-navy flex items-center gap-2 border-b border-gray-100 pb-2">
                  <Award className="w-5 h-5 text-primary shrink-0" />
                  {t("common.keyAchievements", "Key Achievements & Milestones")}
                </h3>
                <ul className="flex flex-col gap-3">
                  {person.achievements.map((ach, idx) => (
                    <li key={idx} className="flex items-start gap-3 text-sm text-gray-500 font-semibold leading-relaxed">
                      <div className="w-2 h-2 rounded-full bg-primary mt-2 shrink-0" />
                      <span>{ach}</span>
                    </li>
                  ))}
                </ul>
              </section>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}