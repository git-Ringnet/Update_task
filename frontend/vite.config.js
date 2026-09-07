import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
  ],
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8081',
        changeOrigin: true,
      },
      // Legacy and newly-uploaded attachments use /storage URLs. Proxy them
      // in development instead of letting Vite return its HTML fallback.
      '/storage': {
        target: 'http://127.0.0.1:8081',
        changeOrigin: true,
      }
    }
  },
  build: {
    outDir: '../backend/public/build',
    emptyOutDir: true,
  }
})
