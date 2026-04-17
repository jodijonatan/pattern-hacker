import { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useAuth } from "../../hooks/useAuth.js";
import { BrightButton } from "../ui/BrightButton";
import { BrightInput } from "../ui/BrightInput";
import { BrightPanel } from "../ui/BrightPanel";

export function AuthPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const isLoginPage = location.pathname === "/login";
  const [authMode, setAuthMode] = useState(isLoginPage ? "login" : "register");
  const auth = useAuth();
  const [localError, setLocalError] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLocalError(null);
    const formData = new FormData(e.target);
    const username = formData.get("username").trim();
    const password = formData.get("password");

    try {
      if (authMode === "login") {
        await auth.login(username, password);
      } else {
        await auth.register(username, password);
        // auto login after register
        await auth.login(username, password);
      }
      navigate("/");
    } catch (err) {
      setLocalError(err.message || "Authentication failed");
    }
  };

  return (
    <div className="min-h-dvh w-full flex flex-col items-center justify-center py-12 px-6 md:px-12 animate-screen-in">
      <div className="bright-bg" />
      <div className="bright-overlay" />
      
      <BrightPanel 
        title={authMode === "login" ? "Welcome Back!" : "New Adventure!"} 
        className="max-w-sm w-full mx-auto shadow-2xl border-4 border-white/60"
      >
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="space-y-2">
            <label className="font-pixel text-sm uppercase text-ui-border/60 ml-2">Username</label>
            <BrightInput 
              name="username" 
              type="text" 
              placeholder="e.g. PixelHero" 
              className="w-full"
              required 
            />
          </div>
          <div className="space-y-2">
            <label className="font-pixel text-sm uppercase text-ui-border/60 ml-2">Password</label>
            <BrightInput 
              name="password" 
              type="password" 
              placeholder="••••••••" 
              className="w-full"
              required 
            />
          </div>

          {localError && (
            <div className="pixel-error animate-shake text-sm">
              {localError}
            </div>
          )}

          <BrightButton 
            variant={authMode === "login" ? "blue" : "lavender"} 
            size="lg" 
            type="submit" 
            className="w-full shadow-lg"
            disabled={auth.loading}
          >
            {auth.loading ? "Processing..." : (authMode === "login" ? "Login" : "Join Now")}
          </BrightButton>
        </form>

        <div className="mt-8 pt-6 border-t-2 border-ui-border/10 text-center">
          <p className="font-pixel text-ui-border/50 text-sm uppercase mb-4">
            {authMode === "login" ? "Don't have an account?" : "Already a member?"}
          </p>
          <button 
            type="button"
            onClick={() => setAuthMode(authMode === "login" ? "register" : "login")}
            className="font-game text-[10px] text-sky-blue hover:text-lavender transition-colors underline underline-offset-4"
          >
            {authMode === "login" ? "Create Account" : "Back to Login"}
          </button>
        </div>
      </BrightPanel>

      <button 
        onClick={() => navigate("/")}
        className="mt-8 font-pixel text-ui-border/40 hover:text-ui-border transition-colors uppercase tracking-widest text-sm"
      >
        ← Back to Menu
      </button>
    </div>
  );
}
