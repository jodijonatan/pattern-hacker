import React from 'react';

export const BrightCard = ({ 
  children, 
  className = '', 
  glow = false,
  padding = 'md',
  ...props 
}) => {
  const paddings = {
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8'
  };

  const glowClass = glow 
    ? 'shadow-[12px_12px_0_0_rgba(56,189,248,0.3)] hover:shadow-[16px_16px_0_0_rgba(56,189,248,0.5)]' 
    : '';

  return (
    <div className={`
      bright-card ${className}
      ${paddings[padding] || paddings.md}
    `} {...props}>
      {children}
    </div>
  );
};

