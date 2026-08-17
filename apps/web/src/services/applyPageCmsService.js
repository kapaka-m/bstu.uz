import { api } from "../lib/api";

const unwrap = (response) => response?.data ?? response;

export const applyPageCmsService = {
  getPage() {
    return api.get("/apply-page").then((response) => unwrap(response) || {});
  },
};
