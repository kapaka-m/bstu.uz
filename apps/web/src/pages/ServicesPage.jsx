import React, { useEffect } from "react";
import Services from "../sections/Services";

export default function ServicesPage() {
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  return (
    <div className="pt-20 bg-white overflow-x-hidden">
      <Services />
    </div>
  );
}
