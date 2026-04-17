import React from 'react';

export const BrightButton = ({ 
  children, 
  variant = 'blue', 
  size = 'md', 
  onClick, 
  disabled,
  className = '', 
  ...props 
}) => {
  const variants = {
    blue: 'bg-sky text-white border-primary focus:ring-sky/30',
    green: 'bg-green text-white border-primary focus:ring-green/30',
    yellow: 'bg-yellow text-slate-900 border-primary focus:ring-yellow/30',
    sky: 'bg-sky text-white border-primary focus:ring-sky/30',
    peach: 'bg-peach text-white border-primary focus:ring-peach/30',
    lavender: 'bg-lavender text-white border-primary focus:ring-lavender/30',
    white: 'bg-panel-bg text-primary border-primary focus:ring-primary/20'
  };

  const sizes = {
    sm: 'px-4 py-2 text-[10px]',
    md: 'px-6 py-3 text-xs',
    lg: 'px-8 py-4 text-sm',
    xl: 'px-12 py-5 text-base'
  };

  const baseClasses = `
    bright-button transition-all duration-100 disabled:opacity-50 disabled:pointer-events-none
    ${sizes[size] || sizes.md}
    ${className}
  `;

  return (
    <button
      onClick={onClick}
      disabled={disabled}
      className={baseClasses}
      style={{ '--button-color': `var(--color-${variant === 'blue' ? 'sky' : variant})` }}
      {...props}
    >
      {children}
    </button>
  );
};
