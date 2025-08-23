/// <reference types="vite/client" />

declare module 'ziggy-js' {
  export interface Config { url: string; port?: number | null; defaults?: any; routes: Record<string, any>; }
  export default function route(name?: string, params?: any, absolute?: boolean, config?: Config): string;
}

declare module '*.svg' { const src: string; export default src; }
declare module '*.png' { const src: string; export default src; }
declare module '*.jpg' { const src: string; export default src; }
declare module '*.jpeg' { const src: string; export default src; }
declare module '*.webp' { const src: string; export default src; }
declare module '*.css' { const src: string; export default src; }

declare module '*.json' {
  const value: any;
  export default value;
}
