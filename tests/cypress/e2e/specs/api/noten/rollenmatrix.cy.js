/**
 * The role matrix: which permission may do which action.
 *
 * Each account asks getCisConfig for its actions. Each action has two tests: one runs when the matrix
 * allows the action, the other when the matrix withholds it. The profile "rollen" withholds the Freigabe
 * and the kommissionelle Pruefung from the Assistenz, otherwise the second test never runs.
 */

import { apiGet, apiPost, loginAsLektor } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import { requireKommissionellerAntritt, skipIf } from "../../../../support/helpers/notenConfig";
import { antrittDate, loadNotenContext, requireDbReset } from "../../../../support/helpers/notenTestData";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";

const accounts = () => [
	{
		label: "Lektor",
		user: Cypress.env("NOTEN_LEKTOR_USER"),
		pass: Cypress.env("NOTEN_LEKTOR_PASSWORD"),
	},
	{
		label: "Assistenz",
		user: Cypress.env("NOTEN_ASSISTENZ_USER"),
		pass: Cypress.env("NOTEN_ASSISTENZ_PASSWORD"),
	},
];

// the first statement of each endpoint checks its action; kommpruef is checked at the kommissionell Antritt
const ENDPOINT = {
	lvnote: "saveLvNote",
	pruefung: "savePruefung",
	freigabe: "saveFreigabe",
	import: "importLvNoten",
};

/** A call that only reaches the action check. The empty body ends in missingParameters otherwise. */
const callAction = (auth, action, ctx) =>
	apiPost(ENDPOINT[action], { lv_id: ctx.lvId, sem_kurzbz: ctx.semKurzbz }, auth);

const errorCodes = (response) => (response.body?.errors ?? []).map((e) => e.code);

describe("Noten API - die Rollenmatrix", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((loaded) => {
			ctx = loaded;
		});
	});

	beforeEach(() => loginAsLektor());

	accounts().forEach(({ label, user, pass }) => {
		describe(label, () => {
			const auth = { username: user, password: pass };
			let allowed = [];

			before(function () {
				skipIf(this, !user || !pass, `Übersprungen: kein Konto für "${label}" in tests/cypress/suites/.env.`);

				cy.clearAllCookies();
				apiGet("getCisConfig", undefined, auth).then((response) => {
					// every account of the suite holds a permission of the matrix; seeder group benotungstool_berechtigungen creates it
					expect(response.status, `getCisConfig als ${label}`).to.eq(200);
					allowed = response.body.data.CIS_GESAMTNOTE_AKTIONEN || [];
					cy.log(`${label} darf: ${JSON.stringify(allowed)}`);
				});
			});

			// otherwise the request sends the session cookie of loginAsLektor, and the server prefers the cookie
			beforeEach(() => cy.clearAllCookies());

			Object.keys(ENDPOINT).forEach((action) => {
				it(`führt "${action}" aus, wenn die Matrix es erlaubt`, function () {
					skipIf(this, !allowed.includes(action), `Übersprungen: die Matrix entzieht ${label} "${action}".`);

					callAction(auth, action, ctx).then((res) => {
						expect(errorCodes(res), `${label} darf "${action}"`).to.not.include("aktionNichtErlaubt");
					});
				});

				it(`lehnt "${action}" ab, wenn die Matrix es entzieht`, function () {
					skipIf(this, allowed.includes(action), `Übersprungen: die Matrix erlaubt ${label} "${action}".`);

					callAction(auth, action, ctx).then((res) => expectNotenError(res, "aktionNichtErlaubt"));
				});
			});

			describe("der kommissionelle Antritt", () => {
				const student = () => ctx.students[6];

				beforeEach(function () {
					requireKommissionellerAntritt(this, ctx);
					requireDbReset();
				});

				/** NOTEN_LEKTOR_USER builds the chain up to the kommissionell Antritt. Returns its date. */
				const bringToBeforeKommission = () => {
					const fromAntritt = ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT;

					givenBaseline(ctx, student());
					for (let nr = 2; nr < fromAntritt; nr += 1) {
						addPruefung(ctx, student(), { note: ctx.noten.negativ, datum: antrittDate(ctx, nr - 1) }).then(
							(response) => expectNotenSuccess(response, `Antritt ${nr}`),
						);
					}
					return antrittDate(ctx, fromAntritt - 1);
				};

				const createKommissionell = (datum) => {
					const s = student();
					// the chain requests can leave a session cookie of NOTEN_LEKTOR_USER
					cy.clearAllCookies();
					return apiPost(
						"savePruefung",
						{
							lv_id: ctx.lvId,
							sem_kurzbz: ctx.semKurzbz,
							student_uid: s.uid,
							pruefung_id: null,
							lehreinheit_id: s.lehreinheit_id,
							datum,
							note: ctx.noten.negativ,
							punkte: null,
						},
						auth,
					);
				};

				it("legt ihn an, wenn die Matrix es erlaubt", function () {
					skipIf(
						this,
						!allowed.includes("kommpruef"),
						`Übersprungen: die Matrix entzieht ${label} "kommpruef".`,
					);

					createKommissionell(bringToBeforeKommission()).then((response) => {
						const { pruefung } = expectNotenSuccess(response, `kommissioneller Antritt als ${label}`)[
							student().uid
						];
						expect(pruefung.pruefungstyp_kurzbz, "der Antritt ist kommissionell").to.eq(
							ctx.cisConfig.PRUEFUNG_TYP_KOMMISSIONELL,
						);
					});
				});

				it("lehnt ihn ab, wenn die Matrix es entzieht", function () {
					skipIf(
						this,
						allowed.includes("kommpruef"),
						`Übersprungen: die Matrix erlaubt ${label} "kommpruef".`,
					);

					createKommissionell(bringToBeforeKommission()).then((response) =>
						expectNotenError(response, "kommPruefNichtErlaubt"),
					);
				});
			});
		});
	});
});
