import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useGame } from "../../hooks/useGame.js";
import { useAuth } from "../../hooks/useAuth.js";
import { BrightButton } from "../ui/BrightButton";
import { BrightInput } from "../ui/BrightInput";
import { BrightHUD } from "../ui/BrightHUD";
import { BrightPanel } from "../ui/BrightPanel";

export function GamePage() {
  const navigate = useNavigate();
  const [input, setInput] = useState("");
  const [feedback, setFeedback] = useState(null);
  const [isError, setIsError] = useState(false);
  const auth = useAuth();
  
  // Custom hook usage
  const game = useGame({ 
    user: auth.user,
    onGameOver: (finalScore) => {
      console.log("Game Over! Score:", finalScore);
    }
  });
  
  const { state, question, timer, rank, submitAnswer, resetGame, loading, error } = game;
  const sequence = question?.sequence || [];
  const lives = state?.lives ?? 3;
  const score = state?.score ?? 0;
  const difficulty = state?.difficulty || 1;

  useEffect(() => {
    if (!auth.user && !auth.loading) {
      navigate("/login");
    }
  }, [auth.user, auth.loading, navigate]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (loading) return;

    const valStr = input.trim();
    if (!valStr) return;

    const value = Number(valStr);
    if (Number.isNaN(value)) {
      setFeedback("Numbers only, please! ✍️");
      setIsError(true);
      return;
    }

    const isCorrect = await submitAnswer(value);
    
    if (isCorrect) {
      setFeedback("✨ Brilliant! +10 Points");
      setIsError(false);
    } else {
      setFeedback("💭 Not quite... Keep trying!");
      setIsError(true);
    }

    setInput("");
    
    // Clear feedback after 2 seconds
    setTimeout(() => setFeedback(null), 2000);
  };

  if (!auth.user) return null;

  return (
    <div className="min-h-dvh w-full overflow-x-hidden">
      <div className="bright-bg" />
      <div className="bright-overlay" />
      
      <main className="relative z-20 flex flex-col items-center justify-start min-h-dvh py-8 px-4 md:px-12 w-full max-w-4xl mx-auto animate-screen-in">
        
        {/* Superior HUD */}
        <div className="w-full mb-8">
          <BrightHUD 
            username={auth.user.username}
            difficulty={difficulty}
            score={score}
            lives={lives}
            timer={timer}
            onLeaderboard={() => navigate("/leaderboard")}
            onMenu={() => navigate("/")}
          />
        </div>

        {lives <= 0 || timer <= 0 ? (
          /* Game Over State */
          <div className="w-full max-w-lg mx-auto py-12">
            <BrightPanel title="Adventure Complete! 🌟" className="text-center shadow-2xl border-2 border-yellow/30">
              <div className="space-y-10 py-6">
                <div>
                  <p className="font-pixel text-ui-border/60 uppercase tracking-widest mb-2">Final Score</p>
                  <div className="font-game text-6xl md:text-7xl text-warm-yellow drop-shadow-lg">
                    {score}
                  </div>
                </div>
                
                <div className="grid grid-cols-2 gap-6">
                  <div className="bg-primary/10 p-6 pixel-box border-2 border-primary/10">
                    <span className="font-pixel text-xs text-secondary uppercase block mb-1">Rank (LV.{difficulty})</span>
                    <span className="font-game text-xl text-sky">#{rank?.difficulty || '--'}</span>
                  </div>
                  <div className="bg-primary/10 p-6 pixel-box border-2 border-primary/10">
                    <span className="font-pixel text-xs text-secondary uppercase block mb-1">Global Rank</span>
                    <span className="font-game text-xl text-yellow">#{rank?.global || '--'}</span>
                  </div>
                </div>

                <div className="space-y-4">
                  <BrightButton variant="green" size="xl" onClick={resetGame} className="w-full shadow-lg">
                    Play Again
                  </BrightButton>
                  <BrightButton variant="white" size="lg" onClick={() => navigate("/")} className="w-full">
                    Return to Menu
                  </BrightButton>
                </div>
              </div>
            </BrightPanel>
          </div>

        ) : (
          /* Active Gameplay */
          <div className="w-full space-y-10">
            
            {/* The Pattern Display */}
            <BrightPanel className="relative overflow-visible">
              <div className="absolute -top-6 left-1/2 -translate-x-1/2 bg-sky text-white px-8 py-2 pixel-corners font-game text-[10px] shadow-lg z-30 uppercase tracking-widest border-4 border-primary/10">
                Sequence Challenge
              </div>
              
              <div className="pt-8 pb-4 text-center">
                <div className="flex flex-wrap items-center justify-center gap-4 md:gap-8 mb-12 min-h-[160px]">
                  {sequence.map((n, i) => (
                    <div 
                      key={i} 
                      className="group relative"
                    >
                      <div className="absolute inset-0 bg-sky/20 rounded-2xl blur-xl group-hover:bg-sky/40 transition-all opacity-0 group-hover:opacity-100" />
                      <div className="relative font-game text-3xl md:text-5xl lg:text-6xl px-8 py-10 bg-panel-bg border-2 border-primary/10 rounded-3xl shadow-md transition-all duration-300 group-hover:-translate-y-2 group-hover:border-sky/30 group-hover:shadow-xl flex items-center justify-center min-w-[100px] text-primary">
                        {n}
                      </div>
                    </div>
                  ))}
                  
                  <div className="font-game text-5xl md:text-7xl text-yellow animate-pulse px-6 py-4 bg-yellow/5 rounded-3xl border-2 border-dashed border-yellow/30 flex items-center justify-center min-w-[100px]">
                    ?
                  </div>
                </div>
                
                <p className="font-pixel text-secondary uppercase tracking-[0.3em] text-sm">
                  What comes next in the logic?
                </p>
              </div>
            </BrightPanel>

            {/* Answer Region */}
            <div className="max-w-md mx-auto w-full">
  <form onSubmit={handleSubmit} className="space-y-6">
    <div className="relative group">
      <div className="absolute -inset-1 bg-gradient-to-r from-sky-blue via-soft-green to-warm-yellow rounded-[20px] blur opacity-30 group-focus-within:opacity-100 transition duration-1000 group-focus-within:duration-200"></div>
      
      <BrightInput
        type="number"
        value={input}
        onChange={(e) => setInput(e.target.value)}
        placeholder="Enter missing number..."
        size="xl"
        className="w-full relative shadow-inner"
        autoFocus
        disabled={loading}
      />
    </div>
    
    <BrightButton
      type="submit"
      disabled={loading || !input}
      variant="blue"
      size="xl"
      className="w-full shadow-2xl hover:shadow-sky-blue/40"
    >
      {loading ? "Verifying..." : "Confirm Answer ✨"}
    </BrightButton>
  </form>
</div>
          </div>
        )}

        {/* Dynamic Floating Feedback */}
        {feedback && (
          <div className={`fixed bottom-12 left-1/2 -translate-x-1/2 z-50 font-game text-sm md:text-base px-10 py-6 rounded-2xl shadow-2xl border-4 transition-all duration-500 animate-bounce ${
            isError 
              ? 'bg-peach text-white border-primary/20 shadow-peach/40' 
              : 'bg-green text-white border-primary/20 shadow-green/40'
          }`}>
            {feedback}
          </div>
        )}

        {/* Global Error Handle */}
        {error && (
          <div className="fixed inset-0 z-[100] flex items-center justify-center bg-primary/20 backdrop-blur-sm p-6">
            <BrightPanel title="Ouch! 💥" className="max-w-sm w-full text-center border-4 border-peach shadow-2xl">
              <p className="font-pixel mb-8 text-secondary font-bold">{error}</p>
              <BrightButton variant="blue" className="w-full" onClick={game.refetch}>
                Re-establish Connection
              </BrightButton>
              <button onClick={() => navigate("/")} className="mt-4 font-pixel text-xs text-secondary uppercase underline underline-offset-4">Return Home</button>
            </BrightPanel>
          </div>
        )}

      </main>
    </div>
  );
}
