/**
 * Konfigurationsabhängige Specs.
 *
 * CIS_GESAMTNOTE_PUNKTE ist ein PHP define() in config/global.config.inc.php und damit eine
 * Eigenschaft der INSTANZ, nicht des Requests - ein Testlauf kann den Modus nicht umschalten.
 * Die Suite deckt deshalb beide Modi ab und überspringt jeweils den unpassenden Teil.
 *
 * Für die Pipeline heisst das: zwei Jobs gegen je eine Instanz. Damit ein Job nicht grün meldet,
 * weil in Wahrheit alles übersprungen wurde, gibt NOTEN_PUNKTE_MODUS ('on'|'off') die Erwartung
 * vor - passt die Instanz nicht dazu, scheitert der Lauf sofort und laut.
 */

/** Ist der Punktemodus auf dieser Instanz aktiv? */
export const punkteModus = (ctx) => Boolean(ctx.cisConfig.CIS_GESAMTNOTE_PUNKTE);

/**
 * In before() aufrufen: prüft die Instanz gegen NOTEN_PUNKTE_MODUS. Ohne gesetzte Variable
 * (lokaler Lauf) wird nur geloggt.
 */
export const assertPunkteModus = (ctx) => {
	const erwartet = String(Cypress.env("NOTEN_PUNKTE_MODUS") || "").toLowerCase();
	const ist = punkteModus(ctx);

	if (erwartet !== "on" && erwartet !== "off") {
		cy.log(`CIS_GESAMTNOTE_PUNKTE = ${ist} (NOTEN_PUNKTE_MODUS nicht gesetzt)`);
		return;
	}

	expect(
		ist,
		`NOTEN_PUNKTE_MODUS=${erwartet}, die Instanz steht aber auf CIS_GESAMTNOTE_PUNKTE=${ist}. ` +
			"Der Lauf würde sonst grün melden, obwohl der halbe Umfang übersprungen wurde. " +
			"Flag sitzt in config/global.config.inc.php - cis.config.inc.php wird zu spät geladen.",
	).to.eq(erwartet === "on");
};

/** Skip, wenn der Punktemodus aus ist. Als erste Zeile in beforeEach(function(){...}) aufrufen. */
export const requirePunkteModus = (testContext, ctx) => {
	if (punkteModus(ctx)) return;
	cy.log("Übersprungen: CIS_GESAMTNOTE_PUNKTE ist aus.");
	testContext.skip();
};

/**
 * Skip, wenn der Schlüssel aus getCisConfig nicht den Zweig trägt, den der Test prüft. Ein Profil aus
 * tests/cypress/profiles/noten.js schaltet den anderen Zweig ein.
 */
export const requireKonfiguration = (testContext, ctx, key, wert) => {
	if (ctx.cisConfig[key] === wert) return;
	Cypress.log({
		name: "skip",
		message: `Übersprungen: ${key} ist ${JSON.stringify(ctx.cisConfig[key])}, der Test braucht ${JSON.stringify(wert)}.`,
	});
	testContext.skip();
};

/**
 * Skip, wenn das Werkzeug keinen zweiten Antritt anlegt. Ohne CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF
 * endet die Kette für das Werkzeug vor dem kommissionellen Antritt.
 */
export const requireWiederholung = (testContext, ctx) => {
	const ab = ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT ?? null;
	const anlegbar =
		ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF === false && ab !== null
			? Math.min(ctx.maxAntritte, ab - 1)
			: ctx.maxAntritte;
	if (anlegbar >= 2) return;
	Cypress.log({ name: "skip", message: `Übersprungen: das Werkzeug legt nur ${anlegbar} Antritt an.` });
	testContext.skip();
};

/**
 * Skip, wenn das Werkzeug keinen kommissionellen Antritt anlegt: die Kette endet ohne Kommission, oder
 * CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF verbietet die Anlage.
 */
export const requireKommissionellerAntritt = (testContext, ctx) => {
	const ab = ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT ?? null;
	const anlegbar = ctx.cisConfig.CIS_GESAMTNOTE_ALLOW_CREATE_KOMMPRUEF !== false;
	if (anlegbar && ab !== null && ab >= 2 && ab <= ctx.maxAntritte) return;
	Cypress.log({
		name: "skip",
		message: `Übersprungen: kein anlegbarer kommissioneller Antritt (ab ${JSON.stringify(ab)}, anlegen ${anlegbar}).`,
	});
	testContext.skip();
};

/** Skip, wenn der Punktemodus an ist. */
export const requireNotenModus = (testContext, ctx) => {
	if (!punkteModus(ctx)) return;
	cy.log("Übersprungen: CIS_GESAMTNOTE_PUNKTE ist aktiv.");
	testContext.skip();
};
