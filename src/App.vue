<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'
import { useSiteShell } from '@/composables/useSiteShell'

const { siteInfo, primaryMenu, footerMenu, shellError, shellLoading, ensureLoaded } = useSiteShell()

onMounted(() => {
  void ensureLoaded()
})
</script>

<template>
  <div class="app-shell">
    <SiteHeader :site-info="siteInfo" :menu-items="primaryMenu" :loading="shellLoading" />

    <main id="main-content" class="app-main">
      <div class="container">
        <div v-if="shellError" class="app-banner" role="alert" data-variant="warning">
          {{ shellError }}
        </div>

        <RouterView />
      </div>
    </main>

    <SiteFooter :site-info="siteInfo" :menu-items="footerMenu" />
  </div>
</template>
