import { useNavigate } from "react-router-dom";
import { useAuth } from "../../hooks/useAuth.js";
import { BrightButton } from "../ui/BrightButton";
import { BrightCard } from "../ui/BrightCard";

export function MenuPage() {
  const navigate = useNavigate();
  const auth = useAuth();

  const handleLogout = () => {
    auth.logout();
    navigate("/");
  };

  return (
    <div className="min-h-dvh w-full flex flex-col items-center justify-center py-12 px-6 md:px-12">
      <div className="bright-bg" />
      <div className="bright-overlay" />
      
      <div className="text-center max-w-md space-y-12 animate-screen-in">
        <div className="animate-float">
          <h1 className="font-game text-4xl md:text-5xl lg:text-7xl bg-gradient-to-r from-sky via-green to-yellow bg-clip-text text-transparent mb-6 drop-shadow-[0_10px_10px_rgba(0,0,0,0.2)]">
            Pattern Hacker
          </h1>
          <p className="font-pixel text-xl md:text-2xl text-secondary uppercase tracking-[0.2em] bg-panel-bg border-4 border-primary/10 py-2 pixel-corners inline-block px-10 shadow-[4px_4px_0_0_var(--shadow-color)]">
            Cracking the Sequence
          </p>
        </div>

        <div className="space-y-6">
          {auth.user ? (
            <div className="space-y-6">
              <BrightButton 
                variant="green" 
                size="xl" 
                onClick={() => navigate("/game")} 
                className="w-full max-w-sm mx-auto shadow-2xl"
              >
                🎮 Start Game
              </BrightButton>
              <BrightButton 
                variant="lavender" 
                size="lg" 
                onClick={() => navigate("/leaderboard")} 
                className="w-full max-w-sm mx-auto shadow-xl"
              >
                🏆 Leaderboard
              </BrightButton>
              <div className="pt-8">
                <BrightButton variant="peach" size="sm" onClick={handleLogout} className="mx-auto">
                  Sign Out ({auth.user.username})
                </BrightButton>
              </div>
            </div>
          ) : (
            <BrightCard padding="lg" className="w-full max-w-md mx-auto shadow-2xl border-2 border-primary/10">
              <div className="space-y-4">
                <BrightButton 
                  variant="blue" 
                  size="lg" 
                  onClick={() => navigate("/login")}
                  className="w-full"
                >
                  Login
                </BrightButton>
                <div className="relative">
                  <div className="absolute inset-0 flex items-center"><span className="w-full border-t border-primary/10"></span></div>
                  <div className="relative flex justify-center text-xs uppercase"><span className="bg-transparent px-2 text-secondary font-bold font-pixel">or</span></div>
                </div>
                <BrightButton 
                  variant="lavender" 
                  size="lg" 
                  onClick={() => navigate("/register")}
                  className="w-full"
                >
                  Register
                </BrightButton>
              </div>
            </BrightCard>
          )}
        </div>
      </div>
    </div>
  );
}
