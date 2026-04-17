import { useState } from "react";
import { api } from "../services/api.js";
import { useAuthStore } from "../store/useAuthStore.js";

export function useAuth() {
  const { user, setUser, logout: clearUser } = useAuthStore();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // =====================
  // LOGIN
  // =====================
  const login = async (username, password) => {
    setLoading(true);
    setError(null);

    try {
      const res = await api.login({ username, password });
      
      // Persist to global store
      const userData = res.data?.user || { username };
      setUser(userData);

      return res;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // =====================
  // REGISTER
  // =====================
  const register = async (username, password) => {
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
  };

  // =====================
  // LOGOUT
  // =====================
  const logout = async () => {
    try {
      await api.logout();
    } catch (err) {
      console.warn("Logout API failed");
    } finally {
      clearUser();
    }
  };

  return {
    user,
    loading,
    error,
    login,
    register,
    logout
  };
}