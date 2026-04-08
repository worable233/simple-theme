<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { isExternalUrl } from '@/lib/theme-config'
import type { MenuItem, SiteInfo } from '@/types/wordpress'

const props = defineProps<{
  siteInfo: SiteInfo
  menuItems: MenuItem[]
}>()

const currentYear = new Date().getFullYear()

const visibleMenuItems = computed(() => props.menuItems.slice(0, 6))
</script>

<template>
  <footer class="site-footer">
    <div class="container">
      <section class="card site-footer__panel">
        <div class="row">
          <div class="col-7">
            <div class="vstack gap-4">
              <div class="vstack gap-2">
                <h2>{{ siteInfo.name }}</h2>
                <p class="text-light">
                  {{ siteInfo.description || '一个经过重构的 WordPress + Vue 前台主题。' }}
                </p>
              </div>
            </div>
          </div>

          <div class="col-5">
            <div class="vstack gap-3">
              <h3>菜单</h3>

              <ul class="site-footer__links unstyled">
                <li v-for="item in visibleMenuItems" :key="item.id">
                  <RouterLink v-if="!isExternalUrl(item.url)" :to="item.path">
                    <span v-html="item.title"></span>
                  </RouterLink>

                  <a
                    v-else
                    :href="item.url"
                    :target="item.target || '_blank'"
                    rel="noreferrer noopener"
                  >
                    <span v-html="item.title"></span>
                  </a>
                </li>

                <li v-if="visibleMenuItems.length === 0">
                  <RouterLink to="/">返回首页</RouterLink>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-4">
            <p>© {{ currentYear }} {{ siteInfo.name }}。</p>
          </div>
          <div class="col-8 justify-end hstack">
            <a class="button ghost small" href="https://github.com/worable233/simple-theme" target="_blank" rel="noreferrer noopener">SimpleTheme</a>
          </div>
        </div>
      </section>
    </div>
  </footer>
</template>
