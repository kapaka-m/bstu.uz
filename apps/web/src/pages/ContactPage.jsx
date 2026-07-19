import React, { useEffect } from "react";
import Contact from "../sections/Contact";
import FAQ from "../sections/FAQ";

export default function ContactPage() {
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  return (
    <div className="pt-20 bg-white">
      <Contact />
      <FAQ />
    </div>
  );
}