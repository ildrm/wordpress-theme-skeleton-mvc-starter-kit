import js from '@eslint/js';
import globals from 'globals';

export default [
  {
    ignores: ['public/build/**', 'node_modules/**', 'vendor/**'],
  },
  js.configs.recommended,
  {
    files: ['resources/js/**/*.js', 'vite.config.js', 'playwright.config.js', 'tests/Browser/**/*.js'],
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.node,
      },
    },
    rules: {
      'no-console': 'error',
    },
  },
];
