import { useEffect, useState } from "react";
import { useLanguage } from "../context/LanguageContext";
import { homeCmsService } from "../services/homeCmsService";

export function useHomeSection(sectionKey) {
  const { language } = useLanguage();
  const [section, setSection] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let alive = true;
    setLoading(true);

    homeCmsService
      .getSections()
      .then((sections) => {
        if (alive) setSection(sections?.[sectionKey] || null);
      })
      .catch(() => {
        if (alive) setSection(null);
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [language, sectionKey]);

  return { section, loading };
}
