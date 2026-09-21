/**
 * Configuration-dependent specs.
 *
 * CIS_GESAMTNOTE_PUNKTE is a PHP define() in config/global.config.inc.php and is therefore a
 * property of the INSTANCE, not of the request. A test run cannot switch modes.
 * The suite therefore covers both modes and skips the irrelevant part in each case.
 *
 * For the pipeline, this means: two jobs, each against a single instance. To prevent a job from reporting a pass
 * when in reality everything was skipped, NOTEN_PUNKTE_MODE (‘on’|'off') specifies the expectation.
 * If the instance does not match this, the run fails immediately and with a clear error message.
 */

/**
 * The only way to skip a test. `condition` true -> skip; `reason` explains why.
 * Call within it(function(){...}) / beforeEach(function(){...}), never in an arrow function: an
 * arrow function does not bind `this` and cannot access test.skip().
 *
 * The `require*` helpers below each specify a business requirement and, in turn, call `skipIf`.
 */
export const skipIf = (testContext, condition, reason) => {
	if (!condition) return;
	Cypress.log({ name: "skip", message: reason });
	testContext.skip();
};

/** Ist der Punktemodus auf dieser Instanz aktiv? */
export const punkteMode = (ctx) => Boolean(ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE);

/**
 * In before() aufrufen: prüft die Instanz gegen NOTEN_PUNKTE_MODE. Ohne gesetzte Variable
 * (lokaler Lauf) wird nur geloggt.
 */
export const assertPunkteMode = (ctx) => {
	const expectedMode = String(Cypress.env("NOTEN_PUNKTE_MODE") || "").toLowerCase();
	const actualMode = punkteMode(ctx);

	if (expectedMode !== "on" && expectedMode !== "off") {
		cy.log(`CIS_GESAMTNOTE_PUNKTE = ${actualMode} (NOTEN_PUNKTE_MODE nicht gesetzt)`);
		return;
	}

	expect(
		actualMode,
		`NOTEN_PUNKTE_MODE=${expectedMode}, die Instanz steht aber auf CIS_GESAMTNOTE_PUNKTE=${actualMode}. ` +
			"Der Lauf würde sonst grün melden, obwohl der halbe Umfang übersprungen wurde. " +
			"Flag sitzt in config/global.config.inc.php - cis.config.inc.php wird zu spät geladen.",
	).to.eq(expectedMode === "on");
};

/** Skip, wenn der Punktemodus aus ist. Als erste Zeile in beforeEach(function(){...}) aufrufen. */
export const requirePunkteMode = (testContext, ctx) =>
	skipIf(testContext, !punkteMode(ctx), "Übersprungen: CIS_GESAMTNOTE_PUNKTE ist aus.");

/**
 * Skip, wenn der Schlüssel aus getCisConfig nicht den Zweig trägt, den der Test prüft. Ein Profil aus
 * tests/cypress/profiles/noten.js schaltet den anderen Zweig ein.
 */
export const requireConfig = (testContext, ctx, key, value) =>
	skipIf(
		testContext,
		ctx.cisConfig[key] !== value,
		`Übersprungen: ${key} ist ${JSON.stringify(ctx.cisConfig[key])}, der Test braucht ${JSON.stringify(value)}.`,
	);

/**
 * Skip, wenn das Werkzeug keinen zweiten Antritt anlegt. Ohne CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF
 * endet die Kette für das Werkzeug vor dem kommissionellen Antritt.
 */
export const requireWiederholung = (testContext, ctx) => {
	const fromAntritt = ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT ?? null;
	const creatable =
		ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF === false && fromAntritt !== null
			? Math.min(ctx.maxAntritte, fromAntritt - 1)
			: ctx.maxAntritte;
	skipIf(testContext, creatable < 2, `Übersprungen: das Werkzeug legt nur ${creatable} Antritt an.`);
};

/**
 * Skip, wenn das Werkzeug keinen kommissionellen Antritt anlegt: die Kette endet ohne Kommission, oder
 * CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF verbietet die Anlage.
 */
export const requireKommissionellerAntritt = (testContext, ctx) => {
	const fromAntritt = ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT ?? null;
	const creatable = ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF !== false;
	const possible = creatable && fromAntritt !== null && fromAntritt >= 2 && fromAntritt <= ctx.maxAntritte;
	skipIf(
		testContext,
		!possible,
		`Übersprungen: kein anlegbarer kommissioneller Antritt (ab ${JSON.stringify(fromAntritt)}, anlegen ${creatable}).`,
	);
};

/** Skip, wenn der Punktemodus an ist. */
export const requireNotenMode = (testContext, ctx) =>
	skipIf(testContext, punkteMode(ctx), "Übersprungen: CIS_GESAMTNOTE_PUNKTE ist aktiv.");
