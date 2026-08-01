import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { apanelService } from "../../../services/apanelService";
import SearchFilterBar from "../components/SearchFilterBar";
import DataTable from "../components/DataTable";
import Pagination from "../components/Pagination";
import FormBuilder from "../components/FormBuilder";
import ConfirmDialog from "../components/ConfirmDialog";
import { Loader2, Plus } from "lucide-react";
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
  pages: {
    title: "apanel.crud.ui.title.pages",
    columns: [
      { key: "slug", label: "apanel.crud.ui.label.slug", sortable: true },
      { key: "template", label: "apanel.crud.ui.label.layoutTemplate" },
      { key: "is_published", label: "apanel.crud.ui.label.published", type: "boolean" },
    ],
    fields: [
      { name: "slug", label: "apanel.crud.ui.label.slug", type: "text", required: true },
      { name: "template", label: "apanel.crud.ui.label.templateLayout", type: "text" },
      { name: "is_published", label: "apanel.crud.ui.label.publishImmediately", type: "boolean" },
      { name: "sort_order", label: "apanel.crud.ui.label.sortOrder", type: "number" },
      {
        name: "title",
        label: "apanel.crud.ui.label.pageTitle",
        type: "text",
        required: true,
        translated: true,
      },
    ],
  },
  "page-blocks": {
    title: "apanel.crud.ui.title.pageBlocks",
    columns: [
      { key: "page_id", label: "apanel.crud.ui.label.pageId", sortable: true },
      { key: "block_key", label: "apanel.crud.ui.label.blockKey", sortable: true },
      { key: "type", label: "apanel.crud.ui.label.type" },
      { key: "is_active", label: "apanel.crud.ui.label.active", type: "boolean" },
    ],
    fields: [
      { name: "page_id", label: "apanel.crud.ui.label.pageIdKey", type: "number", required: true },
      {
        name: "block_key",
        label: "apanel.crud.ui.label.uniqueBlockIdentifier",
        type: "text",
        required: true,
      },
      {
        name: "type",
        label: "apanel.crud.ui.label.blockLayoutType",
        type: "text",
        required: true,
      },
      { name: "sort_order", label: "apanel.crud.ui.label.sortOrder", type: "number" },
      { name: "is_active", label: "apanel.crud.ui.label.activeStatus", type: "boolean" },
      {
        name: "title",
        label: "apanel.crud.ui.label.blockTitleHeader",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "content",
        label: "apanel.crud.ui.label.blockContentBody",
        type: "textarea",
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
      { name: "image", label: "apanel.crud.ui.label.bannerImage", type: "media" },
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
        type: "select",
        options: ["full_time", "part_time", "distance"],
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
        label: "apanel.crud.ui.label.courseOverviewDetails",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "requirements",
        label: "apanel.crud.ui.label.entryRequirementsChecklist",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "career_opportunities",
        label: "apanel.crud.ui.label.jobCareerProspects",
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
        <div className="fixed inset-0 z-9999 flex items-center justify-center bg-navy/40 backdrop-blur-xs p-4 overflow-y-auto">
          <div className="bg-white border border-gray-100 rounded-3xl max-w-3xl w-full p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200 my-8">
            <h3 className="font-extrabold text-navy text-lg border-b border-gray-50 pb-4 mb-6 uppercase tracking-wider">
              {editItem
                ? `${t("apanel.crud.editRecord")} ${schema.title} (#${editItem.id})`
                : `${t("apanel.crud.createNew")} ${schema.title}`}
            </h3>

            <FormBuilder
              fields={formFields}
              initialValues={editItem || {}}
              onSubmit={handleFormSubmit}
              onCancel={() => setIsFormOpen(false)}
              isSubmitting={isSubmitting}
              isEdit={!!editItem}
              validationErrors={validationErrors}
            />
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
