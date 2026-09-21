const js = require("@eslint/js");
const globals = require("globals");
const pluginCypress = require("eslint-plugin-cypress");
const prettier = require("eslint-config-prettier");

/**
 * Nur die Cypress-Suite. Der übrige Code des Repos ist älter als dieses Setup; ihn mitzulinten
 * würde hunderte Funde erzeugen, die niemand in dieser Story abarbeitet.
 *
 * Die no-restricted-syntax-Regeln setzen die Konventionen aus .claude/rules/benotungstool-tests.md durch.
 * Sie sind der Grund für diese Datei: eine Konvention, die kein Werkzeug prüft, zerfällt.
 *
 * Pfade gelten ab dem Repo-Root: `npm run lint` in local/ wechselt dorthin.
 */
module.exports = [
	{ ignores: ["tests/cypress/e2e/fhcomplete/**", "tests/cypress/e2e/*.cy.js"] },

	// --- Node-Seite: Tasks, Werkzeuge, Profile, eigener Arbeitsplatz, Konfiguration (CommonJS) ---
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

	// --- Browser-Seite: Specs und Support (ES-Module, im Cypress-Runner) ---
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

			/*
			 * Warnung statt Fehler. Das Muster .clear().type() steckt an vier Stellen im Page
			 * Object und läuft dort. Drei davon gehören zum Punktemodus, den nur das Profil
			 * "punkte" ausführt - eine Umstellung wäre hier nicht prüfbar. Für neuen Code
			 * bleibt der Hinweis sichtbar.
			 */
			"cypress/unsafe-to-chain-command": "warn",
		},
	},

	// --- die Konventionen der Suite ---
	{
		files: ["tests/cypress/e2e/specs/**/*.cy.js"],
		rules: {
			"no-restricted-syntax": [
				"error",
				{
					// skipIf / require* aus notenConfig.js sind der einzige Weg.
					selector: "CallExpression[callee.property.name='skip'][callee.object.type='ThisExpression']",
					message:
						"Kein this.skip() im Spec. Nimm skipIf(this, condition, reason) oder einen require*-Helfer aus support/helpers/notenConfig.js.",
				},
				{
					// Die Noten-API zu stubben hebt genau die Serverregeln auf, die die Suite prüft.
					selector:
						"CallExpression[callee.object.name='cy'][callee.property.name='intercept'] > ObjectExpression:has(Property[key.name='body'])",
					message:
						"Kein Stub der Noten-API. cy.intercept nur zum Aliasen und Warten, siehe .claude/rules/benotungstool-tests.md.",
				},
			],
		},
	},

	// notenConfig.js hält die einzige Umsetzung von skip.
	{
		files: ["tests/cypress/support/helpers/notenConfig.js"],
		rules: { "no-restricted-syntax": "off" },
	},

	// muss zuletzt stehen: schaltet alle Stilregeln ab, die Prettier ohnehin setzt
	prettier,
];
