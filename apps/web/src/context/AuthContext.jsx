/* eslint-disable react-refresh/only-export-components */
import React, {
  createContext,
  useCallback,
  useContext,
  useState,
  useEffect,
} from "react";
import { authStorage } from "../lib/auth";
import { authService } from "../services/authService";

const AuthContext = createContext();

export const APANEL_ROLE_SLUGS = [
  "apanel",
  "super_admin",
  "admin",
  "admission_officer",
  "international_office_staff",
  "call_center_staff",
  "faculty_staff",
  "department_staff",
  "registrar_office_staff",
  "dormitory_manager",
  "teacher",
  "finance_staff",
  "document_officer",
  "content_manager",
];

const APANEL_BYPASS_ROLE_SLUGS = ["apanel", "super_admin"];

export function hasApanelAccess(roles = []) {
  return roles.some((role) => APANEL_ROLE_SLUGS.includes(role));
}

export function hasApanelPermission(user, permission) {
  if (!permission) return true;

  const roles = Array.isArray(user?.roles) ? user.roles : [];
  if (roles.some((role) => APANEL_BYPASS_ROLE_SLUGS.includes(role))) {
    return true;
  }

  const permissions = Array.isArray(user?.permissions) ? user.permissions : [];
  const required = Array.isArray(permission)
    ? permission
    : String(permission)
        .split("|")
        .map((item) => item.trim())
        .filter(Boolean);

  return required.some((item) => permissions.includes(item));
}

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => authStorage.getUser());
  const [loading, setLoading] = useState(true);

  const normalizeUser = useCallback((payload) => {
    if (!payload) return null;

    const baseUser = payload.user || payload;
    const roles = payload.roles || baseUser.roles || [];
    const permissions = payload.permissions || baseUser.permissions || [];

    return {
      ...baseUser,
      roles: Array.isArray(roles) ? roles : Object.values(roles),
      permissions: Array.isArray(permissions)
        ? permissions
        : Object.values(permissions),
    };
  }, []);

  const login = async (email, password, intendedRole = null) => {
    const data = await authService.login(email, password, intendedRole);
    const normalizedUser = normalizeUser(data.user);
    authStorage.setToken(data.access_token);
    authStorage.setUser(normalizedUser);
    setUser(normalizedUser);
    return normalizedUser;
  };

  const clearSession = useCallback(() => {
    authStorage.clear();
    setUser(null);
  }, []);

  const logout = async () => {
    try {
      await authService.logout();
    } catch (e) {
      console.error("Logout request failed, clearing local session", e);
    } finally {
      authStorage.clear();
      setUser(null);
    }
  };

  useEffect(() => {
    const validateToken = async () => {
      const token = authStorage.getToken();
      if (!token) {
        setLoading(false);
        return;
      }
      try {
        const currentUser = normalizeUser(await authService.getCurrentUser());
        authStorage.setUser(currentUser);
        setUser(currentUser);
      } catch (e) {
        console.error("Token validation failed, logging out", e);
        authStorage.clear();
        setUser(null);
      } finally {
        setLoading(false);
      }
    };
    validateToken();
  }, [normalizeUser]);

  const checkAdminRole = useCallback(async () => {
    try {
      const currentUser = normalizeUser(await authService.getCurrentUser());
      authStorage.setUser(currentUser);
      setUser(currentUser);
      const roles = currentUser?.roles || [];
      return hasApanelAccess(roles);
    } catch {
      return false;
    }
  }, [normalizeUser]);

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated: !!user,
        loading,
        login,
        logout,
        clearSession,
        checkAdminRole,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
}
