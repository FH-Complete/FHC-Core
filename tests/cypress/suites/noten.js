/**
 * Wires the Gesamtnoteneingabe suite into cypress.config.js: its cy.task handlers, its run hooks and
 * the Cypress.env() values its specs read.
 *
 * The suite's keys come from tests/cypress/suites/.env or from the environment.
 */

const path = require("path");

// before the task files: db.js reads TEST_ENV_PREFIX when it loads
require("dotenv").config({ path: path.join(__dirname, ".env") });

const { registerNotenDbTasks } = require("../tasks/notenDb");
const { closeDb } = require("../tasks/db");

// cy.request sends one request after the other; the lock test needs two at the same time.
// Basic auth without a session cookie: PHP serializes two requests of one session on the session lock,
// and the test would no longer reach the advisory lock.
const parallelPost = ({ path: apiPath, bodies }) => {
	const base = String(process.env.BASE_URL).replace(/\/+$/, "");
	const auth = "Basic " + Buffer.from(`${process.env.NOTEN_USER}:${process.env.NOTEN_PASSWORD}`).toString("base64");

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

const env = () => ({
	// the lecturer the suite works as; separate from USER_NAME, which belongs to every suite
	NOTEN_USER: process.env.NOTEN_USER || null,
	NOTEN_PASSWORD: process.env.NOTEN_PASSWORD || null,

	// Optional pins; semester and LV are discovered at runtime when unset.
	NOTEN_SEM: process.env.NOTEN_SEM || null,
	NOTEN_LV_ID: process.env.NOTEN_LV_ID || null,

	// LDAP password for the Freigabe specs; empty = NOTEN_PASSWORD
	NOTEN_FREIGABE_PASSWORD: process.env.NOTEN_FREIGABE_PASSWORD || null,

	// Access-control specs need real per-role credentials: this API has no impersonation.
	NOTEN_TEACHER_USER: process.env.NOTEN_TEACHER_USER || null,
	NOTEN_TEACHER_PASSWORD: process.env.NOTEN_TEACHER_PASSWORD || null,
	NOTEN_FOREIGN_LV_ID: process.env.NOTEN_FOREIGN_LV_ID || null,

	// assistant account for the role matrix: carries only lehre/benotungstool_assistenz
	NOTEN_ASSISTENZ_USER: process.env.NOTEN_ASSISTENZ_USER || null,
	NOTEN_ASSISTENZ_PASSWORD: process.env.NOTEN_ASSISTENZ_PASSWORD || null,
});

module.exports = {
	setupNodeEvents: (on, config) => {
		registerNotenDbTasks(on);
		on("task", { "noten:http:parallel": parallelPost });

		// the pool closes after the run, so the database keeps no idle connections of it
		on("after:run", () => closeDb());

		// a value from --env wins over the suite's .env
		config.env = { ...env(), ...config.env };
		return config;
	},
};
