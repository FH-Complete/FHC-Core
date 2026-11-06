const path = require("path");
const { defineConfig } = require("cypress");

// Next to this file, so a run with --project from another directory finds the same .env.
require("dotenv").config({ path: path.join(__dirname, ".env") });

/**
 * The suite of this run. CY_SUITE is the name of a suite in suites/ (default noten), or the tests/cypress
 * folder of an extension: relative to this folder or absolute. The contract is in suites/readme_suites.txt.
 */
const loadSuite = (name) => {
	const isFolder = /[\\/]/.test(name);
	// the folder that the specPattern and the fixtures of the suite start in
	const dir = isFolder ? path.resolve(__dirname, name) : __dirname;
	const file = isFolder ? path.join(dir, "suite.js") : path.join(__dirname, "suites", `${name}.js`);

	// the keys of the suite for a manual run; a value from the environment wins
	require("dotenv").config({ path: path.join(path.dirname(file), ".env") });

	const suite = require(file);
	if (!suite.specPattern || typeof suite.setupNodeEvents !== "function") {
		throw new Error(`${file} needs specPattern and setupNodeEvents, see suites/readme_suites.txt`);
	}
	return { ...suite, dir };
};

const suite = loadSuite(process.env.CY_SUITE || "noten");

// The Cypress project is tests/cypress. All paths below start in this folder.
module.exports = defineConfig({
	// Cypress 15 deprecates Cypress.env() inside cy.request options, which the API helpers use.
	allowCypressEnv: true,

	fixturesFolder: path.join(suite.dir, "fixtures"),
	screenshotsFolder: "screenshots",
	videosFolder: "videos",
	downloadsFolder: "downloads",

	e2e: {
		baseUrl: process.env.BASE_URL,
		// a glob needs forward slashes, also on Windows
		specPattern: path.join(suite.dir, suite.specPattern).replace(/\\/g, "/"),
		supportFile: "support/e2e.js",
		defaultCommandTimeout: 20000,
		pageLoadTimeout: 20000,
		retries: { runMode: 0, openMode: 0 },

		setupNodeEvents(on, config) {
			// No default: an empty BASE_URL fails the run instead of hitting someone else's instance.
			if (!process.env.BASE_URL) throw new Error("Missing in tests/cypress/.env: BASE_URL");

			// the core parts a suite gets, so an extension suite requires no package and no core file
			const db = require("./tasks/db");
			const result = suite.setupNodeEvents(on, config, { db }) || config;

			// Cypress keeps only one after:run handler, so core owns it: the pool must not outlive the run
			on("after:run", () => db.closeDb());
			return result;
		},
	},

	env: {
		adminusername: process.env.USER_NAME,
		adminpassword: process.env.USER_PASSWORD,
	},
});
