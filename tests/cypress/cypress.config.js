const path = require("path");
const { defineConfig } = require("cypress");

// Next to this file, so a run with --project from another directory finds the same .env.
require("dotenv").config({ path: path.join(__dirname, ".env") });

// The Cypress project is tests/cypress. All paths below start in this folder.
module.exports = defineConfig({
	// Cypress 15 deprecates Cypress.env() inside cy.request options, which the API helpers use.
	allowCypressEnv: true,

	fixturesFolder: "fixtures",
	screenshotsFolder: "screenshots",
	videosFolder: "videos",
	downloadsFolder: "downloads",

	e2e: {
		baseUrl: process.env.BASE_URL,
		specPattern: "e2e/**/*.cy.{js,jsx,ts,tsx}",
		supportFile: "support/e2e.js",
		defaultCommandTimeout: 20000,
		pageLoadTimeout: 20000,
		retries: { runMode: 0, openMode: 0 },

		setupNodeEvents(on, config) {
			// No default: an empty BASE_URL fails the run instead of hitting someone else's instance.
			if (!process.env.BASE_URL) throw new Error("Missing in tests/cypress/.env: BASE_URL");

			// each suite registers its own tasks and Cypress.env() values
			return require("./suites/noten").setupNodeEvents(on, config);
		},
	},

	env: {
		adminusername: process.env.USER_NAME,
		adminpassword: process.env.USER_PASSWORD,
	},
});
