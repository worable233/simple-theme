<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useSiteShell } from '@/composables/useSiteShell'
import { toInternalPath } from '@/lib/theme-config'
import { fetchLatestPosts, getErrorMessage } from '@/lib/wordpress'
import type { WordPressPost } from '@/types/wordpress'

const { ensureLoaded, siteInfo } = useSiteShell()

const latestPosts = ref<WordPressPost[]>([])
const loading = ref(true)
const errorMessage = ref('')

const formatDate = (dateString: string) =>
  new Intl.DateTimeFormat('zh-CN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(dateString))

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
})
</script>
<template>
  <main>
    <div class="container">
      <section class="intro">
        <div class="vstack gap-2">
          <h1>{{ siteInfo.name }}</h1>
          <h3 class="slogan text-light">{{ siteInfo.description || '副标题。' }}</h3>
        </div>
      </section>

      <section>
        <div class="hstack justify-between">
          <h2>文章</h2>
          <p class="text-light">{{ latestPosts.length }} 篇文章</p>
        </div>
      </section>

      <section class="features">
        <!-- 骨架 -->
        <div v-if="loading" class="row">
          <div v-for="item in 4" :key="item" class="col-6">
            <article class="card">
              <div class="vstack gap-3">
                <div class="skeleton line" role="status"></div>
                <div class="skeleton line" role="status"></div>
                <div class="skeleton line" role="status"></div>
              </div>
            </article>
          </div>
        </div>
        <!-- 错误信息 -->
        <div v-else-if="errorMessage" role="alert" data-variant="error">
          {{ errorMessage }}
        </div>
        <!-- 暂无文章 -->
        <div v-else-if="latestPosts.length === 0" role="alert" data-variant="warning">
          随便发点东西再来看看吧。
        </div>
        <!-- 文章列表 -->
        <div v-else class="row">
          <article v-for="post in latestPosts" :key="post.id" class="card">
            <div class="vstack gap-4">
              <span class="badge secondary">文章</span>
              <time class="text-light" :datetime="post.date">{{ formatDate(post.date) }}</time>
              <header>
                <a :href="post.link" v-html="post.title.rendered"></a>
              </header>
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