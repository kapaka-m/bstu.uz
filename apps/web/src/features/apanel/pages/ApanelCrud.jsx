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

// Config schemas for all whitelisted resources
const RESOURCE_SCHEMAS = {
  locales: {
    title: "Locales",
    columns: [
      { key: "code", label: "Code", sortable: true },
      { key: "name", label: "Name", sortable: true },
      { key: "native_name", label: "Native Name" },
      { key: "direction", label: "Direction" },
      { key: "is_active", label: "Active", type: "boolean" },
    ],
    fields: [
      { name: "code", label: "Locale Code", type: "text", required: true },
      { name: "name", label: "Name", type: "text", required: true },
      {
        name: "native_name",
        label: "Native Name",
        type: "text",
        required: true,
      },
      {
        name: "direction",
        label: "Direction",
        type: "select",
        options: ["ltr", "rtl"],
        required: true,
      },
      { name: "is_active", label: "Active Status", type: "boolean" },
      { name: "sort_order", label: "Sort Order", type: "number" },
    ],
  },
  "translation-keys": {
    title: "Translation Keys",
    columns: [
      { key: "group", label: "Group", sortable: true },
      { key: "key", label: "Key Name", sortable: true },
      { key: "description", label: "Description" },
      { key: "is_system", label: "System Key", type: "boolean" },
    ],
    fields: [
      { name: "group", label: "Group Name", type: "text", required: true },
      { name: "key", label: "Key Slug", type: "text", required: true },
      { name: "description", label: "Context Description", type: "textarea" },
      { name: "is_system", label: "System Core Key", type: "boolean" },
    ],
  },
  "translation-values": {
    title: "Translation Values",
    columns: [
      { key: "translation_key_id", label: "Key ID", sortable: true },
      { key: "locale", label: "Locale", sortable: true },
      { key: "value", label: "Text Value" },
    ],
    fields: [
      {
        name: "translation_key_id",
        label: "Key Reference ID",
        type: "number",
        required: true,
      },
      {
        name: "locale",
        label: "Locale Code",
        type: "select",
        options: ["en", "uz", "ru", "ar"],
        required: true,
      },
      { name: "value", label: "Value Label", type: "textarea", required: true },
    ],
  },
  settings: {
    title: "Global settings",
    columns: [
      { key: "key", label: "Key", sortable: true },
      { key: "value", label: "Value" },
      { key: "group", label: "Group", sortable: true },
      { key: "is_public", label: "Public Website", type: "boolean" },
    ],
    fields: [
      { name: "key", label: "Key", type: "text", required: true },
      { name: "value", label: "Value", type: "textarea" },
      { name: "group", label: "Settings Group", type: "text" },
      { name: "type", label: "Value Type", type: "text" },
      { name: "is_public", label: "Expose on Public Site", type: "boolean" },
    ],
  },
  menus: {
    title: "Navigation Menus",
    columns: [
      { key: "key", label: "Key", sortable: true },
      { key: "location", label: "Position", sortable: true },
      { key: "is_active", label: "Active", type: "boolean" },
    ],
    fields: [
      { name: "key", label: "Key Code", type: "text", required: true },
      {
        name: "location",
        label: "Theme Location",
        type: "text",
        required: true,
      },
      { name: "is_active", label: "Active", type: "boolean" },
    ],
  },
  "menu-items": {
    title: "Menu Items",
    columns: [
      { key: "menu_id", label: "Menu ID", sortable: true },
      { key: "route_name", label: "Route Link", sortable: true },
      { key: "sort_order", label: "Sort", sortable: true },
      { key: "is_active", label: "Active", type: "boolean" },
    ],
    fields: [
      {
        name: "menu_id",
        label: "Parent Menu ID",
        type: "number",
        required: true,
      },
      { name: "parent_id", label: "Sub-menu Parent ID", type: "number" },
      { name: "route_name", label: "React Router Path", type: "text" },
      { name: "url", label: "External URL Link", type: "text" },
      { name: "icon", label: "Lucide Icon Name", type: "text" },
      { name: "sort_order", label: "Sort Index", type: "number" },
      { name: "is_active", label: "Active", type: "boolean" },
      {
        name: "title",
        label: "Nav Label Title",
        type: "text",
        required: true,
        translated: true,
      },
    ],
  },
  pages: {
    title: "Pages",
    columns: [
      { key: "slug", label: "Slug", sortable: true },
      { key: "template", label: "Layout Template" },
      { key: "is_published", label: "Published", type: "boolean" },
    ],
    fields: [
      { name: "slug", label: "Slug", type: "text", required: true },
      { name: "template", label: "Template layout", type: "text" },
      { name: "is_published", label: "Publish Immediately", type: "boolean" },
      { name: "sort_order", label: "Sort Order", type: "number" },
      {
        name: "title",
        label: "Page Title",
        type: "text",
        required: true,
        translated: true,
      },
    ],
  },
  "page-blocks": {
    title: "Page Blocks",
    columns: [
      { key: "page_id", label: "Page ID", sortable: true },
      { key: "block_key", label: "Block Key", sortable: true },
      { key: "type", label: "Type" },
      { key: "is_active", label: "Active", type: "boolean" },
    ],
    fields: [
      { name: "page_id", label: "Page ID Key", type: "number", required: true },
      {
        name: "block_key",
        label: "Unique block identifier",
        type: "text",
        required: true,
      },
      {
        name: "type",
        label: "Block layout type",
        type: "text",
        required: true,
      },
      { name: "sort_order", label: "Sort Order", type: "number" },
      { name: "is_active", label: "Active Status", type: "boolean" },
      {
        name: "title",
        label: "Block Title Header",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "content",
        label: "Block Content Body",
        type: "textarea",
        required: true,
        translated: true,
      },
    ],
  },
  faculties: {
    title: "Faculties",
    columns: [
      { key: "translations.0.name", label: "Name" },
      { key: "slug", label: "Slug", sortable: true },
      { key: "code", label: "Code", sortable: true },
      { key: "is_active", label: "Active", type: "boolean" },
    ],
    fields: [
      { name: "slug", label: "Slug Url", type: "text", required: true },
      { name: "code", label: "Faculty code", type: "text" },
      { name: "image", label: "Banner Image", type: "media" },
      { name: "icon", label: "Lucide Icon Code", type: "text" },
      { name: "sort_order", label: "Sort Index", type: "number" },
      { name: "is_active", label: "Active Status", type: "boolean" },
      {
        name: "name",
        label: "Faculty Name",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "description",
        label: "About Overview",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "content_sections",
        label: "Rich Content Sections JSON",
        type: "json",
        translated: true,
      },
    ],
  },
  departments: {
    title: "Departments",
    columns: [
      { key: "translations.0.name", label: "Name" },
      { key: "faculty_id", label: "Faculty ID", sortable: true },
      { key: "slug", label: "Slug", sortable: true },
      { key: "is_active", label: "Active", type: "boolean" },
    ],
    fields: [
      {
        name: "faculty_id",
        label: "Parent Faculty ID",
        type: "number",
        required: true,
      },
      { name: "slug", label: "Slug Url", type: "text", required: true },
      { name: "code", label: "Department code", type: "text" },
      { name: "image", label: "Featured Image", type: "media" },
      { name: "icon", label: "Lucide Icon Code", type: "text" },
      { name: "head_name", label: "Department Head Name", type: "text" },
      { name: "email", label: "Department Contact Email", type: "email" },
      { name: "phone", label: "Department Contact Phone", type: "text" },
      {
        name: "reception_time",
        label: "Reception Time",
        type: "text",
      },
      { name: "source_url", label: "Verification Source URL", type: "text" },
      { name: "sort_order", label: "Sort Index", type: "number" },
      { name: "is_active", label: "Active Status", type: "boolean" },
      {
        name: "name",
        label: "Department Name",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "description",
        label: "Curriculum Intro",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "content_sections",
        label: "Rich Content Sections JSON",
        type: "json",
        translated: true,
      },
    ],
  },
  programs: {
    title: "Study Programs",
    columns: [
      { key: "translations.0.name", label: "Name" },
      { key: "department_id", label: "Dept ID", sortable: true },
      { key: "degree", label: "Degree", sortable: true },
      { key: "duration_years", label: "Years", sortable: true },
      { key: "is_active", label: "Active", type: "boolean" },
    ],
    fields: [
      {
        name: "faculty_id",
        label: "Faculty ID",
        type: "number",
        required: true,
      },
      {
        name: "department_id",
        label: "Department ID",
        type: "number",
        required: true,
      },
      { name: "slug", label: "Slug URL", type: "text", required: true },
      { name: "code", label: "Program code", type: "text" },
      { name: "official_code", label: "Official Public Code", type: "text" },
      { name: "track", label: "Specialization Track", type: "text" },
      {
        name: "degree",
        label: "Degree level",
        type: "select",
        options: ["bachelor", "master", "phd"],
        required: true,
      },
      {
        name: "duration_years",
        label: "Duration in Years",
        type: "number",
        required: true,
      },
      {
        name: "study_mode",
        label: "Study Mode",
        type: "select",
        options: ["full_time", "part_time", "distance"],
        required: true,
      },
      {
        name: "language_of_study",
        label: "Language of study",
        type: "checkbox-group",
        options: [
          { value: "english", label: "English" },
          { value: "uzbek", label: "Uzbek" },
          { value: "russian", label: "Russian" },
          { value: "arabic", label: "Arabic" }
        ],
        required: true,
      },
      {
        name: "tuition_fee",
        label: "Tuition Fee Cost",
        type: "number",
        required: true,
      },
      { name: "currency", label: "ISO Currency", type: "text" },
      { name: "image", label: "Featured Image Banner", type: "media" },
      { name: "is_active", label: "Active Status", type: "boolean" },
      { name: "sort_order", label: "Sort Index", type: "number" },
      {
        name: "name",
        label: "Program Name",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "description",
        label: "Course Overview Details",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "requirements",
        label: "Entry Requirements Checklist",
        type: "textarea",
        required: true,
        translated: true,
      },
      {
        name: "career_opportunities",
        label: "Job Career Prospects",
        type: "textarea",
        required: true,
        translated: true,
      },
    ],
  },
  courses: {
    title: "Courses",
    columns: [
      { key: "translations.0.name", label: "Course Name" },
      { key: "code", label: "Code", sortable: true },
      { key: "credits", label: "Credits", sortable: true },
      { key: "semester", label: "Semester", sortable: true },
      { key: "is_active", label: "Active", type: "boolean" },
    ],
    fields: [
      { name: "code", label: "Course code", type: "text", required: true },
      {
        name: "credits",
        label: "ECTS credits count",
        type: "number",
        required: true,
      },
      {
        name: "semester",
        label: "Active Semester Year",
        type: "number",
        required: true,
      },
      { name: "is_active", label: "Active Status", type: "boolean" },
      {
        name: "name",
        label: "Course Subject Title",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "description",
        label: "Course Description",
        type: "textarea",
        required: true,
        translated: true,
      },
    ],
  },
  staff: {
    title: "Staff & Academics Profiles",
    columns: [
      { key: "translations.0.full_name", label: "Full Name" },
      { key: "translations.0.position", label: "Position" },
      { key: "email", label: "Email Address", sortable: true },
      { key: "is_active", label: "Active", type: "boolean" },
    ],
    fields: [
      { name: "slug", label: "Public Profile Slug", type: "text" },
      { name: "faculty_id", label: "Associated Faculty ID", type: "number" },
      {
        name: "department_id",
        label: "Associated Department ID",
        type: "number",
      },
      { name: "photo", label: "Staff Portrait Image", type: "media" },
      { name: "email", label: "Email Address", type: "email" },
      { name: "phone", label: "Mobile Phone Number", type: "text" },
      { name: "sort_order", label: "Sort rank index", type: "number" },
      { name: "is_active", label: "Active Profile", type: "boolean" },
      {
        name: "full_name",
        label: "Staff Full Name",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "position",
        label: "Job Academic Title",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "bio",
        label: "Biographical Overview",
        type: "textarea",
        required: true,
        translated: true,
      },
    ],
  },
  services: {
    title: "Campus Services",
    columns: [
      { key: "slug", label: "Slug Url", sortable: true },
      { key: "is_active", label: "Active Status", type: "boolean" },
    ],
    fields: [
      { name: "slug", label: "Slug Url link", type: "text", required: true },
      { name: "icon", label: "Lucide Icon Code", type: "text" },
      { name: "image", label: "Cover Picture Banner", type: "media" },
      { name: "sort_order", label: "Sort index", type: "number" },
      { name: "is_active", label: "Active Status", type: "boolean" },
      {
        name: "title",
        label: "Service Title Header",
        type: "text",
        required: true,
        translated: true,
      },
      {
        name: "description",
        label: "Service Details body",
        type: "textarea",
        required: true,
        translated: true,
      },
    ],
  },
  users: {
    title: "System Users",
    columns: [
      { key: "id", label: "ID", sortable: true },
      { key: "name", label: "Name", sortable: true },
      { key: "email", label: "Email Address", sortable: true },
    ],
    fields: [
      {
        name: "name",
        label: "Display Full Name",
        type: "text",
        required: true,
      },
      {
        name: "email",
        label: "Login Email Address",
        type: "email",
        required: true,
      },
      {
        name: "password",
        label: "Account Password",
        type: "password",
        required: false,
      },
    ],
  },
  roles: {
    title: "System Roles",
    columns: [
      { key: "name", label: "Name", sortable: true },
      { key: "slug", label: "Role Code Slug", sortable: true },
      { key: "description", label: "Role Scope Details" },
    ],
    fields: [
      {
        name: "name",
        label: "Role Display Label",
        type: "text",
        required: true,
      },
      { name: "slug", label: "Role Code Key", type: "text", required: true },
      { name: "description", label: "Role Scope Context", type: "textarea" },
    ],
  },
  permissions: {
    title: "Security Permissions",
    columns: [
      { key: "name", label: "Name", sortable: true },
      { key: "slug", label: "Permission Slug", sortable: true },
      { key: "description", label: "Access Description" },
    ],
    fields: [
      { name: "name", label: "Permission Label", type: "text", required: true },
      {
        name: "slug",
        label: "Permission Code key",
        type: "text",
        required: true,
      },
      { name: "description", label: "Access scopes context", type: "textarea" },
    ],
  },
  students: {
    title: "Admissions Students Profiles",
    columns: [
      { key: "passport_number", label: "Passport Number", sortable: true },
      { key: "nationality", label: "Country", sortable: true },
      { key: "phone", label: "Phone Number" },
    ],
    fields: [
      {
        name: "user_id",
        label: "User Account ID",
        type: "number",
        required: true,
      },
      { name: "phone", label: "Phone Number", type: "text", required: true },
      {
        name: "gender",
        label: "Gender",
        type: "select",
        options: ["male", "female"],
        required: true,
      },
      { name: "birth_date", label: "Birth Date", type: "date", required: true },
      {
        name: "passport_number",
        label: "Passport Code ID",
        type: "text",
        required: true,
      },
      {
        name: "nationality",
        label: "Country Origin",
        type: "text",
        required: true,
      },
      {
        name: "address",
        label: "Home Address",
        type: "textarea",
        required: true,
      },
    ],
  },
  applications: {
    title: "Admissions Applications",
    columns: [
      { key: "id", label: "ID", sortable: true },
      {
        key: "studentProfile.user.name",
        label: "Student Name",
        sortable: false,
      },
      { key: "studentProfile.user.email", label: "Email", sortable: false },
      {
        key: "studentProfile.nationality",
        label: "Nationality",
        sortable: false,
      },
      { key: "program.translations.0.name", label: "Program", sortable: false },
      { key: "status", label: "Status", sortable: true },
    ],
    fields: [
      {
        name: "student_profile_id",
        label: "Student Profile Reference ID",
        type: "number",
        required: true,
      },
      {
        name: "program_id",
        label: "Program Reference ID",
        type: "number",
        required: true,
      },
      { name: "faculty_id", label: "Faculty Reference ID", type: "number" },
      {
        name: "department_id",
        label: "Department Reference ID",
        type: "number",
      },
      {
        name: "degree_level",
        label: "Degree Level",
        type: "select",
        options: ["bachelor", "master", "phd"],
      },
      { name: "language_of_study", label: "Language of Study", type: "text" },
      { name: "study_mode", label: "Study Mode", type: "text" },
      {
        name: "status",
        label: "Admissions Status",
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
    title: "Application Documents",
    columns: [
      { key: "application_id", label: "App ID", sortable: true },
      { key: "document_name", label: "Document Name", sortable: true },
      { key: "document_type", label: "Type", sortable: true },
      { key: "status", label: "Status", sortable: true },
      { key: "file_path", label: "File Path" },
    ],
    fields: [
      {
        name: "application_id",
        label: "Parent Application ID Reference",
        type: "number",
        required: true,
      },
      {
        name: "document_name",
        label: "Verified Document Name Title",
        type: "text",
        required: true,
      },
      {
        name: "document_type",
        label: "Document Type",
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
        label: "Uploaded File Path",
        type: "media",
        required: true,
      },
      {
        name: "status",
        label: "Document Status",
        type: "select",
        options: ["pending", "approved", "rejected", "requested"],
      },
      { name: "note", label: "Review Note", type: "textarea" },
    ],
  },
  contracts: {
    title: "Billing Contracts",
    columns: [
      { key: "contract_number", label: "Contract Number", sortable: true },
      { key: "amount", label: "Amount Price", sortable: true },
      { key: "status", label: "Contract Status", sortable: true },
    ],
    fields: [
      {
        name: "application_id",
        label: "Associated Application ID",
        type: "number",
        required: true,
      },
      {
        name: "contract_number",
        label: "Contract Unique Code Number",
        type: "text",
        required: true,
      },
      {
        name: "amount",
        label: "Yearly Contract Tuition Amount",
        type: "number",
        required: true,
      },
      {
        name: "status",
        label: "Contract Status",
        type: "select",
        options: ["pending", "active", "signed", "cancelled"],
        required: true,
      },
    ],
  },
  payments: {
    title: "Payment Slips",
    columns: [
      { key: "payment_number", label: "Payment Slip #", sortable: true },
      { key: "amount", label: "Amount Paid", sortable: true },
      { key: "payment_date", label: "Payment Date" },
      { key: "status", label: "Payment Status", sortable: true },
    ],
    fields: [
      {
        name: "contract_id",
        label: "Parent Contract ID",
        type: "number",
        required: true,
      },
      {
        name: "payment_number",
        label: "Payment Receipt code #",
        type: "text",
        required: true,
      },
      {
        name: "amount",
        label: "Tuition Amount paid",
        type: "number",
        required: true,
      },
      {
        name: "payment_date",
        label: "Slip Payment Date",
        type: "date",
        required: true,
      },
      {
        name: "status",
        label: "Payment Status",
        type: "select",
        options: ["pending", "paid", "partially_paid", "rejected", "cancelled"],
        required: true,
      },
    ],
  },
  inquiries: {
    title: "Admissions Inquiries",
    columns: [
      { key: "name", label: "Contact Name", sortable: true },
      { key: "email", label: "Email Address", sortable: true },
      { key: "subject", label: "Query Subject" },
      {
        key: "status",
        label: "Status",
        type: "select",
        options: ["pending", "resolved"],
      },
    ],
    fields: [
      {
        name: "name",
        label: "Inquirer Full Name",
        type: "text",
        required: true,
      },
      { name: "email", label: "Inquirer Email", type: "email", required: true },
      {
        name: "subject",
        label: "Message subject",
        type: "text",
        required: true,
      },
      {
        name: "message",
        label: "Message Body query details",
        type: "textarea",
        required: true,
      },
      {
        name: "status",
        label: "Support Status",
        type: "select",
        options: ["pending", "resolved"],
        required: true,
      },
    ],
  },
  "support-tickets": {
    title: "Admissions Support Tickets",
    columns: [
      { key: "user_id", label: "User ID", sortable: true },
      { key: "subject", label: "Ticket Subject" },
      { key: "status", label: "Status" },
      { key: "priority", label: "Priority" },
    ],
    fields: [
      {
        name: "user_id",
        label: "Author User ID",
        type: "number",
        required: true,
      },
      {
        name: "subject",
        label: "Ticket subject title",
        type: "text",
        required: true,
      },
      {
        name: "status",
        label: "Ticket Status",
        type: "select",
        options: ["open", "closed", "pending"],
        required: true,
      },
      {
        name: "priority",
        label: "Urgency priority",
        type: "select",
        options: ["low", "normal", "high"],
        required: true,
      },
    ],
  },
  comments: {
    title: "News Comments",
    columns: [
      { key: "user_id", label: "User ID", sortable: true },
      { key: "commentable_type", label: "Target Context" },
      { key: "content", label: "Comment Content" },
    ],
    fields: [
      { name: "user_id", label: "Author User ID", type: "number" },
      {
        name: "commentable_type",
        label: "Context Type Model",
        type: "text",
        required: true,
      },
      {
        name: "commentable_id",
        label: "Context Object ID",
        type: "number",
        required: true,
      },
      {
        name: "content",
        label: "Comment content body text",
        type: "textarea",
        required: true,
      },
    ],
  },
  notifications: {
    title: "Student Alert Notifications",
    columns: [
      { key: "user_id", label: "User ID", sortable: true },
      { key: "title", label: "Title Alert", sortable: true },
      { key: "is_read", label: "Is Read", type: "boolean" },
    ],
    fields: [
      {
        name: "user_id",
        label: "Student User ID",
        type: "number",
        required: true,
      },
      {
        name: "title",
        label: "Alert Notification Title",
        type: "text",
        required: true,
      },
      {
        name: "message",
        label: "Alert message text content",
        type: "textarea",
        required: true,
      },
      {
        name: "is_read",
        label: "Notification Read by student",
        type: "boolean",
      },
    ],
  },
  "audit-logs": {
    title: "System Audit Logs (Read Only)",
    columns: [
      { key: "id", label: "Log ID", sortable: true },
      { key: "user_id", label: "Admin ID", sortable: true },
      { key: "action", label: "CRUD Action", sortable: true },
      { key: "model_type", label: "Updated Model Table" },
      { key: "model_id", label: "Record ID" },
      { key: "ip_address", label: "IP address" },
    ],
    fields: [],
  },
  "application-status-histories": {
    title: "Application Status History",
    columns: [
      { key: "application_id", label: "Application ID", sortable: true },
      { key: "old_status", label: "Old Status" },
      { key: "new_status", label: "New Status" },
      { key: "status", label: "Status", sortable: true },
      { key: "changed_by", label: "Changed By" },
    ],
    fields: [
      {
        name: "application_id",
        label: "Application ID",
        type: "number",
        required: true,
      },
      { name: "old_status", label: "Old Status", type: "text" },
      { name: "new_status", label: "New Status", type: "text" },
      {
        name: "status",
        label: "Status",
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
      { name: "comment", label: "Comment", type: "textarea" },
      { name: "note", label: "Note", type: "textarea" },
      {
        name: "changed_by",
        label: "Changed By User ID",
        type: "number",
        required: true,
      },
    ],
  },
};

export default function ApanelCrud() {
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
      setError("Failed to fetch whitelisted resource listings.");
    } finally {
      setLoading(false);
    }
  }, [resource, search, page, sortBy, sortDir, activeFilters]);

  useEffect(() => {
    fetchRecords();
  }, [fetchRecords]);

  const schema = RESOURCE_SCHEMAS[resource];
  if (!schema) {
    return (
      <div className="bg-white border border-gray-100 rounded-3xl p-8 text-center text-rose-600 font-bold shadow-xs">
        Resource config '{resource}' is not defined or whitelisted.
      </div>
    );
  }

  // Handle item status toggle directly from table
  const handleStatusToggle = async (id, statusField, nextVal) => {
    try {
      setError("");
      const item = dataList.find((d) => d.id === id);
      if (!item) return;

      const payload = { ...item, [statusField]: nextVal };
      await apanelService.update(resource, id, payload);
      showToast("Status updated successfully!");
      fetchRecords();
    } catch {
      setError("Failed to toggle status flag.");
      showToast("Failed to toggle status flag.", "error");
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
      setError("Failed to retrieve record details.");
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
        showToast("Record updated successfully!");
      } else {
        await apanelService.create(resource, formState);
        showToast("Record created successfully!");
      }

      setIsFormOpen(false);
      setEditItem(null);
      fetchRecords();
    } catch (err) {
      if (err?.status === 422 && err?.errors) {
        setValidationErrors(err.errors);
        showToast("Please correct the highlighted validation errors.", "error");
      } else {
        const errMsg = err?.message || "Failed to save record.";
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
      showToast("Record deleted successfully!");
      setDeleteTarget(null);
      fetchRecords();
    } catch {
      setError("Failed to delete record.");
      showToast("Failed to delete record.", "error");
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
            Resource slug: /apanel/{resource}
          </p>
        </div>

        {schema.fields.length > 0 && (
          <button
            onClick={handleOpenCreate}
            className="w-full sm:w-auto bg-primary hover:bg-primary-hover text-white px-5 py-2.5 rounded-xl text-xs font-extrabold shadow-sm hover:shadow-md transition-all cursor-pointer inline-flex items-center justify-center gap-1.5 shrink-0"
          >
            <Plus className="w-4 h-4" />
            Add New Record
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
                  label: "Status",
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
            placeholder="Program ID"
            className="px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
          />
          <input
            type="text"
            value={activeFilters.nationality || ""}
            onChange={(e) => handleFilterChange("nationality", e.target.value)}
            placeholder="Nationality"
            className="px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-primary text-xs font-semibold bg-white text-navy"
          />
          <button
            type="button"
            onClick={() => setActiveFilters({})}
            className="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-extrabold text-navy hover:bg-gray-50 cursor-pointer"
          >
            Clear Filters
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
            onEditClick={schema.fields.length > 0 ? handleOpenEdit : null}
            onDeleteClick={schema.fields.length > 0 ? setDeleteTarget : null}
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
                ? `Edit ${schema.title} Record (#${editItem.id})`
                : `Create New ${schema.title}`}
            </h3>

            <FormBuilder
              fields={schema.fields}
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
        title="Delete Record?"
        message={`Are you sure you want to delete this ${schema.title} record (#${deleteTarget?.id})? This is irreversible and will delete any related translations.`}
        onConfirm={handleDeleteConfirm}
        onCancel={() => setDeleteTarget(null)}
      />
    </div>
  );
}
