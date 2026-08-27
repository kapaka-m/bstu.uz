import React, { useEffect, useMemo, useState } from "react";
import { ChevronDown } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";
import { contactService } from "../services/contactService";

export default function FAQ({ page: providedPage = null }) {
  const [openId, setOpenId] = useState(0);
  const { language } = useLanguage();
  const [fetchedPage, setFetchedPage] = useState(null);

  useEffect(() => {
    if (providedPage) return undefined;

    let alive = true;

    contactService
      .getPage(language)
      .then((nextPage) => {
        if (alive) setFetchedPage(nextPage || null);
      })
      .catch(() => {
        if (alive) setFetchedPage(null);
      });

    return () => {
      alive = false;
    };
  }, [language, providedPage]);

  const toggleFAQ = (id) => {
    setOpenId(openId === id ? null : id);
  };

  const page = providedPage || fetchedPage;
  const faq = page?.content?.faq || {};
  const faqItems = useMemo(() => faq.items || [], [faq.items]);

  if (!page || faqItems.length === 0) {
    return null;
  }

  const renderFaqItem = (faq) => {
    const isOpen = openId === faq.id;

    return (
      <div
        key={faq.id}
        className={`bg-white border rounded-2xl overflow-hidden transition-all duration-300 min-w-0 h-full ${
          isOpen ? "border-primary/20 shadow-md shadow-primary/5" : "border-gray-100 hover:border-gray-200"
        }`}
      >
        <button
          onClick={() => toggleFAQ(faq.id)}
          className="w-full min-h-22 text-start px-5 md:px-6 py-5 flex items-center justify-between gap-4 font-bold text-base text-navy transition-colors hover:text-primary group cursor-pointer min-w-0"
        >
          <span className="break-words min-w-0">{faq.question}</span>
          <ChevronDown
            className={`w-4 h-4 shrink-0 text-gray-400 group-hover:text-primary transition-transform duration-300 ${
              isOpen ? "rotate-180 text-primary" : ""
            }`}
          />
        </button>
        <AnimatePresence initial={false}>
          {isOpen && (
            <motion.div
              initial={{ height: 0 }}
              animate={{ height: "auto" }}
              exit={{ height: 0 }}
              transition={{ duration: 0.3, ease: "easeInOut" }}
              className="overflow-hidden"
            >
              <div className="px-5 md:px-6 pb-6 text-sm text-gray-500 leading-relaxed border-t border-gray-50 pt-4 text-start break-words">
                {faq.answer}
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    );
  };

  return (
    <section id="faq" className="py-24 bg-primary-light overflow-hidden">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl min-w-0">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16 min-w-0">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3 break-words">
            {faq.tag || ""}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy break-words">
            {faq.title || ""}
          </p>
        </div>

        {/* FAQ Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 items-stretch min-w-0">
          {faqItems.map(renderFaqItem)}
        </div>
      </div>
    </section>
  );
}
