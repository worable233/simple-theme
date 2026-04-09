export interface SimpleThemeConfig {
  siteUrl: string
  homeUrl: string
  restRoot: string
  themeUrl: string
  routes: {
    resolveUrl: string
    menusBase: string
    siteInfo: string
    collection: string
  }
}

export type HomePostColumns = '1' | '2' | '4'
export type ThemeRadius = 'small' | 'medium' | 'large'
export type ThemeShadow = 'none' | 'small' | 'medium' | 'large'

export interface ThemeSettings {
  homePostColumns: HomePostColumns
  primaryColor: string
  bodyFont: string
  headingFont: string
  radius: ThemeRadius
  shadow: ThemeShadow
  backgroundLight: string
  backgroundDark: string
  cardLight: string
  cardDark: string
  foregroundLight: string
  foregroundDark: string
  accentLight: string
  accentDark: string
  borderLight: string
  borderDark: string
  containerMaxWidth: number
  articleMaxWidth: number
  heroOverlay: number
  cardMeta?: {
    showCategory: boolean
    showPublishDate: boolean
    showModifiedDate: boolean
    showCommentCount: boolean
    showViewCount: boolean
    showReadingTime: boolean
    showWordCount: boolean
  }
}

export interface HeroSettings {
  enabled: boolean
  displayMode: 'full' | 'half' | 'inset'
  useImage: boolean
  image: string
  showAvatar: boolean
  avatar: string
  title: string
  subtitle: string
  typewriterEnabled: boolean
  typewriterInterval: number
  typewriterTexts: string
}

export interface CommentFormSettings {
  requireNameEmail: boolean
  registrationOnly: boolean
  showEmailField: boolean
  showUrlField: boolean
  showCookiesOptIn: boolean
}

export interface FooterLink {
  label: string
  url: string
}

export interface CollectionSettings {
  postsTitle: string
  postsSubtitle: string
  shuoshuoTitle: string
  shuoshuoSubtitle: string
  showShuoshuoSection: boolean
  homePostCount: number
  homeShuoshuoCount: number
  shuoshuoPageSize: number
}

export interface SiteInfo {
  name: string
  description: string
  url: string
  introTitle?: string
  introSubtitle?: string
  footerHtml?: string
  footerLinks?: FooterLink[]
  hero?: HeroSettings
  comments?: CommentFormSettings
  theme?: ThemeSettings
  collections?: CollectionSettings
}

export interface RenderedText {
  rendered: string
}

export interface WordPressPost {
  id: number
  date: string
  modified?: string
  link: string
  type: string
  comment_status?: 'open' | 'closed'
  categories?: string[]
  tags?: string[]
  featuredImage?: string
  commentCount?: number
  viewCount?: number
  readingTime?: number
  wordCount?: number
  title: RenderedText
  excerpt?: RenderedText
  content?: RenderedText
  _embedded?: Record<string, unknown>
}

export interface CommentMetaInfo {
  location: string
  browser: string
  os: string
  ipMask: string
}

export interface WordPressComment {
  id: number
  parent: number
  date: string
  authorName: string
  content: RenderedText
  likes: number
  metaInfo: CommentMetaInfo
  children: WordPressComment[]
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

export interface PagedPostCollection {
  items: WordPressPost[]
  total: number
  totalPages: number
  page: number
  perPage: number
}

export interface ResolveResponse {
  type: 'home' | 'post' | 'page' | 'term' | '404' | 'error' | 'shuoshuo'
  id?: number
  name?: string
  taxonomy?: string
  permalink?: string
  restUrl?: string
  message?: string
}
