import { Navigate } from "react-router-dom";
import { useAuthStore } from "../../store/useAuthStore.js";

/**
 * ProtectedRoute — guards routes that require authentication.
 * 
 * Handles three states:
 * 1. Session not yet validated → show loading spinner
 * 2. No user → redirect to /login
 * 3. User exists + session validated → render children
 */
export function ProtectedRoute({ children }) {
  const user = useAuthStore((s) => s.user);
  const sessionValidated = useAuthStore((s) => s.sessionValidated);

  // Still checking if backend session is alive
  if (!sessionValidated) {
    return (
      <div className="min-h-dvh w-full flex items-center justify-center">
        <div className="bright-bg" />
        <div className="bright-overlay" />
        <div className="relative z-10 flex flex-col items-center gap-6 animate-pulse">
          <div className="w-12 h-12 border-4 border-sky/30 border-t-sky pixel-box animate-spin" />
          <span className="font-pixel text-sm text-secondary uppercase tracking-widest">
            Verifying session...
          </span>
        </div>
      </div>
    );
  }

  // Not authenticated → redirect
  if (!user) {
    return <Navigate to="/login" replace />;
  }

  return children;
}
