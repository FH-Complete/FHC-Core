/**
 * Wires the Gesamtnoteneingabe suite into cypress.config.js: its cy.task handlers, its run hooks and
 * the Cypress.env() values its specs read.
 *
 * The split is the point -- nothing NOTEN_* belongs in the config, and nothing generic belongs
 * here. A second suite adds its own file next to this one and the config changes by one require.
 */

const { registerNotenDbTasks } = require("../tasks/notenDb");
const profil = require("../tasks/notenProfil");

// cy.request sends one request after the other; the lock test needs two at the same time.
// Basic auth without a session cookie: PHP serializes two requests of one session on the session lock,
// and the test would no longer reach the advisory lock.
const parallelPost = ({ path, bodies }) => {
	const base = String(process.env.BASE_URL).replace(/\/+$/, "");
	const auth = "Basic " + Buffer.from(`${process.env.USER_NAME}:${process.env.USER_PASSWORD}`).toString("base64");

	return Promise.all(
		bodies.map((body) =>
			fetch(`${base}/index.ci.php/api/frontend/v1/Noten/${path}`, {
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

module.exports = {
	registerTasks: (on) => {
		registerNotenDbTasks(on);
		on("task", { "noten:http:parallel": parallelPost });
	},

	// NOTEN_PROFIL switches the instance before the run. Without it the run restores the originals, so an
	// aborted run does not leave the instance on a profile.
	beforeRun: (config) => profil.anwenden(config.env.NOTEN_PROFIL || null),
	afterRun: (config) => (config.env.NOTEN_PROFIL ? profil.wiederherstellen() : null),

	env: {
		// Optional pins; semester and LV are discovered at runtime when unset.
		NOTEN_SEM: process.env.NOTEN_SEM || null,
		NOTEN_LV_ID: process.env.NOTEN_LV_ID || null,

		// configuration profile from tests/cypress/profiles/noten.js; empty = the instance as deployed
		NOTEN_PROFIL: process.env.NOTEN_PROFIL || null,

		// LDAP password for the Freigabe specs; empty = USER_PASSWORD
		NOTEN_FREIGABE_PASSWORD: process.env.NOTEN_FREIGABE_PASSWORD || null,

		// Access-control specs need real per-role credentials: this API has no impersonation.
		NOTEN_TEACHER_USER: process.env.NOTEN_TEACHER_USER || null,
		NOTEN_TEACHER_PASSWORD: process.env.NOTEN_TEACHER_PASSWORD || null,
		NOTEN_FOREIGN_LV_ID: process.env.NOTEN_FOREIGN_LV_ID || null,

		// assistant account for the role matrix: carries only lehre/benotungstool_assistenz
		NOTEN_ASSISTENZ_USER: process.env.NOTEN_ASSISTENZ_USER || null,
		NOTEN_ASSISTENZ_PASSWORD: process.env.NOTEN_ASSISTENZ_PASSWORD || null,
	},
};
