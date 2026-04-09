<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useSiteShell } from '@/composables/useSiteShell'
import { fetchCollection, getErrorMessage } from '@/lib/wordpress'
import { toInternalPath } from '@/lib/theme-config'
import type { WordPressPost } from '@/types/wordpress'

const { ensureLoaded, siteInfo } = useSiteShell()

const items = ref<WordPressPost[]>([])
const loading = ref(true)
const errorMessage = ref('')

const title = computed(() => siteInfo.value.collections?.shuoshuoTitle || '说说')
const subtitle = computed(
  () => siteInfo.value.collections?.shuoshuoSubtitle || '记录更轻量、更及时的想法和动态。',
)

const formatDate = (dateString: string) =>
  new Intl.DateTimeFormat('zh-CN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(dateString))

async function loadShuoshuo() {
  loading.value = true
  errorMessage.value = ''

  try {
    await ensureLoaded()
    const response = await fetchCollection('shuoshuo', {
      limit: siteInfo.value.collections?.shuoshuoPageSize || 12,
    })
    items.value = response.items
  } catch (error) {
    errorMessage.value = getErrorMessage(error, '说说内容加载失败，请稍后重试。')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  void loadShuoshuo()
})
</script>

<template>
  <section class="page-section shuoshuo-view vstack gap-5">
    <section class="hero-cover hero-cover--half shuoshuo-hero">
      <div class="hero-cover__inner shuoshuo-hero__inner">
        <div class="vstack gap-2">
          <span class="badge outline">Shuoshuo</span>
          <h1>{{ title }}</h1>
          <p class="text-light">{{ subtitle }}</p>
        </div>
      </div>
    </section>

    <div v-if="loading" class="vstack gap-3">
      <div v-for="item in 4" :key="item" class="card">
        <div class="vstack gap-3">
          <div class="skeleton line" role="status"></div>
          <div class="skeleton line" role="status"></div>
        </div>
      </div>
    </div>

    <div v-else-if="errorMessage" role="alert" data-variant="error">
      {{ errorMessage }}
    </div>

    <div v-else-if="items.length === 0" role="alert" data-variant="warning">
      还没有发布说说内容。
    </div>

    <div v-else class="timeline-list timeline-list--full">
      <article v-for="post in items" :key="post.id" class="card timeline-card timeline-card--full">
        <div class="timeline-card__rail"></div>

        <div class="vstack gap-3 timeline-card__body">
          <div class="hstack justify-between">
            <span class="badge secondary">说说</span>
            <time class="text-light" :datetime="post.date">{{ formatDate(post.date) }}</time>
          </div>

          <a class="timeline-card__title" :href="post.link" v-html="post.title.rendered"></a>
          <div class="text-light" v-html="post.excerpt?.rendered || post.content?.rendered"></div>

          <div class="hstack gap-2">
            <RouterLink :to="toInternalPath(post.link)" class="button ghost small">阅读全文</RouterLink>
            <span class="text-light">评论 {{ post.commentCount || 0 }}</span>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>
