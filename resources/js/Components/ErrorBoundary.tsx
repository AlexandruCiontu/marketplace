import React from 'react';
export default class ErrorBoundary extends React.Component<{children: React.ReactNode}, {hasError: boolean}> {
  constructor(props:any){
    super(props);
    this.state = { hasError: false };
  }
  static getDerivedStateFromError(){
    return { hasError: true };
  }
  componentDidCatch(err:any, info:any){
    console.error('UI error:', err, info);
  }
  render(){
    return this.state.hasError ? <div className="p-6">Something went wrong rendering this page.</div> : this.props.children;
  }
}
