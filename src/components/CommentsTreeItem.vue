<script setup lang="ts">
import { computed } from 'vue'
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
}>()

const level = computed(() => props.depth || 0)

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
  const nextLikes = await likeComment(props.item.id)
  emit('liked', { id: props.item.id, likes: nextLikes })
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
        <button class="button ghost small" type="button" @click="handleLike">👍 {{ item.likes }}</button>
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
      />
    </ol>
  </li>
</template>
