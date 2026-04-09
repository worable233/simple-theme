<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { RouterView } from 'vue-router'
import SiteFooter from '@/components/SiteFooter.vue'
import SiteHeader from '@/components/SiteHeader.vue'
import { useSiteShell } from '@/composables/useSiteShell'
import type { ThemeRadius, ThemeSettings, ThemeShadow } from '@/types/wordpress'

const { siteInfo, primaryMenu, footerMenu, shellError, shellLoading, ensureLoaded } =
  useSiteShell()

const radiusMap: Record<ThemeRadius, { medium: string; large: string }> = {
  small: { medium: '0.25rem', large: '0.5rem' },
  medium: { medium: '0.375rem', large: '0.75rem' },
  large: { medium: '0.625rem', large: '1rem' },
}

const shadowMap: Record<ThemeShadow, { small: string; medium: string; large: string }> = {
  none: { small: 'none', medium: 'none', large: 'none' },
  small: {
    small: '0 1px 2px 0 rgb(0 0 0 / 0.06)',
    medium: '0 2px 4px rgb(0 0 0 / 0.08)',
    large: '0 6px 12px rgb(0 0 0 / 0.1)',
  },
  medium: {
    small: '0 2px 4px rgb(0 0 0 / 0.1)',
    medium: '0 6px 18px rgb(0 0 0 / 0.12)',
    large: '0 12px 28px rgb(0 0 0 / 0.16)',
  },
  large: {
    small: '0 4px 10px rgb(0 0 0 / 0.12)',
    medium: '0 10px 24px rgb(0 0 0 / 0.16)',
    large: '0 18px 40px rgb(0 0 0 / 0.2)',
  },
}

function applyThemeSettings(theme?: ThemeSettings) {
  if (!theme) {
    return
  }

  const root = document.documentElement
  const radius = radiusMap[theme.radius]
  const shadow = shadowMap[theme.shadow]

  root.style.setProperty('--primary', theme.primaryColor)
  root.style.setProperty('--font-sans', theme.bodyFont)
  root.style.setProperty('--theme-heading-font', theme.headingFont)
  root.style.setProperty('--radius-medium', radius.medium)
  root.style.setProperty('--radius-large', radius.large)
  root.style.setProperty('--shadow-small', shadow.small)
  root.style.setProperty('--shadow-medium', shadow.medium)
  root.style.setProperty('--shadow-large', shadow.large)
  root.style.setProperty('--theme-bg-light', theme.backgroundLight)
  root.style.setProperty('--theme-bg-dark', theme.backgroundDark)
  root.style.setProperty('--theme-card-light', theme.cardLight)
  root.style.setProperty('--theme-card-dark', theme.cardDark)
  root.style.setProperty('--theme-fg-light', theme.foregroundLight)
  root.style.setProperty('--theme-fg-dark', theme.foregroundDark)
  root.style.setProperty('--theme-accent-light', theme.accentLight)
  root.style.setProperty('--theme-accent-dark', theme.accentDark)
  root.style.setProperty('--theme-border-light', theme.borderLight)
  root.style.setProperty('--theme-border-dark', theme.borderDark)
  root.style.setProperty('--container-max', `${theme.containerMaxWidth}px`)
  root.style.setProperty('--article-max-width', `${theme.articleMaxWidth}px`)
  root.style.setProperty('--hero-overlay-opacity', String(theme.heroOverlay))
}

onMounted(() => {
  void ensureLoaded()
})

watch(
  () => siteInfo.value.theme,
  (theme) => {
    applyThemeSettings(theme)
  },
  { immediate: true, deep: true },
)
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
