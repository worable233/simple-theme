import { createRouter, createWebHistory } from 'vue-router'
import { getRouterBase } from '@/lib/theme-config'
import ContentView from '@/views/ContentView.vue'
import HomeView from '@/views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(getRouterBase()),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/:pathMatch(.*)*', name: 'content', component: ContentView },
  ],
  scrollBehavior(_to, _from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }

    return { top: 0 }
  },
})

export default router
