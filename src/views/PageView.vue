<script setup lang="ts">
import type { WordPressPost } from '@/types/wordpress'

defineProps<{
  pageData: WordPressPost
}>()
</script>

<template>
  <article class="card article-card page-view">
    <header class="vstack gap-3 article-card__header">
      <span class="badge outline">页面</span>
      <h1 v-html="pageData.title.rendered"></h1>
      <p class="text-light article-card__permalink">
        页面链接：
        <a :href="pageData.link">{{ pageData.link }}</a>
      </p>
    </header>

    <div class="wp-content oat-prose" v-html="pageData.content.rendered"></div>
  </article>
</template>
