import { authStorage } from "./auth";
import { translateApiMessageKey } from "./apiMessageDictionary";
import { localeStorage } from "./locale";

const BASE_URL = String(import.meta.env.VITE_API_BASE_URL || "").replace(/\/$/, "");
const API_ORIGIN = BASE_URL.replace(/\/api\/v1\/?$/, "");

export const apiBaseUrl = BASE_URL;

const API_MESSAGE_KEYS = {
  "This account is not allowed to use this login portal.": "api.messages.loginPortalNotAllowed",
  "Invalid login credentials.": "api.messages.invalidLoginCredentials",
  "Invalid credentials.": "api.messages.invalidCredentials",
  "Login successful": "api.messages.loginSuccessful",
  "Logged out successfully": "api.messages.loggedOutSuccessfully",
  "User details fetched successfully": "api.messages.userDetailsFetched",
  "Password reset link sent to your email address.": "api.messages.passwordResetLinkSent",
  "Invalid or expired password reset token.": "api.messages.invalidOrExpiredPasswordResetToken",
  "Password reset successfully.": "api.messages.passwordResetSuccessfully",
  "Direct account registration is disabled. Use the application form.": "api.messages.directRegistrationDisabled",
  "Unauthenticated.": "api.messages.unauthenticated",
  "Unauthorized. Missing required role.": "api.messages.missingRequiredRole",
  "Unauthorized. Missing required permission.": "api.messages.missingRequiredPermission",
  "Validation failed": "api.messages.validationFailed",
  "auth.emailMustApplyFirst": "api.messages.emailMustApplyFirst",
};

const translateApiMessage = (message) => {
  const value = String(message || "").trim();
  if (!value) return message;

  const key = API_MESSAGE_KEYS[value] || (value.startsWith("api.messages.") ? value : "");

  return key ? translateApiMessageKey(key, message) : message;
};

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
    throw new ApiError(response.status, json, translateApiMessage(json?.message) || `HTTP ${response.status}`);
  }

  if (json?.message) {
    json = { ...json, message: translateApiMessage(json.message) };
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
