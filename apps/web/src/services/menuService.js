import { api } from "../lib/api";

export const menuService = {
  getMenu(location) {
    return api.get(`/menus/${location}`)
      .then(res => res.data || [])
      .catch(() => []); // Graceful fallback to prevent crashes if footer menu is missing
  }
};
