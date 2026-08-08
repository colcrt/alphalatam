/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/**/*.php',
    './assets/js/**/*.js',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
        serif: ['Merriweather', 'Georgia', 'serif'],
        mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
      },
      colors: {
        brand: {
          50:  '#eef4ff',
          100: '#dbe7ff',
          200: '#bcd3ff',
          300: '#8db4ff',
          400: '#5b8cf7',
          500: '#3b6ff0',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#101c46',
        },
      },
      boxShadow: {
        'card': '0 1px 2px 0 rgb(16 28 70 / 0.05), 0 1px 3px 0 rgb(16 28 70 / 0.06)',
        'lift': '0 16px 32px -16px rgb(16 28 70 / 0.28)',
        'block': '0 24px 48px -24px rgb(16 28 70 / 0.55)',
      },
    },
  },
  plugins: [],
}
