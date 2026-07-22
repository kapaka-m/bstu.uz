import React, { useEffect, useMemo, useState } from "react";
import { ExternalLink, CheckCircle2, Landmark } from "lucide-react";
import { motion } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { serviceService } from "../services/serviceService";

export default function RegistrarOffice() {
  const { language } = useLanguage();
  const [services, setServices] = useState([]);

  useEffect(() => {
    let alive = true;

    serviceService
      .getServices()
      .then((items) => {
        if (alive) setServices(items || []);
      })
      .catch(() => {
        if (alive) setServices([]);
      });

    return () => {
      alive = false;
    };
  }, [language]);

  const registrar = useMemo(
    () => services.find((service) => service.slug === "office-of-the-registrar") || null,
    [services],
  );
  const relatedServices = useMemo(
    () => services.filter((service) => service.slug !== "office-of-the-registrar").slice(0, 4),
    [services],
  );

  if (!registrar) {
    return null;
  }

  return (
    <section id="registrar-office" className="py-20 bg-white overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
          {/* Left Column - Image */}
          <motion.div
            initial={{ opacity: 0, x: -30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="relative order-2 lg:order-1"
          >
            <div className="relative group p-4 bg-linear-to-br from-primary-light to-white rounded-[2.5rem] shadow-xl border border-gray-100/50">
              <div className="overflow-hidden rounded-4xl shadow-lg relative aspect-16/10 bg-white flex items-center justify-center">
                <div className="absolute inset-0 bg-[radial-gradient(#0d6efd12_1px,transparent_1px)] bg-size-[18px_18px]" />
                <div className="relative w-28 h-28 rounded-3xl bg-primary/10 text-primary flex items-center justify-center shadow-sm border border-primary/10">
                  <Landmark className="w-12 h-12" />
                </div>
              </div>
              {/* Decorative accent */}
              <div className="absolute -top-3 -left-3 w-16 h-16 bg-primary/5 rounded-full blur-xl" />
              <div className="absolute -bottom-3 -right-3 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl" />
            </div>
          </motion.div>

          {/* Right Column - Content */}
          <motion.div
            initial={{ opacity: 0, x: 30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8, delay: 0.2 }}
            className="flex flex-col text-start order-1 lg:order-2"
          >
            <span className="self-start inline-flex items-center gap-1 bg-primary/10 text-primary text-[11px] font-extrabold uppercase tracking-widest px-3.5 py-1.5 rounded-full mb-4">
              {registrar.action_label || ""}
            </span>
            <h2 className="text-3xl md:text-4xl font-extrabold text-navy leading-tight mb-4">
              {registrar.title || ""}
            </h2>
            <p className="text-gray-500 text-base md:text-lg leading-relaxed mb-6">
              {registrar.description || ""}
            </p>

            {/* List of Services */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
              {relatedServices.map((service, index) => (
                <div key={index} className="flex items-start gap-3">
                  <CheckCircle2 className="w-5 h-5 text-primary shrink-0 mt-0.5" />
                  <span className="text-navy font-bold text-sm leading-snug text-start">
                    {service.title}
                  </span>
                </div>
              ))}
            </div>

            {/* CTA Button */}
            <div>
              <a
                href={registrar.url}
                target={registrar.opens_new_tab ? "_blank" : undefined}
                rel={registrar.opens_new_tab ? "noopener noreferrer" : undefined}
                className="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white px-8 py-4 rounded-xl font-extrabold shadow-md shadow-primary/20 hover:shadow-primary/30 transition-all hover:-translate-y-0.5 cursor-pointer"
              >
                {registrar.action_label || ""}
                <ExternalLink className="w-4 h-4" />
              </a>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
