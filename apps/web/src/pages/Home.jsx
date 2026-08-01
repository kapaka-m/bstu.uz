import React, { useEffect } from "react";
import Hero from "../sections/Hero";
import About from "../sections/About";
import Values from "../sections/Values";
import Stats from "../sections/Stats";
import Features from "../sections/Features";
import AltFeatures from "../sections/AltFeatures";
import Services from "../sections/Services";
import RegistrarOffice from "../sections/RegistrarOffice";
import Programs from "../sections/Programs";
import Leadership from "../sections/Leadership";
import Announcements from "../sections/Announcements";
import RecentBlog from "../sections/RecentBlog";

import GreenCampusSection from "../sections/GreenCampusSection";
import VideoGallery from "../sections/VideoGallery";
import News from "../sections/News";
import { useLanguage } from "../context/LanguageContext";

const REQUIRED_HOME_TRANSLATIONS = [
  "home.hero.title",
  "home.hero.subtitle",
  "common.applyNow",
  "home.hero.accredited",
  "home.hero.statePrograms",
];

export default function Home() {
  const { loading, translationsReady, hasTranslation } = useLanguage();
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

  const hasRequiredHomeContent =
    !loading &&
    translationsReady &&
    REQUIRED_HOME_TRANSLATIONS.every((key) => hasTranslation(key));

  if (!hasRequiredHomeContent) {
    return null;
  }

  return (
    <>
      <Hero />
      <About />
      <Values />
      <Stats />
      <Features />
      <AltFeatures />
      <Services limit={4} />
      <RegistrarOffice />
      <Programs limit={6} />
      <Leadership />
      <Announcements />

      <GreenCampusSection />
      <VideoGallery />
      <RecentBlog />
      <News />
    </>
  );
}
