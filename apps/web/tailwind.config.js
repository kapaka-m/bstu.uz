/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#4154f1',
          hover: '#717ff5',
          light: '#f6f9ff',
        },
        navy: {
          DEFAULT: '#012970',
          light: '#444444',
          dark: '#011944',
        }
      },
      fontFamily: {
        sans: ['var(--app-font-sans)'],
        heading: ['var(--app-font-heading)'],
      },
      animation: {
        'float-slow': 'floatSlow 4s ease-in-out infinite',
      },
      keyframes: {
        floatSlow: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-15px)' },
        }
      }
    },
  },
  plugins: [],
}
