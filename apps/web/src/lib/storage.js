// In-memory fallback if localStorage is disabled or throws errors
const memoryStorage = {};

export const storage = {
  get(key, defaultValue = null) {
    try {
      const val = localStorage.getItem(key);
      if (val === null) return defaultValue;
      return JSON.parse(val);
    } catch {
      return memoryStorage[key] !== undefined ? memoryStorage[key] : defaultValue;
    }
  },

  set(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
    } catch {
      memoryStorage[key] = value;
    }
  },

  remove(key) {
    try {
      localStorage.removeItem(key);
    } catch {
      delete memoryStorage[key];
    }
  },

  clear() {
    try {
      localStorage.clear();
    } catch {
      Object.keys(memoryStorage).forEach(k => delete memoryStorage[k]);
    }
  }
};
