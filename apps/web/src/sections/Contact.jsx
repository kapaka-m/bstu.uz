import React, { useEffect, useMemo, useState } from "react";
import { MapPin, Phone, Mail, Clock, Send, Loader2 } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { contactService } from "../services/contactService";
import { inquiryService } from "../services/inquiryService";

const iconMap = {
  address: MapPin,
  phone: Phone,
  email: Mail,
  hours: Clock,
};

const colorMap = {
  address: "text-blue-600 bg-blue-50/50",
  phone: "text-orange-600 bg-orange-50/50",
  email: "text-green-600 bg-green-50/50",
  hours: "text-pink-600 bg-pink-50/50",
};

export default function Contact() {
  const { language } = useLanguage();
  const [page, setPage] = useState(null);
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    subject: "",
    message: ""
  });
  const [status, setStatus] = useState("idle");
  const [submitError, setSubmitError] = useState("");

  useEffect(() => {
    let alive = true;

    contactService
      .getPage(language)
      .then((nextPage) => {
        if (alive) setPage(nextPage || null);
      })
      .catch(() => {
        if (alive) setPage(null);
      });

    return () => {
      alive = false;
    };
  }, [language]);

  const handleChange = (e) => {
    setFormData((prev) => ({
      ...prev,
      [e.target.name]: e.target.value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setStatus("loading");
    setSubmitError("");

    try {
      await inquiryService.submitInquiry(formData);
      setStatus("success");
      setFormData({ name: "", email: "", subject: "", message: "" });
    } catch (err) {
      const errors = err?.errors || err?.data?.errors;
      const firstError = errors && Object.values(errors).flat().find(Boolean);
      setSubmitError(firstError || err?.message || form.errorMessage || "");
      setStatus("error");
    }
  };

  const content = page?.content || {};
  const form = content.form || {};
  const contactInfo = useMemo(
    () =>
      (content.cards || []).map((info) => ({
        ...info,
        icon: iconMap[info.kind] || MapPin,
        color: colorMap[info.kind] || colorMap.address,
        details: Array.isArray(info.details) ? info.details : [],
      })),
    [content.cards],
  );

  if (!page) {
    return null;
  }

  return (
    <section id="contact" className="py-24 bg-white border-t border-gray-50 overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">{content.tag || ""}</h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">{content.title || ""}</p>
        </div>

        {/* Google Map */}
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
          className="mb-12 rounded-3xl overflow-hidden shadow-lg border border-gray-100 h-96 relative"
        >
          <iframe
            src={page.map_embed_url || ""}
            className="w-full h-full border-0 absolute inset-0"
            allowFullScreen=""
            loading="lazy"
            referrerPolicy="no-referrer-when-downgrade"
            title={content.mapTitle || ""}
          ></iframe>
        </motion.div>

        {/* Contact Content Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
          {/* Left Column (Info Boxes) */}
          <div className="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-6">
            {contactInfo.map((info, index) => {
              const Icon = info.icon;
              return (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.5, delay: index * 0.1 }}
                  className="bg-primary-light border border-gray-100/50 p-6 rounded-3xl shadow-sm hover:shadow-md transition-shadow flex flex-col items-start"
                >
                  <div className={`w-12 h-12 rounded-xl flex items-center justify-center mb-4 ${info.color}`}>
                    <Icon className="w-5 h-5" />
                  </div>
                  <h4 className="text-lg font-bold text-navy mb-2">{info.title}</h4>
                  {info.details.map((detail, dIdx) => {
                    if (info.kind === "phone") {
                      return (
                        <p key={dIdx} className="text-sm text-gray-500 font-semibold leading-relaxed">
                          <a
                            href={`tel:${detail.replace(/\s+/g, '').replace(/[()]/g, '')}`}
                            dir="ltr"
                            style={{ unicodeBidi: "isolate" }}
                            className="inline-block hover:text-primary transition-colors"
                          >
                            {detail}
                          </a>
                        </p>
                      );
                    } else if (info.kind === "email") {
                      return (
                        <p key={dIdx} className="text-sm text-gray-500 font-semibold leading-relaxed">
                          <a href={`mailto:${detail}`} className="hover:text-primary transition-colors">
                            {detail}
                          </a>
                        </p>
                      );
                    }
                    return (
                      <p key={dIdx} className="text-sm text-gray-500 font-semibold leading-relaxed">
                        {detail}
                      </p>
                    );
                  })}
                </motion.div>
              );
            })}
          </div>

          {/* Right Column (Form) */}
          <motion.div
            initial={{ opacity: 0, x: 30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="lg:col-span-7 bg-primary-light border border-gray-100/50 p-8 md:p-10 rounded-3xl shadow-sm flex flex-col justify-between"
          >
            <form onSubmit={handleSubmit} className="flex flex-col gap-6">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div className="flex flex-col gap-2">
                  <label htmlFor="name" className="text-xs font-bold text-navy uppercase tracking-wider">{form.nameLabel || ""}</label>
                  <input
                    type="text"
                    id="name"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    className="w-full bg-white border border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none transition-colors shadow-sm"
                    placeholder={form.namePlaceholder || ""}
                    required
                  />
                </div>
                <div className="flex flex-col gap-2">
                  <label htmlFor="email" className="text-xs font-bold text-navy uppercase tracking-wider">{form.emailLabel || ""}</label>
                  <input
                    type="email"
                    id="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    className="w-full bg-white border border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none transition-colors shadow-sm"
                    placeholder={form.emailPlaceholder || ""}
                    required
                  />
                </div>
              </div>

              <div className="flex flex-col gap-2">
                <label htmlFor="subject" className="text-xs font-bold text-navy uppercase tracking-wider">{form.subjectLabel || ""}</label>
                <input
                  type="text"
                  id="subject"
                  name="subject"
                  value={formData.subject}
                  onChange={handleChange}
                  className="w-full bg-white border border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none transition-colors shadow-sm"
                  placeholder={form.subjectPlaceholder || ""}
                  required
                />
              </div>

              <div className="flex flex-col gap-2">
                <label htmlFor="message" className="text-xs font-bold text-navy uppercase tracking-wider">{form.messageLabel || ""}</label>
                <textarea
                  id="message"
                  name="message"
                  rows={5}
                  value={formData.message}
                  onChange={handleChange}
                  className="w-full bg-white border border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none transition-colors shadow-sm resize-none"
                  placeholder={form.messagePlaceholder || ""}
                  required
                />
              </div>

              <div>
                <button
                  type="submit"
                  disabled={status === "loading"}
                  className="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover disabled:bg-primary/50 text-white px-8 py-3.5 rounded-xl text-sm font-semibold transition-all duration-300 shadow-md shadow-primary/20 hover:shadow-primary/30 disabled:shadow-none hover:-translate-y-0.5 cursor-pointer"
                >
                  {status === "loading" ? (
                    <>
                      <Loader2 className="w-4 h-4 animate-spin" />
                      {form.sendingLabel || ""}
                    </>
                  ) : (
                    <>
                      <Send className="w-4 h-4" />
                      {form.sendLabel || ""}
                    </>
                  )}
                </button>
              </div>

              {/* Status Notification */}
              {status === "success" && (
                <motion.div
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  className="p-4 bg-green-50 text-green-700 text-sm font-semibold rounded-xl text-center border border-green-100"
                >
                  {form.successMessage || ""}
                </motion.div>
              )}
              {status === "error" && (
                <motion.div
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  className="p-4 bg-rose-50 text-rose-700 text-sm font-semibold rounded-xl text-center border border-rose-100"
                >
                  {submitError || form.errorMessage || ""}
                </motion.div>
              )}
            </form>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
