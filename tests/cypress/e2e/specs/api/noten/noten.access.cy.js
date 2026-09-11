/**
 * Access control (P2) - das Scoping von assertLvAccess.
 *
 * Diese API kennt keine Impersonation, der Lektoren-Scope braucht daher echte Zugangsdaten:
 * NOTEN_TEACHER_USER / NOTEN_TEACHER_PASSWORD / NOTEN_FOREIGN_LV_ID.
 */

import { notenApi } from "../../../../support/api/notenApi";
import {
	expectAuthError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import { loadNotenContext } from "../../../../support/helpers/notenTestData";

const NOTEN_API = "/index.ci.php/api/frontend/v1/Noten";

const teacherConfigured = () =>
	Boolean(Cypress.env("NOTEN_TEACHER_USER") && Cypress.env("NOTEN_FOREIGN_LV_ID"));

/** A getStudentenNoten call as an explicitly chosen user (or with no credentials at all). */
const getStudentenNotenAs = (auth, lvId, semKurzbz) =>
	cy.request({
		method: "GET",
		url: `${NOTEN_API}/getStudentenNoten`,
		qs: { lv_id: lvId, sem_kurzbz: semKurzbz },
		auth,
		failOnStatusCode: false,
	});

describe("Noten API - access control", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	it("rejects requests that are not authenticated", () => {
		cy.clearAllCookies(); // otherwise the previous login is still active

		cy.request({ method: "GET", url: `${NOTEN_API}/getCisConfig`, failOnStatusCode: false }).then(
			(response) => expectAuthError(response),
		);

		cy.request({
			method: "GET",
			url: `${NOTEN_API}/getCisConfig`,
			auth: { username: "no-such-user", password: "no-such-password" },
			failOnStatusCode: false,
		}).then((response) => expectAuthError(response));
	});

	describe("the configured API user", () => {
		it("can read the students of the test LV", () => {
			notenApi.getStudentenNoten(ctx.lvId, ctx.semKurzbz).then((response) => {
				const data = expectNotenSuccess(response, "getStudentenNoten on the own/admin LV");
				expect(data[0], "student list").to.be.an("array").and.not.be.empty;
			});
		});
	});

	describe("teacher scoping", () => {
		beforeEach(function () {
			if (!teacherConfigured()) {
				Cypress.log({
					name: "skip",
					message:
					"Skipped: needs NOTEN_TEACHER_USER / NOTEN_TEACHER_PASSWORD and NOTEN_FOREIGN_LV_ID " +
						"(an LV that teacher does NOT teach). This API has no impersonation, so a second " +
						"real login is required.",
				});
				this.skip();
			}
			// cy.request reuses the session cookie of the previous (admin) call and the server prefers
			// it over the Basic header - without this every test here silently runs as the admin user.
			cy.clearAllCookies();
		});

		const teacherAuth = () => ({
			username: Cypress.env("NOTEN_TEACHER_USER"),
			password: Cypress.env("NOTEN_TEACHER_PASSWORD"),
		});

		// assertLvAccess denies through terminateWithError -> 500 + phrase, not a 401.
		it("denies a teacher access to an LV they do not teach", () => {
			getStudentenNotenAs(
				teacherAuth(),
				Cypress.env("NOTEN_FOREIGN_LV_ID"),
				ctx.semKurzbz,
			).then((response) => {
				expectNotenError(response, "keineBerechtigungNoten");
			});
		});

		it("denies a teacher writing a grade in an LV they do not teach", () => {
			cy.request({
				method: "POST",
				url: `${NOTEN_API}/saveNotenvorschlag`,
				body: {
					lv_id: Cypress.env("NOTEN_FOREIGN_LV_ID"),
					sem_kurzbz: ctx.semKurzbz,
					student_uid: ctx.students[0].uid,
					note: ctx.gradeNotes[0],
					punkte: null,
				},
				auth: teacherAuth(),
				failOnStatusCode: false,
			}).then((response) => {
				expectNotenError(response, "keineBerechtigungNoten");
			});
		});
	});

	// getLvForStudiengang ist NICHT abgedeckt: als Admin greift isBerechtigt('admin') und jeder
	// Aufruf gelingt. Braucht denselben Nicht-Admin-Login wie die Lektorentests oben.

	describe("getBenotungstoolContext shape", () => {
	});
});
