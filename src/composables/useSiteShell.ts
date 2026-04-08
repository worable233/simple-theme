import { computed, ref } from 'vue'
import { fetchNavigation, fetchSiteInfo, getErrorMessage } from '@/lib/wordpress'
import type { MenuItem, SiteInfo } from '@/types/wordpress'

const fallbackSiteInfo: SiteInfo = {
  name: 'Simple Theme',
  description: '使用 Vue 3 与 Oat 驱动的 WordPress 前台主题。',
  url: window.location.origin,
}

const siteInfo = ref<SiteInfo>(fallbackSiteInfo)
const primaryMenu = ref<MenuItem[]>([])
const footerMenu = ref<MenuItem[]>([])
const shellLoadingState = ref(false)
const shellLoadedState = ref(false)
const shellErrorState = ref('')

export function useSiteShell() {
  // 这里使用模块级单例缓存，避免头部、页脚和页面内容重复请求站点信息。
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

      siteInfo.value = { ...fallbackSiteInfo, ...nextSiteInfo }
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
