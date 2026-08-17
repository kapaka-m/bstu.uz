import React, { useEffect, useState } from "react";
import Contact from "../sections/Contact";
import FAQ from "../sections/FAQ";
import { useLanguage } from "../context/LanguageContext";
import { contactService } from "../services/contactService";

function ContactPageSkeleton() {
  return (
    <div className="pt-20 bg-white overflow-x-hidden">
      <section className="py-24 bg-white border-t border-gray-50 overflow-hidden">
        <div className="container mx-auto px-4 md:px-8 max-w-7xl">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <div className="h-4 w-32 rounded-full bg-primary/20 mx-auto mb-4 animate-pulse" />
            <div className="h-10 w-3/4 rounded-full bg-gray-200 mx-auto animate-pulse" />
          </div>
          <div className="h-96 rounded-3xl bg-gray-100 border border-gray-100 mb-12 animate-pulse" />
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div className="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-6">
              {[1, 2, 3, 4].map((item) => (
                <div key={item} className="bg-primary-light border border-gray-100/50 p-6 rounded-3xl">
                  <div className="w-12 h-12 rounded-xl bg-gray-200 animate-pulse mb-4" />
                  <div className="h-5 w-28 rounded-full bg-gray-200 animate-pulse mb-3" />
                  <div className="h-4 w-full rounded-full bg-gray-100 animate-pulse" />
                </div>
              ))}
            </div>
            <div className="lg:col-span-7 bg-primary-light border border-gray-100/50 p-8 md:p-10 rounded-3xl">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                <div className="h-12 rounded-xl bg-white animate-pulse" />
                <div className="h-12 rounded-xl bg-white animate-pulse" />
              </div>
              <div className="h-12 rounded-xl bg-white animate-pulse mb-6" />
              <div className="h-36 rounded-xl bg-white animate-pulse mb-6" />
              <div className="h-12 w-40 rounded-xl bg-primary/30 animate-pulse" />
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

export default function ContactPage() {
  const { language } = useLanguage();
  const [page, setPage] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  useEffect(() => {
    let alive = true;
    setLoading(true);

    contactService
      .getPage(language)
      .then((nextPage) => {
        if (alive) setPage(nextPage || null);
      })
      .catch(() => {
        if (alive) setPage(null);
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [language]);

  if (loading) {
    return <ContactPageSkeleton />;
  }

  return (
    <div className="pt-20 bg-white overflow-x-hidden">
      <Contact page={page} />
      <FAQ page={page} />
    </div>
  );
}
