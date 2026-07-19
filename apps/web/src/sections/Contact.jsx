import React, { useState } from "react";
import { MapPin, Phone, Mail, Clock, Send, Loader2 } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";

export default function Contact() {
  const { t } = useLanguage();
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    subject: "",
    message: ""
  });
  const [status, setStatus] = useState("idle"); // idle, loading, success, error

  const handleChange = (e) => {
    setFormData((prev) => ({
      ...prev,
      [e.target.name]: e.target.value
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setStatus("loading");

    // Simulate form submission
    setTimeout(() => {
      setStatus("success");
      setFormData({ name: "", email: "", subject: "", message: "" });
    }, 1500);
  };

  const contactInfo = [
    {
      kind: "address",
      icon: MapPin,
      title: t("home.contact.addressTitle", "Address"),
      details: [
        t("home.contact.addressLine1", "15 Q. Murtazoyev Street"),
        t("home.contact.addressLine2", "Bukhara city, Uzbekistan")
      ],
      color: "text-blue-600 bg-blue-50/50"
    },
    {
      kind: "phone",
      icon: Phone,
      title: t("home.contact.callTitle", "Call Us"),
      details: ["+998 65 224 64 35", "+998 65 223 28 83"],
      color: "text-orange-600 bg-orange-50/50"
    },
    {
      kind: "email",
      icon: Mail,
      title: t("home.contact.emailTitle", "Email Us"),
      details: ["info@bstu.uz", "rector@bstu.uz"],
      color: "text-green-600 bg-green-50/50"
    },
    {
      kind: "hours",
      icon: Clock,
      title: t("home.contact.hoursTitle", "Open Hours"),
      details: [
        t("home.contact.hoursDays", "Monday - Saturday"),
        t("home.contact.hoursTime", "8:30 AM - 5:30 PM")
      ],
      color: "text-pink-600 bg-pink-50/50"
    }
  ];

  return (
    <section id="contact" className="py-24 bg-white border-t border-gray-50 overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">{t("home.contact.tag", "Contact")}</h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">{t("home.contact.title", "Contact Us")}</p>
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
            src="https://maps.google.com/maps?q=Buxoro%20muhandislik-texnologiya%20instituti&t=&z=16&ie=UTF8&iwloc=&output=embed"
            className="w-full h-full border-0 absolute inset-0"
            allowFullScreen=""
            loading="lazy"
            referrerPolicy="no-referrer-when-downgrade"
            title={t("home.contact.mapTitle", "Bukhara State Technical University Map")}
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
                          <a href={`tel:${detail.replace(/\s+/g, '').replace(/[()]/g, '')}`} className="hover:text-primary transition-colors">
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
                  <label htmlFor="name" className="text-xs font-bold text-navy uppercase tracking-wider">{t("home.contact.form.nameLabel", "Your Name")}</label>
                  <input
                    type="text"
                    id="name"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    className="w-full bg-white border border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none transition-colors shadow-sm"
                    placeholder={t("home.contact.form.namePlaceholder", "John Doe")}
                    required
                  />
                </div>
                <div className="flex flex-col gap-2">
                  <label htmlFor="email" className="text-xs font-bold text-navy uppercase tracking-wider">{t("home.contact.form.emailLabel", "Your Email")}</label>
                  <input
                    type="email"
                    id="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    className="w-full bg-white border border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none transition-colors shadow-sm"
                    placeholder={t("home.contact.form.emailPlaceholder", "john@example.com")}
                    required
                  />
                </div>
              </div>

              <div className="flex flex-col gap-2">
                <label htmlFor="subject" className="text-xs font-bold text-navy uppercase tracking-wider">{t("home.contact.form.subjectLabel", "Subject")}</label>
                <input
                  type="text"
                  id="subject"
                  name="subject"
                  value={formData.subject}
                  onChange={handleChange}
                  className="w-full bg-white border border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none transition-colors shadow-sm"
                  placeholder={t("home.contact.form.subjectPlaceholder", "Inquiry about services")}
                  required
                />
              </div>

              <div className="flex flex-col gap-2">
                <label htmlFor="message" className="text-xs font-bold text-navy uppercase tracking-wider">{t("home.contact.form.messageLabel", "Message")}</label>
                <textarea
                  id="message"
                  name="message"
                  rows={5}
                  value={formData.message}
                  onChange={handleChange}
                  className="w-full bg-white border border-gray-100 rounded-xl px-4 py-3 text-sm focus:border-primary focus:outline-none transition-colors shadow-sm resize-none"
                  placeholder={t("home.contact.form.messagePlaceholder", "Write your message here...")}
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
                      {t("home.contact.form.sending", "Sending...")}
                    </>
                  ) : (
                    <>
                      <Send className="w-4 h-4" />
                      {t("home.contact.form.send", "Send Message")}
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
                  {t("home.contact.form.success", "Your message has been sent successfully. Thank you!")}
                </motion.div>
              )}
            </form>
          </motion.div>
        </div>
      </div>
    </section>
  );
}

