import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { apanelService } from "../../../services/apanelService";
import { publicAssetUrl } from "../../../lib/api";
import SearchFilterBar from "../components/SearchFilterBar";
import DataTable from "../components/DataTable";
import ApanelStatsCards from "../components/ApanelStatsCards";
import Pagination from "../components/Pagination";
import FormBuilder from "../components/FormBuilder";
import ConfirmDialog from "../components/ConfirmDialog";
import {
  BookOpen,
  Building2,
  CheckCircle2,
  Clock,
  ExternalLink,
  GraduationCap,
  Hash,
  Loader2,
  Mail,
  MessageCircle,
  Pencil,
  Phone,
  Plus,
  Reply,
  ShieldAlert,
  Trash2,
  UsersRound,
  X,
} from "lucide-react";
import FormError from "../../../components/common/FormError";
import { useLanguage } from "../../../context/LanguageContext";

// Config schemas for all whitelisted resources
const RESOURCE_SCHEMAS = {
  locales: {
    title: "apanel.crud.ui.title.locales",
    columns: [
      { key: "code", label: "apanel.crud.ui.label.code", sortable: true },
      { key: "name", label: "apanel.crud.ui.label.name", sortable: true },
      { key: "native_name", label: "apanel.crud.ui.label.nativeName" },
      { key: "direction", label: "apanel.crud.ui.label.direction" },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
    ],
    fields: [
      { name: "code", label: "apanel.crud.ui.label.localeCode", type: "text", required: true },
      { name: "name", label: "apanel.crud.ui.label.name", type: "text", required: true },
      {
        name: "native_name",
        label: "apanel.crud.ui.label.nativeName",
        type: "text",
        required: true,
      },
      {
        name: "direction",
        label: "apanel.crud.ui.label.direction",
        type: "select",
        options: ["ltr", "rtl"],
        required: true,
      },
      { name: "is_active", label: "apanel.crud.ui.label.activeStatus", type: "boolean" },
      { name: "sort_order", label: "apanel.crud.ui.label.sortOrder", type: "number" },
    ],
  },
  "translation-keys": {
    title: "apanel.crud.ui.title.translationKeys",
    columns: [
      { key: "group", label: "apanel.crud.ui.label.group", sortable: true },
      { key: "key", label: "apanel.crud.ui.label.keyName", sortable: true },
      { key: "description", label: "apanel.crud.ui.label.description" },
      { key: "is_system", label: "apanel.crud.ui.label.systemKey", type: "boolean" },
    ],
    fields: [
      { name: "group", label: "apanel.crud.ui.label.groupName", type: "text", required: true },
      { name: "key", label: "apanel.crud.ui.label.keySlug", type: "text", required: true },
      { name: "description", label: "apanel.crud.ui.label.contextDescription", type: "textarea" },
      { name: "is_system", label: "apanel.crud.ui.label.systemCoreKey", type: "boolean" },
    ],
  },
  "translation-values": {
    title: "apanel.crud.ui.title.translationValues",
    columns: [
      { key: "translation_key_id", label: "apanel.crud.ui.label.keyId", sortable: true },
      { key: "locale", label: "apanel.crud.ui.label.locale", sortable: true },
      { key: "value", label: "apanel.crud.ui.label.textValue" },
    ],
    fields: [
      {
        name: "translation_key_id",
        label: "apanel.crud.ui.label.keyReferenceId",
        type: "number",
        required: true,
      },
      {
        name: "locale",
        label: "apanel.crud.ui.label.localeCode",
        type: "select",
        options: "__active_locales__",
        required: true,
      },
      { name: "value", label: "apanel.crud.ui.label.valueLabel", type: "textarea", required: true },
    ],
  },
  settings: {
    title: "apanel.crud.ui.title.globalSettings",
    columns: [
      { key: "key", label: "apanel.crud.ui.label.key", sortable: true },
      { key: "value", label: "apanel.crud.ui.label.value" },
      { key: "group", label: "apanel.crud.ui.label.group", sortable: true },
      { key: "is_public", label: "apanel.crud.ui.label.publicWebsite", type: "boolean" },
    ],
    fields: [
      { name: "key", label: "apanel.crud.ui.label.key", type: "text", required: true },
      { name: "value", label: "apanel.crud.ui.label.value", type: "setting-value" },
      { name: "group", label: "apanel.crud.ui.label.settingsGroup", type: "text" },
      { name: "type", label: "apanel.crud.ui.label.valueType", type: "text" },
      { name: "is_public", label: "apanel.crud.ui.label.exposeOnPublicSite", type: "boolean" },
    ],
  },
  countries: {
    title: "apanel.crud.ui.title.applicationCountries",
    columns: [
      { key: "name", label: "apanel.crud.ui.label.country", sortable: true },
      { key: "code", label: "apanel.crud.ui.label.code", sortable: true },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
      { key: "sort_order", label: "apanel.crud.ui.label.sort", sortable: true },
    ],
    fields: [
      { name: "name", label: "apanel.crud.ui.label.countryName", type: "text", required: true },
      { name: "code", label: "apanel.crud.ui.label.isoShortCode", type: "text" },
      { name: "is_active", label: "apanel.crud.ui.label.availableInApplyForm", type: "boolean" },
      { name: "sort_order", label: "apanel.crud.ui.label.sortOrder", type: "number" },
    ],
  },
  nationalities: {
    title: "apanel.crud.ui.title.applicationNationalities",
    columns: [
      { key: "name", label: "apanel.crud.ui.label.nationality", sortable: true },
      { key: "country_name", label: "apanel.crud.ui.label.country", sortable: true },
      { key: "code", label: "apanel.crud.ui.label.code", sortable: true },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
      { key: "sort_order", label: "apanel.crud.ui.label.sort", sortable: true },
    ],
    fields: [
      { name: "name", label: "apanel.crud.ui.label.nationalityName", type: "text", required: true },
      { name: "country_name", label: "apanel.crud.ui.label.relatedCountry", type: "text" },
      { name: "code", label: "apanel.crud.ui.label.isoShortCode", type: "text" },
      { name: "is_active", label: "apanel.crud.ui.label.availableInApplyForm", type: "boolean" },
      { name: "sort_order", label: "apanel.crud.ui.label.sortOrder", type: "number" },
    ],
  },
  menus: {
    title: "apanel.crud.ui.title.navigationMenus",
    columns: [
      { key: "key", label: "apanel.crud.ui.label.key", sortable: true },
      { key: "location", label: "apanel.crud.ui.label.position", sortable: true },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
    ],
    fields: [
      { name: "key", label: "apanel.crud.ui.label.keyCode", type: "text", required: true },
      {
        name: "location",
        label: "apanel.crud.ui.label.themeLocation",
        type: "text",
        required: true,
      },
      { name: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
    ],
  },
  "menu-items": {
    title: "apanel.crud.ui.title.menuItems",
    columns: [
      { key: "menu_id", label: "apanel.crud.ui.label.menuId", sortable: true },
      { key: "route_name", label: "apanel.crud.ui.label.routeLink", sortable: true },
      { key: "sort_order", label: "apanel.crud.ui.label.sort", sortable: true },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
    ],
    fields: [
      {
        name: "menu_id",
        label: "apanel.crud.ui.label.parentMenuId",
        type: "number",
        required: true,
      },
      { name: "parent_id", label: "apanel.crud.ui.label.subMenuParentId", type: "number" },
      { name: "route_name", label: "apanel.crud.ui.label.reactRouterPath", type: "text" },
      { name: "url", label: "apanel.crud.ui.label.externalUrlLink", type: "text" },
      { name: "icon", label: "apanel.crud.ui.label.lucideIconName", type: "text" },
      { name: "sort_order", label: "apanel.crud.ui.label.sortIndex", type: "number" },
      { name: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
      {
        name: "title",
        label: "apanel.crud.ui.label.navLabelTitle",
        type: "text",
        required: true,
        translated: true,
      },
    ],
  },
  faculties: {
    title: "apanel.crud.ui.title.faculties",
    columns: [
      { key: "translations.0.name", label: "apanel.crud.ui.label.name" },
      { key: "slug", label: "apanel.crud.ui.label.slug", sortable: true },
      { key: "code", label: "apanel.crud.ui.label.code", sortable: true },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
    ],
    fields: [
      { name: "slug", label: "apanel.crud.ui.label.slugUrl", type: "text", required: true },
      { name: "code", label: "apanel.crud.ui.label.facultyCode", type: "text" },
      { name: "icon", label: "apanel.crud.ui.label.lucideIconCode", type: "text" },
      { name: "sort_order", label: "apanel.crud.ui.label.sortIndex", type: "number" },
      { name: "is_active", label: "apanel.crud.ui.label.activeStatus", type: "boolean" },
      {
        name: "name",
        label: "apanel.crud.ui.label.facultyName",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "description",
        label: "apanel.crud.ui.label.aboutOverview",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "content_sections",
        label: "apanel.crud.ui.label.richContentSectionsJson",
        type: "json",
        translated: true,
      },
    ],
  },
  departments: {
    title: "apanel.crud.ui.title.departments",
    columns: [
      { key: "translations.0.name", label: "apanel.crud.ui.label.name" },
      { key: "faculty_id", label: "apanel.crud.ui.label.facultyId", sortable: true },
      { key: "slug", label: "apanel.crud.ui.label.slug", sortable: true },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
    ],
    fields: [
      {
        name: "faculty_id",
        label: "apanel.crud.ui.label.parentFacultyId",
        type: "number",
        required: true,
      },
      { name: "slug", label: "apanel.crud.ui.label.slugUrl", type: "text", required: true },
      { name: "code", label: "apanel.crud.ui.label.departmentCode", type: "text" },
      { name: "image", label: "apanel.crud.ui.label.featuredImage", type: "media" },
      { name: "icon", label: "apanel.crud.ui.label.lucideIconCode", type: "text" },
      { name: "head_name", label: "apanel.crud.ui.label.departmentHeadName", type: "text" },
      { name: "email", label: "apanel.crud.ui.label.departmentContactEmail", type: "email" },
      { name: "phone", label: "apanel.crud.ui.label.departmentContactPhone", type: "text" },
      {
        name: "reception_time",
        label: "apanel.crud.ui.label.receptionTime",
        type: "text",
      },
      { name: "source_url", label: "apanel.crud.ui.label.verificationSourceUrl", type: "text" },
      { name: "sort_order", label: "apanel.crud.ui.label.sortIndex", type: "number" },
      { name: "is_active", label: "apanel.crud.ui.label.activeStatus", type: "boolean" },
      {
        name: "name",
        label: "apanel.crud.ui.label.departmentName",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "description",
        label: "apanel.crud.ui.label.curriculumIntro",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "content_sections",
        label: "apanel.crud.ui.label.richContentSectionsJson",
        type: "json",
        translated: true,
      },
    ],
  },
  programs: {
    title: "apanel.crud.ui.title.studyPrograms",
    columns: [
      { key: "translations.0.name", label: "apanel.crud.ui.label.name" },
      { key: "department_id", label: "apanel.crud.ui.label.deptId", sortable: true },
      { key: "degree", label: "apanel.crud.ui.label.degree", sortable: true },
      { key: "duration_years", label: "apanel.crud.ui.label.years", sortable: true },
      { key: "show_on_homepage", label: "Home", type: "boolean" },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
    ],
    fields: [
      {
        name: "faculty_id",
        label: "apanel.crud.ui.label.facultyId",
        type: "number",
        required: true,
      },
      {
        name: "department_id",
        label: "apanel.crud.ui.label.departmentId",
        type: "number",
        required: true,
      },
      { name: "slug", label: "apanel.crud.ui.label.slugUrl", type: "text", required: true },
      { name: "code", label: "apanel.crud.ui.label.programCode", type: "text" },
      { name: "official_code", label: "apanel.crud.ui.label.officialPublicCode", type: "text" },
      { name: "track", label: "apanel.crud.ui.label.specializationTrack", type: "text" },
      {
        name: "degree",
        label: "apanel.crud.ui.label.degreeLevel",
        type: "select",
        options: ["bachelor", "master", "phd"],
        required: true,
      },
      {
        name: "duration_years",
        label: "apanel.crud.ui.label.durationInYears",
        type: "number",
        required: true,
      },
      {
        name: "study_mode",
        label: "apanel.crud.ui.label.studyMode",
        type: "checkbox-group",
        preserveValues: true,
        options: [
          { value: "full_time", label: "Full-time" },
          { value: "part_time", label: "Part-time" },
          { value: "distance", label: "Distance" },
        ],
        required: true,
      },
      {
        name: "language_of_study",
        label: "apanel.crud.ui.label.languageOfStudy",
        type: "checkbox-group",
        options: [
          { value: "english", label: "apanel.crud.ui.label.english" },
          { value: "uzbek", label: "apanel.crud.ui.label.uzbek" },
          { value: "russian", label: "apanel.crud.ui.label.russian" },
          { value: "arabic", label: "apanel.crud.ui.label.arabic" }
        ],
        required: true,
      },
      {
        name: "tuition_fee",
        label: "apanel.crud.ui.label.tuitionFeeCost",
        type: "number",
        required: true,
      },
      { name: "currency", label: "apanel.crud.ui.label.isoCurrency", type: "text" },
      { name: "image", label: "apanel.crud.ui.label.featuredImageBanner", type: "media" },
      { name: "is_active", label: "apanel.crud.ui.label.activeStatus", type: "boolean" },
      { name: "show_on_homepage", label: "Show on homepage programs section", type: "boolean" },
      { name: "homepage_sort_order", label: "Homepage display order", type: "number" },
      { name: "sort_order", label: "apanel.crud.ui.label.sortIndex", type: "number" },
      {
        name: "name",
        label: "apanel.crud.ui.label.programName",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "description",
        label: "Program Overview Details",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "requirements",
        label: "Admission Requirements Checklist",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "documents",
        label: "Required Documents Checklist",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "curriculum_summary",
        label: "Course Curriculum Summary",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "career_opportunities",
        label: "Career Opportunities",
        type: "textarea",
        required: true,
        translated: true,
      },
    ],
  },
  courses: {
    title: "apanel.crud.ui.title.courses",
    columns: [
      { key: "translations.0.name", label: "apanel.crud.ui.label.courseName" },
      { key: "code", label: "apanel.crud.ui.label.code", sortable: true },
      { key: "credits", label: "apanel.crud.ui.label.credits", sortable: true },
      { key: "semester", label: "apanel.crud.ui.label.semester", sortable: true },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
    ],
    fields: [
      { name: "code", label: "apanel.crud.ui.label.courseCode", type: "text", required: true },
      {
        name: "credits",
        label: "apanel.crud.ui.label.ectsCreditsCount",
        type: "number",
        required: true,
      },
      {
        name: "semester",
        label: "apanel.crud.ui.label.activeSemesterYear",
        type: "number",
        required: true,
      },
      { name: "is_active", label: "apanel.crud.ui.label.activeStatus", type: "boolean" },
      {
        name: "name",
        label: "apanel.crud.ui.label.courseSubjectTitle",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "description",
        label: "apanel.crud.ui.label.courseDescription",
        type: "textarea",
        required: true,
        translated: true,
      },
    ],
  },
  staff: {
    title: "apanel.crud.ui.title.staffAndAcademicsProfiles",
    columns: [
      { key: "translations.0.full_name", label: "apanel.crud.ui.label.fullName" },
      { key: "translations.0.position", label: "apanel.crud.ui.label.position" },
      { key: "email", label: "apanel.crud.ui.label.emailAddress", sortable: true },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
    ],
    fields: [
      { name: "slug", label: "apanel.crud.ui.label.publicProfileSlug", type: "text" },
      { name: "faculty_id", label: "apanel.crud.ui.label.associatedFacultyId", type: "number" },
      {
        name: "department_id",
        label: "apanel.crud.ui.label.associatedDepartmentId",
        type: "number",
      },
      {
        name: "secondary_department_ids",
        label: "apanel.crud.ui.label.additionalDepartments",
        type: "checkbox-group",
        options: "__staff_departments__",
      },
      { name: "photo", label: "apanel.crud.ui.label.staffPortraitImage", type: "media" },
      { name: "email", label: "apanel.crud.ui.label.emailAddress", type: "email" },
      { name: "phone", label: "apanel.crud.ui.label.mobilePhoneNumber", type: "text" },
      { name: "sort_order", label: "apanel.crud.ui.label.sortRankIndex", type: "number" },
      { name: "is_active", label: "apanel.crud.ui.label.activeProfile", type: "boolean" },
      {
        name: "full_name",
        label: "apanel.crud.ui.label.staffFullName",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "position",
        label: "apanel.crud.ui.label.jobAcademicTitle",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "bio",
        label: "apanel.crud.ui.label.biographicalOverview",
        type: "textarea",
        required: true,
        translated: true,
      },
    ],
  },
  services: {
    title: "apanel.crud.ui.title.campusServices",
    columns: [
      { key: "slug", label: "apanel.crud.ui.label.slugUrl", sortable: true },
      { key: "is_active", label: "apanel.crud.ui.label.activeStatus", type: "boolean" },
    ],
    fields: [
      { name: "slug", label: "apanel.crud.ui.label.slugUrlLink", type: "text", required: true },
      { name: "icon", label: "apanel.crud.ui.label.lucideIconCode", type: "text" },
      { name: "image", label: "apanel.crud.ui.label.coverPictureBanner", type: "media" },
      { name: "sort_order", label: "apanel.crud.ui.label.sortIndex", type: "number" },
      { name: "is_active", label: "apanel.crud.ui.label.activeStatus", type: "boolean" },
      {
        name: "title",
        label: "apanel.crud.ui.label.serviceTitleHeader",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "description",
        label: "apanel.crud.ui.label.serviceDetailsBody",
        type: "textarea",
        required: true,
        translated: true,
      },
    ],
  },
  users: {
    title: "apanel.crud.ui.title.systemUsers",
    columns: [
      { key: "id", label: "apanel.crud.ui.label.id", sortable: true },
      { key: "name", label: "apanel.crud.ui.label.name", sortable: true },
      { key: "email", label: "apanel.crud.ui.label.emailAddress", sortable: true },
    ],
    fields: [
      {
        name: "name",
        label: "apanel.crud.ui.label.displayFullName",
        type: "text",
        required: true,
      },
      {
        name: "email",
        label: "apanel.crud.ui.label.loginEmailAddress",
        type: "email",
        required: true,
      },
      {
        name: "password",
        label: "apanel.crud.ui.label.accountPassword",
        type: "password",
        required: false,
      },
    ],
  },
  roles: {
    title: "apanel.crud.ui.title.systemRoles",
    columns: [
      { key: "name", label: "apanel.crud.ui.label.name", sortable: true },
      { key: "slug", label: "apanel.crud.ui.label.roleCodeSlug", sortable: true },
      { key: "description", label: "apanel.crud.ui.label.roleScopeDetails" },
    ],
    fields: [
      {
        name: "name",
        label: "apanel.crud.ui.label.roleDisplayLabel",
        type: "text",
        required: true,
      },
      { name: "slug", label: "apanel.crud.ui.label.roleCodeKey", type: "text", required: true },
      { name: "description", label: "apanel.crud.ui.label.roleScopeContext", type: "textarea" },
    ],
  },
  permissions: {
    title: "apanel.crud.ui.title.securityPermissions",
    columns: [
      { key: "name", label: "apanel.crud.ui.label.name", sortable: true },
      { key: "slug", label: "apanel.crud.ui.label.permissionSlug", sortable: true },
      { key: "description", label: "apanel.crud.ui.label.accessDescription" },
    ],
    fields: [
      { name: "name", label: "apanel.crud.ui.label.permissionLabel", type: "text", required: true },
      {
        name: "slug",
        label: "apanel.crud.ui.label.permissionCodeKey",
        type: "text",
        required: true,
      },
      { name: "description", label: "apanel.crud.ui.label.accessScopesContext", type: "textarea" },
    ],
  },
  students: {
    title: "apanel.crud.ui.title.admissionsStudentsProfiles",
    columns: [
      { key: "passport_number", label: "apanel.crud.ui.label.passportNumber", sortable: true },
      { key: "nationality", label: "apanel.crud.ui.label.country", sortable: true },
      { key: "phone", label: "apanel.crud.ui.label.phoneNumber" },
    ],
    fields: [
      {
        name: "user_id",
        label: "apanel.crud.ui.label.userAccountId",
        type: "number",
        required: true,
      },
      { name: "phone", label: "apanel.crud.ui.label.phoneNumber", type: "text", required: true },
      {
        name: "gender",
        label: "apanel.crud.ui.label.gender",
        type: "select",
        options: ["male", "female"],
        required: true,
      },
      { name: "birth_date", label: "apanel.crud.ui.label.birthDate", type: "date", required: true },
      {
        name: "passport_number",
        label: "apanel.crud.ui.label.passportCodeId",
        type: "text",
        required: true,
      },
      {
        name: "nationality",
        label: "apanel.crud.ui.label.countryOrigin",
        type: "text",
        required: true,
      },
      {
        name: "address",
        label: "apanel.crud.ui.label.homeAddress",
        type: "textarea",
        required: true,
      },
    ],
  },
  applications: {
    title: "apanel.crud.ui.title.admissionsApplications",
    columns: [
      { key: "id", label: "apanel.crud.ui.label.id", sortable: true },
      {
        key: "studentProfile.user.name",
        label: "apanel.crud.ui.label.studentName",
        sortable: false,
      },
      { key: "studentProfile.user.email", label: "apanel.crud.ui.label.email", sortable: false },
      {
        key: "studentProfile.nationality",
        label: "apanel.crud.ui.label.nationality",
        sortable: false,
      },
      { key: "program.translations.0.name", label: "apanel.crud.ui.label.program", sortable: false },
      { key: "status", label: "apanel.crud.ui.label.status", sortable: true },
    ],
    fields: [
      {
        name: "student_profile_id",
        label: "apanel.crud.ui.label.studentProfileReferenceId",
        type: "number",
        required: true,
      },
      {
        name: "program_id",
        label: "apanel.crud.ui.label.programReferenceId",
        type: "number",
        required: true,
      },
      { name: "faculty_id", label: "apanel.crud.ui.label.facultyReferenceId", type: "number" },
      {
        name: "department_id",
        label: "apanel.crud.ui.label.departmentReferenceId",
        type: "number",
      },
      {
        name: "degree_level",
        label: "apanel.crud.ui.label.degreeLevel",
        type: "select",
        options: ["bachelor", "master", "phd"],
      },
      { name: "language_of_study", label: "apanel.crud.ui.label.languageOfStudy", type: "text" },
      { name: "study_mode", label: "apanel.crud.ui.label.studyMode", type: "text" },
      {
        name: "status",
        label: "apanel.crud.ui.label.admissionsStatus",
        type: "select",
        options: [
          "draft",
          "submitted",
          "under_review",
          "missing_documents",
          "accepted",
          "rejected",
          "contract_pending",
          "payment_pending",
          "enrolled",
          "active_student",
          "graduated",
        ],
        required: true,
      },
    ],
  },
  "application-documents": {
    title: "apanel.crud.ui.title.applicationDocuments",
    columns: [
      { key: "application_id", label: "apanel.crud.ui.label.appId", sortable: true },
      { key: "document_name", label: "apanel.crud.ui.label.documentName", sortable: true },
      { key: "document_type", label: "apanel.crud.ui.label.type", sortable: true },
      { key: "status", label: "apanel.crud.ui.label.status", sortable: true },
      { key: "file_path", label: "apanel.crud.ui.label.filePath" },
    ],
    fields: [
      {
        name: "application_id",
        label: "apanel.crud.ui.label.parentApplicationIdReference",
        type: "number",
        required: true,
      },
      {
        name: "document_name",
        label: "apanel.crud.ui.label.verifiedDocumentNameTitle",
        type: "text",
        required: true,
      },
      {
        name: "document_type",
        label: "apanel.crud.ui.label.documentType",
        type: "select",
        options: [
          "passport",
          "photo",
          "education_certificate",
          "transcript",
          "medical_certificate",
          "language_certificate",
          "payment_receipt",
          "other",
        ],
      },
      {
        name: "file_path",
        label: "apanel.crud.ui.label.uploadedFilePath",
        type: "media",
        required: true,
      },
      {
        name: "status",
        label: "apanel.crud.ui.label.documentStatus",
        type: "select",
        options: ["pending", "approved", "rejected", "requested"],
      },
      { name: "note", label: "apanel.crud.ui.label.reviewNote", type: "textarea" },
    ],
  },
  contracts: {
    title: "apanel.crud.ui.title.billingContracts",
    columns: [
      { key: "contract_number", label: "apanel.crud.ui.label.contractNumber", sortable: true },
      { key: "amount", label: "apanel.crud.ui.label.amountPrice", sortable: true },
      { key: "status", label: "apanel.crud.ui.label.contractStatus", sortable: true },
    ],
    fields: [
      {
        name: "application_id",
        label: "apanel.crud.ui.label.associatedApplicationId",
        type: "number",
        required: true,
      },
      {
        name: "contract_number",
        label: "apanel.crud.ui.label.contractUniqueCodeNumber",
        type: "text",
        required: true,
      },
      {
        name: "amount",
        label: "apanel.crud.ui.label.yearlyContractTuitionAmount",
        type: "number",
        required: true,
      },
      {
        name: "status",
        label: "apanel.crud.ui.label.contractStatus",
        type: "select",
        options: ["pending", "active", "signed", "cancelled"],
        required: true,
      },
    ],
  },
  payments: {
    title: "apanel.crud.ui.title.paymentSlips",
    columns: [
      { key: "payment_number", label: "apanel.crud.ui.label.paymentSlipNumber", sortable: true },
      { key: "amount", label: "apanel.crud.ui.label.amountPaid", sortable: true },
      { key: "payment_date", label: "apanel.crud.ui.label.paymentDate" },
      { key: "status", label: "apanel.crud.ui.label.paymentStatus", sortable: true },
    ],
    fields: [
      {
        name: "contract_id",
        label: "apanel.crud.ui.label.parentContractId",
        type: "number",
        required: true,
      },
      {
        name: "payment_number",
        label: "apanel.crud.ui.label.paymentReceiptCodeNumber",
        type: "text",
        required: true,
      },
      {
        name: "amount",
        label: "apanel.crud.ui.label.tuitionAmountPaid",
        type: "number",
        required: true,
      },
      {
        name: "payment_date",
        label: "apanel.crud.ui.label.slipPaymentDate",
        type: "date",
        required: true,
      },
      {
        name: "status",
        label: "apanel.crud.ui.label.paymentStatus",
        type: "select",
        options: ["pending", "paid", "partially_paid", "rejected", "cancelled"],
        required: true,
      },
    ],
  },
  inquiries: {
    title: "apanel.crud.ui.title.admissionsInquiries",
    columns: [
      { key: "name", label: "apanel.crud.ui.label.contactName", sortable: true },
      { key: "email", label: "apanel.crud.ui.label.emailAddress", sortable: true },
      { key: "subject", label: "apanel.crud.ui.label.querySubject" },
      {
        key: "status",
        label: "apanel.crud.ui.label.status",
        type: "select",
        options: ["pending", "resolved"],
      },
    ],
    fields: [
      {
        name: "name",
        label: "apanel.crud.ui.label.inquirerFullName",
        type: "text",
        required: true,
      },
      { name: "email", label: "apanel.crud.ui.label.inquirerEmail", type: "email", required: true },
      {
        name: "subject",
        label: "apanel.crud.ui.label.messageSubject",
        type: "text",
        required: true,
      },
      {
        name: "message",
        label: "apanel.crud.ui.label.messageBodyQueryDetails",
        type: "textarea",
        required: true,
      },
      {
        name: "status",
        label: "apanel.crud.ui.label.supportStatus",
        type: "select",
        options: ["pending", "resolved"],
        required: true,
      },
    ],
  },
  "support-tickets": {
    title: "apanel.crud.ui.title.admissionsSupportTickets",
    columns: [
      { key: "user_id", label: "apanel.crud.ui.label.userId", sortable: true },
      { key: "subject", label: "apanel.crud.ui.label.ticketSubject" },
      { key: "status", label: "apanel.crud.ui.label.status" },
      { key: "priority", label: "apanel.crud.ui.label.priority" },
    ],
    fields: [
      {
        name: "user_id",
        label: "apanel.crud.ui.label.authorUserId",
        type: "number",
        required: true,
      },
      {
        name: "subject",
        label: "apanel.crud.ui.label.ticketSubjectTitle",
        type: "text",
        required: true,
      },
      {
        name: "status",
        label: "apanel.crud.ui.label.ticketStatus",
        type: "select",
        options: ["open", "closed", "pending"],
        required: true,
      },
      {
        name: "priority",
        label: "apanel.crud.ui.label.urgencyPriority",
        type: "select",
        options: ["low", "normal", "high"],
        required: true,
      },
    ],
  },
  comments: {
    title: "Blog Comments",
    columns: [
      { key: "blog_id", label: "Blog ID", sortable: true },
      { key: "blog_title_en", label: "Blog Title (EN)" },
      { key: "blog_url", label: "Blog URL" },
      { key: "author_name", label: "Author", sortable: true },
      { key: "email", label: "Email" },
      { key: "content", label: "Comment" },
      { key: "is_approved", label: "Approved", type: "boolean" },
    ],
    fields: [
      {
        name: "blog_id",
        label: "Blog ID",
        type: "number",
        required: true,
      },
      {
        name: "parent_id",
        label: "Parent Comment ID",
        type: "number",
      },
      {
        name: "author_name",
        label: "Author Name",
        type: "text",
        required: true,
      },
      {
        name: "email",
        label: "Email",
        type: "email",
      },
      {
        name: "content",
        label: "Comment Content",
        type: "textarea",
        required: true,
      },
      {
        name: "is_approved",
        label: "Approved",
        type: "boolean",
      },
    ],
  },
  "video-comments": {
    title: "Video Comments",
    columns: [
      { key: "video_id", label: "Video ID", sortable: true },
      { key: "video_title_en", label: "Video Title (EN)" },
      { key: "video_url", label: "Video URL" },
      { key: "author_name", label: "Author", sortable: true },
      { key: "email", label: "Email" },
      { key: "content", label: "Comment" },
      { key: "is_approved", label: "Approved", type: "boolean" },
    ],
    fields: [
      {
        name: "video_id",
        label: "Video ID",
        type: "number",
        required: true,
      },
      {
        name: "parent_id",
        label: "Parent Comment ID",
        type: "number",
      },
      {
        name: "author_name",
        label: "Author Name",
        type: "text",
        required: true,
      },
      {
        name: "email",
        label: "Email",
        type: "email",
      },
      {
        name: "content",
        label: "Comment Content",
        type: "textarea",
        required: true,
      },
      {
        name: "is_approved",
        label: "Approved",
        type: "boolean",
      },
    ],
  },
  notifications: {
    title: "apanel.crud.ui.title.studentAlertNotifications",
    columns: [
      { key: "user_id", label: "apanel.crud.ui.label.userId", sortable: true },
      { key: "title", label: "apanel.crud.ui.label.titleAlert", sortable: true },
      { key: "is_read", label: "apanel.crud.ui.label.isRead", type: "boolean" },
    ],
    fields: [
      {
        name: "user_id",
        label: "apanel.crud.ui.label.studentUserId",
        type: "number",
        required: true,
      },
      {
        name: "title",
        label: "apanel.crud.ui.label.alertNotificationTitle",
        type: "text",
        required: true,
      },
      {
        name: "message",
        label: "apanel.crud.ui.label.alertMessageTextContent",
        type: "textarea",
        required: true,
      },
      {
        name: "is_read",
        label: "apanel.crud.ui.label.notificationReadByStudent",
        type: "boolean",
      },
    ],
  },
  "audit-logs": {
    title: "apanel.crud.ui.title.systemAuditLogsReadOnly",
    columns: [
      { key: "id", label: "apanel.crud.ui.label.logId", sortable: true },
      { key: "user_id", label: "apanel.crud.ui.label.adminId", sortable: true },
      { key: "action", label: "apanel.crud.ui.label.crudAction", sortable: true },
      { key: "model_type", label: "apanel.crud.ui.label.updatedModelTable" },
      { key: "model_id", label: "apanel.crud.ui.label.recordId" },
      { key: "ip_address", label: "apanel.crud.ui.label.ipAddress" },
    ],
    fields: [],
  },
  "application-status-histories": {
    title: "apanel.crud.ui.title.applicationStatusHistory",
    columns: [
      { key: "application_id", label: "apanel.crud.ui.label.applicationId", sortable: true },
      { key: "old_status", label: "apanel.crud.ui.label.oldStatus" },
      { key: "new_status", label: "apanel.crud.ui.label.newStatus" },
      { key: "status", label: "apanel.crud.ui.label.status", sortable: true },
      { key: "changed_by", label: "apanel.crud.ui.label.changedBy" },
    ],
    fields: [
      {
        name: "application_id",
        label: "apanel.crud.ui.label.applicationId",
        type: "number",
        required: true,
      },
      { name: "old_status", label: "apanel.crud.ui.label.oldStatus", type: "text" },
      { name: "new_status", label: "apanel.crud.ui.label.newStatus", type: "text" },
      {
        name: "status",
        label: "apanel.crud.ui.label.status",
        type: "select",
        options: [
          "draft",
          "submitted",
          "under_review",
          "missing_documents",
          "accepted",
          "rejected",
          "contract_pending",
          "payment_pending",
          "enrolled",
          "active_student",
          "graduated",
        ],
        required: true,
      },
      { name: "comment", label: "apanel.crud.ui.label.comment", type: "textarea" },
      { name: "note", label: "apanel.crud.ui.label.note", type: "textarea" },
      {
        name: "changed_by",
        label: "apanel.crud.ui.label.changedByUserId",
        type: "number",
        required: true,
      },
    ],
  },
};

const translateSchemaValue = (value, t) => {
  if (typeof value !== "string") return value;
  return value.startsWith("apanel.") ? t(value) : value;
};

const translateSchemaOptions = (options, t) => {
  if (!Array.isArray(options)) return options;
  return options.map((option) => {
    if (!option || typeof option !== "object") return option;
    return {
      ...option,
      label: translateSchemaValue(option.label, t),
    };
  });
};

const localizeResourceSchema = (schema, t) => ({
  ...schema,
  title: translateSchemaValue(schema.title, t),
  columns: (schema.columns || []).map((column) => ({
    ...column,
    label: translateSchemaValue(column.label, t),
  })),
  fields: (schema.fields || []).map((field) => ({
    ...field,
    label: translateSchemaValue(field.label, t),
    placeholder: translateSchemaValue(field.placeholder, t),
    description: translateSchemaValue(field.description, t),
    options: translateSchemaOptions(field.options, t),
  })),
});

const getPrimaryTranslation = (item, preferredLocale = "en") => {
  const translations = Array.isArray(item?.translations) ? item.translations : [];
  return (
    translations.find((translation) => translation.locale === preferredLocale) ||
    translations.find((translation) => translation.locale === "en") ||
    translations[0] ||
    {}
  );
};

const countByDepartmentId = (items = []) =>
  items.reduce((acc, item) => {
    if (!item?.department_id) return acc;
    acc[item.department_id] = (acc[item.department_id] || 0) + 1;
    return acc;
  }, {});

const mapById = (items = []) =>
  items.reduce((acc, item) => {
    if (item?.id) acc[item.id] = item;
    return acc;
  }, {});

const formatDegreeLabel = (value) =>
  String(value || "unknown")
    .replaceAll("_", " ")
    .replace(/\b\w/g, (letter) => letter.toUpperCase());

const formatProgramTuition = (program) => {
  const fee = Number(program?.tuition_fee || 0);
  const currency = String(program?.currency || "USD").trim() || "USD";
  if (!fee) return "--";
  return `${fee.toLocaleString()} ${currency}`;
};

const getInitials = (name) => {
  const parts = String(name || "")
    .trim()
    .split(/\s+/)
    .filter(Boolean);
  if (parts.length === 0) return "ST";
  return parts
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase();
};

export default function ApanelCrud() {
  const { t, locales: availableLocales } = useLanguage();
  const { resource } = useParams();
  const navigate = useNavigate();

  const [dataList, setDataList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [validationErrors, setValidationErrors] = useState({});

  // Query state modifiers
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [lastPage, setLastPage] = useState(1);
  const [sortBy, setSortBy] = useState("id");
  const [sortDir, setSortDir] = useState("desc");
  const [activeFilters, setActiveFilters] = useState({});

  // Modal display states
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [editItem, setEditItem] = useState(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [facultyRelations, setFacultyRelations] = useState({
    departments: {},
    programs: {},
    staff: {},
    loading: false,
  });
  const [departmentRelations, setDepartmentRelations] = useState({
    faculties: {},
    programs: {},
    staff: {},
    loading: false,
  });
  const [programRelations, setProgramRelations] = useState({
    faculties: {},
    departments: {},
    loading: false,
  });
  const [staffRelations, setStaffRelations] = useState({
    faculties: {},
    departments: {},
    loading: false,
  });

  const [toast, setToast] = useState(null);
  const showToast = (message, type = "success") => {
    setToast({ message, type });
    setTimeout(() => {
      setToast(null);
    }, 4000);
  };

  useEffect(() => {
    setPage(1);
    setSearch("");
    setActiveFilters({});
  }, [resource]);

  const fetchRecords = React.useCallback(async () => {
    if (!RESOURCE_SCHEMAS[resource]) return;
    try {
      setLoading(true);
      setError("");

      const params = {
        search,
        page,
        sort_by: sortBy,
        sort_dir: sortDir,
        ...activeFilters,
      };

      const pageData = await apanelService.listPage(resource, params);

      setDataList(pageData.items);
      setTotal(pageData.total);
      setLastPage(pageData.lastPage);
    } catch {
      setError(t("apanel.crud.fetchFailed"));
    } finally {
      setLoading(false);
    }
  }, [resource, search, page, sortBy, sortDir, activeFilters, t]);

  useEffect(() => {
    fetchRecords();
  }, [fetchRecords]);

  useEffect(() => {
    if (resource !== "faculties") return;

    let cancelled = false;
    const fetchFacultyRelations = async () => {
      try {
        setFacultyRelations((prev) => ({ ...prev, loading: true }));
        const visibleFacultyIds = dataList.map((faculty) => faculty.id).filter(Boolean);

        if (visibleFacultyIds.length === 0) {
          if (!cancelled) {
            setFacultyRelations({
              departments: {},
              programs: {},
              staff: {},
              loading: false,
            });
          }
          return;
        }

        const countLinkedResource = async (linkedResource) => {
          const entries = await Promise.all(
            visibleFacultyIds.map(async (facultyId) => {
              const pageData = await apanelService.listPage(linkedResource, {
                per_page: 1,
                filter: { faculty_id: facultyId },
              });
              return [facultyId, pageData.total || 0];
            }),
          );

          return Object.fromEntries(entries);
        };

        const [departments, programs, staff] = await Promise.all([
          countLinkedResource("departments"),
          countLinkedResource("programs"),
          countLinkedResource("staff"),
        ]);

        if (cancelled) return;

        setFacultyRelations({
          departments,
          programs,
          staff,
          loading: false,
        });
      } catch {
        if (!cancelled) {
          setFacultyRelations({
            departments: {},
            programs: {},
            staff: {},
            loading: false,
          });
        }
      }
    };

    fetchFacultyRelations();

    return () => {
      cancelled = true;
    };
  }, [resource, dataList]);

  useEffect(() => {
    if (resource !== "departments") return;

    let cancelled = false;
    const fetchDepartmentRelations = async () => {
      try {
        setDepartmentRelations((prev) => ({ ...prev, loading: true }));
        const [facultiesPage, programsPage, staffPage] = await Promise.all([
          apanelService.listPage("faculties", { per_page: 500, sort_by: "sort_order", sort_dir: "asc" }),
          apanelService.listPage("programs", { per_page: 500, sort_by: "department_id", sort_dir: "asc" }),
          apanelService.listPage("staff", { per_page: 500, sort_by: "department_id", sort_dir: "asc" }),
        ]);

        if (cancelled) return;

        setDepartmentRelations({
          faculties: mapById(facultiesPage.items),
          programs: countByDepartmentId(programsPage.items),
          staff: countByDepartmentId(staffPage.items),
          loading: false,
        });
      } catch {
        if (!cancelled) {
          setDepartmentRelations({
            faculties: {},
            programs: {},
            staff: {},
            loading: false,
          });
        }
      }
    };

    fetchDepartmentRelations();

    return () => {
      cancelled = true;
    };
  }, [resource, dataList]);

  useEffect(() => {
    if (resource !== "programs") return;

    let cancelled = false;
    const fetchProgramRelations = async () => {
      try {
        setProgramRelations((prev) => ({ ...prev, loading: true }));
        const [facultiesPage, departmentsPage] = await Promise.all([
          apanelService.listPage("faculties", { per_page: 500, sort_by: "sort_order", sort_dir: "asc" }),
          apanelService.listPage("departments", { per_page: 500, sort_by: "sort_order", sort_dir: "asc" }),
        ]);

        if (cancelled) return;

        setProgramRelations({
          faculties: mapById(facultiesPage.items),
          departments: mapById(departmentsPage.items),
          loading: false,
        });
      } catch {
        if (!cancelled) {
          setProgramRelations({
            faculties: {},
            departments: {},
            loading: false,
          });
        }
      }
    };

    fetchProgramRelations();

    return () => {
      cancelled = true;
    };
  }, [resource, dataList]);

  useEffect(() => {
    if (resource !== "staff") return;

    let cancelled = false;
    const fetchStaffRelations = async () => {
      try {
        setStaffRelations((prev) => ({ ...prev, loading: true }));
        const [facultiesPage, departmentsPage] = await Promise.all([
          apanelService.listPage("faculties", { per_page: 500, sort_by: "sort_order", sort_dir: "asc" }),
          apanelService.listPage("departments", { per_page: 500, sort_by: "sort_order", sort_dir: "asc" }),
        ]);

        if (cancelled) return;

        setStaffRelations({
          faculties: mapById(facultiesPage.items),
          departments: mapById(departmentsPage.items),
          loading: false,
        });
      } catch {
        if (!cancelled) {
          setStaffRelations({
            faculties: {},
            departments: {},
            loading: false,
          });
        }
      }
    };

    fetchStaffRelations();

    return () => {
      cancelled = true;
    };
  }, [resource, dataList]);

  const resourceStats = React.useMemo(() => {
    if (resource === "faculties") {
      const active = dataList.filter((item) => item.is_active).length;
      const inactive = dataList.length - active;
      const departments = Object.values(facultyRelations.departments).reduce((sum, value) => sum + value, 0);
      const programs = Object.values(facultyRelations.programs).reduce((sum, value) => sum + value, 0);
      const staff = Object.values(facultyRelations.staff).reduce((sum, value) => sum + value, 0);

      return [
        {
          label: "Faculties",
          value: total,
          hint: "Total records",
          icon: GraduationCap,
          tone: "text-blue-600 bg-blue-50 border-blue-100",
        },
        {
          label: "Active",
          value: active,
          hint: inactive ? `${inactive} inactive on this page` : "All visible records active",
          icon: CheckCircle2,
          tone: "text-emerald-600 bg-emerald-50 border-emerald-100",
        },
        {
          label: "Departments",
          value: departments,
          hint: facultyRelations.loading ? "Refreshing counts" : "Linked records",
          icon: Building2,
          tone: "text-cyan-600 bg-cyan-50 border-cyan-100",
        },
        {
          label: "Programs",
          value: programs,
          hint: staff ? `${staff} staff profiles` : "Linked records",
          icon: BookOpen,
          tone: "text-amber-600 bg-amber-50 border-amber-100",
        },
      ];
    }

    if (resource === "departments") {
      const active = dataList.filter((item) => item.is_active).length;
      const inactive = dataList.length - active;
      const facultyCount = new Set(dataList.map((item) => item.faculty_id).filter(Boolean)).size;
      const programs = Object.values(departmentRelations.programs).reduce((sum, value) => sum + value, 0);
      const staff = Object.values(departmentRelations.staff).reduce((sum, value) => sum + value, 0);

      return [
        {
          label: "Departments",
          value: total,
          hint: "Total records",
          icon: Building2,
          tone: "text-blue-600 bg-blue-50 border-blue-100",
        },
        {
          label: "Active",
          value: active,
          hint: inactive ? `${inactive} inactive on this page` : "All visible records active",
          icon: CheckCircle2,
          tone: "text-emerald-600 bg-emerald-50 border-emerald-100",
        },
        {
          label: "Faculties",
          value: facultyCount,
          hint: departmentRelations.loading ? "Refreshing links" : "Represented on this page",
          icon: GraduationCap,
          tone: "text-cyan-600 bg-cyan-50 border-cyan-100",
        },
        {
          label: "Programs",
          value: programs,
          hint: staff ? `${staff} staff profiles` : "Linked records",
          icon: BookOpen,
          tone: "text-amber-600 bg-amber-50 border-amber-100",
        },
      ];
    }

    if (resource === "programs") {
      const active = dataList.filter((item) => item.is_active).length;
      const inactive = dataList.length - active;
      const facultyCount = new Set(dataList.map((item) => item.faculty_id).filter(Boolean)).size;
      const departmentCount = new Set(dataList.map((item) => item.department_id).filter(Boolean)).size;
      const bachelorCount = dataList.filter((item) => item.degree === "bachelor").length;
      const featuredCount = dataList.filter((item) => item.show_on_homepage).length;

      return [
        {
          label: "Programs",
          value: total,
          hint: "Total records",
          icon: BookOpen,
          tone: "text-blue-600 bg-blue-50 border-blue-100",
        },
        {
          label: "Active",
          value: active,
          hint: inactive ? `${inactive} inactive on this page` : "All visible records active",
          icon: CheckCircle2,
          tone: "text-emerald-600 bg-emerald-50 border-emerald-100",
        },
        {
          label: "Structure",
          value: `${facultyCount}/${departmentCount}`,
          hint: programRelations.loading ? "Refreshing links" : "Faculties / departments",
          icon: Building2,
          tone: "text-cyan-600 bg-cyan-50 border-cyan-100",
        },
        {
          label: "Homepage",
          value: featuredCount,
          hint: `${bachelorCount} bachelor programs on this page`,
          icon: GraduationCap,
          tone: "text-amber-600 bg-amber-50 border-amber-100",
        },
      ];
    }

    if (resource === "courses") {
      const active = dataList.filter((item) => item.is_active).length;
      const inactive = dataList.length - active;
      const credits = dataList.reduce((sum, item) => sum + Number(item.credits || 0), 0);
      const semesters = new Set(dataList.map((item) => item.semester).filter(Boolean)).size;

      return [
        {
          label: "Courses",
          value: total,
          hint: "Total records",
          icon: BookOpen,
          tone: "text-blue-600 bg-blue-50 border-blue-100",
        },
        {
          label: "Active",
          value: active,
          hint: inactive ? `${inactive} inactive on this page` : "All visible records active",
          icon: CheckCircle2,
          tone: "text-emerald-600 bg-emerald-50 border-emerald-100",
        },
        {
          label: "Credits",
          value: credits,
          hint: "Total credits on this page",
          icon: Clock,
          tone: "text-cyan-600 bg-cyan-50 border-cyan-100",
        },
        {
          label: "Semesters",
          value: semesters,
          hint: "Represented on this page",
          icon: GraduationCap,
          tone: "text-amber-600 bg-amber-50 border-amber-100",
        },
      ];
    }

    if (resource === "staff") {
      const active = dataList.filter((item) => item.is_active).length;
      const inactive = dataList.length - active;
      const withPhotos = dataList.filter((item) => item.photo || item.photo_url).length;
      const facultyCount = new Set(dataList.map((item) => item.faculty_id).filter(Boolean)).size;
      const departmentCount = new Set(dataList.map((item) => item.department_id).filter(Boolean)).size;

      return [
        {
          label: "Staff",
          value: total,
          hint: "Total profiles",
          icon: UsersRound,
          tone: "text-blue-600 bg-blue-50 border-blue-100",
        },
        {
          label: "Active",
          value: active,
          hint: inactive ? `${inactive} inactive on this page` : "All visible profiles active",
          icon: CheckCircle2,
          tone: "text-emerald-600 bg-emerald-50 border-emerald-100",
        },
        {
          label: "Portraits",
          value: withPhotos,
          hint: "Profiles with photos on this page",
          icon: UsersRound,
          tone: "text-cyan-600 bg-cyan-50 border-cyan-100",
        },
        {
          label: "Structure",
          value: `${facultyCount}/${departmentCount}`,
          hint: staffRelations.loading ? "Refreshing links" : "Faculties / departments",
          icon: Building2,
          tone: "text-amber-600 bg-amber-50 border-amber-100",
        },
      ];
    }

    if (!["comments", "video-comments"].includes(resource)) return [];
    const approved = dataList.filter((item) => item.is_approved).length;
    const pending = dataList.filter((item) => !item.is_approved).length;
    const replies = dataList.filter((item) => item.parent_id).length;

    return [
      {
        label: "Total comments",
        value: total,
        hint: "All matching records",
        icon: MessageCircle,
        tone: "text-blue-600 bg-blue-50 border-blue-100",
      },
      {
        label: "Approved on page",
        value: approved,
        hint: "Visible approved comments",
        icon: CheckCircle2,
        tone: "text-emerald-600 bg-emerald-50 border-emerald-100",
      },
      {
        label: "Pending on page",
        value: pending,
        hint: "Need moderation",
        icon: ShieldAlert,
        tone: "text-amber-600 bg-amber-50 border-amber-100",
      },
      {
        label: "Replies on page",
        value: replies,
        hint: "Threaded comments",
        icon: Reply,
        tone: "text-violet-600 bg-violet-50 border-violet-100",
      },
    ];
  }, [dataList, departmentRelations, facultyRelations, programRelations, resource, staffRelations, total]);

  const rawSchema = RESOURCE_SCHEMAS[resource];
  if (!rawSchema) {
    return (
      <div className="bg-white border border-gray-100 rounded-3xl p-8 text-center text-rose-600 font-bold shadow-xs">
        {t("apanel.crud.resourceNotConfigured")} {resource}
      </div>
    );
  }
  const schema = localizeResourceSchema(rawSchema, t);
  const formFields = schema.fields.map((field) => {
    if (field.options === "__staff_departments__") {
      return {
        ...field,
        options: Object.values(staffRelations.departments).map((department) => {
          const translation = getPrimaryTranslation(department);
          return {
            value: department.id,
            label: translation.name || department.slug || `Department #${department.id}`,
          };
        }),
      };
    }

    if (field.options !== "__active_locales__") return field;
    return {
      ...field,
      options: (availableLocales || []).map((locale) => ({
        value: locale.code,
        label: locale.native_name || locale.name || locale.code,
      })),
    };
  });

  // Handle item status toggle directly from table
  const handleStatusToggle = async (id, statusField, nextVal) => {
    try {
      setError("");
      const item = dataList.find((d) => d.id === id);
      if (!item) return;

      const payload = { ...item, [statusField]: nextVal };
      await apanelService.update(resource, id, payload);
      showToast(t("apanel.crud.statusUpdated"));
      fetchRecords();
    } catch {
      setError(t("apanel.crud.statusToggleFailed"));
      showToast(t("apanel.crud.statusToggleFailed"), "error");
    }
  };

  const handleOpenCreate = () => {
    setEditItem(null);
    setValidationErrors({});
    setIsFormOpen(true);
  };

  const handleOpenEdit = async (item) => {
    try {
      setLoading(true);
      // Fetch full record including translations from show endpoint
      const fullItem = await apanelService.get(resource, item.id);
      setEditItem(fullItem);
      setValidationErrors({});
      setIsFormOpen(true);
    } catch {
      setError(t("apanel.crud.retrieveFailed"));
    } finally {
      setLoading(false);
    }
  };

  const handleFormSubmit = async (formState) => {
    try {
      setIsSubmitting(true);
      setValidationErrors({});
      setError("");

      if (editItem) {
        await apanelService.update(resource, editItem.id, formState);
        showToast(t("apanel.crud.recordUpdated"));
      } else {
        await apanelService.create(resource, formState);
        showToast(t("apanel.crud.recordCreated"));
      }

      setIsFormOpen(false);
      setEditItem(null);
      fetchRecords();
    } catch (err) {
      if (err?.status === 422 && err?.errors) {
        setValidationErrors(err.errors);
        showToast(t("apanel.crud.validationErrors"), "error");
      } else {
        const errMsg = err?.message || t("apanel.crud.saveFailed");
        setError(errMsg);
        showToast(errMsg, "error");
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDeleteConfirm = async () => {
    if (!deleteTarget) return;

    try {
      setError("");
      await apanelService.delete(resource, deleteTarget.id);
      showToast(t("apanel.crud.recordDeleted"));
      setDeleteTarget(null);
      fetchRecords();
    } catch {
      setError(t("apanel.crud.deleteFailed"));
      showToast(t("apanel.crud.deleteFailed"), "error");
    }
  };

  // Override view actions for specialized application portal reviews
  const handleViewAction = (item) => {
    if (resource === "applications") {
      navigate(`/apanel/applications/${item.id}`);
    } else {
      handleOpenEdit(item);
    }
  };

  const applicationStatusOptions = [
    "draft",
    "submitted",
    "under_review",
    "missing_documents",
    "accepted",
    "rejected",
    "contract_pending",
    "payment_pending",
    "enrolled",
    "active_student",
    "graduated",
  ];

  const handleFilterChange = (name, value) => {
    setActiveFilters((prev) => ({ ...prev, [name]: value }));
    setPage(1);
  };

  const renderFacultyManagementBoard = () => {
    if (resource !== "faculties" || loading || dataList.length === 0) return null;

    return (
      <section className="space-y-4">
        <div className="flex flex-col gap-3 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between">
          <div className="min-w-0">
            <p className="text-[10px] font-black uppercase tracking-widest text-primary">
              Faculty Management
            </p>
            <h2 className="mt-1 text-xl font-black text-navy">
              Academic structure overview
            </h2>
          </div>
          <div className="grid grid-cols-3 gap-2 text-center">
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-navy">{dataList.length}</p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Shown</p>
            </div>
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-emerald-600">
                {dataList.filter((item) => item.is_active).length}
              </p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Active</p>
            </div>
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-cyan-600">
                {Object.values(facultyRelations.departments).reduce((sum, value) => sum + value, 0)}
              </p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Departments</p>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 gap-4 xl:grid-cols-2">
          {dataList.map((faculty) => {
            const translation = getPrimaryTranslation(faculty);
            const departmentsCount = facultyRelations.departments[faculty.id] || 0;
            const programsCount = facultyRelations.programs[faculty.id] || 0;
            const staffCount = facultyRelations.staff[faculty.id] || 0;

            return (
              <article
                key={faculty.id}
                className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
              >
                <div className="flex min-w-0 flex-col">
                    <div className="flex items-start justify-between gap-3">
                      <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                          <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-primary/10 bg-primary-light text-primary">
                            <GraduationCap className="h-5 w-5" />
                          </span>
                          <h3 className="min-w-0 truncate text-lg font-black text-navy">
                            {translation.name || faculty.slug}
                          </h3>
                          <span
                            className={`rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ${
                              faculty.is_active
                                ? "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100"
                                : "bg-rose-50 text-rose-700 ring-1 ring-rose-100"
                            }`}
                          >
                            {faculty.is_active ? "Active" : "Inactive"}
                          </span>
                        </div>
                        <div className="mt-2 flex flex-wrap items-center gap-2 text-[11px] font-extrabold text-gray-400">
                          <span className="inline-flex min-w-0 items-center gap-1 rounded-full bg-gray-50 px-2.5 py-1">
                            <Hash className="h-3 w-3 shrink-0" />
                            <span className="truncate">{faculty.slug}</span>
                          </span>
                          {faculty.code && (
                            <span className="rounded-full bg-primary-light px-2.5 py-1 text-primary">
                              {faculty.code}
                            </span>
                          )}
                        </div>
                      </div>

                      <div className="flex shrink-0 items-center gap-1">
                        <button
                          type="button"
                          onClick={() => handleOpenEdit(faculty)}
                          className="rounded-xl border border-gray-100 p-2 text-gray-500 transition-all hover:border-primary/20 hover:bg-primary-light hover:text-primary"
                          title={t("button.edit")}
                        >
                          <Pencil className="h-4 w-4" />
                        </button>
                        <button
                          type="button"
                          onClick={() => setDeleteTarget(faculty)}
                          className="rounded-xl border border-gray-100 p-2 text-gray-500 transition-all hover:border-rose-100 hover:bg-rose-50 hover:text-rose-600"
                          title={t("button.delete")}
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                    </div>

                    <p className="mt-3 line-clamp-2 text-xs font-semibold leading-5 text-gray-500">
                      {translation.description || "No overview entered yet."}
                    </p>

                    <div className="mt-4 grid grid-cols-3 gap-2">
                      {[
                        { label: "Departments", value: departmentsCount, icon: Building2 },
                        { label: "Programs", value: programsCount, icon: BookOpen },
                        { label: "Staff", value: staffCount, icon: UsersRound },
                      ].map((item) => {
                        const Icon = item.icon;
                        return (
                          <div key={item.label} className="rounded-2xl border border-gray-100 bg-gray-50/60 p-3">
                            <div className="flex items-center gap-2 text-gray-400">
                              <Icon className="h-3.5 w-3.5" />
                              <span className="truncate text-[10px] font-black uppercase tracking-wider">{item.label}</span>
                            </div>
                            <p className="mt-2 text-xl font-black leading-none text-navy">{item.value}</p>
                          </div>
                        );
                      })}
                    </div>

                    <div className="mt-4 grid gap-2 text-xs font-bold text-gray-500 sm:grid-cols-2">
                      {faculty.head_name && (
                        <span className="flex min-w-0 items-center gap-2">
                          <UsersRound className="h-3.5 w-3.5 shrink-0 text-primary" />
                          <span className="truncate">{faculty.head_name}</span>
                        </span>
                      )}
                      {faculty.phone && (
                        <span className="flex min-w-0 items-center gap-2">
                          <Phone className="h-3.5 w-3.5 shrink-0 text-primary" />
                          <span className="truncate">{faculty.phone}</span>
                        </span>
                      )}
                      {faculty.email && (
                        <span className="flex min-w-0 items-center gap-2">
                          <Mail className="h-3.5 w-3.5 shrink-0 text-primary" />
                          <span className="truncate">{faculty.email}</span>
                        </span>
                      )}
                      {faculty.reception_time && (
                        <span className="flex min-w-0 items-center gap-2">
                          <Clock className="h-3.5 w-3.5 shrink-0 text-primary" />
                          <span className="truncate">{faculty.reception_time}</span>
                        </span>
                      )}
                    </div>

                    <div className="mt-5 flex flex-wrap items-center gap-2 border-t border-gray-50 pt-4">
                      <button
                        type="button"
                        onClick={() => navigate(`/faculty/${faculty.slug}`)}
                        className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                      >
                        <ExternalLink className="h-3.5 w-3.5" />
                        Public page
                      </button>
                      <button
                        type="button"
                        onClick={() => navigate("/apanel/departments")}
                        className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                      >
                        <Building2 className="h-3.5 w-3.5" />
                        Departments
                      </button>
                      <button
                        type="button"
                        onClick={() => navigate("/apanel/programs")}
                        className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                      >
                        <BookOpen className="h-3.5 w-3.5" />
                        Programs
                      </button>
                    </div>
                </div>
              </article>
            );
          })}
        </div>
      </section>
    );
  };

  const renderDepartmentManagementBoard = () => {
    if (resource !== "departments" || loading || dataList.length === 0) return null;

    return (
      <section className="space-y-4">
        <div className="flex flex-col gap-3 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between">
          <div className="min-w-0">
            <p className="text-[10px] font-black uppercase tracking-widest text-primary">
              Department Management
            </p>
            <h2 className="mt-1 text-xl font-black text-navy">
              Academic departments overview
            </h2>
          </div>
          <div className="grid grid-cols-3 gap-2 text-center">
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-navy">{dataList.length}</p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Shown</p>
            </div>
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-emerald-600">
                {dataList.filter((item) => item.is_active).length}
              </p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Active</p>
            </div>
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-cyan-600">
                {new Set(dataList.map((item) => item.faculty_id).filter(Boolean)).size}
              </p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Faculties</p>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 gap-4 xl:grid-cols-2">
          {dataList.map((department) => {
            const translation = getPrimaryTranslation(department);
            const faculty = departmentRelations.faculties[department.faculty_id];
            const facultyTranslation = getPrimaryTranslation(faculty);
            const programsCount = departmentRelations.programs[department.id] || 0;
            const staffCount = departmentRelations.staff[department.id] || 0;

            return (
              <article
                key={department.id}
                className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
              >
                <div className="flex min-w-0 flex-col">
                  <div className="flex items-start justify-between gap-3">
                    <div className="min-w-0">
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-cyan-100 bg-cyan-50 text-cyan-700">
                          <Building2 className="h-5 w-5" />
                        </span>
                        <h3 className="min-w-0 truncate text-lg font-black text-navy">
                          {translation.name || department.slug}
                        </h3>
                        <span
                          className={`rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ${
                            department.is_active
                              ? "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100"
                              : "bg-rose-50 text-rose-700 ring-1 ring-rose-100"
                          }`}
                        >
                          {department.is_active ? "Active" : "Inactive"}
                        </span>
                      </div>

                      <div className="mt-2 flex flex-wrap items-center gap-2 text-[11px] font-extrabold text-gray-400">
                        <span className="inline-flex min-w-0 items-center gap-1 rounded-full bg-gray-50 px-2.5 py-1">
                          <Hash className="h-3 w-3 shrink-0" />
                          <span className="truncate">{department.slug}</span>
                        </span>
                        {department.code && (
                          <span className="rounded-full bg-primary-light px-2.5 py-1 text-primary">
                            {department.code}
                          </span>
                        )}
                        <span className="inline-flex min-w-0 items-center gap-1 rounded-full bg-cyan-50 px-2.5 py-1 text-cyan-700">
                          <GraduationCap className="h-3 w-3 shrink-0" />
                          <span className="truncate">
                            {facultyTranslation.name || `Faculty #${department.faculty_id}`}
                          </span>
                        </span>
                      </div>
                    </div>

                    <div className="flex shrink-0 items-center gap-1">
                      <button
                        type="button"
                        onClick={() => handleOpenEdit(department)}
                        className="rounded-xl border border-gray-100 p-2 text-gray-500 transition-all hover:border-primary/20 hover:bg-primary-light hover:text-primary"
                        title={t("button.edit")}
                      >
                        <Pencil className="h-4 w-4" />
                      </button>
                      <button
                        type="button"
                        onClick={() => setDeleteTarget(department)}
                        className="rounded-xl border border-gray-100 p-2 text-gray-500 transition-all hover:border-rose-100 hover:bg-rose-50 hover:text-rose-600"
                        title={t("button.delete")}
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                  </div>

                  <p className="mt-3 line-clamp-2 text-xs font-semibold leading-5 text-gray-500">
                    {translation.description || "No overview entered yet."}
                  </p>

                  <div className="mt-4 grid grid-cols-2 gap-2">
                    {[
                      { label: "Programs", value: programsCount, icon: BookOpen },
                      { label: "Staff", value: staffCount, icon: UsersRound },
                    ].map((item) => {
                      const Icon = item.icon;
                      return (
                        <div key={item.label} className="rounded-2xl border border-gray-100 bg-gray-50/60 p-3">
                          <div className="flex items-center gap-2 text-gray-400">
                            <Icon className="h-3.5 w-3.5" />
                            <span className="truncate text-[10px] font-black uppercase tracking-wider">{item.label}</span>
                          </div>
                          <p className="mt-2 text-xl font-black leading-none text-navy">{item.value}</p>
                        </div>
                      );
                    })}
                  </div>

                  <div className="mt-4 grid gap-2 text-xs font-bold text-gray-500 sm:grid-cols-2">
                    {department.head_name && (
                      <span className="flex min-w-0 items-center gap-2">
                        <UsersRound className="h-3.5 w-3.5 shrink-0 text-primary" />
                        <span className="truncate">{department.head_name}</span>
                      </span>
                    )}
                    {department.phone && (
                      <span className="flex min-w-0 items-center gap-2">
                        <Phone className="h-3.5 w-3.5 shrink-0 text-primary" />
                        <span className="truncate">{department.phone}</span>
                      </span>
                    )}
                    {department.email && (
                      <span className="flex min-w-0 items-center gap-2">
                        <Mail className="h-3.5 w-3.5 shrink-0 text-primary" />
                        <span className="truncate">{department.email}</span>
                      </span>
                    )}
                    {department.reception_time && (
                      <span className="flex min-w-0 items-center gap-2">
                        <Clock className="h-3.5 w-3.5 shrink-0 text-primary" />
                        <span className="truncate">{department.reception_time}</span>
                      </span>
                    )}
                  </div>

                  <div className="mt-5 flex flex-wrap items-center gap-2 border-t border-gray-50 pt-4">
                    <button
                      type="button"
                      onClick={() => navigate(`/department/${department.slug}`)}
                      className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                    >
                      <ExternalLink className="h-3.5 w-3.5" />
                      Public page
                    </button>
                    <button
                      type="button"
                      onClick={() => navigate("/apanel/programs")}
                      className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                    >
                      <BookOpen className="h-3.5 w-3.5" />
                      Programs
                    </button>
                    <button
                      type="button"
                      onClick={() => navigate("/apanel/staff")}
                      className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                    >
                      <UsersRound className="h-3.5 w-3.5" />
                      Staff
                    </button>
                  </div>
                </div>
              </article>
            );
          })}
        </div>
      </section>
    );
  };

  const renderProgramManagementBoard = () => {
    if (resource !== "programs" || loading || dataList.length === 0) return null;

    const bachelorCount = dataList.filter((item) => item.degree === "bachelor").length;
    const masterCount = dataList.filter((item) => item.degree === "master").length;

    return (
      <section className="space-y-4">
        <div className="flex flex-col gap-3 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between">
          <div className="min-w-0">
            <p className="text-[10px] font-black uppercase tracking-widest text-primary">
              Program Management
            </p>
            <h2 className="mt-1 text-xl font-black text-navy">
              Study programs overview
            </h2>
          </div>
          <div className="grid grid-cols-3 gap-2 text-center">
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-navy">{dataList.length}</p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Shown</p>
            </div>
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-emerald-600">
                {dataList.filter((item) => item.is_active).length}
              </p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Active</p>
            </div>
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-amber-600">{bachelorCount}/{masterCount}</p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">B/M</p>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 gap-4 xl:grid-cols-2">
          {dataList.map((program) => {
            const translation = getPrimaryTranslation(program);
            const faculty = programRelations.faculties[program.faculty_id];
            const department = programRelations.departments[program.department_id];
            const facultyTranslation = getPrimaryTranslation(faculty);
            const departmentTranslation = getPrimaryTranslation(department);

            return (
              <article
                key={program.id}
                className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
              >
                <div className="flex min-w-0 flex-col">
                  <div className="flex items-start justify-between gap-3">
                    <div className="min-w-0">
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-amber-100 bg-amber-50 text-amber-700">
                          <BookOpen className="h-5 w-5" />
                        </span>
                        <h3 className="min-w-0 truncate text-lg font-black text-navy">
                          {translation.name || program.slug}
                        </h3>
                        <span
                          className={`rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ${
                            program.is_active
                              ? "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100"
                              : "bg-rose-50 text-rose-700 ring-1 ring-rose-100"
                          }`}
                        >
                          {program.is_active ? "Active" : "Inactive"}
                        </span>
                      </div>

                      <div className="mt-2 flex flex-wrap items-center gap-2 text-[11px] font-extrabold text-gray-400">
                        <span className="inline-flex min-w-0 items-center gap-1 rounded-full bg-gray-50 px-2.5 py-1">
                          <Hash className="h-3 w-3 shrink-0" />
                          <span className="truncate">{program.slug}</span>
                        </span>
                        {(program.official_code || program.code) && (
                          <span className="rounded-full bg-primary-light px-2.5 py-1 text-primary">
                            {program.official_code || program.code}
                          </span>
                        )}
                        <span className="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700">
                          {formatDegreeLabel(program.degree)}
                        </span>
                        {program.show_on_homepage && (
                          <span className="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700">
                            Home #{program.homepage_sort_order || "--"}
                          </span>
                        )}
                      </div>
                    </div>

                    <div className="flex shrink-0 items-center gap-1">
                      <button
                        type="button"
                        onClick={() => handleOpenEdit(program)}
                        className="rounded-xl border border-gray-100 p-2 text-gray-500 transition-all hover:border-primary/20 hover:bg-primary-light hover:text-primary"
                        title={t("button.edit")}
                      >
                        <Pencil className="h-4 w-4" />
                      </button>
                      <button
                        type="button"
                        onClick={() => setDeleteTarget(program)}
                        className="rounded-xl border border-gray-100 p-2 text-gray-500 transition-all hover:border-rose-100 hover:bg-rose-50 hover:text-rose-600"
                        title={t("button.delete")}
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                  </div>

                  <p className="mt-3 line-clamp-2 text-xs font-semibold leading-5 text-gray-500">
                    {translation.description || "No overview entered yet."}
                  </p>

                  <div className="mt-4 grid grid-cols-2 gap-2 lg:grid-cols-5">
                    {[
                      { label: "Years", value: program.duration_years || "--", icon: Clock },
                      { label: "Mode", value: formatDegreeLabel(program.study_mode), icon: CheckCircle2 },
                      { label: "Language", value: program.language_of_study || "--", icon: MessageCircle },
                      { label: "Tuition", value: formatProgramTuition(program), icon: BookOpen },
                      { label: "Home", value: program.show_on_homepage ? `#${program.homepage_sort_order || "--"}` : "Hidden", icon: ExternalLink },
                    ].map((item) => {
                      const Icon = item.icon;
                      return (
                        <div key={item.label} className="rounded-2xl border border-gray-100 bg-gray-50/60 p-3">
                          <div className="flex items-center gap-2 text-gray-400">
                            <Icon className="h-3.5 w-3.5" />
                            <span className="truncate text-[10px] font-black uppercase tracking-wider">{item.label}</span>
                          </div>
                          <p className="mt-2 truncate text-sm font-black leading-none text-navy" title={String(item.value)}>
                            {item.value}
                          </p>
                        </div>
                      );
                    })}
                  </div>

                  <div className="mt-4 grid gap-2 text-xs font-bold text-gray-500 sm:grid-cols-2">
                    <span className="flex min-w-0 items-center gap-2">
                      <GraduationCap className="h-3.5 w-3.5 shrink-0 text-primary" />
                      <span className="truncate">
                        {facultyTranslation.name || `Faculty #${program.faculty_id}`}
                      </span>
                    </span>
                    <span className="flex min-w-0 items-center gap-2">
                      <Building2 className="h-3.5 w-3.5 shrink-0 text-primary" />
                      <span className="truncate">
                        {departmentTranslation.name || `Department #${program.department_id}`}
                      </span>
                    </span>
                    {program.track && (
                      <span className="flex min-w-0 items-center gap-2 sm:col-span-2">
                        <Hash className="h-3.5 w-3.5 shrink-0 text-primary" />
                        <span className="truncate">{program.track}</span>
                      </span>
                    )}
                  </div>

                  <div className="mt-5 flex flex-wrap items-center gap-2 border-t border-gray-50 pt-4">
                    <button
                      type="button"
                      onClick={() => navigate(`/programs/${program.slug}`)}
                      className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                    >
                      <ExternalLink className="h-3.5 w-3.5" />
                      Public page
                    </button>
                    <button
                      type="button"
                      onClick={() => navigate("/apanel/faculties")}
                      className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                    >
                      <GraduationCap className="h-3.5 w-3.5" />
                      Faculties
                    </button>
                    <button
                      type="button"
                      onClick={() => navigate("/apanel/departments")}
                      className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                    >
                      <Building2 className="h-3.5 w-3.5" />
                      Departments
                    </button>
                  </div>
                </div>
              </article>
            );
          })}
        </div>
      </section>
    );
  };

  const renderCourseManagementBoard = () => {
    if (resource !== "courses" || loading || dataList.length === 0) return null;

    const credits = dataList.reduce((sum, item) => sum + Number(item.credits || 0), 0);
    const semesters = new Set(dataList.map((item) => item.semester).filter(Boolean)).size;

    return (
      <section className="space-y-4">
        <div className="flex flex-col gap-3 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between">
          <div className="min-w-0">
            <p className="text-[10px] font-black uppercase tracking-widest text-primary">
              Course Management
            </p>
            <h2 className="mt-1 text-xl font-black text-navy">
              Course catalog overview
            </h2>
          </div>
          <div className="grid grid-cols-3 gap-2 text-center">
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-navy">{dataList.length}</p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Shown</p>
            </div>
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-cyan-600">{credits}</p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Credits</p>
            </div>
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-amber-600">{semesters}</p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Semesters</p>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 gap-4 xl:grid-cols-2">
          {dataList.map((course) => {
            const translation = getPrimaryTranslation(course);

            return (
              <article
                key={course.id}
                className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
              >
                <div className="flex min-w-0 flex-col">
                  <div className="flex items-start justify-between gap-3">
                    <div className="min-w-0">
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-700">
                          <BookOpen className="h-5 w-5" />
                        </span>
                        <h3 className="min-w-0 truncate text-lg font-black text-navy">
                          {translation.name || course.code || `Course #${course.id}`}
                        </h3>
                        <span
                          className={`rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ${
                            course.is_active
                              ? "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100"
                              : "bg-rose-50 text-rose-700 ring-1 ring-rose-100"
                          }`}
                        >
                          {course.is_active ? "Active" : "Inactive"}
                        </span>
                      </div>

                      <div className="mt-2 flex flex-wrap items-center gap-2 text-[11px] font-extrabold text-gray-400">
                        <span className="inline-flex min-w-0 items-center gap-1 rounded-full bg-gray-50 px-2.5 py-1">
                          <Hash className="h-3 w-3 shrink-0" />
                          <span className="truncate">{course.code || `ID ${course.id}`}</span>
                        </span>
                        <span className="rounded-full bg-cyan-50 px-2.5 py-1 text-cyan-700">
                          {course.credits || 0} credits
                        </span>
                        <span className="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700">
                          Semester {course.semester || "—"}
                        </span>
                      </div>
                    </div>

                    <div className="flex shrink-0 items-center gap-1">
                      <button
                        type="button"
                        onClick={() => handleOpenEdit(course)}
                        className="rounded-xl border border-gray-100 p-2 text-gray-500 transition-all hover:border-primary/20 hover:bg-primary-light hover:text-primary"
                        title={t("button.edit")}
                      >
                        <Pencil className="h-4 w-4" />
                      </button>
                      <button
                        type="button"
                        onClick={() => setDeleteTarget(course)}
                        className="rounded-xl border border-gray-100 p-2 text-gray-500 transition-all hover:border-rose-100 hover:bg-rose-50 hover:text-rose-600"
                        title={t("button.delete")}
                      >
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                  </div>

                  <p className="mt-3 line-clamp-3 text-xs font-semibold leading-5 text-gray-500">
                    {translation.description || "No course description entered yet."}
                  </p>

                  <div className="mt-4 grid grid-cols-3 gap-2">
                    {[
                      { label: "Code", value: course.code || "—", icon: Hash },
                      { label: "Credits", value: course.credits || 0, icon: Clock },
                      { label: "Semester", value: course.semester || "—", icon: GraduationCap },
                    ].map((item) => {
                      const Icon = item.icon;
                      return (
                        <div key={item.label} className="rounded-2xl border border-gray-100 bg-gray-50/60 p-3">
                          <div className="flex items-center gap-2 text-gray-400">
                            <Icon className="h-3.5 w-3.5" />
                            <span className="truncate text-[10px] font-black uppercase tracking-wider">{item.label}</span>
                          </div>
                          <p className="mt-2 truncate text-sm font-black leading-none text-navy" title={String(item.value)}>
                            {item.value}
                          </p>
                        </div>
                      );
                    })}
                  </div>

                  <div className="mt-5 flex flex-wrap items-center gap-2 border-t border-gray-50 pt-4">
                    <button
                      type="button"
                      onClick={() => handleOpenEdit(course)}
                      className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                    >
                      <Pencil className="h-3.5 w-3.5" />
                      Edit course
                    </button>
                    <button
                      type="button"
                      onClick={() => navigate("/apanel/programs")}
                      className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                    >
                      <BookOpen className="h-3.5 w-3.5" />
                      Programs
                    </button>
                  </div>
                </div>
              </article>
            );
          })}
        </div>
      </section>
    );
  };

  const renderStaffManagementBoard = () => {
    if (resource !== "staff" || loading || dataList.length === 0) return null;

    const visibleActive = dataList.filter((item) => item.is_active).length;
    const visibleWithPhotos = dataList.filter((item) => item.photo || item.photo_url).length;
    const visibleDepartments = new Set(dataList.map((item) => item.department_id).filter(Boolean)).size;

    return (
      <section className="space-y-4">
        <div className="flex flex-col gap-3 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between">
          <div className="min-w-0">
            <p className="text-[10px] font-black uppercase tracking-widest text-primary">
              Staff Management
            </p>
            <h2 className="mt-1 text-xl font-black text-navy">
              Academic and leadership profiles
            </h2>
          </div>
          <div className="grid grid-cols-3 gap-2 text-center">
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-navy">{dataList.length}</p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Shown</p>
            </div>
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-emerald-600">{visibleActive}</p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Active</p>
            </div>
            <div className="rounded-2xl border border-gray-100 px-4 py-3">
              <p className="text-lg font-black text-cyan-600">{visibleWithPhotos}/{visibleDepartments}</p>
              <p className="text-[10px] font-black uppercase tracking-wider text-gray-400">Photos/Dept</p>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 gap-4 xl:grid-cols-2">
          {dataList.map((staff) => {
            const translation = getPrimaryTranslation(staff);
            const faculty = staffRelations.faculties[staff.faculty_id];
            const department = staffRelations.departments[staff.department_id];
            const facultyTranslation = getPrimaryTranslation(faculty);
            const departmentTranslation = getPrimaryTranslation(department);
            const displayName = translation.full_name || staff.full_name || staff.slug || `Staff #${staff.id}`;
            const position = translation.position || staff.position || "Staff profile";
            const portrait = publicAssetUrl(staff.photo_url || staff.photo || "");

            return (
              <article
                key={staff.id}
                className="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
              >
                <div className="flex min-w-0 gap-4">
                  <div className="h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-gray-100 bg-gradient-to-br from-primary-light to-cyan-50 shadow-inner">
                    {portrait ? (
                      <img
                        src={portrait}
                        alt={displayName}
                        className="h-full w-full object-cover"
                        loading="lazy"
                      />
                    ) : (
                      <div className="flex h-full w-full items-center justify-center text-lg font-black text-primary">
                        {getInitials(displayName)}
                      </div>
                    )}
                  </div>

                  <div className="min-w-0 flex-1">
                    <div className="flex items-start justify-between gap-3">
                      <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                          <h3 className="min-w-0 truncate text-lg font-black text-navy">
                            {displayName}
                          </h3>
                          <span
                            className={`rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider ${
                              staff.is_active
                                ? "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100"
                                : "bg-rose-50 text-rose-700 ring-1 ring-rose-100"
                            }`}
                          >
                            {staff.is_active ? "Active" : "Inactive"}
                          </span>
                        </div>
                        <p className="mt-1 truncate text-xs font-black uppercase tracking-wider text-primary">
                          {position}
                        </p>
                        <div className="mt-2 flex flex-wrap items-center gap-2 text-[11px] font-extrabold text-gray-400">
                          <span className="inline-flex min-w-0 items-center gap-1 rounded-full bg-gray-50 px-2.5 py-1">
                            <Hash className="h-3 w-3 shrink-0" />
                            <span className="truncate">{staff.slug || `ID ${staff.id}`}</span>
                          </span>
                          {portrait ? (
                            <span className="rounded-full bg-cyan-50 px-2.5 py-1 text-cyan-700">Photo ready</span>
                          ) : (
                            <span className="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700">No photo</span>
                          )}
                        </div>
                      </div>

                      <div className="flex shrink-0 items-center gap-1">
                        <button
                          type="button"
                          onClick={() => handleOpenEdit(staff)}
                          className="rounded-xl border border-gray-100 p-2 text-gray-500 transition-all hover:border-primary/20 hover:bg-primary-light hover:text-primary"
                          title={t("button.edit")}
                        >
                          <Pencil className="h-4 w-4" />
                        </button>
                        <button
                          type="button"
                          onClick={() => setDeleteTarget(staff)}
                          className="rounded-xl border border-gray-100 p-2 text-gray-500 transition-all hover:border-rose-100 hover:bg-rose-50 hover:text-rose-600"
                          title={t("button.delete")}
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                    </div>

                    <p className="mt-3 line-clamp-2 text-xs font-semibold leading-5 text-gray-500">
                      {translation.bio || "No biography entered yet."}
                    </p>

                    <div className="mt-4 grid gap-2 text-xs font-bold text-gray-500 sm:grid-cols-2">
                      <span className="flex min-w-0 items-center gap-2">
                        <GraduationCap className="h-3.5 w-3.5 shrink-0 text-primary" />
                        <span className="truncate">
                          {facultyTranslation.name || (staff.faculty_id ? `Faculty #${staff.faculty_id}` : "No faculty")}
                        </span>
                      </span>
                      <span className="flex min-w-0 items-center gap-2">
                        <Building2 className="h-3.5 w-3.5 shrink-0 text-primary" />
                        <span className="truncate">
                          {departmentTranslation.name || (staff.department_id ? `Department #${staff.department_id}` : "Faculty leadership")}
                        </span>
                      </span>
                      {staff.phone && (
                        <span className="flex min-w-0 items-center gap-2">
                          <Phone className="h-3.5 w-3.5 shrink-0 text-primary" />
                          <span className="truncate">{staff.phone}</span>
                        </span>
                      )}
                      {staff.email && (
                        <span className="flex min-w-0 items-center gap-2">
                          <Mail className="h-3.5 w-3.5 shrink-0 text-primary" />
                          <span className="truncate">{staff.email}</span>
                        </span>
                      )}
                    </div>

                    <div className="mt-5 flex flex-wrap items-center gap-2 border-t border-gray-50 pt-4">
                      <button
                        type="button"
                        onClick={() => navigate(`/profile/${staff.slug}`)}
                        className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                      >
                        <ExternalLink className="h-3.5 w-3.5" />
                        Public profile
                      </button>
                      <button
                        type="button"
                        onClick={() => navigate("/apanel/faculties")}
                        className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                      >
                        <GraduationCap className="h-3.5 w-3.5" />
                        Faculties
                      </button>
                      <button
                        type="button"
                        onClick={() => navigate("/apanel/departments")}
                        className="inline-flex items-center gap-1.5 rounded-xl border border-gray-100 px-3 py-2 text-[11px] font-black text-navy transition-all hover:bg-gray-50"
                      >
                        <Building2 className="h-3.5 w-3.5" />
                        Departments
                      </button>
                    </div>
                  </div>
                </div>
              </article>
            );
          })}
        </div>
      </section>
    );
  };

  return (
    <div className="space-y-6 animate-in fade-in duration-200">
      {/* Toast Notification */}
      {toast && (
        <div className={`fixed top-6 right-6 z-9999 flex items-center gap-3 rounded-2xl border px-5 py-3 text-xs font-bold text-white shadow-xl animate-in fade-in slide-in-from-top-4 duration-300 backdrop-blur-md ${
          toast.type === "error" 
            ? "bg-rose-950/90 border-rose-500/20" 
            : "bg-navy/90 border-navy/10"
        }`}>
          <div className={`h-2 w-2 rounded-full animate-pulse ${
            toast.type === "error" ? "bg-rose-500" : "bg-primary"
          }`} />
          {toast.message}
        </div>
      )}

      {/* Header title */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-navy uppercase tracking-wider">
            {schema.title}
          </h1>
          <p className="text-gray-400 text-xs font-semibold mt-1">
            {t("apanel.crud.resourceSlug")} /apanel/{resource}
          </p>
        </div>

        {formFields.length > 0 && (
          <button
            onClick={handleOpenCreate}
            className="w-full sm:w-auto bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-xl text-xs font-extrabold shadow-sm hover:shadow-md transition-all cursor-pointer inline-flex items-center justify-center gap-1.5 shrink-0"
          >
            <Plus className="w-4 h-4" />
            {t("apanel.crud.addNewRecord")}
          </button>
        )}
      </div>

      {error && <FormError message={error} />}

      <ApanelStatsCards items={resourceStats} />

      {/* Toolbar Search Bar */}
      <SearchFilterBar
        searchQuery={search}
        onSearchChange={(val) => {
          setSearch(val);
          setPage(1);
        }}
        onSearchSubmit={fetchRecords}
        filters={
          resource === "applications"
            ? [
                {
                  name: "status",
                  label: t("apanel.workflow.status"),
                  options: applicationStatusOptions.map((status) => ({
                    value: status,
                    label: status.replaceAll("_", " "),
                  })),
                },
              ]
            : []
        }
        activeFilters={activeFilters}
        onFilterChange={handleFilterChange}
      />

      {resource === "applications" && (
        <div className="bg-white border border-gray-100 p-4 rounded-3xl shadow-xs grid grid-cols-1 md:grid-cols-3 gap-3">
          <input
            type="number"
            value={activeFilters.program_id || ""}
            onChange={(e) => handleFilterChange("program_id", e.target.value)}
            placeholder={t("apanel.crud.programId")}
            className="px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
          />
          <input
            type="text"
            value={activeFilters.nationality || ""}
            onChange={(e) => handleFilterChange("nationality", e.target.value)}
            placeholder={t("apanel.workflow.nationality")}
            className="px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
          />
          <button
            type="button"
            onClick={() => setActiveFilters({})}
            className="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-extrabold text-navy hover:bg-gray-50 cursor-pointer"
          >
            {t("apanel.crud.clearFilters")}
          </button>
        </div>
      )}

      {renderFacultyManagementBoard()}
      {renderDepartmentManagementBoard()}
      {renderProgramManagementBoard()}
      {renderCourseManagementBoard()}
      {renderStaffManagementBoard()}

      {/* Table view */}
      {loading && dataList.length === 0 ? (
        <div className="flex items-center justify-center min-h-75">
          <Loader2 className="w-8 h-8 animate-spin text-primary" />
        </div>
      ) : (
        <div className="space-y-6">
          <DataTable
            columns={schema.columns}
            data={dataList}
            sortBy={sortBy}
            sortDir={sortDir}
            onSortChange={(key, dir) => {
              setSortBy(key);
              setSortDir(dir);
            }}
            onViewClick={handleViewAction}
            onEditClick={formFields.length > 0 ? handleOpenEdit : null}
            onDeleteClick={formFields.length > 0 ? setDeleteTarget : null}
            onStatusToggle={handleStatusToggle}
          />

          <Pagination
            currentPage={page}
            lastPage={lastPage}
            total={total}
            perPage={15}
            onPageChange={setPage}
          />
        </div>
      )}

      {/* Form Dialog Panel */}
      {isFormOpen && (
        <div className="fixed inset-0 z-9999 flex items-center justify-center bg-navy/45 p-3 backdrop-blur-xs sm:p-5">
          <div className="flex max-h-[calc(100vh-1.5rem)] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-2xl animate-in fade-in zoom-in-95 duration-200 sm:max-h-[calc(100vh-2.5rem)]">
            <div className="flex shrink-0 items-start justify-between gap-4 border-b border-gray-50 px-5 py-4 sm:px-6">
              <div className="min-w-0">
                <p className="text-[10px] font-black uppercase tracking-widest text-primary">
                  {schema.title}
                </p>
                <h3 className="mt-1 truncate text-lg font-black text-navy">
                  {editItem
                    ? `${t("apanel.crud.editRecord")} #${editItem.id}`
                    : t("apanel.crud.createNew")}
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setIsFormOpen(false)}
                className="rounded-xl border border-gray-100 p-2 text-gray-400 transition-all hover:bg-gray-50 hover:text-navy"
                title={t("button.cancel")}
              >
                <X className="h-4 w-4" />
              </button>
            </div>

            <div className="flex min-h-0 flex-1 flex-col px-5 py-4 sm:px-6">
            <FormBuilder
              fields={formFields}
              initialValues={editItem || {}}
              onSubmit={handleFormSubmit}
              onCancel={() => setIsFormOpen(false)}
              isSubmitting={isSubmitting}
              isEdit={!!editItem}
              validationErrors={validationErrors}
              modalMode
            />
            </div>
          </div>
        </div>
      )}

      {/* Confirm deletion warnings */}
      <ConfirmDialog
        isOpen={!!deleteTarget}
        title={t("apanel.crud.deleteRecordTitle")}
        message={`${t("apanel.crud.deleteRecordMessage")} ${schema.title} (#${deleteTarget?.id})`}
        onConfirm={handleDeleteConfirm}
        onCancel={() => setDeleteTarget(null)}
      />
    </div>
  );
}
