<script setup lang="ts">
import type { SiteInfo } from '@/types/wordpress'

defineProps<{
  siteInfo: SiteInfo
}>()
</script>

<template>
  <footer class="site-footer">
    <div class="container">
      <section class="card site-footer__panel">
        <div class="site-footer__content wp-content" v-html="siteInfo.footerHtml"></div>
      </section>
    </div>
  </footer>
</template>