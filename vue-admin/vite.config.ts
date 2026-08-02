import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import { createSvgIconsPlugin } from 'vite-plugin-svg-icons'
import path from 'path'
import sassImplementation from 'sass'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueJsx(),
    createSvgIconsPlugin({
      // 指定需要缓存的图标文件夹
      iconDirs: [path.resolve(process.cwd(), 'src/assets/icons/svg')],
      // 指定symbolId格式
      symbolId: '[name]',
    }),
  ],
  server: {
    port: 8080, // 端口
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        implementation: sassImplementation,
        api: 'modern-compiler', // 使用现代编译器
        sassOptions: {
          warnLegacyJsApi: false
        }
      }
    },
    devSourcemap: true // 方便调试
  },
})
