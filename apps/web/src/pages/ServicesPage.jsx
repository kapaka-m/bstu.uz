import React, { useEffect } from "react";
import Services from "../sections/Services";
import { useAppData } from "../context/AppDataContext";

export default function ServicesPage() {
  const { loading } = useAppData();

  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  if (loading) {
    return null;
  }

  return (
    <div className="pt-20 bg-white">
      <Services />
    </div>
  );
}
