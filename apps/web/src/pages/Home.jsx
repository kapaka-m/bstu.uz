import React, { Suspense, useEffect, useState } from "react";
import Hero from "../sections/Hero";

const About = React.lazy(() => import("../sections/About"));
const Values = React.lazy(() => import("../sections/Values"));
const Stats = React.lazy(() => import("../sections/Stats"));
const Features = React.lazy(() => import("../sections/Features"));
const AltFeatures = React.lazy(() => import("../sections/AltFeatures"));
const Services = React.lazy(() => import("../sections/Services"));
const RegistrarOffice = React.lazy(() => import("../sections/RegistrarOffice"));
const Programs = React.lazy(() => import("../sections/Programs"));
const Leadership = React.lazy(() => import("../sections/Leadership"));
const Announcements = React.lazy(() => import("../sections/Announcements"));
const GreenCampusSection = React.lazy(() => import("../sections/GreenCampusSection"));
const VideoGallery = React.lazy(() => import("../sections/VideoGallery"));
const RecentBlog = React.lazy(() => import("../sections/RecentBlog"));
const News = React.lazy(() => import("../sections/News"));

function DeferredSection({ children, className = "min-h-[38rem]" }) {
  const [shouldRender, setShouldRender] = useState(false);
  const [shouldWatch, setShouldWatch] = useState(false);
  const ref = React.useRef(null);

  useEffect(() => {
    if (window.location.hash) {
      setShouldWatch(true);
      return undefined;
    }

    if (shouldWatch) return undefined;

    const enable = () => setShouldWatch(true);
    window.addEventListener("scroll", enable, { once: true, passive: true });
    window.addEventListener("keydown", enable, { once: true });
    window.addEventListener("pointerdown", enable, { once: true });

    return () => {
      window.removeEventListener("scroll", enable);
      window.removeEventListener("keydown", enable);
      window.removeEventListener("pointerdown", enable);
    };
  }, [shouldWatch]);

  useEffect(() => {
    if (!shouldWatch) return undefined;

    const element = ref.current;
    if (!element) return undefined;

    if (!("IntersectionObserver" in window)) {
      setShouldRender(true);
      return undefined;
    }

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setShouldRender(true);
          observer.disconnect();
        }
      },
      { rootMargin: "160px 0px" },
    );

    observer.observe(element);
    return () => observer.disconnect();
  }, [shouldWatch]);

  return (
    <div ref={ref} className={shouldRender ? undefined : className}>
      {shouldRender ? (
        <Suspense fallback={<div className={className} aria-busy="true" />}>
          {children}
        </Suspense>
      ) : null}
    </div>
  );
}

export default function Home() {
  // Scroll to top on page render or hash matching
  useEffect(() => {
    const hash = window.location.hash;
    if (hash) {
      const el = document.getElementById(hash.substring(1));
      if (el) {
        setTimeout(() => {
          el.scrollIntoView({ behavior: "smooth" });
        }, 100);
      }
    } else {
      window.scrollTo(0, 0);
    }
  }, []);

  return (
    <>
      <Hero />
      <DeferredSection className="min-h-[46rem]"><About /></DeferredSection>
      <DeferredSection className="min-h-[42rem]"><Values /></DeferredSection>
      <DeferredSection className="min-h-40"><Stats /></DeferredSection>
      <DeferredSection className="min-h-[48rem]"><Features /></DeferredSection>
      <DeferredSection className="min-h-[36rem]"><AltFeatures /></DeferredSection>
      <DeferredSection className="min-h-[52rem]"><Services limit={4} /></DeferredSection>
      <DeferredSection className="min-h-[40rem]"><RegistrarOffice /></DeferredSection>
      <DeferredSection className="min-h-[52rem]"><Programs limit={6} featured /></DeferredSection>
      <DeferredSection className="min-h-[52rem]"><Leadership /></DeferredSection>
      <DeferredSection className="min-h-[42rem]"><Announcements /></DeferredSection>
      <DeferredSection className="min-h-[50rem]"><GreenCampusSection /></DeferredSection>
      <DeferredSection className="min-h-[42rem]"><VideoGallery /></DeferredSection>
      <DeferredSection className="min-h-[46rem]"><RecentBlog /></DeferredSection>
      <DeferredSection className="min-h-[42rem]"><News /></DeferredSection>
    </>
  );
}
