import { useState, useEffect } from 'react';
import { api } from '../services/api.js';
import { BrightButton } from './ui/BrightButton';
import { BrightCard } from './ui/BrightCard';

const CozyRank = ({ rank }) => {
  const medals = {
    0: { color: 'text-yellow', bg: 'from-yellow/20', icon: '🥇' },
    1: { color: 'text-sky font-bold', bg: 'from-sky/10', icon: '🥈' },
    2: { color: 'text-peach', bg: 'from-peach/20', icon: '🥉' }
  };
  
  const rankStyle = rank < 3 ? medals[rank] : 'text-primary/60 bg-primary/5';
  
  return (
    <div className={`
      w-12 h-12 md:w-14 md:h-14 pixel-box flex items-center justify-center font-game text-xl md:text-2xl font-bold transition-all duration-300 hover:scale-110
      ${typeof rankStyle === 'object' ? rankStyle.color + ' bg-gradient-to-r ' + rankStyle.bg + ' border-primary/10' : rankStyle + ' border-primary/10'}
    `}>
      {rankStyle?.icon || `#${rank + 1}`}
    </div>
  );
};

export function Leaderboard({ onClose, difficulty: initialDifficulty }) {
  const [leaderboard, setLeaderboard] = useState([]);
  const [loading, setLoading] = useState(true);
  const [activeDifficulty, setActiveDifficulty] = useState(initialDifficulty || null);

  const filterOptions = [
    { label: 'Global', value: null },
    ...Array.from({ length: 10 }, (_, i) => ({ label: `LV.${i + 1}`, value: i + 1 }))
  ];

  useEffect(() => {
    const fetchLeaderboard = async () => {
      setLoading(true);
      try {
        const res = await api.getLeaderboard(activeDifficulty);
        setLeaderboard(res.data?.leaderboard || res.leaderboard || []);
      } catch (err) {
        console.error("Failed to fetch leaderboard", err);
      } finally {
        setLoading(false);
      }
    };
    fetchLeaderboard();
  }, [activeDifficulty]);

  return (
    <div className="w-full max-w-2xl space-y-6 p-2">
      <div className="flex justify-between items-center pb-2 border-b-2 border-primary/5">
        <div className="flex flex-col text-left">
          <h2 className="font-game text-2xl md:text-3xl bg-gradient-to-r from-sky to-lavender bg-clip-text text-transparent font-bold tracking-tight">
            Hall of Fame
          </h2>
          <p className="font-pixel text-xs text-secondary uppercase tracking-widest opacity-60">
            {activeDifficulty ? `Top for Level ${activeDifficulty}` : 'Overall Global Rankings'}
          </p>
        </div>
        <BrightButton variant="white" size="sm" onClick={onClose} className="scale-90 opacity-80 hover:opacity-100 transition-opacity">
          ✕
        </BrightButton>
      </div>

      {/* Difficulty Filter */}
      <div className="flex gap-2 overflow-x-auto pt-2 pb-4 scrollbar-hide">
        {filterOptions.map((opt) => (
          <button
            key={opt.label}
            onClick={() => setActiveDifficulty(opt.value)}
            className={`
              px-4 py-2 pixel-box whitespace-nowrap font-pixel text-[10px] uppercase transition-all
              ${activeDifficulty === opt.value 
                ? 'bg-sky text-white border-primary shadow-[2px_2px_0_0_rgba(0,0,0,0.2)]' 
                : 'bg-primary/5 text-secondary opacity-60 hover:opacity-100 border-primary/5'}
            `}
          >
            {opt.label}
          </button>
        ))}
      </div>

      <BrightCard padding="none" className="max-h-[450px] overflow-y-auto border-2 border-primary/10 bg-panel-bg scrollbar-hide">
        {loading ? (
          <div className="flex flex-col items-center justify-center py-20 animate-pulse space-y-4">
            <div className="w-10 h-10 border-4 border-sky/30 border-t-sky pixel-box animate-spin"></div>
            <span className="font-pixel text-sm text-sky tracking-widest uppercase">Fetching rankings...</span>
          </div>
        ) : leaderboard.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-20 text-center opacity-60">
            <span className="text-4xl mb-4">🏜️</span>
            <div className="font-pixel text-lg text-primary uppercase tracking-widest">
              No records found
            </div>
            <p className="font-pixel text-sm text-secondary">Be the first to leave a mark in this category!</p>
          </div>
        ) : (
          <div className="divide-y divide-primary/5">
            {leaderboard.map((entry, i) => (
              <div key={i} className="flex items-center justify-between p-5 hover:bg-primary/5 transition-all group animate-screen-in" style={{ animationDelay: `${i * 0.1}s` }}>
                <CozyRank rank={i} />
                
                <div className="flex-1 px-6 min-w-0 text-left">
                  <div className="font-game text-base md:text-lg truncate font-bold text-primary group-hover:text-sky transition-colors">
                    {entry.username}
                  </div>
                  <div className="font-pixel text-[10px] text-secondary uppercase tracking-widest opacity-60">
                    High Score Achievement
                  </div>
                </div>

                <div className="flex flex-col items-end gap-1 min-w-[100px]">
                  <div className="font-game text-xl md:text-2xl text-yellow font-bold drop-shadow-sm">
                    {Number(entry.score).toLocaleString()}
                  </div>
                  <div className="flex items-center gap-2">
                    <span className="w-2 h-2 bg-green shadow-[1px_1px_0_0_rgba(0,0,0,0.1)]"></span>
                    <span className="font-pixel text-xs uppercase text-green font-bold">
                      LV.{entry.difficulty}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </BrightCard>
    </div>
  );
}
