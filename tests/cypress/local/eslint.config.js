const js = require("@eslint/js");
const globals = require("globals");
const pluginCypress = require("eslint-plugin-cypress");
const prettier = require("eslint-config-prettier");

/**
 * Lint for the Cypress suite only. The rest of the repo is older than this setup.
 *
 * The no-restricted-syntax rules check two conventions from tests/cypress/suites/readme_noten.txt.
 * Paths start at the repo root: `npm run lint` in local/ changes to it first.
 */
module.exports = [
	{ ignores: ["tests/cypress/e2e/fhcomplete/**", "tests/cypress/e2e/*.cy.js"] },

	// --- Node side: tasks, tools, profiles, local tools, configuration (CommonJS) ---
	{
		files: ["tests/cypress/{tasks,tools,profiles,suites,local}/**/*.js", "cypress.config.js"],
		languageOptions: {
			ecmaVersion: 2023,
			sourceType: "commonjs",
			globals: { ...globals.node },
		},
		rules: {
			...js.configs.recommended.rules,
			"no-unused-vars": ["warn", { argsIgnorePattern: "^_", caughtErrors: "none" }],
		},
	},

	// --- browser side: specs and support (ES modules in the Cypress runner) ---
	{
		files: ["tests/cypress/e2e/**/*.js", "tests/cypress/support/**/*.js"],
		languageOptions: {
			ecmaVersion: 2023,
			sourceType: "module",
			globals: { ...globals.browser, ...globals.mocha, cy: "readonly", Cypress: "readonly", expect: "readonly" },
		},
		plugins: { cypress: pluginCypress },
		rules: {
			...js.configs.recommended.rules,
			...pluginCypress.configs.recommended.rules,
			"no-unused-vars": ["warn", { argsIgnorePattern: "^_", caughtErrors: "none" }],

			// warn, not error: four .clear().type() calls in the page object work, and three of them
			// run only in the Punkte profile, so a change there cannot be tested here
			"cypress/unsafe-to-chain-command": "warn",
		},
	},

	// --- the conventions of the suite ---
	{
		files: ["tests/cypress/e2e/specs/**/*.cy.js"],
		rules: {
			"no-restricted-syntax": [
				"error",
				{
					// skipIf and the require* helpers are the only way to skip
					selector: "CallExpression[callee.property.name='skip'][callee.object.type='ThisExpression']",
					message:
						"Kein this.skip() im Spec. Nimm skipIf(this, condition, reason) oder einen require*-Helfer aus support/helpers/notenConfig.js.",
				},
				{
					// a stub of the Noten API removes exactly the server rules that the suite checks
					selector:
						"CallExpression[callee.object.name='cy'][callee.property.name='intercept'] > ObjectExpression:has(Property[key.name='body'])",
					message:
						"Kein Stub der Noten-API. cy.intercept nur zum Aliasen und Warten, siehe tests/cypress/suites/readme_noten.txt.",
				},
			],
		},
	},

	// notenConfig.js has the only this.skip() call
	{
		files: ["tests/cypress/support/helpers/notenConfig.js"],
		rules: { "no-restricted-syntax": "off" },
	},

	// must be last: turns off all style rules that Prettier sets
	prettier,
];
