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
    lg: 'p-8',
    none: '',
  };

  // FIX: glow class is now actually applied to the element
  const glowClass = glow 
    ? 'shadow-[12px_12px_0_0_rgba(56,189,248,0.3)] hover:shadow-[16px_16px_0_0_rgba(56,189,248,0.5)]' 
    : '';

  return (
    <div className={`
      bright-card ${glowClass}
      ${paddings[padding] || paddings.md}
      ${className}
    `} {...props}>
      {children}
    </div>
  );
};
