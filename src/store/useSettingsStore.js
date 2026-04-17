import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export const useSettingsStore = create(
  persist(
    (set) => ({
      theme: 'light',
      fontSize: 16,
      fontFamily: 'body', // 'body' | 'pixel' | 'game'
      
      setTheme: (theme) => set({ theme }),
      setFontSize: (size) => set({ fontSize: size }),
      setFontFamily: (font) => set({ fontFamily: font }),
      
      toggleTheme: () => set((state) => ({ 
        theme: state.theme === 'light' ? 'dark' : 'light' 
      })),
      
      resetSettings: () => set({
        theme: 'light',
        fontSize: 16,
        fontFamily: 'body'
      })
    }),
    {
      name: 'pattern-hacker-settings',
    }
  )
);
