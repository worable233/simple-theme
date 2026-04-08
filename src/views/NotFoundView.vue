<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <section class="page-section">
    <article class="card not-found-card">
      <div class="vstack gap-4 align-center">
        <span class="badge danger">404</span>
        <h1>页面不存在</h1>
        <p class="text-light">这个地址没有匹配到 WordPress 内容，请检查链接是否正确。</p>
        <RouterLink class="button" to="/">返回首页</RouterLink>
      </div>
    </article>
  </section>
</template>
