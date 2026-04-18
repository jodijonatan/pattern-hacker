import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export const useAuthStore = create(
  persist(
    (set, get) => ({
      user: null,
      loading: false,
      error: null,
      csrfToken: null,
      sessionValidated: false, // tracks whether we've verified the backend session

      setUser: (user) => set({ user, error: null }),
      setCsrfToken: (token) => set({ csrfToken: token }),
      setLoading: (loading) => set({ loading }),
      setError: (error) => set({ error }),
      setSessionValidated: (v) => set({ sessionValidated: v }),

      logout: () => set({
        user: null,
        csrfToken: null,
        error: null,
        loading: false,
        sessionValidated: false
      }),
    }),
    {
      name: 'pattern-hacker-auth',
      // Only persist user and csrfToken — NOT loading/error/sessionValidated
      partialize: (state) => ({
        user: state.user,
        csrfToken: state.csrfToken,
      }),
    }
  )
);
