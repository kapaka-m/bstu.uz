import { authStorage } from "./auth";
import { localeStorage } from "./locale";

const BASE_URL = String(import.meta.env.VITE_API_BASE_URL || "").replace(/\/$/, "");
const API_ORIGIN = BASE_URL.replace(/\/api\/v1\/?$/, "");

export const apiBaseUrl = BASE_URL;

export const publicAssetUrl = (path) => {
  const value = String(path || "").trim();
  if (!value) return "";
  if (/^https?:\/\//i.test(value)) return value;
  if (value.startsWith("/storage/")) return API_ORIGIN ? `${API_ORIGIN}${value}` : value;
  if (value.startsWith("/")) return value;
  return `${API_ORIGIN}/storage/${value.replace(/^public\//, "")}`;
};

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
  const url = `${BASE_URL}${path}${locale && !hasLocale ? `${separator}locale=${locale}` : ""}`;

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

export async function downloadBlob(path) {
  const response = await fetch(`${BASE_URL}${path}`, {
    headers: {
      Accept: "application/octet-stream",
      ...(authStorage.getToken() ? { Authorization: `Bearer ${authStorage.getToken()}` } : {}),
    },
  });

  if (!response.ok) {
    throw new ApiError(response.status, null);
  }

  const blob = await response.blob();
  const url = URL.createObjectURL(blob);
  window.open(url, "_blank", "noopener,noreferrer");
  setTimeout(() => URL.revokeObjectURL(url), 30000);
}
