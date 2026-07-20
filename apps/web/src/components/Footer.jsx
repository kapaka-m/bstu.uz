import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import {
  Facebook,
  Instagram,
  Youtube,
  Send,
  Globe,
  GraduationCap,
} from "lucide-react";
import { useLanguage } from "../context/LanguageContext";
import { footerService } from "../services/footerService";

const socialIcons = {
  website: Globe,
  telegram: Send,
  instagram: Instagram,
  youtube: Youtube,
  facebook: Facebook,
};

export default function Footer() {
  const { logoSrc, locale } = useLanguage();
  const isRtl = locale === "ar";
  const [footerContent, setFooterContent] = useState(null);
  const [newsletterEmail, setNewsletterEmail] = useState("");
  const [newsletterState, setNewsletterState] = useState({
    status: "idle",
    message: "",
  });

  useEffect(() => {
    let active = true;

    footerService
      .getPublicFooter(locale)
      .then((data) => {
        if (active) setFooterContent(data);
      })
      .catch(() => {
        if (active) setFooterContent(null);
      });

    return () => {
      active = false;
    };
  }, [locale]);

  if (!footerContent) {
    return null;
  }

  const footer = footerContent;
  const phoneHref = `tel:${(footer.phone || "").replace(/[^\d+]/g, "")}`;
  const emailHref = `mailto:${footer.email || ""}`;
  const rightsTailMatch = isRtl
    ? footer.rights_text?.match(/^(.*?)\s+([A-Za-z0-9]+)\s*(©)\s*(\d{4})\s*$/)
    : null;
  const rightsLeadMatch =
    isRtl && !rightsTailMatch
      ? footer.rights_text?.match(/^(\d{4})\s*(©)\s*([A-Za-z0-9]+)\s+(.*)$/)
      : null;
  const rightsParts = rightsTailMatch
    ? {
        prefix: `${rightsTailMatch[4]} ${rightsTailMatch[3]} ${rightsTailMatch[2]}`,
        text: rightsTailMatch[1].trim(),
      }
    : rightsLeadMatch
      ? {
          prefix: `${rightsLeadMatch[1]} ${rightsLeadMatch[2]} ${rightsLeadMatch[3]}`,
          text: rightsLeadMatch[4].trim(),
        }
      : null;

  const handleNewsletterSubmit = async (event) => {
    event.preventDefault();
    if (!newsletterEmail) return;

    try {
      setNewsletterState({ status: "loading", message: "" });
      await footerService.subscribe(newsletterEmail);
      setNewsletterEmail("");
      setNewsletterState({
        status: "success",
        message: footer.newsletter_success_message || "",
      });
    } catch (err) {
      setNewsletterState({
        status: "error",
        message: err?.errors?.email?.[0] || err?.message || "",
      });
    }
  };

  return (
    <>
      <div className="bg-white border-t border-gray-100 py-16">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          <div className="grid grid-cols-1 lg:grid-cols-4 gap-8 items-stretch">
            <div
              dir="ltr"
              className="lg:col-span-3 bg-linear-to-br from-[#eef4ff] to-primary-light border border-blue-100 p-8 rounded-3xl flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden group shadow-sm hover:shadow-md transition-all duration-300 text-start"
            >
              <div className="absolute top-0 inset-e-0 w-32 h-32 bg-primary/5 rounded-full translate-x-12 -translate-y-12 transition-transform duration-500 group-hover:scale-110" />
              <div
                dir={isRtl ? "rtl" : "ltr"}
                className={`flex flex-col gap-3 w-full max-w-xl ${
                  isRtl
                    ? "items-end text-right md:order-2 md:ml-auto"
                    : "items-start text-start md:order-1"
                }`}
                style={{
                  direction: isRtl ? "rtl" : "ltr",
                  textAlign: isRtl ? "right" : "start",
                }}
              >
                <span
                  dir={isRtl ? "rtl" : "ltr"}
                  className={`${
                    isRtl ? "self-start" : "self-start"
                  } inline-flex items-center gap-1 bg-primary text-white text-[10px] font-extrabold uppercase tracking-widest px-3 py-1 rounded-full shadow-sm`}
                  style={{
                    unicodeBidi: "plaintext",
                    direction: isRtl ? "rtl" : "ltr",
                    textAlign: isRtl ? "right" : "start",
                  }}
                >
                  <GraduationCap className="w-3.5 h-3.5" />{" "}
                  {footer.admissions_badge}
                </span>
                <h3
                  dir={isRtl ? "rtl" : "ltr"}
                  className={`w-full text-xl md:text-2xl font-extrabold text-navy leading-tight ${
                    isRtl ? "text-right" : "text-start"
                  }`}
                  style={{ unicodeBidi: "plaintext" }}
                >
                  {footer.admissions_heading}
                </h3>
                <p
                  dir={isRtl ? "rtl" : "ltr"}
                  className={`w-full text-gray-500 text-xs md:text-sm leading-relaxed ${
                    isRtl ? "text-right" : "text-start"
                  }`}
                  style={{ unicodeBidi: "plaintext" }}
                >
                  {footer.admissions_description}
                </p>
              </div>
              <a
                href={footer.admissions_apply_url}
                rel="noopener noreferrer"
                className={`w-full md:w-auto shrink-0 bg-primary hover:bg-primary-hover text-white px-7 py-3.5 rounded-xl text-sm font-extrabold transition-all shadow-md shadow-primary/20 hover:shadow-primary/30 hover:-translate-y-0.5 text-center cursor-pointer ${
                  isRtl ? "md:order-1" : "md:order-2"
                }`}
              >
                {footer.admissions_button_label}
              </a>
            </div>

            <div className="lg:col-span-1 bg-primary-light border border-gray-100 p-8 rounded-3xl flex flex-col justify-center gap-4 text-start">
              <h4 className="text-sm font-extrabold uppercase tracking-wider text-navy">
                {footer.newsletter_title}
              </h4>
              <p className="text-gray-500 text-[11px] leading-normal">
                {footer.newsletter_description}
              </p>
              <form
                onSubmit={handleNewsletterSubmit}
                className="flex border border-primary/20 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow bg-white"
              >
                <input
                  id="footer-newsletter-email"
                  name="newsletter_email"
                  type="email"
                  autoComplete="email"
                  value={newsletterEmail}
                  onChange={(event) => setNewsletterEmail(event.target.value)}
                  placeholder={footer.newsletter_placeholder || ""}
                  className="grow px-3 py-2 text-xs focus:outline-none w-full bg-white text-gray-700 text-start"
                  required
                />
                <button
                  type="submit"
                  disabled={newsletterState.status === "loading"}
                  aria-label={footer.newsletter_title || ""}
                  className="bg-primary hover:bg-primary-hover disabled:opacity-60 text-white px-3 py-2 transition-colors flex items-center justify-center cursor-pointer shrink-0"
                >
                  <Send className="w-3.5 h-3.5" />
                </button>
              </form>
              {newsletterState.message && (
                <p
                  className={`text-[11px] font-bold ${
                    newsletterState.status === "success"
                      ? "text-emerald-600"
                      : "text-rose-600"
                  }`}
                >
                  {newsletterState.message}
                </p>
              )}
            </div>
          </div>
        </div>
      </div>

      <footer className="bg-primary-light text-navy border-t border-gray-100 text-start">
        <div className="container mx-auto px-4 md:px-8 pt-16 pb-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
          <div className="flex flex-col gap-4">
            <Link
              to="/"
              aria-label={footer.logo_alt || ""}
              className="flex items-center gap-3"
            >
              <img
                src={logoSrc}
                alt={footer.logo_alt || ""}
                className="h-10"
                width="90"
                height="40"
              />
            </Link>
            <p className="text-sm text-gray-500 leading-relaxed font-semibold">
              {footer.description}
            </p>
            <div className="flex items-center gap-3 mt-2">
              {(footer.social_links || []).map((social, index) => {
                const Icon = socialIcons[social.key] || Globe;
                return (
                  <a
                    key={index}
                    href={social.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    title={social.label}
                    aria-label={social.label}
                    className="w-10 h-10 rounded-lg border border-gray-200 hover:border-primary hover:bg-primary hover:text-white transition-all flex items-center justify-center text-gray-500 hover:shadow-md hover:-translate-y-0.5"
                  >
                    <Icon className="w-4 h-4" />
                  </a>
                );
              })}
            </div>
          </div>

          <div>
            <h4 className="text-sm font-extrabold uppercase tracking-wider text-navy mb-6">
              {footer.useful_links_title}
            </h4>
            <ul className="flex flex-col gap-3.5 text-sm font-semibold text-gray-500">
              {(footer.useful_links || []).map((link) => (
                <li key={link.key || link.url}>
                  <Link
                    to={link.url}
                    className="hover:text-primary transition-colors"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h4 className="text-sm font-extrabold uppercase tracking-wider text-navy mb-6">
              {footer.faculties_title}
            </h4>
            <ul className="flex flex-col gap-3.5 text-sm font-semibold text-gray-500">
              {(footer.faculty_links || []).map((link) => (
                <li key={link.key || link.url}>
                  <Link
                    generation-skip={
                      link.key === "service" ? "true" : undefined
                    }
                    to={link.url}
                    className="hover:text-primary transition-colors"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h4 className="text-sm font-extrabold uppercase tracking-wider text-navy mb-6">
              {footer.contact_title}
            </h4>
            <ul className="flex flex-col gap-3.5 text-sm font-semibold text-gray-500">
              <li>{footer.address_line_1}</li>
              <li>{footer.address_line_2}</li>
              <li>
                <strong>{footer.phone_label}</strong>{" "}
                <a
                  href={phoneHref}
                  className="hover:text-primary transition-colors"
                >
                  <bdi dir="ltr">{footer.phone}</bdi>
                </a>
              </li>
              <li>
                <strong>{footer.email_label}</strong>{" "}
                <a
                  href={emailHref}
                  className="hover:text-primary transition-colors"
                >
                  <bdi dir="ltr">{footer.email}</bdi>
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div className="bg-[#f0f4fc] py-6 text-center text-xs md:text-sm text-gray-500 border-t border-gray-200/50">
          <div className="container mx-auto px-4">
            <p
              dir={isRtl ? "ltr" : "auto"}
              className={`font-extrabold text-navy ${
                isRtl ? "" : "tracking-wide uppercase"
              }`}
            >
              {rightsParts ? (
                <span className="inline-flex flex-row items-center justify-center gap-1.5">
                  <span dir="ltr" className="inline-block">
                    {rightsParts.prefix}
                  </span>
                  <span dir="rtl" className="inline-block">
                    {rightsParts.text}
                  </span>
                </span>
              ) : (
                <bdi
                  dir={isRtl ? "rtl" : "auto"}
                  style={{ unicodeBidi: "plaintext" }}
                >
                  {footer.rights_text}
                </bdi>
              )}
            </p>
          </div>
        </div>
      </footer>
    </>
  );
}
