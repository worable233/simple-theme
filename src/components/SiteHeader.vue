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
  title: '棣栭〉',
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
  if (savedTheme === 'light' || savedTheme === 'dark') {
    currentTheme.value = savedTheme
  } else {
    currentTheme.value = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }
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
  document.body.setAttribute('data-theme', theme)
  document.documentElement.style.colorScheme = theme
}
</script>

<template>
  <div class="nav">
    <nav data-topnav class="navtop">
      <div class="navtop__bar">
        <a href="/" class="logo button ghost">{{ siteInfo.name }}</a>

        <div class="nav-actions">
          <span v-if="loading" class="badge secondary nav-loading">鑿滃崟鍔犺浇涓?..</span>

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
            class="nav-menu-toggle desktop-only outline small"
            @click="toggleTheme"
            :aria-label="currentTheme === 'dark' ? 'Dark mode' : 'Light mode'"
          >
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              class="icon-light"
              :style="{ display: currentTheme === 'dark' ? 'none' : 'block' }"
              role="img"
              aria-hidden="true"
            >
              <circle cx="12" cy="12" r="5"></circle>
              <line x1="12" y1="1" x2="12" y2="3"></line>
              <line x1="12" y1="21" x2="12" y2="23"></line>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
              <line x1="1" y1="12" x2="3" y2="12"></line>
              <line x1="21" y1="12" x2="23" y2="12"></line>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
              <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              class="icon-dark"
              :style="{ display: currentTheme === 'dark' ? 'block' : 'none' }"
              role="img"
              aria-hidden="true"
            >
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
          </button>

          <button
            class="nav-menu-toggle mobile-only button ghost"
            type="button"
            :aria-expanded="mobileMenuOpen ? 'true' : 'false'"
            aria-label="鍒囨崲瀵艰埅鑿滃崟"
            @click="toggleMobileMenu"
          >
            鑿滃崟
          </button>
        </div>
      </div>

      <button
        v-show="mobileMenuOpen"
        class="nav-mobile-overlay mobile-only"
        type="button"
        aria-label="鍏抽棴鑿滃崟"
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
          {{ currentTheme === 'dark' ? '鍒囨崲娴呰壊妯″紡' : '鍒囨崲娣辫壊妯″紡' }}
        </button>
      </aside>
    </nav>
  </div>
</template>

