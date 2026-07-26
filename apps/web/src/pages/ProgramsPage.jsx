import React, { useEffect } from "react";
import Programs from "../sections/Programs";

export default function ProgramsPage() {
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);

  return (
    <div className="pt-20 bg-white">
      <Programs />
    </div>
  );
}
