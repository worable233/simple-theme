<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useSiteShell } from '@/composables/useSiteShell'
import { fetchCollection, getErrorMessage } from '@/lib/wordpress'
import { toInternalPath } from '@/lib/theme-config'
import type { WordPressPost } from '@/types/wordpress'

const { ensureLoaded, siteInfo } = useSiteShell()

const latestPosts = ref<WordPressPost[]>([])
const latestShuoshuo = ref<WordPressPost[]>([])
const loading = ref(true)
const errorMessage = ref('')
const typedSubtitle = ref('')
let typingTimer: number | null = null

const postListClass = computed(() => {
  const columns = siteInfo.value.theme?.homePostColumns || '2'
  return `post-list--${columns}`
})

const metaConfig = computed(
  () =>
    siteInfo.value.theme?.cardMeta || {
      showCategory: true,
      showPublishDate: true,
      showModifiedDate: false,
      showCommentCount: true,
      showViewCount: true,
      showReadingTime: true,
      showWordCount: false,
    },
)

const collections = computed(() => siteInfo.value.collections)

const heroModeClass = computed(() => {
  const mode = siteInfo.value.hero?.displayMode || 'inset'
  return `hero-cover--${mode}`
})

const heroStyle = computed(() => {
  const hero = siteInfo.value.hero
  if (!hero?.useImage || !hero.image) {
    return {
      background:
        'radial-gradient(circle at top left, rgb(from var(--primary) r g b / 0.18), transparent 35%), var(--background)',
    }
  }

  return {
    backgroundImage: `linear-gradient(rgb(0 0 0 / var(--hero-overlay-opacity)), rgb(0 0 0 / calc(var(--hero-overlay-opacity) - 0.08))), url(${hero.image})`,
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    color: '#fff',
  }
})

const heroSubtitle = computed(() => {
  const hero = siteInfo.value.hero
  if (!hero) {
    return ''
  }

  return hero.subtitle || siteInfo.value.introSubtitle || siteInfo.value.description || ''
})

const heroTitle = computed(() => {
  const hero = siteInfo.value.hero
  if (!hero) {
    return siteInfo.value.introTitle || siteInfo.value.name
  }

  return hero.title || siteInfo.value.introTitle || siteInfo.value.name
})

const formatDate = (dateString: string) =>
  new Intl.DateTimeFormat('zh-CN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(dateString))

function stopTyping() {
  if (typingTimer) {
    window.clearTimeout(typingTimer)
    typingTimer = null
  }
}

function startTyping() {
  stopTyping()

  const hero = siteInfo.value.hero
  if (!hero?.typewriterEnabled) {
    typedSubtitle.value = heroSubtitle.value
    return
  }

  const lines = hero.typewriterTexts
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean)

  const source = lines.length > 0 ? lines : [heroSubtitle.value]
  if (source.length === 0) {
    typedSubtitle.value = ''
    return
  }

  const interval = Math.max(30, hero.typewriterInterval || 110)
  let lineIndex = 0
  let charIndex = 0
  let deleting = false

  const tick = () => {
    const current = source[lineIndex] || ''

    if (!deleting) {
      charIndex += 1
      typedSubtitle.value = current.slice(0, charIndex)

      if (charIndex >= current.length) {
        deleting = true
        typingTimer = window.setTimeout(tick, 900)
        return
      }

      typingTimer = window.setTimeout(tick, interval)
      return
    }

    charIndex -= 1
    typedSubtitle.value = current.slice(0, Math.max(0, charIndex))

    if (charIndex <= 0) {
      deleting = false
      lineIndex = (lineIndex + 1) % source.length
    }

    typingTimer = window.setTimeout(tick, Math.max(20, Math.floor(interval * 0.55)))
  }

  typedSubtitle.value = ''
  typingTimer = window.setTimeout(tick, interval)
}

const loadHomepageData = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    await ensureLoaded()

    const [postsResponse, shuoshuoResponse] = await Promise.all([
      fetchCollection('post', { limit: collections.value?.homePostCount || 6 }),
      collections.value?.showShuoshuoSection
        ? fetchCollection('shuoshuo', { limit: collections.value?.homeShuoshuoCount || 3 })
        : Promise.resolve({ items: [], total: 0, totalPages: 0, page: 1, perPage: 0 }),
    ])

    latestPosts.value = postsResponse.items
    latestShuoshuo.value = shuoshuoResponse.items
  } catch (error) {
    errorMessage.value = getErrorMessage(error, '首页内容加载失败，请稍后再试。')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  void loadHomepageData()
  startTyping()
})

watch(
  () => [siteInfo.value.hero?.typewriterEnabled, siteInfo.value.hero?.typewriterTexts, siteInfo.value.hero?.subtitle],
  () => {
    startTyping()
  },
)

onUnmounted(() => {
  stopTyping()
})
</script>

<template>
  <main>
    <section v-if="siteInfo.hero?.enabled" class="hero-cover home-hero" :class="heroModeClass" :style="heroStyle">
      <div class="hero-cover__inner home-hero__inner">
        <img
          v-if="siteInfo.hero?.showAvatar && siteInfo.hero?.avatar"
          class="hero-cover__avatar"
          :src="siteInfo.hero.avatar"
          alt="Avatar"
        />

        <div class="vstack gap-3 home-hero__copy">
          <span class="badge outline">{{ siteInfo.name }}</span>
          <h1>{{ heroTitle }}</h1>
          <h3 class="slogan text-light" :class="{ 'hero-cover__typed': siteInfo.hero?.typewriterEnabled }">
            {{ siteInfo.hero?.typewriterEnabled ? typedSubtitle : heroSubtitle }}
          </h3>
          <div class="hstack gap-2">
            <RouterLink class="button ghost" to="/#main-content">浏览文章</RouterLink>
            <RouterLink class="button ghost" to="/shuoshuo">查看说说</RouterLink>
          </div>
        </div>
      </div>
    </section>

    <div class="container">
      <section v-if="!siteInfo.hero?.enabled" class="intro">
        <div class="vstack gap-2">
          <h1>{{ siteInfo.introTitle || siteInfo.name }}</h1>
          <h3 class="slogan text-light">
            {{ siteInfo.introSubtitle || siteInfo.description || '欢迎来到这里。' }}
          </h3>
        </div>
      </section>

      <section class="feed-section">
        <div class="feed-section__head">
          <div class="vstack gap-1">
            <h2>{{ collections?.postsTitle || '最新文章' }}</h2>
            <p class="text-light">{{ collections?.postsSubtitle || '最近更新的长文内容。' }}</p>
          </div>
          <p class="text-light">{{ latestPosts.length }} 篇内容</p>
        </div>

        <div v-if="loading" class="row post-list post-list--2">
          <div v-for="item in 4" :key="item" class="post-list__item">
            <article class="card">
              <div class="vstack gap-3">
                <div class="skeleton line" role="status"></div>
                <div class="skeleton line" role="status"></div>
                <div class="skeleton line" role="status"></div>
              </div>
            </article>
          </div>
        </div>

        <div v-else-if="errorMessage" role="alert" data-variant="error">
          {{ errorMessage }}
        </div>

        <div v-else-if="latestPosts.length === 0" role="alert" data-variant="warning">
          还没有文章，先发布几篇再来看看吧。
        </div>

        <div v-else class="row post-list content-grid" :class="postListClass">
          <article v-for="post in latestPosts" :key="post.id" class="card post-list__item content-card">
            <a
              v-if="post.featuredImage"
              class="content-card__cover"
              :href="post.link"
              :style="{ backgroundImage: `url(${post.featuredImage})` }"
              :aria-label="post.title.rendered"
            ></a>

            <div class="vstack gap-4 content-card__body">
              <header class="vstack gap-2">
                <div class="hstack gap-2">
                  <span class="badge secondary">{{ post.categories?.[0] || '文章' }}</span>
                  <span v-if="metaConfig.showPublishDate" class="text-light">
                    {{ formatDate(post.date) }}
                  </span>
                </div>

                <a :href="post.link" class="content-card__title" v-html="post.title.rendered"></a>
              </header>

              <div class="post-card-meta hstack gap-2">
                <span v-if="metaConfig.showModifiedDate && post.modified" class="text-light">
                  更新 {{ formatDate(post.modified) }}
                </span>
                <span v-if="metaConfig.showCommentCount" class="text-light">
                  评论 {{ post.commentCount || 0 }}
                </span>
                <span v-if="metaConfig.showViewCount" class="text-light">
                  浏览 {{ post.viewCount || 0 }}
                </span>
                <span v-if="metaConfig.showReadingTime" class="text-light">
                  阅读 {{ post.readingTime || 1 }} 分钟
                </span>
                <span v-if="metaConfig.showWordCount" class="text-light">
                  字数 {{ post.wordCount || 0 }}
                </span>
              </div>

              <div class="content-card__excerpt text-light" v-html="post.excerpt?.rendered"></div>

              <div class="hstack gap-2">
                <RouterLink :to="toInternalPath(post.link)" class="button ghost">阅读全文</RouterLink>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section
        v-if="collections?.showShuoshuoSection"
        class="feed-section feed-section--timeline"
      >
        <div class="feed-section__head">
          <div class="vstack gap-1">
            <h2>{{ collections?.shuoshuoTitle || '最近说说' }}</h2>
            <p class="text-light">{{ collections?.shuoshuoSubtitle || '更短、更即时的动态记录。' }}</p>
          </div>
          <RouterLink class="button ghost small" to="/shuoshuo">全部说说</RouterLink>
        </div>

        <div v-if="!loading && latestShuoshuo.length === 0" class="text-light">还没有发布说说内容。</div>

        <div v-else class="timeline-list">
          <article v-for="post in latestShuoshuo" :key="post.id" class="card timeline-card">
            <div class="timeline-card__rail"></div>

            <div class="vstack gap-3 timeline-card__body">
              <div class="hstack justify-between">
                <span class="badge outline">说说</span>
                <time class="text-light" :datetime="post.date">{{ formatDate(post.date) }}</time>
              </div>

              <a class="timeline-card__title" :href="post.link" v-html="post.title.rendered"></a>
              <div class="text-light" v-html="post.excerpt?.rendered || post.content?.rendered"></div>

              <div class="hstack gap-2">
                <RouterLink :to="toInternalPath(post.link)" class="button ghost small">查看详情</RouterLink>
              </div>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>
