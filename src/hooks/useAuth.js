import { useCallback, useEffect } from "react";
import { api } from "../services/api.js";
import { useAuthStore } from "../store/useAuthStore.js";

/**
 * useAuth — centralised auth hook.
 * 
 * FIX: loading/error now live in Zustand (shared across all consumers).
 * FIX: validates backend session on mount to prevent stale localStorage state.
 */
export function useAuth() {
  const {
    user, setUser, logout: clearUser,
    loading, setLoading,
    error, setError,
    csrfToken, setCsrfToken,
    sessionValidated, setSessionValidated,
  } = useAuthStore();

  // =====================
  // SESSION VALIDATION
  // On first mount, verify the backend session is still alive.
  // This prevents the "flash of authenticated content" bug.
  // =====================
  useEffect(() => {
    if (user && !sessionValidated) {
      let cancelled = false;

      (async () => {
        try {
          const res = await api.checkSession();
          if (cancelled) return;

          if (res.success && res.data?.user) {
            setUser(res.data.user);
            setCsrfToken(res.data.csrf_token || csrfToken);
          } else {
            // Backend session expired — clear local state
            clearUser();
          }
        } catch {
          // Network error or 401 — clear local state
          if (!cancelled) clearUser();
        } finally {
          if (!cancelled) setSessionValidated(true);
        }
      })();

      return () => { cancelled = true; };
    } else if (!user) {
      setSessionValidated(true); // no user = nothing to validate
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps — intentional mount-only

  // =====================
  // LOGIN
  // =====================
  const login = useCallback(async (username, password) => {
    setLoading(true);
    setError(null);

    try {
      const res = await api.login({ username, password });
      
      // Persist to global store
      const userData = res.data?.user || { username };
      setUser(userData);
      
      // Store CSRF token from server
      if (res.data?.csrf_token) {
        setCsrfToken(res.data.csrf_token);
      }
      
      setSessionValidated(true);
      return res;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  }, [setUser, setCsrfToken, setLoading, setError, setSessionValidated]);

  // =====================
  // REGISTER
  // =====================
  const register = useCallback(async (username, password) => {
    setLoading(true);
    setError(null);

    try {
      const res = await api.register({ username, password });
      return res;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  }, [setLoading, setError]);

  // =====================
  // LOGOUT
  // =====================
  const logout = useCallback(async () => {
    try {
      await api.logout();
    } catch (err) {
      console.warn("Logout API failed:", err.message);
    } finally {
      clearUser();
    }
  }, [clearUser]);

  return {
    user,
    loading,
    error,
    csrfToken,
    sessionValidated,
    login,
    register,
    logout
  };
}