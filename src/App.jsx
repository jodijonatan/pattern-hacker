import { useEffect, useRef } from "react";
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

function BackgroundMusic() {
  const { soundEnabled, isGameOver } = useSettingsStore();
  const audioRef = useRef(null);

  useEffect(() => {
    if (!audioRef.current) {
      audioRef.current = new Audio("/sounds/primary-sound.mp3");
      audioRef.current.loop = true;
      audioRef.current.volume = 0.4; // Soft background volume
    }

    let clickHandler = null;

    if (soundEnabled && !isGameOver) {
      audioRef.current.play().catch(() => {
        clickHandler = () => {
          audioRef.current?.play().catch(e => console.error("Retry play failed:", e));
          window.removeEventListener('click', clickHandler);
        };
        window.addEventListener('click', clickHandler);
      });
    } else {
      audioRef.current.pause();
      if (isGameOver) {
        audioRef.current.currentTime = 0; // Restart from beginning next time
      }
    }

    return () => {
      if (audioRef.current) {
        audioRef.current.pause();
      }
      if (clickHandler) {
        window.removeEventListener('click', clickHandler);
      }
    };
  }, [soundEnabled, isGameOver]);

  return null;
}

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
      <BackgroundMusic />
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
