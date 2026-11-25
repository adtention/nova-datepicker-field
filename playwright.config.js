import { defineConfig } from '@playwright/test'

const port = 5179

export default defineConfig({
  testDir: './tests/browser',
  fullyParallel: true,
  reporter: 'list',
  use: {
    baseURL: `http://localhost:${port}`,
    // The Chrome installed on the machine, so no Playwright browser download is needed.
    channel: 'chrome',
    viewport: { width: 1280, height: 800 },
  },
  webServer: {
    command: `npx vite --config tests/browser/vite.config.mjs --port ${port}`,
    url: `http://localhost:${port}`,
    reuseExistingServer: false,
  },
})
