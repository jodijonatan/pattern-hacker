import React from 'react';
import { BrightButton } from './BrightButton';

// Local stylized StatBox - Ultra Pixel
const StatBox = ({ label, value, color }) => (
  <div className="flex flex-col items-center px-4 py-2 pixel-box shadow-[4px_4px_0_0_rgba(0,0,0,0.1)] min-w-[110px] transition-all duration-300">
    <span className="font-pixel text-[12px] uppercase text-secondary tracking-widest font-bold opacity-80">{label}</span>
    <span className={`font-game text-base ${color} drop-shadow-sm`}>{value}</span>
  </div>
);

// Local stylized LifeBar - Ultra Pixel
const LifeBar = ({ lives }) => {
  return (
    <div className="flex gap-2 p-2 pixel-box px-4 transition-all duration-300">
      {[...Array(3)].map((_, i) => (
        <span 
          key={i} 
          className={`text-xl transition-all duration-500 ${i < lives ? 'filter-none grayscale-0 animate-bounce-gentle' : 'grayscale opacity-10 scale-90'}`}
          style={{ animationDelay: `${i * 0.2}s` }}
        >
          ❤️
        </span>
      ))}
    </div>
  );
};

export const BrightHUD = ({ 
  username, 
  difficulty, 
  score, 
  lives, 
  timer,
  onLeaderboard, 
  onMenu 
}) => {
  return (
    <div className="flex flex-col md:flex-row items-center justify-between w-full gap-6 p-6 bright-panel animate-screen-in transition-all duration-300">
      {/* Left: Player Info */}
      <div className="flex items-center gap-4">
        <div className="relative group">
          <div className="absolute inset-0 bg-sky/10 pixel-corners blur-lg group-hover:bg-sky/20 transition-all"></div>
          <div className="relative w-14 h-14 bg-gradient-to-br from-sky/90 via-lavender/90 to-peach/90 pixel-corners shadow-[4px_4px_0_0_rgba(0,0,0,0.2)] flex items-center justify-center text-white text-2xl border-4 border-white/30">
            👤
          </div>
        </div>
        <div>
          <div className="font-pixel text-xs uppercase text-secondary tracking-[0.2em] font-bold opacity-70">Active Player</div>
          <div className="font-game text-xs text-primary font-bold">{username}</div>
        </div>
      </div>

      {/* Center: Stats */}
      <div className="flex items-center gap-4 md:gap-8 flex-wrap justify-center">
        <StatBox label="Score" value={score} color="text-sky" />
        <StatBox label="Level" value={difficulty} color="text-lavender" />
        <StatBox label="Time" value={`${timer}s`} color={timer < 10 ? "text-peach animate-pulse" : "text-primary"} />
        <LifeBar lives={lives} />
      </div>

      {/* Right: Controls */}
      <div className="flex items-center gap-3">
        <BrightButton 
          variant="white" 
          size="sm"
          onClick={onLeaderboard}
        >
          Rank
        </BrightButton>
        <BrightButton 
          variant="white" 
          size="sm"
          onClick={onMenu}
        >
          Esc
        </BrightButton>
      </div>
    </div>
  );
};
