import { useEffect } from "react";
import { Routes, Route } from "react-router-dom";
import { useSettingsStore } from "./store/useSettingsStore.js";
import { useAuth } from "./hooks/useAuth.js";

// Layout
import { ProtectedRoute } from "./components/layout/ProtectedRoute";

// Pages
import { MenuPage } from "./components/pages/MenuPage";
import { AuthPage } from "./components/pages/AuthPage";
import { GamePage } from "./components/pages/GamePage";
import { LeaderboardPage } from "./components/pages/LeaderboardPage";
import { AccessibilityTool } from "./components/ui/AccessibilityTool";

export default function App() {
  const { theme, fontFamily, fontSize } = useSettingsStore();

  // Initialize auth — triggers session validation on mount
  useAuth();

  useEffect(() => {
    // Apply Global Styles
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.style.setProperty('--base-font-size', `${fontSize}px`);
    
    // Universal Font Swapping: Apply selected font to ALL categories for max accessibility
    const selectedFont = `var(--font-${fontFamily}-src)`;
    document.documentElement.style.setProperty('--applied-font-body', selectedFont);
    document.documentElement.style.setProperty('--applied-font-game', selectedFont);
    document.documentElement.style.setProperty('--applied-font-pixel', selectedFont);
  }, [theme, fontFamily, fontSize]);

  return (
    <div className="font-body selection:bg-sky-blue/30 text-primary bg-transparent transition-colors duration-300">
      <Routes>
        <Route path="/" element={<MenuPage />} />
        <Route path="/login" element={<AuthPage />} />
        <Route path="/register" element={<AuthPage />} />
        <Route path="/game" element={
          <ProtectedRoute>
            <GamePage />
          </ProtectedRoute>
        } />
        <Route path="/leaderboard" element={<LeaderboardPage />} />
      </Routes>

      {/* Global Accessibility UI */}
      <AccessibilityTool />
    </div>
  );
}
