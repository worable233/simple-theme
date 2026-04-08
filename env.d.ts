/// <reference types="vite/client" />

import type { DefineComponent } from 'vue'
import type { SimpleThemeConfig } from './src/types/wordpress'

declare module '*.vue' {
  const component: DefineComponent<Record<string, never>, Record<string, never>, unknown>
  export default component
}

declare module '*.js' {
  const value: unknown
  export default value
}

declare global {
  interface Window {
    SimpleThemeConfig?: SimpleThemeConfig
  }
}

export {}
