import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/Browser',
  use: {
    baseURL: process.env.WP_BASE_URL || 'http://localhost:8080',
    launchOptions: process.env.PLAYWRIGHT_CHROME_PATH
      ? { executablePath: process.env.PLAYWRIGHT_CHROME_PATH }
      : {},
  },
  webServer: undefined,
});
