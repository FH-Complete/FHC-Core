const path = require("path");
const { defineConfig } = require("cypress");

require("dotenv").config({ path: "tests/cypress/.env" });

/**
 * The suite being run: its cy.task handlers and its Cypress.env() values.
 * CY_SUITE is a suite name in tests/cypress/suites (default noten), or the tests/cypress folder of an
 * extension with a suite.js. See tests/cypress/suites/readme_suites.txt.
 */
const loadSuite = (name) => {
  if (!/[\\/]/.test(name)) return require(`./tests/cypress/suites/${name}`);

  const dir = path.resolve(__dirname, name);
  // the accounts of a manual run; a value from the environment wins
  require("dotenv").config({ path: path.join(dir, ".env") });
  return { ...require(path.join(dir, "suite.js")), dir };
};

const suite = loadSuite(process.env.CY_SUITE || "noten");

// a glob needs forward slashes, also on Windows
const suiteGlob = (pattern) => path.join(suite.dir, pattern).replace(/\\/g, "/");

module.exports = defineConfig({
  // Cypress 15 deprecates Cypress.env() inside cy.request options, which the API helpers use.
  allowCypressEnv: true,

  // The suite lives under tests/cypress, so every default folder has to be pointed there.
  fixturesFolder: suite.dir ? path.join(suite.dir, "fixtures") : "tests/cypress/fixtures",
  screenshotsFolder: "tests/cypress/screenshots",
  videosFolder: "tests/cypress/videos",
  downloadsFolder: "tests/cypress/downloads",

  e2e: {
    baseUrl: process.env.BASE_URL,
    specPattern: suite.dir ? suiteGlob(suite.specPattern) : "tests/cypress/e2e/**/*.cy.{js,jsx,ts,tsx}",
    supportFile: "tests/cypress/support/e2e.js",
    defaultCommandTimeout: 20000,
    pageLoadTimeout: 20000,
    retries: { runMode: 0, openMode: 0 },

    setupNodeEvents(on, config) {
      // No defaults anywhere: an empty .env fails the run instead of hitting someone else's instance.
      const missing = ["BASE_URL", "USER_NAME", "USER_PASSWORD"].filter((key) => !process.env[key]);
      if (missing.length) throw new Error(`Missing in tests/cypress/.env: ${missing.join(", ")}`);

      // the core parts a suite gets: an extension suite requires no package and no core file itself
      const db = require("./tests/cypress/tasks/db");
      suite.registerTasks(on, { db });

      // Cypress keeps one after:run handler, so core owns it: the pool and the SSH forward must not outlive Cypress
      on("after:run", () => db.closeDb());

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
