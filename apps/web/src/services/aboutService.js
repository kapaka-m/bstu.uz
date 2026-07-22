import { api } from "../lib/api";

const unwrap = (response) => response?.data ?? response;

export const aboutService = {
  getPage(locale) {
    const query = locale ? `?locale=${encodeURIComponent(locale)}` : "";
    return api.get(`/about-page${query}`).then((response) => unwrap(response));
  },
};
