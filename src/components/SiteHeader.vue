<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { isExternalUrl } from '@/lib/theme-config'
import type { MenuItem, SiteInfo } from '@/types/wordpress'

const props = defineProps<{
  siteInfo: SiteInfo
  menuItems: MenuItem[]
  loading: boolean
}>()

const route = useRoute()
const mobileMenuOpen = ref(false)

const fallbackMenuItem: MenuItem = {
  id: 0,
  title: '首页',
  url: '/',
  path: '/',
  target: '',
  description: '',
  current: false,
  children: [],
}

const visibleMenuItems = computed(() => {
  if (props.menuItems.length > 0) {
    return props.menuItems
  }

  return [fallbackMenuItem]
})

const isCurrentPath = (path: string) => route.path === path

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

function closeMobileMenu() {
  mobileMenuOpen.value = false
}

const currentTheme = ref('light')

onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  currentTheme.value = savedTheme || 'light'
  applyTheme(currentTheme.value)
})

watch(
  () => route.fullPath,
  () => {
    closeMobileMenu()
  },
)

function toggleTheme() {
  currentTheme.value = currentTheme.value === 'dark' ? 'light' : 'dark'
  applyTheme(currentTheme.value)
  localStorage.setItem('theme', currentTheme.value)
}

function applyTheme(theme: string) {
  document.documentElement.setAttribute('data-theme', theme)
  document.documentElement.style.colorScheme = theme
}
</script>

<template>
  <div class="nav">
    <nav data-topnav class="navtop">
      <div class="navtop__bar">
        <a href="/" class="logo button ghost">{{ siteInfo.name }}</a>

        <div class="nav-actions">
          <span v-if="loading" class="badge secondary nav-loading">菜单加载中...</span>

          <div class="nav-links desktop-only">
            <template v-for="item in visibleMenuItems" :key="item.id">
              <a
                v-if="!isExternalUrl(item.url)"
                :href="item.url"
                :aria-current="isCurrentPath(item.path) ? 'page' : undefined"
                class="nav-menu-toggle"
              >
                {{ item.title }}
              </a>

              <a
                v-else
                :href="item.url"
                :target="item.target || '_blank'"
                rel="noreferrer noopener"
                class="nav-menu-toggle"
              >
                {{ item.title }}
              </a>
            </template>
          </div>

          <button
            id="theme-toggle"
            class="nav-menu-toggle desktop-only button ghost"
            @click="toggleTheme"
            :aria-label="currentTheme === 'dark' ? 'Dark mode' : 'Light mode'"
          >
            {{ currentTheme === 'dark' ? '浅色' : '深色' }}
          </button>

          <button
            class="nav-menu-toggle mobile-only button ghost"
            type="button"
            :aria-expanded="mobileMenuOpen ? 'true' : 'false'"
            aria-label="切换导航菜单"
            @click="toggleMobileMenu"
          >
            菜单
          </button>
        </div>
      </div>

      <button
        v-show="mobileMenuOpen"
        class="nav-mobile-overlay mobile-only"
        type="button"
        aria-label="关闭菜单"
        @click="closeMobileMenu"
      ></button>

      <aside class="nav-mobile-menu mobile-only button ghost" :class="{ open: mobileMenuOpen }">
        <template v-for="item in visibleMenuItems" :key="`mobile-${item.id}`">
          <a
            v-if="!isExternalUrl(item.url)"
            :href="item.url"
            :aria-current="isCurrentPath(item.path) ? 'page' : undefined"
            @click="closeMobileMenu"
          >
            {{ item.title }}
          </a>

          <a
            v-else
            :href="item.url"
            :target="item.target || '_blank'"
            rel="noreferrer noopener"
            @click="closeMobileMenu"
          >
            {{ item.title }}
          </a>
        </template>

        <button class="nav-menu-toggle button ghost" @click="toggleTheme">
          {{ currentTheme === 'dark' ? '切换浅色模式' : '切换深色模式' }}
        </button>
      </aside>
    </nav>
  </div>
</template>
