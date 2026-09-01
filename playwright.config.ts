import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests-e2e/specs', 
  
  timeout: 30 * 1000,
  
  expect: {
    timeout: 5000
  },

  fullyParallel: false,
  reporter: 'html',
  
  use: {
    baseURL: 'http://localhost:80', 
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    headless: true,
    ignoreHTTPSErrors: true,
    extraHTTPHeaders: {
    'X-E2E-Test': 'true'
  },
  contextOptions: {
  baseURL: 'http://localhost',
  },
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});