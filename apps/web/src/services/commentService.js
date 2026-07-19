import { api } from "../lib/api";

export const commentService = {
  postComment(data) {
    return api.post("/comments", data).then(res => res.data);
  }
};
