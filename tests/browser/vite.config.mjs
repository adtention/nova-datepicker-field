import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  root: fileURLToPath(new URL('./harness', import.meta.url)),
  plugins: [vue()],
  resolve: {
    alias: {
      'laravel-nova': fileURLToPath(new URL('./harness/stubs/laravel-nova.js', import.meta.url)),
    },
  },
  server: {
    strictPort: true,
  },
})
