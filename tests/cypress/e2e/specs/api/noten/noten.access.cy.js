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
				cy.log(
					"Skipped: needs NOTEN_TEACHER_USER / NOTEN_TEACHER_PASSWORD and NOTEN_FOREIGN_LV_ID " +
						"(an LV that teacher does NOT teach). This API has no impersonation, so a second " +
						"real login is required.",
				);
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

		/** Ohne das wären die Ablehnungen unten grün, weil der zweite Account gar nicht erst reinkommt. */
		it("authenticates as the configured teacher", () => {
			cy.request({
				method: "GET",
				url: "/index.ci.php/api/frontend/v1/AuthInfo/getAuthUID",
				auth: teacherAuth(),
				failOnStatusCode: false,
			}).then((response) => {
				expect(
					response.status,
					`NOTEN_TEACHER_USER "${Cypress.env("NOTEN_TEACHER_USER")}" cannot log in, so every ` +
						"authorization test below would only be re-testing the login. Fix the credentials.",
				).to.eq(200);
				expect(String(response.body?.data?.uid).toLowerCase()).to.eq(
					String(Cypress.env("NOTEN_TEACHER_USER")).toLowerCase(),
				);
			});
		});

		it("lets a teacher read an LV they teach", () => {
			cy.request({
				method: "GET",
				url: `${NOTEN_API}/getBenotungstoolContext`,
				qs: { sem_kurzbz: ctx.semKurzbz },
				auth: teacherAuth(),
				failOnStatusCode: false,
			}).then((response) => {
				const context = expectNotenSuccess(response, "teacher getBenotungstoolContext");
				const lvs = context.lehrveranstaltungen || [];

				expect(
					lvs.length,
					`${Cypress.env("NOTEN_TEACHER_USER")} must teach at least one LV in ` +
						`${ctx.semKurzbz} for this test to mean anything`,
				).to.be.greaterThan(0);

				getStudentenNotenAs(teacherAuth(), lvs[0].lehrveranstaltung_id, ctx.semKurzbz).then(
					(studentsResponse) => {
						expectNotenSuccess(studentsResponse, "teacher reading their own LV");
					},
				);
			});
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
		it("returns the role-determining payload", () => {
			notenApi.getBenotungstoolContext(ctx.semKurzbz).then((response) => {
				const context = expectNotenSuccess(response, "getBenotungstoolContext");

				expect(context).to.have.property("isAssistenz");
				expect(context.isAssistenz, "isAssistenz is a boolean").to.be.a("boolean");
				expect(context.studiengaenge, "studiengaenge").to.be.an("array");
				expect(context.lehrveranstaltungen, "lehrveranstaltungen").to.be.an("array");
				expect(context).to.have.property("preselectStudiengang_kz");
			});
		});
	});
});
