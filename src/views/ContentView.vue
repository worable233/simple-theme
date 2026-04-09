<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { isExternalUrl, toInternalPath, toResolvablePath } from '@/lib/theme-config'
import {
  fetchContentByRestUrl,
  fetchPostCollectionByTaxonomy,
  getErrorMessage,
  resolveThemePath,
  trackPostView,
} from '@/lib/wordpress'
import type { ResolveResponse, WordPressPost } from '@/types/wordpress'
import CommentsPanel from '@/components/CommentsPanel.vue'
import { useSiteShell } from '@/composables/useSiteShell'
import NotFoundView from '@/views/NotFoundView.vue'
import PageView from '@/views/PageView.vue'

const route = useRoute()
const router = useRouter()
const { siteInfo } = useSiteShell()

const loading = ref(true)
const contentType = ref<ResolveResponse['type']>('home')
const errorMessage = ref('')
const postData = ref<WordPressPost | null>(null)
const termName = ref('')
const termTaxonomy = ref('')
const termPosts = ref<WordPressPost[]>([])
const termPostsLoading = ref(false)

const formatDate = (dateString: string) =>
  new Intl.DateTimeFormat('zh-CN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(dateString))

const readableContentType = computed(() => ('page' === contentType.value ? '页面' : '文章'))

const primaryCategory = computed(() => postData.value?.categories?.[0] || readableContentType.value)

const postTags = computed(() => {
  const post = postData.value as (WordPressPost & { _embedded?: Record<string, unknown> }) | null
  const terms = (post?._embedded?.['wp:term'] as Array<Array<{ taxonomy?: string; name?: string }>> | undefined) || []
  for (const group of terms) {
    const tags = group
      .filter((term) => term?.taxonomy === 'post_tag' && typeof term.name === 'string')
      .map((term) => term.name as string)
    if (tags.length > 0) {
      return tags
    }
  }
  return [] as string[]
})

const articleHeroClass = computed(() => {
  const mode = siteInfo.value.hero?.displayMode || 'inset'
  return mode === 'inset' ? 'article-hero--inset' : 'hero-cover--half'
})

const articleHeroStyle = computed(() => {
  const hero = siteInfo.value.hero
  const post = postData.value as (WordPressPost & { _embedded?: Record<string, unknown> }) | null
  const featuredUrl =
    (post?._embedded?.['wp:featuredmedia'] as Array<{ source_url?: string }> | undefined)?.[0]?.source_url || ''

  if (!hero?.useImage) {
    return { background: 'transparent' }
  }

  const image = featuredUrl || hero.image || ''
  if (!image) {
    return { background: 'transparent' }
  }

  return {
    backgroundImage: `linear-gradient(rgb(0 0 0 / 0.38), rgb(0 0 0 / 0.32)), url(${image})`,
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    color: '#fff',
  }
})

const loadTermPosts = async (taxonomy: string, id: number) => {
  termPostsLoading.value = true
  termPosts.value = []

  try {
    termPosts.value = await fetchPostCollectionByTaxonomy(taxonomy, id)
  } catch (error) {
    errorMessage.value = getErrorMessage(error, '归档内容加载失败，请稍后重试。')
  } finally {
    termPostsLoading.value = false
  }
}

const loadCurrentContent = async () => {
  loading.value = true
  errorMessage.value = ''
  postData.value = null
  termName.value = ''
  termTaxonomy.value = ''
  termPosts.value = []

  try {
    const resolved = await resolveThemePath(route.fullPath)
    contentType.value = resolved.type

    if (('post' === resolved.type || 'page' === resolved.type) && resolved.restUrl) {
      postData.value = await fetchContentByRestUrl(resolved.restUrl)
      if ('post' === resolved.type && postData.value?.id) {
        void trackPostView(postData.value.id)
      }
      return
    }

    if ('term' === resolved.type && resolved.id && resolved.taxonomy) {
      termName.value = resolved.name || '归档'
      termTaxonomy.value = resolved.taxonomy
      await loadTermPosts(resolved.taxonomy, resolved.id)
      return
    }

    if ('404' !== resolved.type && 'error' !== resolved.type && 'home' !== resolved.type) {
      errorMessage.value = resolved.message || '页面解析失败，请稍后重试。'
      contentType.value = 'error'
    }
  } catch (error) {
    errorMessage.value = getErrorMessage(error, '页面解析失败，请稍后重试。')
    contentType.value = 'error'
  } finally {
    loading.value = false
  }
}

const handleContentClick = (event: MouseEvent) => {
  const target = event.target

  if (!(target instanceof HTMLElement)) {
    return
  }

  const anchor = target.closest('a')

  if (!anchor) {
    return
  }

  const href = anchor.getAttribute('href')

  if (
    !href ||
    href.startsWith('#') ||
    'mailto:' === href.slice(0, 7) ||
    'tel:' === href.slice(0, 4) ||
    '_blank' === anchor.target ||
    anchor.hasAttribute('download') ||
    isExternalUrl(href)
  ) {
    return
  }

  event.preventDefault()
  void router.push(toInternalPath(href))
}

watch(
  () => toResolvablePath(route.fullPath),
  () => {
    void loadCurrentContent()
  },
  { immediate: true },
)
</script>

<template>
  <section class="page-section content-view" @click.capture="handleContentClick">
    <div v-if="loading" class="vstack gap-4">
      <div class="card">
        <div class="vstack gap-3">
          <div class="skeleton line" role="status"></div>
          <div class="skeleton line" role="status"></div>
          <div class="skeleton line" role="status"></div>
          <div class="skeleton line" role="status"></div>
        </div>
      </div>
    </div>

    <div v-else-if="'error' === contentType" role="alert" data-variant="error">
      {{ errorMessage || '页面加载失败，请稍后重试。' }}
    </div>

    <NotFoundView v-else-if="'404' === contentType" />

    <section v-else-if="'term' === contentType" class="vstack gap-5">
      <article class="card archive-card">
        <div class="vstack gap-4">
          <div class="hstack gap-2">
            <span class="badge">内容归档</span>
            <span class="badge outline">{{ termTaxonomy }}</span>
          </div>

          <div class="vstack gap-2">
            <h1>{{ termName }}</h1>
            <p class="text-light">当前展示的是 {{ termTaxonomy }} 对应的 WordPress 归档内容。</p>
          </div>
        </div>
      </article>

      <div v-if="termPostsLoading" class="card">
        <div class="vstack gap-3">
          <div class="skeleton line" role="status"></div>
          <div class="skeleton line" role="status"></div>
          <div class="skeleton line" role="status"></div>
        </div>
      </div>

      <div v-else-if="errorMessage" role="alert" data-variant="error">
        {{ errorMessage }}
      </div>

      <div v-else-if="termPosts.length === 0" role="alert" data-variant="warning">
        这个归档下还没有可展示的文章。
      </div>

      <div v-else class="row post-list post-list--two">
        <article v-for="post in termPosts" :key="post.id" class="card post-card">
          <div class="vstack gap-4">
            <div class="hstack gap-2">
              <span class="badge secondary">{{ post.categories?.[0] || '文章' }}</span>
              <time class="text-light" :datetime="post.date">{{ formatDate(post.date) }}</time>
            </div>

            <div class="vstack gap-2">
              <h3 v-html="post.title.rendered"></h3>
              <div class="post-card__excerpt text-light" v-html="post.excerpt.rendered"></div>
            </div>

            <div class="hstack gap-2">
              <RouterLink :to="toInternalPath(post.link)" class="button">阅读全文</RouterLink>
              <a :href="post.link" class="button ghost">原始链接</a>
            </div>
          </div>
        </article>
      </div>
    </section>

    <PageView v-else-if="'page' === contentType && postData" :page-data="postData" />

    <section v-else-if="postData" class="vstack gap-5">
      <section class="hero-cover article-hero" :class="articleHeroClass" :style="articleHeroStyle">
        <div class="hero-cover__inner article-hero__inner">
          <div class="vstack gap-2 article-hero__title">
            <span class="badge secondary">{{ primaryCategory }}</span>
            <h1 v-html="postData.title.rendered"></h1>
          </div>
        </div>
      </section>

      <article class="card article-card post-view">
        <header class="vstack gap-3 article-card__header">
          <div class="hstack gap-2 post-meta-row">
            <time class="text-light" :datetime="postData.date">发布 {{ formatDate(postData.date) }}</time>
            <span v-if="postData.modified" class="text-light">修改 {{ formatDate(postData.modified) }}</span>
            <span v-for="tag in postTags" :key="tag" class="badge outline">#{{ tag }}</span>
          </div>

          <p class="text-light article-card__permalink">
            当前链接：
            <a :href="postData.link">{{ postData.link }}</a>
          </p>
        </header>

        <div class="wp-content oat-prose" v-html="postData.content.rendered"></div>

        <footer class="hstack gap-2 article-card__footer">
          <RouterLink class="button" to="/">返回首页</RouterLink>
          <a :href="postData.link" class="button outline">打开原始链接</a>
        </footer>
      </article>

      <CommentsPanel
        :post-id="postData.id"
        :enabled="'open' === (postData.comment_status || 'closed')"
        :form-settings="siteInfo.comments!"
      />
    </section>
  </section>
</template>
