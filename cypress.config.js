const { defineConfig } = require("cypress");

require("dotenv").config({ path: "tests/cypress/.env" });

// The suite being run: its cy.task handlers and its Cypress.env() values.
const suite = require("./tests/cypress/suites/noten");

module.exports = defineConfig({
  // Cypress 15 deprecates Cypress.env() inside cy.request options, which the API helpers use.
  allowCypressEnv: true,

  // The suite lives under tests/cypress, so every default folder has to be pointed there.
  fixturesFolder: "tests/cypress/fixtures",
  screenshotsFolder: "tests/cypress/screenshots",
  videosFolder: "tests/cypress/videos",
  downloadsFolder: "tests/cypress/downloads",

  e2e: {
    baseUrl: process.env.BASE_URL,
    specPattern: "tests/cypress/e2e/**/*.cy.{js,jsx,ts,tsx}",
    supportFile: "tests/cypress/support/e2e.js",
    defaultCommandTimeout: 20000,
    pageLoadTimeout: 20000,
    retries: { runMode: 0, openMode: 0 },

    setupNodeEvents(on, config) {
      // No defaults anywhere: an empty .env fails the run instead of hitting someone else's instance.
      const missing = ["BASE_URL", "USER_NAME", "USER_PASSWORD"].filter((key) => !process.env[key]);
      if (missing.length) throw new Error(`Missing in tests/cypress/.env: ${missing.join(", ")}`);

      suite.registerTasks(on);

      // otherwise the SSH forward outlives Cypress
      on("after:run", () => require("./tests/cypress/tasks/sshTunnel").closeTunnel());

      return config;
    },
  },

  env: {
    // HTTP Basic + LDAP user the API calls authenticate as.
    adminusername: process.env.USER_NAME,
    adminpassword: process.env.USER_PASSWORD,

    ...suite.env,
  },
});
