import axios from 'axios'
import { getThemeConfig, toInternalPath, toResolvablePath } from '@/lib/theme-config'
import type {
  MenuCollectionResponse,
  PagedPostCollection,
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
  const { items } = await fetchCollection('post', { limit })
  return items
}

export async function fetchCollection(
  type: 'post' | 'page' | 'shuoshuo',
  options?: {
    limit?: number
    page?: number
    taxonomy?: string
    termId?: number
  },
) {
  const collectionUrl = getThemeConfig().routes.collection.replace(/\/+$/, '')
  const params = new URLSearchParams({
    type,
    limit: String(options?.limit || 6),
    page: String(options?.page || 1),
  })

  if (options?.taxonomy && options.termId) {
    params.set('taxonomy', options.taxonomy)
    params.set('termId', String(options.termId))
  }

  const { data } = await apiClient.get<PagedPostCollection>(`${collectionUrl}?${params.toString()}`)
  return data
}

export async function fetchNavigation(location: string) {
  const baseUrl = getThemeConfig().routes.menusBase.replace(/\/+$/, '')
  const { data } = await apiClient.get<MenuCollectionResponse>(
    `${baseUrl}/${encodeURIComponent(location)}`,
  )

  return data.items
}

export async function fetchPostCollectionByTaxonomy(taxonomy: string, termId: number, limit = 12) {
  const { items } = await fetchCollection('post', {
    taxonomy,
    termId,
    limit,
  })

  return items
}

export async function resolveThemePath(path: string) {
  try {
    const { data } = await apiClient.post<ResolveResponse>(getThemeConfig().routes.resolveUrl, {
      path: toResolvablePath(path),
    })

    return data
  } catch (error) {
    if (axios.isAxiosError<ResolveResponse>(error) && error.response?.status === 404) {
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

    if (typeof message === 'string' && message.trim()) {
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
