import React from 'react';
import { BrightCard } from './BrightCard';

export const BrightPanel = ({ 
  children, 
  title, 
  className = '',
  ...props 
}) => {
  return (
    <BrightCard className={`relative overflow-hidden ${className}`} {...props}>
      {title && (
        <div className="flex items-center gap-3 mb-6 pb-4 border-b-2 border-primary/5">
          <div className="w-4 h-4 bg-green shadow-[2px_2px_0_0_rgba(0,0,0,0.1)]"></div>
          <div className="w-4 h-4 bg-yellow shadow-[2px_2px_0_0_rgba(0,0,0,0.1)]"></div>
          <div className="w-4 h-4 bg-peach shadow-[2px_2px_0_0_rgba(0,0,0,0.1)]"></div>
          <h3 className="font-game text-xl font-bold text-sky uppercase tracking-wide drop-shadow-sm">
            {title}
          </h3>
        </div>
      )}
      <div className="space-y-4">
        {children}
      </div>
    </BrightCard>
  );
};

