import { api } from "../lib/api";

export const initialApplicationService = {
  getMetadata() {
    return api.get("/applications/initial/metadata").then((res) => res.data);
  },

  submit(payload) {
    return api.post("/applications/initial", payload).then((res) => res.data);
  },
};
