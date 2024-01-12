import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css','resources/js/app.js'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  envDir: '../',	
  server: {
    host: true,
    hmr: {
      host: 'ec2-54-65-130-29.ap-northeast-1.compute.amazonaws.com'
    },
    // ホットリロード設定
    watch: {
        usePolling: true
    }
  },
  resolve: {
    alias: {
      '@': '/resources/js',
    },
  },
});
