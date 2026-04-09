<script setup lang="ts">
import { computed } from 'vue'
import { isExternalUrl } from '@/lib/theme-config'
import type { MenuItem, SiteInfo } from '@/types/wordpress'

const props = defineProps<{
  siteInfo: SiteInfo
  menuItems: MenuItem[]
}>()

const footerLinks = computed(() => props.siteInfo.footerLinks || [])
</script>

<template>
  <footer class="site-footer">
    <div class="container">
      <section class="card site-footer__panel">
        <div class="site-footer__content wp-content" v-html="siteInfo.footerHtml"></div>

        <div v-if="footerLinks.length > 0 || menuItems.length > 0" class="site-footer__actions">
          <a
            v-for="link in footerLinks"
            :key="`${link.label}-${link.url}`"
            class="button ghost small"
            :href="link.url"
            :target="isExternalUrl(link.url) ? '_blank' : undefined"
            :rel="isExternalUrl(link.url) ? 'noreferrer noopener' : undefined"
          >
            {{ link.label }}
          </a>

          <a
            v-for="item in menuItems"
            :key="item.id"
            class="button ghost small"
            :href="item.url"
            :target="item.target || undefined"
            :rel="item.target ? 'noreferrer noopener' : undefined"
          >
            {{ item.title }}
          </a>
        </div>
      </section>
    </div>
  </footer>
</template>
