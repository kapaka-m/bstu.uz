import React, { useState, useEffect } from "react";
import { ArrowUp } from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";
import { useLanguage } from "../context/LanguageContext";

export default function ScrollToTop() {
  const [isVisible, setIsVisible] = useState(false);
  const { t, hasTranslation } = useLanguage();

  useEffect(() => {
    const toggleVisibility = () => {
      setIsVisible(window.scrollY > 300);
    };

    window.addEventListener("scroll", toggleVisibility);
    return () => window.removeEventListener("scroll", toggleVisibility);
  }, []);

  const scrollToTop = () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  };

  return (
    <AnimatePresence>
      {isVisible && hasTranslation("common.scrollToTop") && (
        <motion.button
          initial={{ opacity: 0, scale: 0.8 }}
          animate={{ opacity: 1, scale: 1 }}
          exit={{ opacity: 0, scale: 0.8 }}
          onClick={scrollToTop}
          className="fixed bottom-6 right-6 rtl:right-auto rtl:left-6 z-40 bg-primary hover:bg-primary-hover text-white w-11 h-11 rounded-xl shadow-lg shadow-primary/20 hover:shadow-primary/30 flex items-center justify-center transition-all hover:-translate-y-1 hover:scale-105 cursor-pointer"
          aria-label={t("common.scrollToTop")}
        >
          <ArrowUp className="w-5 h-5" />
        </motion.button>
      )}
    </AnimatePresence>
  );
}
