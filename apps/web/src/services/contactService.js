import { api } from "../lib/api";

const unwrap = (response) => response?.data ?? response;

export const contactService = {
  getPage(locale) {
    const query = locale ? `?locale=${encodeURIComponent(locale)}` : "";
    return api.get(`/contact-page${query}`).then((response) => unwrap(response));
  },
};
