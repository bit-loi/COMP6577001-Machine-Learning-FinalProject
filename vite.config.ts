import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './'),
    },
  },
  build: {
    rollupOptions: {
      input: {
        // Main app mount (hero + carousel + header + footer)
        'mount-app': path.resolve(__dirname, 'components/mount-app.tsx'),
        // Standalone auth form mounts
        'mount-login': path.resolve(__dirname, 'components/mount-login.tsx'),
        'mount-register': path.resolve(__dirname, 'components/mount-register.tsx'),
      },
      external: ['react', 'react-dom'],
      output: {
        globals: {
          react: 'React',
          'react-dom': 'ReactDOM',
        },
        entryFileNames: '[name].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: '[name]-[hash][extname]',
      },
    },
  },
});
