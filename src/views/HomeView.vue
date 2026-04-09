<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useSiteShell } from '@/composables/useSiteShell'
import { toInternalPath } from '@/lib/theme-config'
import { fetchLatestPosts, getErrorMessage } from '@/lib/wordpress'
import type { WordPressPost } from '@/types/wordpress'

const { ensureLoaded, siteInfo } = useSiteShell()

const latestPosts = ref<WordPressPost[]>([])
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

const heroModeClass = computed(() => {
  const mode = siteInfo.value.hero?.displayMode || 'inset'
  return `hero-cover--${mode}`
})

const heroStyle = computed(() => {
  const hero = siteInfo.value.hero
  if (!hero?.useImage || !hero.image) {
    return {
      background: 'transparent',
    }
  }

  return {
    backgroundImage: `linear-gradient(rgb(0 0 0 / 0.35), rgb(0 0 0 / 0.25)), url(${hero.image})`,
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

const loadLatestPosts = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    await ensureLoaded()
    latestPosts.value = await fetchLatestPosts()
  } catch (error) {
    errorMessage.value = getErrorMessage(error, '首页内容加载失败，请稍后再试。')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  void loadLatestPosts()
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
    <section v-if="siteInfo.hero?.enabled" class="hero-cover" :class="heroModeClass" :style="heroStyle">
      <div class="hero-cover__inner">
        <img
          v-if="siteInfo.hero?.showAvatar && siteInfo.hero?.avatar"
          class="hero-cover__avatar"
          :src="siteInfo.hero.avatar"
          alt="Avatar"
        />

        <div class="vstack gap-2">
          <h1>{{ heroTitle }}</h1>
          <h3 class="slogan text-light" :class="{ 'hero-cover__typed': siteInfo.hero?.typewriterEnabled }">
            {{ siteInfo.hero?.typewriterEnabled ? typedSubtitle : heroSubtitle }}
          </h3>
        </div>
      </div>
    </section>

    <div class="container">
      <section v-if="!siteInfo.hero?.enabled" class="intro">
        <div class="vstack gap-2">
          <h1>{{ siteInfo.introTitle || siteInfo.name }}</h1>
          <h3 class="slogan text-light">{{ siteInfo.introSubtitle || siteInfo.description || '副标题。' }}</h3>
        </div>
      </section>

      <section>
        <div class="hstack justify-between">
          <h2>文章</h2>
          <p class="text-light">{{ latestPosts.length }} 篇文章</p>
        </div>
      </section>

      <section class="features">
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

        <div v-else class="row post-list" :class="postListClass">
          <article v-for="post in latestPosts" :key="post.id" class="card post-list__item">
            <div class="vstack gap-4">
              <header class="vstack gap-2">
                <span class="badge secondary">文章</span>
                <a :href="post.link" v-html="post.title.rendered"></a>
              </header>

              <div class="post-card-meta hstack gap-2">
                <span v-if="metaConfig.showCategory && post.categories && post.categories.length > 0" class="badge outline">
                  {{ post.categories[0] }}
                </span>
                <span v-if="metaConfig.showPublishDate" class="text-light">发布 {{ formatDate(post.date) }}</span>
                <span v-if="metaConfig.showModifiedDate && post.modified" class="text-light">更新 {{ formatDate(post.modified) }}</span>
                <span v-if="metaConfig.showCommentCount" class="text-light">评论 {{ post.commentCount || 0 }}</span>
                <span v-if="metaConfig.showViewCount" class="text-light">浏览 {{ post.viewCount || 0 }}</span>
                <span v-if="metaConfig.showReadingTime" class="text-light">阅读 {{ post.readingTime || 1 }} 分钟</span>
                <span v-if="metaConfig.showWordCount" class="text-light">字数 {{ post.wordCount || 0 }}</span>
              </div>

              <p v-html="post.excerpt.rendered"></p>

              <div class="hstack gap-2">
                <RouterLink :to="toInternalPath(post.link)" class="button">查看</RouterLink>
              </div>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>