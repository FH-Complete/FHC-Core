/**
 * Notenfreigabe (P1, cases 12-14).
 *
 * A successful saveStudentenNoten ALWAYS sends the Notenfreigabe mail (Noten.php:585, not behind a
 * config flag), so the happy path is opt-in via NOTEN_FREIGABE_ENABLED. The wrong-password test
 * needs no gate: the check runs before any write and before the mail.
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	baselineBenotungsdatum,
	loadNotenContext,
	readLvGesamtnote,
	requireDbReset,
	resetNotenState,
	seedBaseline,
} from "../../../../support/helpers/notenTestData";
import { lvNoteOf, readState } from "../../../../support/helpers/notenScenario";

const freigabeEnabled = () => String(Cypress.env("NOTEN_FREIGABE_ENABLED")).toLowerCase() === "true";
const freigabePassword = () =>
	Cypress.env("NOTEN_FREIGABE_PASSWORD") || Cypress.env("adminpassword");

/** The payload shape saveStudentenNoten expects per student (see the studlist builder). */
const notenPayload = (student, noteBezeichnung) => ({
	uid: student.uid,
	matrikelnr: student.matrikelnr || student.matr_nr || "",
	kuerzel: student.kuerzel || "",
	nachname: student.nachname || "",
	vorname: student.vorname || "",
	noteBezeichnung: noteBezeichnung || "",
});

describe("Noten API - Notenfreigabe", () => {
	let ctx;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
		});
	});

	describe("password gate", () => {
		it("rejects a Freigabe with a wrong password and changes nothing", () => {
			requireDbReset();

			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, {
				note: ctx.gradeNotes[0],
				freigegeben: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});

			notenApi
				.saveStudentenNoten(
					"definitely-not-the-password",
					[notenPayload(student)],
					ctx.lvId,
					ctx.semKurzbz,
				)
				.then((response) => {
					expectNotenError(response, "wrongPassword");
				});

			readLvGesamtnote(ctx, student.uid).then((row) => {
				expect(row, "the row still exists").to.not.be.null;
				expect(row.freigabedatum, "a rejected Freigabe must not stamp freigabedatum").to.be.null;
			});
		});

		it("rejects a Freigabe without the required parameters", () => {
			cy.request({
				method: "POST",
				url: "/index.ci.php/api/frontend/v1/Noten/saveStudentenNoten",
				body: { lv_id: ctx.lvId, sem_kurzbz: ctx.semKurzbz },
				auth: {
					username: Cypress.env("adminusername"),
					password: Cypress.env("adminpassword"),
				},
				failOnStatusCode: false,
			}).then((response) => {
				expect(response.status, "missing password/noten").to.eq(500);
				expect(response.body).to.have.nested.property("meta.status", "error");
			});
		});
	});

	describe("happy path (sends mail - opt in via NOTEN_FREIGABE_ENABLED)", () => {
		beforeEach(function () {
			if (!freigabeEnabled()) {
				cy.log(
					"Skipped: saveStudentenNoten sends the Notenfreigabe email on success. " +
						"Set NOTEN_FREIGABE_ENABLED=true only on an environment where that mail is harmless.",
				);
				this.skip();
			}
			requireDbReset();
		});

		it("stamps freigabedatum on a changed note", () => {
			const student = ctx.students[0];

			resetNotenState(ctx);
			seedBaseline(ctx, student.uid, {
				note: ctx.gradeNotes[0],
				freigegeben: false,
				benotungsdatum: baselineBenotungsdatum(ctx),
			});

			notenApi
				.saveStudentenNoten(
					freigabePassword(),
					[notenPayload(student)],
					ctx.lvId,
					ctx.semKurzbz,
				)
				.then((response) => {
					const data = expectNotenSuccess(response, "saveStudentenNoten");
					expect(data, "freigegebene rows are reported back").to.be.an("array");

					const entry = data.find((r) => r.uid === student.uid);
					expect(entry, `${student.uid} must be reported as freigegeben`).to.exist;
					expect(entry.freigabedatum, "freigabedatum is stamped").to.exist;
				});

			readState(ctx).then((data) => {
				const grades = lvNoteOf(data, student.uid);
				expect(grades.freigabedatum, "the note is now freigegeben").to.exist;
			});
		});

		it("only touches rows whose benotungsdatum is newer than their freigabedatum", () => {
			const changed = ctx.students[0];
			const alreadyReleased = ctx.students[1];

			resetNotenState(ctx);

			// changed: benotungsdatum after freigabedatum -> must be re-freigegeben
			seedBaseline(ctx, changed.uid, {
				note: ctx.gradeNotes[0],
				freigegeben: true,
				freigabedatum: `${ctx.semKurzbz.slice(2, 6)}-01-05 08:00:00`,
				benotungsdatum: `${ctx.semKurzbz.slice(2, 6)}-01-10 08:00:00`,
			});

			// already freigegeben and untouched since -> must be left alone
			seedBaseline(ctx, alreadyReleased.uid, {
				note: ctx.gradeNotes[0],
				freigegeben: true,
				freigabedatum: `${ctx.semKurzbz.slice(2, 6)}-01-10 08:00:00`,
				benotungsdatum: `${ctx.semKurzbz.slice(2, 6)}-01-10 08:00:00`,
			});

			notenApi
				.saveStudentenNoten(
					freigabePassword(),
					[notenPayload(changed), notenPayload(alreadyReleased)],
					ctx.lvId,
					ctx.semKurzbz,
				)
				.then((response) => {
					const data = expectNotenSuccess(response, "selective Freigabe");
					const uids = data.map((r) => r.uid);

					expect(uids, "the changed note is freigegeben").to.include(changed.uid);
					expect(
						uids,
						"an unchanged, already freigegebene note must not be freigegeben again",
					).to.not.include(alreadyReleased.uid);
				});

			readLvGesamtnote(ctx, alreadyReleased.uid).then((row) => {
				expect(
					new Date(row.freigabedatum).toISOString().slice(0, 10),
					"the untouched row keeps its original freigabedatum",
				).to.eq(`${ctx.semKurzbz.slice(2, 6)}-01-10`);
			});
		});
	});
});
