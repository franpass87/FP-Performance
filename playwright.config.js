// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * Playwright configuration for FP Performance Suite E2E tests
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',
  use: {
    baseURL: process.env.WP_URL || 'http://fp-development.local',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  webServer: {
    command: 'echo "WordPress should be running at http://fp-development.local"',
    url: process.env.WP_URL || 'http://fp-development.local',
    reuseExistingServer: !process.env.CI,
  },
});










