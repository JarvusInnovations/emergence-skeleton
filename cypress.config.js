const { defineConfig } = require('cypress')

module.exports = defineConfig({
  env: {
    STUDIO_CONTAINER: 'skeleton-studio',
  },
  viewportHeight: 600,
  viewportWidth: 1200,
  e2e: {
    baseUrl: 'http://localhost:7080',
    excludeSpecPattern: ['*.json'],
  },
})
