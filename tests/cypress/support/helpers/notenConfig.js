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

/** Skip, wenn der Punktemodus an ist. */
export const requireNotenModus = (testContext, ctx) => {
	if (!punkteModus(ctx)) return;
	cy.log("Übersprungen: CIS_GESAMTNOTE_PUNKTE ist aktiv.");
	testContext.skip();
};
