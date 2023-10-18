/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./src/templates/**/*.twig'],
  prefix: 'k-',
  theme: {
    extend: {
      colors: {
        'ui-control': 'var(--ui-control-bg-color)',
        'hairline': 'var(--hairline-color)',
      }
    },
  },
  plugins: [],
}

