import axios from 'axios'
import { getThemeConfig, toInternalPath } from '@/lib/theme-config'
import type {
  MenuCollectionResponse,
  ResolveResponse,
  SiteInfo,
  WordPressPost,
} from '@/types/wordpress'

const apiClient = axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
  },
})

const buildRestUrl = (path = '') => {
  const restRoot = getThemeConfig().restRoot.replace(/\/+$/, '')

  if (!path) {
    return `${restRoot}/`
  }

  return `${restRoot}/${path.replace(/^\/+/, '')}`
}

export async function fetchSiteInfo() {
  const { data } = await apiClient.get<SiteInfo>(buildRestUrl())
  return data
}

export async function fetchLatestPosts(limit = 6) {
  const { data } = await apiClient.get<WordPressPost[]>(
    buildRestUrl(`wp/v2/posts?_embed&per_page=${limit}`),
  )

  return data
}

export async function fetchNavigation(location: string) {
  const baseUrl = getThemeConfig().routes.menusBase.replace(/\/+$/, '')
  const { data } = await apiClient.get<MenuCollectionResponse>(
    `${baseUrl}/${encodeURIComponent(location)}`,
  )

  return data.items
}

export async function fetchPostCollectionByTaxonomy(taxonomy: string, termId: number) {
  const queryKey = 'category' === taxonomy ? 'categories' : 'tags'
  const { data } = await apiClient.get<WordPressPost[]>(
    buildRestUrl(`wp/v2/posts?_embed&${queryKey}=${termId}`),
  )

  return data
}

export async function resolveThemePath(path: string) {
  try {
    const { data } = await apiClient.post<ResolveResponse>(getThemeConfig().routes.resolveUrl, {
      path: toInternalPath(path),
    })

    return data
  } catch (error) {
    // 404 在这个接口里是一个可预期结果，需要按正常数据继续交给页面渲染。
    if (axios.isAxiosError<ResolveResponse>(error) && 404 === error.response?.status) {
      return error.response.data
    }

    throw error
  }
}

export async function fetchContentByRestUrl(restUrl: string) {
  const { data } = await apiClient.get<WordPressPost>(restUrl)
  return data
}

export function getErrorMessage(error: unknown, fallback = '请求失败，请稍后重试。') {
  if (axios.isAxiosError(error)) {
    const message = error.response?.data?.message

    if ('string' === typeof message && message.trim()) {
      return message
    }

    if (error.message) {
      return error.message
    }
  }

  if (error instanceof Error && error.message) {
    return error.message
  }

  return fallback
}
