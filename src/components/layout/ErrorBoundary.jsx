import React from 'react';

/**
 * ErrorBoundary catches JavaScript errors anywhere in their child component tree, 
 * logs those errors, and displays a fallback UI instead of the component tree that crashed.
 */
class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error) {
    // Update state so the next render will show the fallback UI.
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    // You can also log the error to an error reporting service
    console.error("Critical System Failure:", error, errorInfo);
  }

  handleReset = () => {
    // Attempt to recover by reloading the app or clearing state
    this.setState({ hasError: false, error: null });
    window.location.href = '/';
  };

  render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-screen w-full bg-[#0f172a] flex flex-col items-center justify-center p-6 text-center">
          <div className="bright-bg" />
          <div className="bright-overlay" />
          
          <div className="relative z-10 max-w-md w-full space-y-8 animate-screen-in">
            <div className="pixel-error bg-red-500/10 border-4 border-red-500 p-8 shadow-[8px_8px_0_0_rgba(239,68,68,0.2)]">
              <h1 className="font-game text-3xl text-red-500 mb-4 uppercase tracking-tighter">
                System Failure
              </h1>
              <p className="font-pixel text-sm text-slate-300 leading-relaxed mb-6">
                A critical logic error has destabilized the sequence. The kernel has been halted to prevent further corruption.
              </p>
              
              <div className="bg-black/50 p-4 font-mono text-[10px] text-red-400 text-left overflow-x-auto whitespace-pre-wrap border border-red-500/20 mb-8">
                ERROR_CODE: {this.state.error?.name || "UNKNOWN_EXC"}<br />
                MESSAGE: {this.state.error?.message || "Segmentation fault"}
              </div>

              <button
                onClick={this.handleReset}
                className="w-full bg-red-500 hover:bg-red-600 text-white font-game py-4 px-8 text-sm transition-all shadow-[4px_4px_0_0_rgba(0,0,0,0.3)] hover:translate-x-[2px] hover:translate-y-[2px] active:shadow-none active:translate-x-[4px] active:translate-y-[4px]"
              >
                REBOOT SYSTEM
              </button>
            </div>
            
            <p className="font-pixel text-[10px] text-slate-500 uppercase tracking-widest">
              Emergency recovery protocol v1.0.4
            </p>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}

export default ErrorBoundary;
