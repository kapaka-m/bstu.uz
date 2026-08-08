import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
  ],
  build: {
    rolldownOptions: {
      checks: {
        pluginTimings: false,
      },
      output: {
        manualChunks(id) {
          if (!id.includes("node_modules")) {
            return;
          }

          if (id.includes("react-dom") || id.includes("react/jsx-runtime")) {
            return "vendor-react";
          }

          if (id.includes("react-router-dom") || id.includes("@remix-run")) {
            return "vendor-router";
          }

          if (id.includes("framer-motion") || id.includes("motion-dom")) {
            return "vendor-motion";
          }

          if (id.includes("lucide-react")) {
            return "vendor-icons";
          }

          if (id.includes("swiper")) {
            return "vendor-swiper";
          }

          if (id.includes("react")) {
            return "vendor-react";
          }
        },
      },
    },
  },
})
