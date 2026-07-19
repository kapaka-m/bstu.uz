import { storage } from "./storage";

const TOKEN_KEY = "bstu_auth_token";
const USER_KEY = "bstu_auth_user";

export const authStorage = {
  getToken() {
    return storage.get(TOKEN_KEY);
  },

  setToken(token) {
    storage.set(TOKEN_KEY, token);
  },

  getUser() {
    return storage.get(USER_KEY);
  },

  setUser(user) {
    storage.set(USER_KEY, user);
  },

  clear() {
    storage.remove(TOKEN_KEY);
    storage.remove(USER_KEY);
  }
};
