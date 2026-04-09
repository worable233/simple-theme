import { computed, ref } from 'vue'
import { fetchNavigation, fetchSiteInfo, getErrorMessage } from '@/lib/wordpress'
import type { CollectionSettings, HeroSettings, MenuItem, SiteInfo, ThemeSettings } from '@/types/wordpress'

const fallbackThemeSettings: ThemeSettings = {
  homePostColumns: '2',
  primaryColor: '#8a5a44',
  bodyFont: '"Noto Sans SC", "PingFang SC", "Microsoft YaHei", sans-serif',
  headingFont: '"Noto Serif SC", "Source Han Serif SC", serif',
  radius: 'medium',
  shadow: 'small',
  backgroundLight: '#fcfbf7',
  backgroundDark: '#111315',
  cardLight: '#ffffff',
  cardDark: '#171a1d',
  foregroundLight: '#1f2937',
  foregroundDark: '#f7f7f2',
  accentLight: '#f3ecdf',
  accentDark: '#22282d',
  borderLight: '#e5d8c5',
  borderDark: '#343c44',
  containerMaxWidth: 1240,
  articleMaxWidth: 860,
  heroOverlay: 0.34,
  cardMeta: {
    showCategory: true,
    showPublishDate: true,
    showModifiedDate: false,
    showCommentCount: true,
    showViewCount: true,
    showReadingTime: true,
    showWordCount: false,
  },
}

const fallbackHeroSettings: HeroSettings = {
  enabled: true,
  displayMode: 'inset',
  useImage: false,
  image: '',
  showAvatar: false,
  avatar: '',
  title: '欢迎来到我的站点',
  subtitle: '在这里发布文章、页面与说说内容。',
  typewriterEnabled: false,
  typewriterInterval: 110,
  typewriterTexts: '',
}

const fallbackCollectionSettings: CollectionSettings = {
  postsTitle: '最新文章',
  postsSubtitle: '整理过的长文、笔记与项目更新。',
  shuoshuoTitle: '最近说说',
  shuoshuoSubtitle: '更轻量的动态、灵感和碎片记录。',
  showShuoshuoSection: true,
  homePostCount: 6,
  homeShuoshuoCount: 3,
  shuoshuoPageSize: 12,
}

const fallbackSiteInfo: SiteInfo = {
  name: '我的站点',
  description: '一个支持文章、页面和说说内容的现代化主题。',
  url: window.location.origin,
  introTitle: '我的站点',
  introSubtitle: '欢迎来到这里，看看最近更新了什么。',
  footerHtml: '<p>© 2026 我的站点</p><p>感谢你的来访。</p>',
  footerLinks: [
    { label: 'WordPress', url: 'https://wordpress.org/' },
    { label: '站点首页', url: window.location.origin },
  ],
  comments: {
    requireNameEmail: true,
    registrationOnly: false,
    showEmailField: true,
    showUrlField: true,
    showCookiesOptIn: true,
  },
  hero: fallbackHeroSettings,
  theme: fallbackThemeSettings,
  collections: fallbackCollectionSettings,
}

const siteInfo = ref<SiteInfo>(fallbackSiteInfo)
const primaryMenu = ref<MenuItem[]>([])
const footerMenu = ref<MenuItem[]>([])
const shellLoadingState = ref(false)
const shellLoadedState = ref(false)
const shellErrorState = ref('')

export function useSiteShell() {
  const ensureLoaded = async () => {
    if (shellLoadingState.value || shellLoadedState.value) {
      return
    }

    shellLoadingState.value = true
    shellErrorState.value = ''

    try {
      const [nextSiteInfo, nextPrimaryMenu, nextFooterMenu] = await Promise.all([
        fetchSiteInfo(),
        fetchNavigation('primary').catch(() => []),
        fetchNavigation('footer').catch(() => []),
      ])

      siteInfo.value = {
        ...fallbackSiteInfo,
        ...nextSiteInfo,
        hero: {
          ...fallbackHeroSettings,
          ...nextSiteInfo.hero,
        },
        theme: {
          ...fallbackThemeSettings,
          ...nextSiteInfo.theme,
          cardMeta: {
            ...fallbackThemeSettings.cardMeta!,
            ...nextSiteInfo.theme?.cardMeta,
          },
        },
        collections: {
          ...fallbackCollectionSettings,
          ...nextSiteInfo.collections,
        },
        comments: {
          requireNameEmail:
            nextSiteInfo.comments?.requireNameEmail ?? fallbackSiteInfo.comments!.requireNameEmail,
          registrationOnly:
            nextSiteInfo.comments?.registrationOnly ?? fallbackSiteInfo.comments!.registrationOnly,
          showEmailField:
            nextSiteInfo.comments?.showEmailField ?? fallbackSiteInfo.comments!.showEmailField,
          showUrlField:
            nextSiteInfo.comments?.showUrlField ?? fallbackSiteInfo.comments!.showUrlField,
          showCookiesOptIn:
            nextSiteInfo.comments?.showCookiesOptIn ?? fallbackSiteInfo.comments!.showCookiesOptIn,
        },
        footerLinks:
          nextSiteInfo.footerLinks && nextSiteInfo.footerLinks.length > 0
            ? nextSiteInfo.footerLinks
            : fallbackSiteInfo.footerLinks,
      }

      primaryMenu.value = nextPrimaryMenu
      footerMenu.value = nextFooterMenu
      shellLoadedState.value = true
    } catch (error) {
      shellErrorState.value = getErrorMessage(error, '站点基础信息加载失败，请稍后重试。')
    } finally {
      shellLoadingState.value = false
    }
  }

  return {
    siteInfo: computed(() => siteInfo.value),
    primaryMenu: computed(() => primaryMenu.value),
    footerMenu: computed(() => footerMenu.value),
    shellLoading: computed(() => shellLoadingState.value),
    shellError: computed(() => shellErrorState.value),
    ensureLoaded,
  }
}
