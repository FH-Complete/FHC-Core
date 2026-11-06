/**
 * The noten suite for cypress.config.js: its specs, its cy.task functions and the Cypress.env() values
 * of its specs. Core loads the keys from tests/cypress/suites/.env. The contract is in readme_suites.txt.
 */

const { registerNotenDbTasks } = require("../tasks/notenDb");
const { buildApiPath } = require("../support/helpers/paths");

// cy.request sends one request after the other; the concurrency test needs two at the same time.
// Basic auth without a session cookie: PHP runs two requests of one session one after the other
// (session lock), and the test would never reach the database lock.
const postParallel = ({ path: apiPath, bodies }) => {
	const base = String(process.env.BASE_URL).replace(/\/+$/, "");
	const auth =
		"Basic " +
		Buffer.from(`${process.env.NOTEN_LEKTOR_USER}:${process.env.NOTEN_LEKTOR_PASSWORD}`).toString("base64");

	return Promise.all(
		bodies.map((body) =>
			fetch(`${base}${buildApiPath(`Noten/${apiPath}`)}`, {
				method: "POST",
				headers: { Authorization: auth, "Content-Type": "application/json" },
				body: JSON.stringify(body),
			}).then(async (r) => {
				const text = await r.text();
				try {
					return { status: r.status, body: JSON.parse(text) };
				} catch (e) {
					return { status: r.status, body: null, text: text.slice(0, 500) };
				}
			}),
		),
	);
};

// the keys of suites/.env.example that the specs read; the DB keys stay in Node
const env = () => ({
	NOTEN_LEKTOR_USER: process.env.NOTEN_LEKTOR_USER || null,
	NOTEN_LEKTOR_PASSWORD: process.env.NOTEN_LEKTOR_PASSWORD || null,
	NOTEN_SEM_KURZBZ: process.env.NOTEN_SEM_KURZBZ || null,
	NOTEN_LV_ID: process.env.NOTEN_LV_ID || null,
	NOTEN_ASSISTENZ_USER: process.env.NOTEN_ASSISTENZ_USER || null,
	NOTEN_ASSISTENZ_PASSWORD: process.env.NOTEN_ASSISTENZ_PASSWORD || null,
	NOTEN_FREIGABE_PASSWORD: process.env.NOTEN_FREIGABE_PASSWORD || null,
});

module.exports = {
	specPattern: "e2e/specs/*/noten/**/*.cy.js",

	setupNodeEvents(on, config) {
		registerNotenDbTasks(on);
		on("task", { "noten:http:postParallel": postParallel });

		// a value from --env wins over the suite's .env
		config.env = { ...env(), ...config.env };
		return config;
	},
};
