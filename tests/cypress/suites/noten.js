/**
 * Wires the Gesamtnoteneingabe suite into cypress.config.js: its cy.task handlers plus the
 * Cypress.env() values its specs read.
 *
 * The split is the point -- nothing NOTEN_* belongs in the config, and nothing generic belongs
 * here. A second suite adds its own file next to this one and the config changes by one require.
 */

const { registerNotenDbTasks } = require("../tasks/notenDb");

module.exports = {
	registerTasks: registerNotenDbTasks,

	env: {
		// Optional pins; semester and LV are discovered at runtime when unset.
		NOTEN_SEM: process.env.NOTEN_SEM || null,
		NOTEN_LV_ID: process.env.NOTEN_LV_ID || null,

		// saveStudentenNoten always sends the Notenfreigabe mail - opt in explicitly.
		NOTEN_FREIGABE_ENABLED: process.env.NOTEN_FREIGABE_ENABLED || "false",
		NOTEN_FREIGABE_PASSWORD: process.env.NOTEN_FREIGABE_PASSWORD || null,

		// Access-control specs need real per-role credentials: this API has no impersonation.
		NOTEN_TEACHER_USER: process.env.NOTEN_TEACHER_USER || null,
		NOTEN_TEACHER_PASSWORD: process.env.NOTEN_TEACHER_PASSWORD || null,
		NOTEN_FOREIGN_LV_ID: process.env.NOTEN_FOREIGN_LV_ID || null,
	},
};
