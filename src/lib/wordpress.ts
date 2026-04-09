import axios from 'axios'
import { getThemeConfig, toInternalPath, toResolvablePath } from '@/lib/theme-config'
import type {
  MenuCollectionResponse,
  ResolveResponse,
  SiteInfo,
  WordPressComment,
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
  const siteInfoUrl = getThemeConfig().routes.siteInfo
  const { data } = await apiClient.get<SiteInfo>(siteInfoUrl)
  return data
}

export async function fetchLatestPosts(limit = 6) {
  const { data } = await apiClient.get<WordPressPost[]>(
    buildRestUrl(`simple-theme/v1/home-posts?limit=${limit}`),
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
      path: toResolvablePath(path),
    })

    return data
  } catch (error) {
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

export async function trackPostView(postId: number) {
  const { data } = await apiClient.post<{ viewCount: number }>(
    buildRestUrl('simple-theme/v1/track-view'),
    { postId },
  )
  return data.viewCount
}

export async function fetchComments(postId: number) {
  const { data } = await apiClient.get<{ items: WordPressComment[] }>(
    buildRestUrl(`simple-theme/v1/comments/${postId}`),
  )
  return data.items
}

export async function createComment(payload: {
  post: number
  parent?: number
  author_name: string
  author_email?: string
  author_url?: string
  content: string
}) {
  const { data } = await apiClient.post<{ item: WordPressComment }>(
    buildRestUrl('simple-theme/v1/comments'),
    payload,
  )
  return data.item
}

export async function likeComment(commentId: number) {
  const { data } = await apiClient.post<{ likes: number }>(
    buildRestUrl('simple-theme/v1/comment-like'),
    { commentId },
  )
  return data.likes
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

export function toRouterPathFromWpLink(value: string) {
  return toInternalPath(value)
}
