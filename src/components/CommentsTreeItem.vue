<script setup lang="ts">
import { computed, ref } from 'vue'
import { likeComment } from '@/lib/wordpress'
import type { WordPressComment } from '@/types/wordpress'

defineOptions({
  name: 'CommentsTreeItem',
})

const props = defineProps<{
  item: WordPressComment
  depth?: number
}>()

const emit = defineEmits<{
  (e: 'reply', id: number): void
  (e: 'liked', payload: { id: number; likes: number }): void
  (e: 'like-error', message: string): void
}>()

const level = computed(() => props.depth || 0)
const liking = ref(false)
const liked = ref(localStorage.getItem(`simple_theme_comment_liked_${props.item.id}`) === '1')

const relativeTime = computed(() => {
  const now = Date.now()
  const then = new Date(props.item.date).getTime()
  const diff = Math.max(0, now - then)
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return '刚刚'
  if (mins < 60) return `${mins} 分钟前`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours} 小时前`
  const days = Math.floor(hours / 24)
  if (days < 30) return `${days} 天前`
  const months = Math.floor(days / 30)
  if (months < 12) return `${months} 个月前`
  const years = Math.floor(months / 12)
  return `${years} 年前`
})

async function handleLike() {
  if (liked.value || liking.value) {
    return
  }

  liking.value = true
  try {
    const nextLikes = await likeComment(props.item.id)
    liked.value = true
    localStorage.setItem(`simple_theme_comment_liked_${props.item.id}`, '1')
    emit('liked', { id: props.item.id, likes: nextLikes })
  } catch (error) {
    const message = error instanceof Error ? error.message : '点赞失败，请稍后再试。'
    emit('like-error', message)
  } finally {
    liking.value = false
  }
}
</script>

<template>
  <li class="comments-item" :style="{ marginLeft: `${Math.min(2, level) * 1}rem` }">
    <article class="vstack gap-2">
      <div class="hstack justify-between">
        <strong>{{ item.authorName || '匿名用户' }}</strong>
        <span class="text-light">{{ relativeTime }}</span>
      </div>

      <p class="text-light comments-meta">
        {{ item.metaInfo.location }} · {{ item.metaInfo.browser }} · {{ item.metaInfo.os }} · {{ item.metaInfo.ipMask }}
      </p>

      <div class="wp-content" v-html="item.content.rendered"></div>

      <div class="hstack gap-2 comments-actions">
        <button class="button ghost small" type="button" :disabled="liked || liking" @click="handleLike">
          {{ liked ? '已赞' : '点赞' }} {{ item.likes }}
        </button>
        <button class="button ghost small" type="button" @click="emit('reply', item.id)">回复</button>
      </div>
    </article>

    <ol v-if="item.children.length > 0" class="comments-list unstyled">
      <CommentsTreeItem
        v-for="child in item.children"
        :key="child.id"
        :item="child"
        :depth="level + 1"
        @reply="emit('reply', $event)"
        @liked="emit('liked', $event)"
        @like-error="emit('like-error', $event)"
      />
    </ol>
  </li>
</template>
