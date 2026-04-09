<script setup lang="ts">
import { computed } from 'vue'
import type { WordPressPost } from '@/types/wordpress'

const props = defineProps<{
  pageData: WordPressPost
}>()

const formatDate = (dateString: string) =>
  new Intl.DateTimeFormat('zh-CN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(dateString))

const primaryBadge = computed(() => props.pageData.categories?.[0] || '页面')

const pageTags = computed(() => {
  const page = props.pageData as WordPressPost & { _embedded?: Record<string, unknown> }
  const terms = (page._embedded?.['wp:term'] as Array<Array<{ taxonomy?: string; name?: string }>> | undefined) || []
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
</script>

<template>
  <article class="card article-card page-view">
    <header class="vstack gap-3 article-card__header">
      <span class="badge secondary">{{ primaryBadge }}</span>
      <h1 v-html="pageData.title.rendered"></h1>

      <div class="hstack gap-2 post-meta-row">
        <time class="text-light" :datetime="pageData.date">发布 {{ formatDate(pageData.date) }}</time>
        <span v-if="pageData.modified" class="text-light">修改 {{ formatDate(pageData.modified) }}</span>
        <span v-for="tag in pageTags" :key="tag" class="badge outline">#{{ tag }}</span>
      </div>

      <p class="text-light article-card__permalink">
        页面链接：
        <a :href="pageData.link">{{ pageData.link }}</a>
      </p>
    </header>

    <div class="wp-content oat-prose" v-html="pageData.content.rendered"></div>
  </article>
</template>
