import React from 'react';

export default class ErrorBoundary extends React.Component<
  { children: React.ReactNode },
  { hasError: boolean; error?: any; info?: any }
> {
  constructor(props: any) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(error: any) {
    return { hasError: true, error };
  }

  componentDidCatch(error: any, info: any) {
    console.error('UI render error:', error, info);
    // @ts-ignore
    window.__LAST_ERROR__ = { error, info };
  }

  render() {
    if (this.state.hasError) {
      if (import.meta?.env?.DEV) {
        return (
          <div className="p-6">
            <h1 className="text-xl font-semibold mb-2">Render error</h1>
            <pre className="whitespace-pre-wrap text-sm">
              {String(this.state.error?.message || this.state.error)}
            </pre>
          </div>
        );
      }
      return (
        <div className="p-6">Something went wrong rendering this page.</div>
      );
    }
    return this.props.children;
  }
}

