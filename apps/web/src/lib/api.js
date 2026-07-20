import { authStorage } from "./auth";
import { localeStorage } from "./locale";

const BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/v1";

export class ApiError extends Error {
  constructor(status, data, message) {
    super(message || data?.message || `API Error with status ${status}`);
    this.status = status;
    this.data = data;
    this.errors = data?.errors || null;
  }
}

async function request(method, path, body = null, options = {}) {
  const locale = localeStorage.getLocale();
  const separator = path.includes("?") ? "&" : "?";
  const hasLocale = /(?:[?&])locale=/.test(path);
  const url = `${BASE_URL}${path}${hasLocale ? "" : `${separator}locale=${locale}`}`;

  const headers = {
    "Accept": "application/json",
    ...options.headers
  };

  const token = authStorage.getToken();
  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
  }

  let finalBody = body;
  if (body) {
    if (body instanceof FormData) {
      // Let browser set Content-Type header dynamically with boundary
    } else {
      headers["Content-Type"] = "application/json";
      finalBody = JSON.stringify(body);
    }
  }

  const response = await fetch(url, {
    method,
    headers,
    body: finalBody,
    ...options
  });

  const text = await response.text();
  let json = null;
  try {
    json = text ? JSON.parse(text) : null;
  } catch {
    // Response not JSON
  }

  if (!response.ok) {
    throw new ApiError(response.status, json, json?.message || `HTTP ${response.status}`);
  }

  return json;
}

export const api = {
  get: (path, options) => request("GET", path, null, options),
  post: (path, body, options) => request("POST", path, body, options),
  put: (path, body, options) => request("PUT", path, body, options),
  patch: (path, body, options) => request("PATCH", path, body, options),
  delete: (path, options) => request("DELETE", path, null, options)
};
