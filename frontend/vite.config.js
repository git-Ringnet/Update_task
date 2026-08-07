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
        target: 'http://192.168.30.121:8080',
        changeOrigin: true,
      }
    }
  },
  build: {
    outDir: '../backend/public/build',
    emptyOutDir: true,
  }
})
