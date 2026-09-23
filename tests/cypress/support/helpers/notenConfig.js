/**
 * Skip helpers for tests that depend on the configuration.
 *
 * The configuration of the instance decides which rule branch is active, and a test run cannot switch
 * it. So a test checks the active branch and skips the other one. A profile in profiles/noten.js
 * switches the instance to the other branch (readme_noten.txt, section 1).
 */

/**
 * The only way to skip a test. `condition` true -> skip; `reason` says why.
 * Call it in it(function () {...}) or beforeEach(function () {...}), never in an arrow function:
 * an arrow function has no Mocha `this`, so `this.skip()` is not available.
 *
 * Each `require*` helper below names one domain precondition and calls `skipIf`.
 */
export const skipIf = (testContext, condition, reason) => {
	if (!condition) return;
	Cypress.log({ name: "skip", message: reason });
	testContext.skip();
};

const punkteMode = (ctx) => Boolean(ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE);

/** Skips if the Punkte mode is off. Call it as the first line in beforeEach(function () {...}). */
export const requirePunkteMode = (testContext, ctx) =>
	skipIf(testContext, !punkteMode(ctx), "Übersprungen: CIS_GESAMTNOTE_PUNKTE ist aus.");

/** Skips if the Punkte mode is on. */
export const requireNotenMode = (testContext, ctx) =>
	skipIf(testContext, punkteMode(ctx), "Übersprungen: CIS_GESAMTNOTE_PUNKTE ist aktiv.");

/** Skips if the getCisConfig key does not have the value that the test needs. */
export const requireConfig = (testContext, ctx, key, value) =>
	skipIf(
		testContext,
		ctx.cisConfig[key] !== value,
		`Übersprungen: ${key} ist ${JSON.stringify(ctx.cisConfig[key])}, der Test braucht ${JSON.stringify(value)}.`,
	);

/**
 * Skips if the Benotungstool cannot create a repeat (Antritt 2). Without CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF
 * the chain ends for the Benotungstool before the kommissionelle Pruefung.
 */
export const requireRepeat = (testContext, ctx) => {
	const fromAntritt = ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT;
	const creatable =
		ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF === false && fromAntritt !== null
			? Math.min(ctx.maxAntritte, fromAntritt - 1)
			: ctx.maxAntritte;
	skipIf(testContext, creatable < 2, `Übersprungen: das Werkzeug legt nur ${creatable} Antritt an.`);
};

/**
 * Skips if the Benotungstool cannot create the kommissionelle Pruefung: the chain has none, or
 * CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF forbids it.
 */
export const requireKommissionellerAntritt = (testContext, ctx) => {
	const fromAntritt = ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT;
	const creatable = ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF;
	const possible = creatable && fromAntritt !== null && fromAntritt >= 2 && fromAntritt <= ctx.maxAntritte;
	skipIf(
		testContext,
		!possible,
		`Übersprungen: kein anlegbarer kommissioneller Antritt (ab ${JSON.stringify(fromAntritt)}, anlegen ${creatable}).`,
	);
};
