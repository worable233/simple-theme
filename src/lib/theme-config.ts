import type { SimpleThemeConfig } from '@/types/wordpress'

const origin = window.location.origin

const fallbackConfig: SimpleThemeConfig = {
  siteUrl: `${origin}/`,
  homeUrl: `${origin}/`,
  restRoot: `${origin}/wp-json/`,
  themeUrl: `${origin}/wp-content/themes/simple-theme`,
  routes: {
    resolveUrl: `${origin}/wp-json/simple-theme/v1/resolve-url`,
    menusBase: `${origin}/wp-json/simple-theme/v1/navigation`,
  },
}

const injectedConfig = window.SimpleThemeConfig

const themeConfig: SimpleThemeConfig = injectedConfig
  ? {
      ...fallbackConfig,
      ...injectedConfig,
      routes: {
        ...fallbackConfig.routes,
        ...injectedConfig.routes,
      },
    }
  : fallbackConfig

const siteBaseUrl = new URL(themeConfig.homeUrl)

const trimTrailingSlash = (value: string) => {
  if (value.length <= 1) {
    return value || '/'
  }

  return value.replace(/\/+$/, '')
}

const siteBasePath = trimTrailingSlash(siteBaseUrl.pathname || '/')

const stripSiteBase = (pathname: string) => {
  if ('/' === siteBasePath) {
    return pathname || '/'
  }

  if (pathname === siteBasePath) {
    return '/'
  }

  if (pathname.startsWith(`${siteBasePath}/`)) {
    return pathname.slice(siteBasePath.length) || '/'
  }

  return pathname || '/'
}

export function getThemeConfig() {
  return themeConfig
}

export function getRouterBase() {
  return '/' === siteBasePath ? '/' : `${siteBasePath}/`
}

export function toInternalPath(value: string) {
  if (!value) {
    return '/'
  }

  try {
    const targetUrl = new URL(value, themeConfig.homeUrl)
    const pathname = stripSiteBase(targetUrl.pathname)
    return `${pathname}${targetUrl.search}${targetUrl.hash}`
  } catch {
    return value.startsWith('/') ? value : `/${value}`
  }
}

export function isExternalUrl(value: string) {
  if (!value) {
    return false
  }

  try {
    const targetUrl = new URL(value, themeConfig.homeUrl)
    const sharesOrigin = targetUrl.origin === siteBaseUrl.origin
    const sharesBase =
      '/' === siteBasePath ||
      targetUrl.pathname === siteBasePath ||
      targetUrl.pathname.startsWith(`${siteBasePath}/`)

    return !(sharesOrigin && sharesBase)
  } catch {
    return false
  }
}
