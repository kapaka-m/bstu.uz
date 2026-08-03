import { authStorage } from "./auth";
import { localeStorage } from "./locale";

const BASE_URL = String(import.meta.env.VITE_API_BASE_URL || "").replace(/\/$/, "");
const API_ORIGIN = BASE_URL.replace(/\/api\/v1\/?$/, "");

export const apiBaseUrl = BASE_URL;

const API_MESSAGE_TRANSLATIONS = {
  "This account is not allowed to use this login portal.": {
    en: "This account is not allowed to use this login portal.",
    uz: "Bu hisob ushbu kirish portalidan foydalanishga ruxsat etilmagan.",
    ru: "Этой учетной записи не разрешено использовать этот портал входа.",
    ar: "هذا الحساب غير مسموح له باستخدام بوابة تسجيل الدخول هذه.",
  },
  "Invalid login credentials.": {
    en: "Invalid login credentials.",
    uz: "Kirish maʼlumotlari noto‘g‘ri.",
    ru: "Неверные данные для входа.",
    ar: "بيانات تسجيل الدخول غير صحيحة.",
  },
  "Invalid credentials.": {
    en: "Invalid credentials.",
    uz: "Maʼlumotlar noto‘g‘ri.",
    ru: "Неверные данные.",
    ar: "البيانات غير صحيحة.",
  },
  "Login successful": {
    en: "Login successful.",
    uz: "Tizimga muvaffaqiyatli kirdingiz.",
    ru: "Вход выполнен успешно.",
    ar: "تم تسجيل الدخول بنجاح.",
  },
  "Logged out successfully": {
    en: "Logged out successfully.",
    uz: "Tizimdan muvaffaqiyatli chiqdingiz.",
    ru: "Вы успешно вышли из системы.",
    ar: "تم تسجيل الخروج بنجاح.",
  },
  "User details fetched successfully": {
    en: "User details fetched successfully.",
    uz: "Foydalanuvchi maʼlumotlari muvaffaqiyatli olindi.",
    ru: "Данные пользователя успешно получены.",
    ar: "تم جلب بيانات المستخدم بنجاح.",
  },
  "Password reset link sent to your email address.": {
    en: "Password reset link sent to your email address.",
    uz: "Parolni tiklash havolasi email manzilingizga yuborildi.",
    ru: "Ссылка для сброса пароля отправлена на ваш email.",
    ar: "تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.",
  },
  "Invalid or expired password reset token.": {
    en: "Invalid or expired password reset token.",
    uz: "Parolni tiklash tokeni noto‘g‘ri yoki muddati tugagan.",
    ru: "Токен сброса пароля недействителен или истек.",
    ar: "رمز إعادة تعيين كلمة المرور غير صالح أو منتهي الصلاحية.",
  },
  "Password reset successfully.": {
    en: "Password reset successfully.",
    uz: "Parol muvaffaqiyatli tiklandi.",
    ru: "Пароль успешно сброшен.",
    ar: "تمت إعادة تعيين كلمة المرور بنجاح.",
  },
  "Direct account registration is disabled. Use the application form.": {
    en: "Direct account registration is disabled. Use the application form.",
    uz: "To‘g‘ridan-to‘g‘ri hisob ro‘yxatdan o‘tkazish o‘chirilgan. Ariza formasidan foydalaning.",
    ru: "Прямая регистрация аккаунта отключена. Используйте форму заявки.",
    ar: "تم تعطيل التسجيل المباشر للحساب. استخدم نموذج التقديم.",
  },
  "Unauthenticated.": {
    en: "Unauthenticated.",
    uz: "Tizimga kirmagansiz.",
    ru: "Вы не авторизованы.",
    ar: "غير مصرح لك.",
  },
  "Unauthorized. Missing required role.": {
    en: "Unauthorized. Missing required role.",
    uz: "Ruxsat yo‘q. Kerakli rol mavjud emas.",
    ru: "Нет доступа. Отсутствует требуемая роль.",
    ar: "غير مصرح. الدور المطلوب غير موجود.",
  },
  "Unauthorized. Missing required permission.": {
    en: "Unauthorized. Missing required permission.",
    uz: "Ruxsat yo‘q. Kerakli huquq mavjud emas.",
    ru: "Нет доступа. Отсутствует требуемое разрешение.",
    ar: "غير مصرح. الصلاحية المطلوبة غير موجودة.",
  },
  "Validation failed": {
    en: "Validation failed.",
    uz: "Tekshiruvdan o‘tmadi.",
    ru: "Проверка данных не пройдена.",
    ar: "فشل التحقق من البيانات.",
  },
  "auth.emailMustApplyFirst": {
    en: "This email is not registered. Please apply first.",
    uz: "Bu email ro‘yxatdan o‘tmagan. Avval ariza topshiring.",
    ru: "Этот email не зарегистрирован. Сначала подайте заявку.",
    ar: "هذا البريد الإلكتروني غير مسجل. يجب التقديم أولا.",
  },
};

const translateApiMessage = (message) => {
  const value = String(message || "").trim();
  if (!value) return message;

  const locale = localeStorage.getLocale() || "en";
  const translations = API_MESSAGE_TRANSLATIONS[value];

  return translations?.[locale] || translations?.en || message;
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
