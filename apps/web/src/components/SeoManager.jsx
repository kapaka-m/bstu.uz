import React, { useEffect, useMemo, useState } from "react";
import { useLocation } from "react-router-dom";
import { publicAssetUrl } from "../lib/api";
import { useAppData } from "../context/AppDataContext";
import { useLanguage } from "../context/LanguageContext";
import { facultyService } from "../services/facultyService";
import { departmentService } from "../services/departmentService";
import { programService } from "../services/programService";
import { centerService } from "../services/centerService";
import { administrationService } from "../services/administrationService";
import { staffService } from "../services/staffService";
import { newsService } from "../services/newsService";
import { announcementService } from "../services/announcementService";
import { blogService } from "../services/blogService";
import { greenCampusService } from "../services/greenCampusService";

const SITE_SUFFIX = "BSTU International";

const STATIC_META = {
  en: {
    home: ["Bukhara State Technical University", "International admissions, academic programs, university services, news, and student resources at Bukhara State Technical University."],
    about: ["About Bukhara State Technical University", "Learn about BSTU, its mission, faculties, academic structure, leadership, and international development."],
    programs: ["Academic Programs", "Explore bachelor, master, and doctoral study programs at Bukhara State Technical University."],
    services: ["Interactive Services", "Access digital university services, student platforms, applications, registrar services, and support resources."],
    announcements: ["Announcements", "Official announcements, notices, and updates from Bukhara State Technical University."],
    news: ["News and Events", "Latest news, events, academic updates, and institutional stories from BSTU."],
    blog: ["Blog", "Articles, insights, research stories, and university updates from BSTU departments and publishers."],
    publishers: ["Publishers", "Browse university publishers, departments, and official content sources at BSTU."],
    video: ["Video Gallery", "Watch official BSTU video stories, campus highlights, academic events, and media updates."],
    green: ["Green Campus", "Discover BSTU sustainability initiatives, green campus projects, environmental activities, and impact stories."],
    contact: ["Contact", "Contact Bukhara State Technical University for admissions, cooperation, services, and general inquiries."],
    apply: ["Apply Online", "Submit your online application to study at Bukhara State Technical University."],
    login: ["Login", "Sign in to access your BSTU student account and admission services."],
    forgot: ["Forgot Password", "Recover access to your BSTU account securely."],
    reset: ["Reset Password", "Create a new password for your BSTU account."],
    notFound: ["Page Not Found", "The requested page could not be found on the BSTU International website."],
  },
  ar: {
    home: ["جامعة بخارى الحكومية التقنية", "القبول الدولي والبرامج الأكاديمية والخدمات الجامعية والأخبار وموارد الطلاب في جامعة بخارى الحكومية التقنية."],
    about: ["عن جامعة بخارى الحكومية التقنية", "تعرف على جامعة بخارى الحكومية التقنية ورسالتها وكلياتها وهيكلها الأكاديمي وقيادتها وتطورها الدولي."],
    programs: ["البرامج الأكاديمية", "استعرض برامج البكالوريوس والماجستير والدكتوراه في جامعة بخارى الحكومية التقنية."],
    services: ["الخدمات التفاعلية", "الوصول إلى الخدمات الرقمية الجامعية ومنصات الطلاب والتقديم وخدمات شؤون الطلاب والدعم."],
    announcements: ["الإعلانات", "الإعلانات والتنبيهات والتحديثات الرسمية من جامعة بخارى الحكومية التقنية."],
    news: ["الأخبار والفعاليات", "آخر الأخبار والفعاليات والتحديثات الأكاديمية وقصص الجامعة."],
    blog: ["المدونة", "مقالات ورؤى وقصص بحثية وتحديثات جامعية من أقسام وناشري جامعة بخارى الحكومية التقنية."],
    publishers: ["الناشرون", "استعرض الجهات والأقسام والناشرين الرسميين لمحتوى الجامعة."],
    video: ["معرض الفيديو", "شاهد فيديوهات الجامعة الرسمية وفعالياتها ولمحات من الحرم الجامعي."],
    green: ["الحرم الجامعي الأخضر", "تعرف على مبادرات الاستدامة والمشروعات البيئية في جامعة بخارى الحكومية التقنية."],
    contact: ["تواصل معنا", "تواصل مع جامعة بخارى الحكومية التقنية للقبول والتعاون والخدمات والاستفسارات العامة."],
    apply: ["التقديم الإلكتروني", "قدّم طلبك للدراسة في جامعة بخارى الحكومية التقنية عبر الإنترنت."],
    login: ["تسجيل الدخول", "سجّل الدخول للوصول إلى حساب الطالب وخدمات القبول في جامعة بخارى الحكومية التقنية."],
    forgot: ["نسيت كلمة المرور", "استعد الوصول إلى حسابك في جامعة بخارى الحكومية التقنية بأمان."],
    reset: ["إعادة تعيين كلمة المرور", "أنشئ كلمة مرور جديدة لحسابك في جامعة بخارى الحكومية التقنية."],
    notFound: ["الصفحة غير موجودة", "الصفحة المطلوبة غير موجودة في موقع جامعة بخارى الحكومية التقنية الدولي."],
  },
};

const FALLBACK_LOCALE = "en";

const routeTitles = {
  "/student/dashboard": "Student Dashboard",
  "/student/profile": "Student Profile",
  "/student/application": "Student Application",
  "/student/academic-information": "Academic Information",
  "/student/documents": "Student Documents",
  "/student/payments": "Payments",
  "/student/admission": "Admission",
  "/student/enrollment": "Enrollment",
  "/student/prikaz": "Prikaz",
  "/student/service-fee": "Service Fee",
  "/student/visa": "Visa",
  "/student/housing": "Housing",
  "/student/residence": "Residence",
  "/student/notifications": "Notifications",
  "/student/support": "Support Center",
  "/apanel": "Admin Panel",
  "/apanel/login": "Admin Login",
};

const cleanText = (value = "", max = 165) =>
  String(value || "")
    .replace(/\s+/g, " ")
    .trim()
    .slice(0, max);

const humanizeSlug = (value = "") =>
  String(value || "")
    .replace(/\.[a-z0-9]+$/i, "")
    .replace(/[-_]+/g, " ")
    .replace(/\s+/g, " ")
    .trim()
    .replace(/\b\w/g, (char) => char.toUpperCase());

const setMeta = (selector, attributes) => {
  let element = document.head.querySelector(selector);
  if (!element) {
    element = document.createElement("meta");
    document.head.appendChild(element);
  }
  Object.entries(attributes).forEach(([key, value]) => {
    if (value !== undefined && value !== null) element.setAttribute(key, String(value));
  });
};

const setCanonical = (href) => {
  let link = document.head.querySelector('link[rel="canonical"]');
  if (!link) {
    link = document.createElement("link");
    link.setAttribute("rel", "canonical");
    document.head.appendChild(link);
  }
  link.setAttribute("href", href);
};

const buildTitle = (title, siteName) => {
  const cleanTitle = cleanText(title, 80);
  const cleanSite = cleanText(siteName || SITE_SUFFIX, 80);
  if (!cleanTitle || cleanTitle === cleanSite) return cleanSite;
  return `${cleanTitle} | ${cleanSite}`;
};

const pickStatic = (locale, key) => {
  const dict = STATIC_META[locale] || STATIC_META[FALLBACK_LOCALE];
  return dict[key] || STATIC_META[FALLBACK_LOCALE][key] || STATIC_META[FALLBACK_LOCALE].home;
};

function getStaticMeta(pathname, locale) {
  if (pathname === "/") return pickStatic(locale, "home");
  if (pathname === "/about") return pickStatic(locale, "about");
  if (pathname === "/programs") return pickStatic(locale, "programs");
  if (pathname === "/services") return pickStatic(locale, "services");
  if (pathname === "/announcements") return pickStatic(locale, "announcements");
  if (pathname === "/news") return pickStatic(locale, "news");
  if (pathname === "/blog") return pickStatic(locale, "blog");
  if (pathname === "/publishers") return pickStatic(locale, "publishers");
  if (pathname === "/video-bdtu") return pickStatic(locale, "video");
  if (pathname === "/green-campus") return pickStatic(locale, "green");
  if (pathname === "/contact") return pickStatic(locale, "contact");
  if (pathname === "/apply") return pickStatic(locale, "apply");
  if (pathname === "/login") return pickStatic(locale, "login");
  if (pathname === "/forgot-password") return pickStatic(locale, "forgot");
  if (pathname === "/reset-password") return pickStatic(locale, "reset");
  return null;
}

const metaFromItem = (item, fallbackTitle) => {
  const title = item?.title || item?.name || item?.full_name || item?.head || fallbackTitle;
  const description =
    item?.summary ||
    item?.excerpt ||
    item?.description ||
    item?.about ||
    item?.bio ||
    item?.overview ||
    item?.content ||
    "";
  const image = item?.image || item?.img || item?.photo_url || item?.photo || item?.banner_image || "";
  return { title, description, image };
};

async function loadDynamicMeta(pathname, locale) {
  const [, section, id] = pathname.split("/");
  if (!id) return null;

  if (section === "faculty") return metaFromItem(await facultyService.getFaculty(id), humanizeSlug(id));
  if (section === "department") return metaFromItem(await departmentService.getDepartment(id), humanizeSlug(id));
  if (section === "programs") return metaFromItem(await programService.getProgram(id), humanizeSlug(id));
  if (section === "center") return metaFromItem(await centerService.getCenter(id), humanizeSlug(id));
  if (section === "news") return metaFromItem(await newsService.getNewsItem(id), humanizeSlug(id));
  if (section === "announcements") return metaFromItem(await announcementService.getAnnouncement(id), humanizeSlug(id));
  if (section === "green-campus") return metaFromItem(await greenCampusService.getArticle(id), humanizeSlug(id));
  if (section === "publishers") return metaFromItem(await blogService.getPublisher(id), humanizeSlug(id));
  if (section === "blog" && pathname.startsWith("/blog/departments/")) {
    const departmentSlug = pathname.split("/")[3];
    return metaFromItem(await blogService.getDepartment(departmentSlug), humanizeSlug(departmentSlug));
  }
  if (section === "blog") return metaFromItem(await blogService.getBlogItem(id), humanizeSlug(id));
  if (section === "profile") {
    const profile = await administrationService
      .getProfile(id, locale)
      .catch(() => staffService.getStaffProfile(id));
    return metaFromItem(profile, humanizeSlug(id));
  }

  return null;
}

export default function SeoManager() {
  const location = useLocation();
  const { locale, settings } = useLanguage();
  const { faculties, departments, programs, services, greenCampusArticles } = useAppData();
  const [dynamicMeta, setDynamicMeta] = useState(null);

  const pathname = location.pathname.replace(/\/+$/, "") || "/";
  const siteName = settings.site_name || SITE_SUFFIX;
  const isPrivate =
    pathname.startsWith("/student") ||
    pathname.startsWith("/apanel") ||
    pathname === "/login" ||
    pathname === "/forgot-password" ||
    pathname === "/reset-password";

  const routeMeta = useMemo(() => {
    const staticMeta = getStaticMeta(pathname, locale);
    if (staticMeta) return { title: staticMeta[0], description: staticMeta[1] };

    const [, section, id] = pathname.split("/");
    const collections = {
      faculty: faculties,
      department: departments,
      programs,
      services,
      "green-campus": greenCampusArticles,
    };
    const match = collections[section]?.find((item) => String(item.slug || item.id) === id);
    if (match) return metaFromItem(match, humanizeSlug(id));

    if (routeTitles[pathname]) {
      return {
        title: routeTitles[pathname],
        description: `${routeTitles[pathname]} for Bukhara State Technical University.`,
      };
    }

    if (pathname.startsWith("/apanel")) {
      return {
        title: humanizeSlug(pathname.split("/").filter(Boolean).slice(-1)[0] || "Admin Panel"),
        description: "BSTU administrative content management panel.",
      };
    }

    if (pathname.startsWith("/student")) {
      return {
        title: humanizeSlug(pathname.split("/").filter(Boolean).slice(-1)[0] || "Student Portal"),
        description: "BSTU student portal services and admission workflow.",
      };
    }

    return {
      title: humanizeSlug(pathname.split("/").filter(Boolean).pop() || siteName),
      description: settings.site_meta_description || pickStatic(locale, "notFound")[1],
    };
  }, [departments, faculties, greenCampusArticles, locale, pathname, programs, services, settings.site_meta_description, siteName]);

  useEffect(() => {
    let active = true;
    setDynamicMeta(null);

    loadDynamicMeta(pathname, locale)
      .then((meta) => {
        if (active && meta) setDynamicMeta(meta);
      })
      .catch(() => {
        if (active) setDynamicMeta(null);
      });

    return () => {
      active = false;
    };
  }, [locale, pathname]);

  useEffect(() => {
    const meta = dynamicMeta || routeMeta;
    const title = buildTitle(meta.title, siteName);
    const description =
      cleanText(meta.description) ||
      cleanText(settings.site_meta_description) ||
      pickStatic(locale, "home")[1];
    const keywords = cleanText(settings.site_meta_keywords, 240);
    const canonical = `${window.location.origin}${pathname === "/" ? "/" : pathname}`;
    const image = publicAssetUrl(meta.image || settings.branding_og_image || settings.branding_logo_default || "");
    const robots = isPrivate ? "noindex,nofollow" : "index,follow";

    document.title = title;
    setCanonical(canonical);
    setMeta('meta[name="description"]', { name: "description", content: description });
    setMeta('meta[name="keywords"]', { name: "keywords", content: keywords });
    setMeta('meta[name="robots"]', { name: "robots", content: robots });
    setMeta('meta[property="og:site_name"]', { property: "og:site_name", content: siteName });
    setMeta('meta[property="og:title"]', { property: "og:title", content: title });
    setMeta('meta[property="og:description"]', { property: "og:description", content: description });
    setMeta('meta[property="og:type"]', { property: "og:type", content: pathname === "/" ? "website" : "article" });
    setMeta('meta[property="og:url"]', { property: "og:url", content: canonical });
    if (image) setMeta('meta[property="og:image"]', { property: "og:image", content: image });
    setMeta('meta[name="twitter:card"]', { name: "twitter:card", content: image ? "summary_large_image" : "summary" });
    setMeta('meta[name="twitter:title"]', { name: "twitter:title", content: title });
    setMeta('meta[name="twitter:description"]', { name: "twitter:description", content: description });
    if (image) setMeta('meta[name="twitter:image"]', { name: "twitter:image", content: image });
  }, [dynamicMeta, isPrivate, locale, pathname, routeMeta, settings, siteName]);

  return null;
}
