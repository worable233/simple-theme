import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig({
  plugins: [vue(), vueDevTools()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  build: {
    // 生成 manifest 交给 WordPress 读取实际产物文件名。
    outDir: 'dist',
    manifest: true,
    rollupOptions: {
      input: 'src/main.ts',
    },
  },
})
