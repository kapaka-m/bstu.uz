import React, { useState } from "react";
import { ChevronDown } from "lucide-react";
import { faqData } from "../data/mockData";
import { motion, AnimatePresence } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";

export default function FAQ() {
  const [openId, setOpenId] = useState(1); // Keep the first FAQ open by default
  const { t } = useLanguage();

  const toggleFAQ = (id) => {
    setOpenId(openId === id ? null : id);
  };

  // Split FAQs into two columns for desktop view
  const midPoint = Math.ceil(faqData.length / 2);
  const leftColFaqs = faqData.slice(0, midPoint);
  const rightColFaqs = faqData.slice(midPoint);

  const renderFaqColumn = (faqs) => (
    <div className="flex flex-col gap-4">
      {faqs.map((faq) => {
        const isOpen = openId === faq.id;
        const questionText = t(`home.faq.q${faq.id}`);
        const answerText = t(`home.faq.a${faq.id}`);

        return (
          <div
            key={faq.id}
            className={`bg-white border rounded-2xl overflow-hidden transition-all duration-300 ${
              isOpen ? "border-primary/20 shadow-md shadow-primary/5" : "border-gray-100 hover:border-gray-200"
            }`}
          >
            <button
              onClick={() => toggleFAQ(faq.id)}
              className="w-full text-start px-6 py-5 flex items-center justify-between gap-4 font-bold text-base text-navy transition-colors hover:text-primary group cursor-pointer"
            >
              <span>{questionText}</span>
              <ChevronDown
                className={`w-4 h-4 text-gray-400 group-hover:text-primary transition-transform duration-300 ${
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
                  <div className="px-6 pb-6 text-sm text-gray-500 leading-relaxed border-t border-gray-50 pt-4 text-start">
                    {answerText}
                  </div>
                </motion.div>
              )}
            </AnimatePresence>
          </div>
        );
      })}
    </div>
  );

  return (
    <section id="faq" className="py-24 bg-primary-light">
      <div className="container mx-auto px-4 md:px-8 max-w-7xl">
        {/* Section Header */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <h2 className="text-sm font-extrabold uppercase tracking-widest text-primary mb-3">
            {t("home.faq.tag")}
          </h2>
          <p className="text-3xl md:text-4xl font-extrabold text-navy">
            {t("home.faq.title")}
          </p>
        </div>

        {/* FAQ Columns Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
          {renderFaqColumn(leftColFaqs)}
          {renderFaqColumn(rightColFaqs)}
        </div>
      </div>
    </section>
  );
}