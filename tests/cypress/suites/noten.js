/**
 * Connects the noten suite to cypress.config.js: its cy.task functions, its run hooks and
 * the Cypress.env() values of its specs. The keys come from tests/cypress/suites/.env.
 */

const path = require("path");

require("dotenv").config({ path: path.join(__dirname, ".env") });

const { registerNotenDbTasks } = require("../tasks/notenDb");
const { closeDb } = require("../tasks/db");

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
			fetch(`${base}/index.ci.php/api/frontend/v1/Noten/${apiPath}`, {
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
	setupNodeEvents: (on, config) => {
		registerNotenDbTasks(on);
		on("task", { "noten:http:postParallel": postParallel });

		// the pool closes after the run, so the database keeps no idle connections of it
		on("after:run", () => closeDb());

		// a value from --env wins over the suite's .env
		config.env = { ...env(), ...config.env };
		return config;
	},
};
