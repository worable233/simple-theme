export interface SimpleThemeConfig {
  siteUrl: string
  homeUrl: string
  restRoot: string
  themeUrl: string
  routes: {
    resolveUrl: string
    menusBase: string
  }
}

export interface SiteInfo {
  name: string
  description: string
  url: string
}

export interface RenderedText {
  rendered: string
}

export interface WordPressPost {
  id: number
  date: string
  link: string
  type: string
  title: RenderedText
  excerpt: RenderedText
  content: RenderedText
}

export interface MenuItem {
  id: number
  title: string
  url: string
  path: string
  target: string
  description: string
  current: boolean
  children: MenuItem[]
}

export interface MenuCollectionResponse {
  items: MenuItem[]
}

export interface ResolveResponse {
  type: 'home' | 'post' | 'page' | 'term' | '404' | 'error'
  id?: number
  name?: string
  taxonomy?: string
  permalink?: string
  restUrl?: string
  message?: string
}
