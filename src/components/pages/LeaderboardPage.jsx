import { useNavigate } from "react-router-dom";
import { useAuth } from "../../hooks/useAuth.js";
import { BrightButton } from "../ui/BrightButton";
import { BrightPanel } from "../ui/BrightPanel";
import { Leaderboard } from "../Leaderboard.jsx";

export function LeaderboardPage() {
  const navigate = useNavigate();
  const auth = useAuth();

  return (
    <div className="min-h-dvh w-full flex flex-col items-center justify-center py-12 px-4 md:px-12 animate-screen-in">
      <div className="bright-bg" />
      <div className="bright-overlay" />
      
      <div className="w-full max-w-3xl">
        <BrightPanel 
          title="Hall of Fame 🏆" 
          className="w-full shadow-2xl border-4 border-white/60 relative"
        >
          <div className="absolute -top-4 -right-4 bg-yellow text-primary w-16 h-16 rounded-2xl flex items-center justify-center font-game text-3xl shadow-xl rotate-12 border-4 border-primary/20">
            ⭐
          </div>

          <Leaderboard 
            onClose={() => navigate("/")}
          />
          
          <div className="flex flex-col sm:flex-row gap-4 mt-12 pt-8 border-t-2 border-ui-border/10">
            <BrightButton 
              variant="green" 
              size="lg" 
              onClick={() => navigate("/game")} 
              className="w-full shadow-md"
            >
              🎮 Try to Beat Them
            </BrightButton>
            <BrightButton 
              variant="lavender" 
              size="lg" 
              onClick={() => navigate("/")} 
              className="w-full shadow-md"
            >
              🏠 Main Menu
            </BrightButton>
          </div>
        </BrightPanel>
      </div>

      <div className="mt-12 text-center animate-pulse">
        <p className="font-pixel text-ui-border/40 uppercase tracking-[0.4em] text-sm">
          Global Ranking System Active
        </p>
      </div>
    </div>
  );
}
