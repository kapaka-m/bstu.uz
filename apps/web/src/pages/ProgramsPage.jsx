import React, { useEffect } from "react";
import Programs from "../sections/Programs";
import { useAppData } from "../context/AppDataContext";

export default function ProgramsPage() {
  const { loading } = useAppData();

  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  if (loading) {
    return null;
  }

  return (
    <div className="pt-20 bg-white">
      <Programs />
    </div>
  );
}
