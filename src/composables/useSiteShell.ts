import { computed, ref } from 'vue'
import { fetchNavigation, fetchSiteInfo, getErrorMessage } from '@/lib/wordpress'
import type { HeroSettings, MenuItem, SiteInfo, ThemeSettings } from '@/types/wordpress'

const fallbackThemeSettings: ThemeSettings = {
  homePostColumns: '2',
  primaryColor: '#574747',
  bodyFont: 'system-ui, sans-serif',
  headingFont: 'system-ui, sans-serif',
  radius: 'medium',
  shadow: 'small',
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
  title: 'Simple Theme',
  subtitle: '使用 Vue 3 与 Oat 驱动的 WordPress 前台主题。',
  typewriterEnabled: false,
  typewriterInterval: 110,
  typewriterTexts: '',
}

const fallbackSiteInfo: SiteInfo = {
  name: 'Simple Theme',
  description: '使用 Vue 3 与 Oat 驱动的 WordPress 前台主题。',
  url: window.location.origin,
  introTitle: 'Simple Theme',
  introSubtitle: '使用 Vue 3 与 Oat 驱动的 WordPress 前台主题。',
  footerHtml: '<p>© 2026 Simple Theme</p><p>Powered by WordPress + Simple Theme</p>',
  comments: {
    requireNameEmail: true,
    registrationOnly: false,
    showEmailField: true,
    showUrlField: true,
    showCookiesOptIn: true,
  },
  hero: fallbackHeroSettings,
  theme: fallbackThemeSettings,
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
