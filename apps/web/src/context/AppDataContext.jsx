/* eslint-disable react-refresh/only-export-components */
import React, { createContext, useCallback, useContext, useState, useEffect } from "react";
import { useLocation } from "react-router-dom";
import { useLocale } from "./LocaleContext";
import { facultyService } from "../services/facultyService";
import { departmentService } from "../services/departmentService";
import { programService } from "../services/programService";
import { serviceService } from "../services/serviceService";
import { videoService } from "../services/videoService";
import { greenCampusService } from "../services/greenCampusService";

const AppDataContext = createContext();

export function AppDataProvider({ children }) {
  const { locale } = useLocale();
  const location = useLocation();
  const [faculties, setFaculties] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [programs, setPrograms] = useState([]);
  const [services, setServices] = useState([]);
  const [videos, setVideos] = useState([]);
  const [greenCampusStats, setGreenCampusStats] = useState([]);
  const [greenCampusArticles, setGreenCampusArticles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const isPublicSite =
    !location.pathname.startsWith("/student") &&
    !location.pathname.startsWith("/apanel");

  const fetchGlobalData = useCallback(async () => {
    if (!isPublicSite) {
      setLoading(false);
      setError(null);
      return;
    }

    try {
      setLoading(true);
      setError(null);

      const [facs, depts, progs, srvs, vids, gcStats, gcArts] =
        await Promise.all([
          facultyService.getFaculties(),
          departmentService.getDepartments(),
          programService.getPrograms(),
          serviceService.getServices(),
          videoService.getVideos(),
          greenCampusService.getStats(),
          greenCampusService.getArticles(),
        ]);

      setFaculties(facs);
      setDepartments(depts);
      setPrograms(progs);
      setServices(srvs);
      setVideos(vids);
      setGreenCampusStats(gcStats);
      setGreenCampusArticles(gcArts);
    } catch (err) {
      console.error("Failed to load global application data from API", err);
      setError(err);
    } finally {
      setLoading(false);
    }
  }, [isPublicSite]);

  useEffect(() => {
    fetchGlobalData();
  }, [fetchGlobalData, locale]);

  return (
    <AppDataContext.Provider
      value={{
        faculties,
        departments,
        programs,
        services,
        videos,
        greenCampusStats,
        greenCampusArticles,
        loading,
        error,
        retry: fetchGlobalData,
      }}
    >
      {children}
    </AppDataContext.Provider>
  );
}

export function useAppData() {
  const context = useContext(AppDataContext);
  if (!context) {
    throw new Error("useAppData must be used within an AppDataProvider");
  }
  return context;
}
