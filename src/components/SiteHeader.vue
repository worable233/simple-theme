<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { isExternalUrl } from '@/lib/theme-config'
import type { MenuItem, SiteInfo } from '@/types/wordpress'

const props = defineProps<{
  siteInfo: SiteInfo
  menuItems: MenuItem[]
  loading: boolean
}>()

const route = useRoute()

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

// 响应式数据：当前主题
const currentTheme = ref('light');

// 页面加载时恢复用户保存的主题偏好
onMounted(() => {
  const savedTheme = localStorage.getItem('theme');
  currentTheme.value = savedTheme || 'light';
  applyTheme(currentTheme.value);
});

// 主题切换功能
function toggleTheme() {
  currentTheme.value = currentTheme.value === 'dark' ? 'light' : 'dark';
  applyTheme(currentTheme.value);
  localStorage.setItem('theme', currentTheme.value);
}

// 应用主题到文档根元素
function applyTheme(theme: string) {
  document.documentElement.setAttribute('data-theme', theme);
  document.documentElement.style.colorScheme = theme;
}

</script>

<template>
  <nav data-topnav>
    <div class="row">
      <div class="col-4 branding">
        <a href="/" class="logo">{{ siteInfo.name }}</a>
      </div>
      
      <div class="col-8 justify-end hstack">
        <span v-if="loading" class="badge secondary">菜单加载中</span>
        
        <template v-for="item in visibleMenuItems" :key="item.id">
          <a 
            v-if="!isExternalUrl(item.url)"
            :href="item.url"
            :aria-current="isCurrentPath(item.path) ? 'page' : undefined"
          >
            {{ item.title }}
          </a>
          
          <a 
            v-else
            :href="item.url"
            :target="item.target || '_blank'"
            rel="noreferrer noopener"
          >
            {{ item.title }}
          </a>
        </template>
        
        <button 
          id="theme-toggle" 
          class="outline small"
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
      </div>
    </div>
  </nav>
</template>