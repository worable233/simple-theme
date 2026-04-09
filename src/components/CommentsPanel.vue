<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import CommentsTreeItem from '@/components/CommentsTreeItem.vue'
import { createComment, fetchComments, getErrorMessage } from '@/lib/wordpress'
import type { CommentFormSettings, WordPressComment } from '@/types/wordpress'

const props = defineProps<{
  postId: number
  enabled: boolean
  formSettings: CommentFormSettings
}>()

const comments = ref<WordPressComment[]>([])
const loading = ref(false)
const submitting = ref(false)
const errorMessage = ref('')

const authorName = ref('')
const authorEmail = ref('')
const authorUrl = ref('')
const content = ref('')
const cookiesConsent = ref(false)
const parentCommentId = ref(0)

function applyLike(items: WordPressComment[], id: number, likes: number): boolean {
  for (const item of items) {
    if (item.id === id) {
      item.likes = likes
      return true
    }
    if (item.children.length > 0 && applyLike(item.children, id, likes)) {
      return true
    }
  }

  return false
}

function handleLiked(payload: { id: number; likes: number }) {
  applyLike(comments.value, payload.id, payload.likes)
}

function showToast(message: string, variant: 'info' | 'success' | 'danger' | 'warning' = 'info', duration = 2800) {
  const ot = (
    window as unknown as {
      ot?: { toast?: (msg: string, title?: string, opts?: { variant?: string; duration?: number }) => void }
    }
  ).ot
  ot?.toast?.(message, '评论通知', { variant, duration })
}

function handleLikeError(message: string) {
  showToast(message, 'warning', 3200)
}

async function loadComments() {
  if (!props.enabled || !props.postId) {
    comments.value = []
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    comments.value = await fetchComments(props.postId)
  } catch (error) {
    errorMessage.value = getErrorMessage(error, '评论加载失败，请稍后重试。')
  } finally {
    loading.value = false
  }
}

function useReply(id: number) {
  parentCommentId.value = id
}

async function submitComment() {
  errorMessage.value = ''

  if (!authorName.value.trim() || !content.value.trim()) {
    errorMessage.value = '请至少填写昵称和评论内容。'
    showToast('请填写必填项后再提交。', 'warning')
    return
  }

  if (props.formSettings.requireNameEmail && props.formSettings.showEmailField && !authorEmail.value.trim()) {
    errorMessage.value = '当前站点要求填写邮箱。'
    showToast('请填写邮箱。', 'warning')
    return
  }

  submitting.value = true

  try {
    await createComment({
      post: props.postId,
      parent: parentCommentId.value || undefined,
      author_name: authorName.value.trim(),
      author_email: props.formSettings.showEmailField ? authorEmail.value.trim() : '',
      author_url: props.formSettings.showUrlField ? authorUrl.value.trim() : '',
      content: content.value.trim(),
    })

    if (cookiesConsent.value) {
      localStorage.setItem('simple_theme_comment_name', authorName.value)
      localStorage.setItem('simple_theme_comment_email', authorEmail.value)
      localStorage.setItem('simple_theme_comment_url', authorUrl.value)
    }

    content.value = ''
    parentCommentId.value = 0
    showToast('评论提交成功，等待审核。', 'success', 3200)
    await loadComments()
  } catch (error) {
    errorMessage.value = getErrorMessage(error, '评论提交失败，请稍后重试。')
    showToast('提交失败，请稍后重试。', 'danger', 3600)
  } finally {
    submitting.value = false
  }
}

watch(
  () => [props.postId, props.enabled],
  () => {
    void loadComments()
  },
  { immediate: true },
)

onMounted(() => {
  authorName.value = localStorage.getItem('simple_theme_comment_name') || ''
  authorEmail.value = localStorage.getItem('simple_theme_comment_email') || ''
  authorUrl.value = localStorage.getItem('simple_theme_comment_url') || ''
  cookiesConsent.value = authorName.value !== '' || authorEmail.value !== '' || authorUrl.value !== ''
  void loadComments()
})
</script>

<template>
  <section class="card comments-panel">
    <header class="vstack gap-1">
      <h3>评论区</h3>
      <p class="text-light">欢迎交流，点赞已限制为每条评论每人一次。</p>
    </header>

    <div v-if="!enabled" role="alert" data-variant="warning">当前文章未开启评论。</div>
    <div v-else-if="formSettings.registrationOnly" role="alert" data-variant="warning">
      站点设置为仅注册用户可评论，请先登录。
    </div>

    <div v-else class="vstack gap-3">
      <form class="comments-form comments-form--compact" @submit.prevent="submitComment">
        <div class="hstack gap-2 comments-form__title-row">
          <h4>{{ parentCommentId ? `回复 #${parentCommentId}` : '发表评论' }}</h4>
          <button
            v-if="parentCommentId"
            type="button"
            class="button ghost small"
            @click="parentCommentId = 0"
          >
            取消回复
          </button>
        </div>

        <div class="row comments-form-row">
          <div class="col-4">
            <label>
              昵称
              <input v-model="authorName" type="text" maxlength="40" required />
            </label>
          </div>

          <div v-if="formSettings.showEmailField" class="col-4">
            <label>
              邮箱
              <input
                v-model="authorEmail"
                type="email"
                maxlength="80"
                :required="formSettings.requireNameEmail"
              />
            </label>
          </div>

          <div v-if="formSettings.showUrlField" class="col-4">
            <label>
              网址
              <input v-model="authorUrl" type="url" maxlength="120" placeholder="https://" />
            </label>
          </div>

          <div class="col-12">
            <label>
              评论内容
              <textarea v-model="content" rows="3" required></textarea>
            </label>
          </div>
        </div>

        <div class="hstack justify-between comments-form__footer">
          <label v-if="formSettings.showCookiesOptIn" class="comments-form__remember">
            <input v-model="cookiesConsent" type="checkbox" />
            记住我的信息
          </label>

          <button type="submit" class="button small" :disabled="submitting">
            {{ submitting ? '提交中...' : '提交评论' }}
          </button>
        </div>
      </form>

      <div v-if="loading" class="vstack gap-2">
        <div class="skeleton line" role="status"></div>
        <div class="skeleton line" role="status"></div>
      </div>

      <div v-else-if="errorMessage" role="alert" data-variant="error">
        {{ errorMessage }}
      </div>

      <div v-else-if="comments.length === 0" class="text-light">还没有评论，来发第一条吧。</div>

      <ol v-else class="comments-list unstyled">
        <CommentsTreeItem
          v-for="item in comments"
          :key="item.id"
          :item="item"
          @reply="useReply"
          @liked="handleLiked"
          @like-error="handleLikeError"
        />
      </ol>
    </div>
  </section>
</template>
