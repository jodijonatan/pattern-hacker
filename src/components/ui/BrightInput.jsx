import React from 'react';

export const BrightInput = ({
  type = 'text',
  value,
  onChange,
  placeholder,
  className = '',
  size = 'md',
  ...props
}) => {
  const sizes = {
    sm: 'text-sm h-12 px-4',
    md: 'text-base h-14 px-6',
    lg: 'text-lg h-16 px-8',
    xl: 'text-xl h-20 px-10'
  };

  return (
    <div className="relative group">
      <input
        type={type}
        value={value}
        onChange={onChange}
        placeholder={placeholder}
        className={`
          pixel-box w-full font-pixel tracking-wide text-center 
          transition-all duration-300 placeholder:text-secondary/50 text-primary
          focus:outline-none focus:ring-4 focus:ring-sky/10
          ${sizes[size] || sizes.md}
          ${className}
        `}
        {...props}
      />
      {/* Decorative Inner Shadow for "Recessed" look */}
      <div className="absolute inset-0 pixel-corners pointer-events-none shadow-[inset_4px_4px_0_0_rgba(0,0,0,0.1)] group-focus-within:shadow-[inset_4px_4px_0_0_rgba(0,0,0,0.2)] transition-shadow" />
    </div>
  );
};
