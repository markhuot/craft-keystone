/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./src/templates/**/*.twig'],
  prefix: 'k-',
  theme: {
    extend: {
      colors: {
        'ui-control': 'var(--ui-control-bg-color)',
        'ui-control-active': 'var(--ui-control-active-bg-color)',
        'hairline': 'var(--hairline-color)',
        'current': 'currentColor',
        'medium-dark-text-color': 'var(--medium-dark-text-color)',
      },
      spacing: {
        'xl': 'var(--xl)',
      },
    },
  },
  plugins: [],
}

