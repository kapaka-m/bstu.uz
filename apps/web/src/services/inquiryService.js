import { api } from "../lib/api";

export const inquiryService = {
  submitInquiry(data) {
    return api.post("/inquiries", data).then(res => res.data);
  }
};
