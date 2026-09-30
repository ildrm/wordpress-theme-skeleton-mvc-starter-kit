import { defineConfig } from '@playwright/test';

if (!process.env.WP_BASE_URL) {
  throw new Error('Set WP_BASE_URL to a running WordPress site.');
}

export default defineConfig({
  testDir: './tests/Browser',
  use: {
    baseURL: process.env.WP_BASE_URL,
    launchOptions: process.env.PLAYWRIGHT_CHROME_PATH
      ? { executablePath: process.env.PLAYWRIGHT_CHROME_PATH }
      : {},
  },
  webServer: undefined,
});
