import { api } from "../lib/api";

const unwrapPayload = (response) => response?.data ?? response;

const normalizePage = (response) => {
  const payload = unwrapPayload(response);

  if (Array.isArray(payload?.data)) {
    return {
      items: payload.data,
      total:
        payload.total ??
        payload.meta?.total ??
        payload.pagination?.total ??
        payload.data.length,
      lastPage:
        payload.last_page ?? payload.meta?.last_page ?? payload.pagination?.last_page ?? 1,
      currentPage:
        payload.current_page ??
        payload.meta?.current_page ??
        payload.pagination?.current_page ??
        1,
      perPage:
        payload.per_page ?? payload.meta?.per_page ?? payload.pagination?.per_page ?? payload.data.length,
      raw: payload,
    };
  }

  const page = payload?.data ?? payload;

  if (Array.isArray(page)) {
    return {
      items: page,
      total: payload?.total ?? payload?.meta?.total ?? page.length,
      lastPage: payload?.last_page ?? payload?.meta?.last_page ?? 1,
      currentPage: payload?.current_page ?? payload?.meta?.current_page ?? 1,
      perPage: payload?.per_page ?? payload?.meta?.per_page ?? page.length,
      raw: payload,
    };
  }

  return {
    items: [],
    total: 0,
    lastPage: 1,
    currentPage: 1,
    perPage: 15,
    raw: payload,
  };
};

export const apanelService = {
  list(resource, params = {}) {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, val]) => {
      if (val !== undefined && val !== null && val !== "") {
        if (typeof val === "object") {
          Object.entries(val).forEach(([subKey, subVal]) => {
            query.append(`${key}[${subKey}]`, subVal);
          });
        } else {
          query.append(key, val);
        }
      }
    });

    const queryString = query.toString();
    return api.get(`/apanel/${resource}${queryString ? "?" + queryString : ""}`);
  },

  async listPage(resource, params = {}) {
    const response = await this.list(resource, params);
    return normalizePage(response);
  },

  get(resource, id) {
    return api.get(`/apanel/${resource}/${id}`).then(unwrapPayload);
  },

  create(resource, payload) {
    return api.post(`/apanel/${resource}`, payload).then(unwrapPayload);
  },

  update(resource, id, payload) {
    return api.put(`/apanel/${resource}/${id}`, payload).then(unwrapPayload);
  },

  delete(resource, id) {
    return api.delete(`/apanel/${resource}/${id}`);
  },

  getNewsEventSettings() {
    return api.get("/apanel/cms/news-events/settings").then(unwrapPayload);
  },

  updateNewsEventSettings(payload) {
    return api.put("/apanel/cms/news-events/settings", payload).then(unwrapPayload);
  },

  getAnnouncementSettings() {
    return api.get("/apanel/cms/announcements/settings").then(unwrapPayload);
  },

  updateAnnouncementSettings(payload) {
    return api.put("/apanel/cms/announcements/settings", payload).then(unwrapPayload);
  },

  getBlogSettings() {
    return api.get("/apanel/cms/blog/settings").then(unwrapPayload);
  },

  updateBlogSettings(payload) {
    return api.put("/apanel/cms/blog/settings", payload).then(unwrapPayload);
  },

  getVideoGallerySettings() {
    return api.get("/apanel/cms/video-bdtu/settings").then(unwrapPayload);
  },

  updateVideoGallerySettings(payload) {
    return api.put("/apanel/cms/video-bdtu/settings", payload).then(unwrapPayload);
  },

  getGreenCampusSettings() {
    return api.get("/apanel/cms/green-campus/settings").then(unwrapPayload);
  },

  updateGreenCampusSettings(payload) {
    return api.put("/apanel/cms/green-campus/settings", payload).then(unwrapPayload);
  },

  getAdministrationSettings() {
    return api.get("/apanel/cms/administration/settings").then(unwrapPayload);
  },

  updateAdministrationSettings(payload) {
    return api.put("/apanel/cms/administration/settings", payload).then(unwrapPayload);
  },

  getInteractiveServiceSettings() {
    return api.get("/apanel/cms/interactive-services/settings").then(unwrapPayload);
  },

  updateInteractiveServiceSettings(payload) {
    return api.put("/apanel/cms/interactive-services/settings", payload).then(unwrapPayload);
  },

  getAboutPage() {
    return api.get("/apanel/cms/about-page").then(unwrapPayload);
  },

  updateAboutPage(payload) {
    return api.put("/apanel/cms/about-page", payload).then(unwrapPayload);
  },

  uploadMedia(file, metadata = {}) {
    const formData = new FormData();
    formData.append("file", file);
    if (typeof metadata === "string") {
      formData.append("alt_key", metadata);
    } else {
      Object.entries(metadata).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== "") {
          formData.append(key, value);
        }
      });
    }
    return api.post("/apanel/media", formData).then(unwrapPayload);
  },

  async dashboard() {
    try {
      const pageTotal = (response) => normalizePage(response).total;
      const pageItems = (response) => normalizePage(response).items;

      const [
        users,
        apps,
        faculties,
        departments,
        progs,
        newsEvents,
        announcements,
        blogs,
        videos,
        newsletterSubscriptions,
        greenCampusArticles,
        greenCampusStats,
        administrationProfiles,
        media,
        pendingDocs,
        supportTickets,
        inqs,
        comms,
        logs
      ] = await Promise.all([
        this.list("users", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("applications", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("faculties", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("departments", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("programs", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("news", { per_page: 1, news_events_only: 1 }).catch(() => ({ total: 0 })),
        this.list("announcements", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("blogs", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("videos", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("newsletter-subscriptions", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("green-campus-articles", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("green-campus-stats", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("administration-profiles", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("media", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("application-documents", { per_page: 1, status: "pending" }).catch(() => ({ total: 0 })),
        this.list("support-tickets", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("inquiries", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("comments", { per_page: 1 }).catch(() => ({ total: 0 })),
        this.list("audit-logs", { per_page: 5 }).catch(() => ({ data: [] }))
      ]);

      return {
        stats: {
          users: pageTotal(users),
          applications: pageTotal(apps),
          faculties: pageTotal(faculties),
          departments: pageTotal(departments),
          programs: pageTotal(progs),
          newsEvents: pageTotal(newsEvents),
          announcements: pageTotal(announcements),
          blogs: pageTotal(blogs),
          videos: pageTotal(videos),
          newsletterSubscriptions: pageTotal(newsletterSubscriptions),
          greenCampusArticles: pageTotal(greenCampusArticles),
          greenCampusStats: pageTotal(greenCampusStats),
          administrationProfiles: pageTotal(administrationProfiles),
          media: pageTotal(media),
          pendingDocuments: pageTotal(pendingDocs),
          supportTickets: pageTotal(supportTickets),
          inquiries: pageTotal(inqs),
          comments: pageTotal(comms)
        },
        recentLogs: pageItems(logs)
      };
    } catch (err) {
      console.error("Dashboard stats failed", err);
      return {
        stats: { users: 0, applications: 0, programs: 0, administrationProfiles: 0, inquiries: 0, comments: 0 },
        recentLogs: []
      };
    }
  },

  async updateApplicationStatus(id, applicationData, status, comment = "", adminUserId = null) {
    void adminUserId;
    return this.update("applications", id, {
      student_profile_id: applicationData.student_profile_id,
      program_id: applicationData.program_id,
      faculty_id: applicationData.faculty_id || null,
      department_id: applicationData.department_id || null,
      degree_level: applicationData.degree_level || null,
      language_of_study: applicationData.language_of_study || null,
      study_mode: applicationData.study_mode || null,
      status: status,
      note: comment
    });
  },

  sendNotification(userId, title, message) {
    return this.create("notifications", {
      user_id: userId,
      title: title,
      message: message,
      is_read: false
    });
  }
};
