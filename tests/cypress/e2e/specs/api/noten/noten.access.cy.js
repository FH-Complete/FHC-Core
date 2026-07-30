/**
 * Access control (P2) - assertLvAccess scoping (Noten.php:243).
 *
 * There is no impersonation in this API, so testing teacher scoping needs that teacher's real
 * credentials: NOTEN_TEACHER_USER / NOTEN_TEACHER_PASSWORD / NOTEN_FOREIGN_LV_ID.
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

	describe("authentication", () => {
		it("rejects an unauthenticated request", () => {
			// Without this the auth is still active from last login
			cy.clearAllCookies();

			cy.request({
				method: "GET",
				url: `${NOTEN_API}/getCisConfig`,
				failOnStatusCode: false,
			}).then((response) => {
				expectAuthError(response);
			});
		});

		it("rejects invalid credentials", () => {
			cy.request({
				method: "GET",
				url: `${NOTEN_API}/getCisConfig`,
				auth: { username: "no-such-user", password: "no-such-password" },
				failOnStatusCode: false,
			}).then((response) => {
				expectAuthError(response);
			});
		});
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
		});

		const teacherAuth = () => ({
			username: Cypress.env("NOTEN_TEACHER_USER"),
			password: Cypress.env("NOTEN_TEACHER_PASSWORD"),
		});

		it("lets a teacher read an LV they teach", () => {
			notenApi.getBenotungstoolContext(ctx.semKurzbz).then(() => {
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
		});

		it("denies a teacher access to an LV they do not teach", () => {
			getStudentenNotenAs(
				teacherAuth(),
				Cypress.env("NOTEN_FOREIGN_LV_ID"),
				ctx.semKurzbz,
			).then((response) => {
				expectAuthError(response);
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
				expectAuthError(response);
			});
		});
	});

	describe("getLvForStudiengang (Assistenz flow)", () => {
		it("refuses a Studiengang the caller is not entitled for", () => {
			// 0 is not a real studiengang_kz, so no caller can be entitled for it. Admins bypass the
			// check entirely, in which case the request succeeds with an empty list instead.
			notenApi.getLvForStudiengang(0, ctx.semKurzbz).then((response) => {
				expect(response.status, "either denied (500) or allowed-as-admin (200)").to.be.oneOf([
					200, 500,
				]);

				if (response.status === 500) {
					expect(response.body).to.have.nested.property("meta.status", "error");
					expect(response.body.errors, "a denial carries a message").to.be.an("array").and.not
						.be.empty;
				} else {
					expect(response.body.data, "an admin gets an empty LV list").to.be.an("array");
				}
			});
		});

		it("rejects a call without the required parameters", () => {
			cy.request({
				method: "GET",
				url: `${NOTEN_API}/getLvForStudiengang`,
				qs: { sem_kurzbz: ctx.semKurzbz },
				auth: {
					username: Cypress.env("adminusername"),
					password: Cypress.env("adminpassword"),
				},
				failOnStatusCode: false,
			}).then((response) => {
				expect(response.status, "missing studiengang_kz").to.eq(500);
				expect(response.body).to.have.nested.property("meta.status", "error");
			});
		});
	});

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
