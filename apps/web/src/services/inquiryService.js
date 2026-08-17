import { api } from "../lib/api";

const unwrap = (response) => response?.data ?? response;

export const inquiryService = {
  submitInquiry(data) {
    return api.post("/inquiries", data).then(unwrap);
  }
};
