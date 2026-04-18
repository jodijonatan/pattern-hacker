import React, { useState } from 'react';
import { useSettingsStore } from '../../store/useSettingsStore';
import { BrightButton } from './BrightButton';

export function AccessibilityTool() {
  const [isOpen, setIsOpen] = useState(false);
  const { 
    theme, toggleTheme, 
    fontSize, setFontSize, 
    fontFamily, setFontFamily,
    soundEnabled, toggleSound
  } = useSettingsStore();

  const fonts = [
    { id: 'body', name: 'Standard' },
    { id: 'pixel', name: 'Retro' },
    { id: 'game', name: 'Arcade' }
  ];

  if (!isOpen) {
    return (
      <button 
        onClick={() => setIsOpen(true)}
        className="fixed bottom-6 right-6 z-[100] w-14 h-14 bg-white dark:bg-slate-800 pixel-box shadow-lg flex items-center justify-center text-2xl active:scale-90 transition-all hover:rotate-12"
        title="Accessibility Settings"
      >
        ♿
      </button>
    );
  }

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/20 backdrop-blur-sm animate-screen-in">
      <div className="relative w-full max-w-sm bright-panel p-8 space-y-8 shadow-2xl">
        <div className="flex justify-between items-center">
          <h2 className="font-game text-lg text-sky">Settings</h2>
          <button 
            onClick={() => setIsOpen(false)}
            className="text-2xl hover:scale-110 transition-transform font-game"
          >
            ✕
          </button>
        </div>

        {/* Theme & Sound Toggles */}
        <div className="grid grid-cols-2 gap-6">
          <div className="space-y-3">
            <label className="font-pixel text-xs uppercase tracking-widest opacity-60">Theme</label>
            <div className="grid grid-cols-1 gap-2">
              <BrightButton 
                variant={theme === 'light' ? 'sky' : 'white'} 
                size="sm" 
                onClick={() => toggleTheme()}
                className="w-full text-[10px]"
              >
                {theme === 'light' ? '☀️ Light' : '🌙 Dark'}
              </BrightButton>
            </div>
          </div>

          <div className="space-y-3">
            <label className="font-pixel text-xs uppercase tracking-widest opacity-60">Audio FX</label>
            <div className="grid grid-cols-1 gap-2">
              <BrightButton 
                variant={soundEnabled ? 'green' : 'peach'} 
                size="sm" 
                onClick={() => toggleSound()}
                className="w-full text-[10px]"
              >
                {soundEnabled ? '🔊 ON' : 'MUTE'}
              </BrightButton>
            </div>
          </div>
        </div>

        {/* Font Family */}
        <div className="space-y-3">
          <label className="font-pixel text-xs uppercase tracking-widest opacity-60">Typography</label>
          <div className="flex flex-wrap gap-2">
            {fonts.map((f) => (
              <button
                key={f.id}
                onClick={() => setFontFamily(f.id)}
                className={`px-4 py-2 pixel-box transition-all text-[10px] font-game ${
                  fontFamily === f.id 
                    ? 'bg-sky text-white border-primary' 
                    : 'bg-primary/5 opacity-60 hover:opacity-100'
                }`}
              >
                {f.name}
              </button>
            ))}
          </div>
        </div>

        {/* Font Size */}
        <div className="space-y-3">
          <div className="flex justify-between items-center">
            <label className="font-pixel text-xs uppercase tracking-widest opacity-60">Text Size</label>
            <span className="font-pixel text-sky">{fontSize}px</span>
          </div>
          <input 
            type="range" 
            min="14" 
            max="22" 
            step="1"
            value={fontSize}
            onChange={(e) => setFontSize(parseInt(e.target.value))}
            className="w-full pixel-slider"
          />
          <div className="flex justify-between font-game text-[8px] opacity-40">
            <span>A</span>
            <span>A</span>
          </div>
        </div>

        <div className="pt-4">
          <BrightButton 
            variant="white" 
            size="md" 
            onClick={() => setIsOpen(false)}
            className="w-full"
          >
            Save & Close
          </BrightButton>
        </div>
      </div>
    </div>
  );
}
