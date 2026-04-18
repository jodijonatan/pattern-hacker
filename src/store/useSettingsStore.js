import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export const useSettingsStore = create(
  persist(
    (set) => ({
      theme: 'light',
      fontSize: 16,
      fontFamily: 'body', // 'body' | 'pixel' | 'game'
      soundEnabled: true,
      isGameOver: false,
      
      setTheme: (theme) => set({ theme }),
      setFontSize: (size) => set({ fontSize: size }),
      setFontFamily: (font) => set({ fontFamily: font }),
      setSoundEnabled: (enabled) => set({ soundEnabled: enabled }),
      setIsGameOver: (val) => set({ isGameOver: val }),
      
      toggleTheme: () => set((state) => ({ 
        theme: state.theme === 'light' ? 'dark' : 'light' 
      })),
      
      toggleSound: () => set((state) => ({ 
        soundEnabled: !state.soundEnabled 
      })),
      
      resetSettings: () => set({
        theme: 'light',
        fontSize: 16,
        fontFamily: 'body',
        soundEnabled: true,
        isGameOver: false
      })
    }),
    {
      name: 'pattern-hacker-settings',
    }
  )
);
