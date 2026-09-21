/**
 * The role matrix: which permission may do which action.
 *
 * Each account asks getCisConfig for its actions. Each action has two tests: one runs when the matrix
 * allows the action, the other when the matrix withholds it. The profile "rollen" withholds the release
 * and the kommissionelle Prüfung from the Assistenz, otherwise the second test never runs.
 */

import { expectNotenError, expectNotenSuccess, messageMatchesPhrase } from "../../../../support/helpers/notenErrors";
import { requireKommissionellerAntritt, skipIf } from "../../../../support/helpers/notenConfig";
import { attemptDate, loadNotenContext, requireDbReset } from "../../../../support/helpers/notenTestData";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";

const NOTEN_API = "/index.ci.php/api/frontend/v1/Noten";

const accounts = () => [
	{
		label: "Lektor",
		user: Cypress.env("NOTEN_TEACHER_USER"),
		pass: Cypress.env("NOTEN_TEACHER_PASSWORD"),
	},
	{
		label: "Assistenz",
		user: Cypress.env("NOTEN_ASSISTENZ_USER"),
		pass: Cypress.env("NOTEN_ASSISTENZ_PASSWORD"),
	},
];

// the first statement of each endpoint checks its action; kommpruef is checked at the kommissionell attempt
const ENDPOINT = {
	vorschlag: "saveNotenvorschlag",
	pruefung: "saveStudentPruefung",
	freigabe: "saveStudentenNoten",
	import: "saveNotenvorschlagBulk",
};

/** A call that only reaches the action check. The empty body ends in missingParameters otherwise. */
const callAction = (auth, action, ctx) =>
	cy.request({
		method: "POST",
		url: `${NOTEN_API}/${ENDPOINT[action]}`,
		body: { lv_id: ctx.lvId, sem_kurzbz: ctx.semKurzbz },
		auth,
		failOnStatusCode: false,
	});

const errorMessages = (response) => ((response.body && response.body.errors) || []).map((e) => e.message);

describe("Noten API - die Rollenmatrix", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	accounts().forEach(({ label, user, pass }) => {
		describe(label, () => {
			const auth = { username: user, password: pass };
			let allowed = [];

			before(function () {
				skipIf(this, !user || !pass, `Übersprungen: kein Konto für "${label}" in tests/cypress/.env.`);

				cy.clearAllCookies();
				cy.request({ method: "GET", url: `${NOTEN_API}/getCisConfig`, auth, failOnStatusCode: false }).then(
					(response) => {
						// every account of the suite holds a permission of the matrix; seeder group benotungstool_berechtigungen creates it
						expect(response.status, `getCisConfig als ${label}`).to.eq(200);
						allowed = response.body.data.CIS_GESAMTNOTE_AKTIONEN || [];
						cy.log(`${label} darf: ${JSON.stringify(allowed)}`);
					},
				);
			});

			// cy.request sends the cookie of the suite user otherwise, and the server prefers the cookie
			beforeEach(() => cy.clearAllCookies());

			Object.keys(ENDPOINT).forEach((action) => {
				it(`führt "${action}" aus, wenn die Matrix es erlaubt`, function () {
					skipIf(this, !allowed.includes(action), `Übersprungen: die Matrix entzieht ${label} "${action}".`);

					callAction(auth, action, ctx).then((res) => {
						expect(
							errorMessages(res).some((m) => messageMatchesPhrase(m, "aktionNichtErlaubt")),
							`${label} darf "${action}": ${JSON.stringify(errorMessages(res))}`,
						).to.be.false;
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

				/** The suite user builds the chain up to the kommissionell attempt. Returns its date. */
				const bringToBeforeKommission = () => {
					const fromAntritt = ctx.cisConfig.CIS_GESAMTNOTE_KOMMISSIONELL_AB_ANTRITT;

					givenBaseline(ctx, student());
					for (let nr = 2; nr < fromAntritt; nr += 1) {
						addPruefung(ctx, student(), { note: ctx.notes.negativ, datum: attemptDate(ctx, nr - 1) }).then(
							(response) => expectNotenSuccess(response, `Antritt ${nr}`),
						);
					}
					return attemptDate(ctx, fromAntritt - 1);
				};

				const createKommissionell = (datum) => {
					const s = student();
					// the chain requests can leave a session cookie of the suite user
					cy.clearAllCookies();
					return cy.request({
						method: "POST",
						url: `${NOTEN_API}/saveStudentPruefung`,
						body: {
							student_uid: s.uid,
							note: ctx.notes.negativ,
							punkte: null,
							datum,
							lva_id: ctx.lvId,
							lehreinheit_id: s.lehreinheit_id,
							sem_kurzbz: ctx.semKurzbz,
							pruefung_id: null,
						},
						auth,
						failOnStatusCode: false,
					});
				};

				it("legt ihn an, wenn die Matrix es erlaubt", function () {
					skipIf(
						this,
						!allowed.includes("kommpruef"),
						`Übersprungen: die Matrix entzieht ${label} "kommpruef".`,
					);

					createKommissionell(bringToBeforeKommission()).then((response) => {
						const [saved] = expectNotenSuccess(response, `kommissioneller Antritt als ${label}`);
						expect(saved.pruefungstyp_kurzbz, "der Antritt ist kommissionell").to.eq(
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
