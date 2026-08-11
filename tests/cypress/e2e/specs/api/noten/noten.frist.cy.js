/**
 * Prüfungsordnung §1 - Noteneintragungsfrist (P0, cases 8-9).
 *
 * Die Prüfung läuft vor jedem Schreibzugriff, ein abgelaufenes Semester wird also abgelehnt ohne
 * eine Zeile anzufassen. Die Fehlermeldung nennt die Frist und prüft damit zugleich deren
 * Ableitung (SS -> 15.11.yyyy, WS -> 15.05.yyyy+1).
 */

import { notenApi } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	expectedFristString,
	fristHasPassed,
	loadNotenContext,
	requireDbReset,
	semesterWithPastFrist,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";

// Deadlines computeNoteneintragungsfrist derives: SS -> 15.11.yyyy, WS -> 15.05.yyyy+1.
const fristConfig = () => ({ ss: { month: 11, day: 15 }, ws: { month: 5, day: 15 } });

describe("Noten API - Noteneintragungsfrist (Prüfungsordnung §1)", () => {
	let ctx;
	let enforced;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
			enforced = Boolean(ctx.cisConfig.CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST);
			cy.log(`CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST = ${enforced}`);
		});
	});

	// §7 und §11: nicht nur der Zeitpunkt der Eingabe zählt, sondern auch das Prüfungsdatum selbst.
	// Die kommissionelle Prüfung muss bis zur Frist STATTFINDEN, sonst verliert der Student ein
	// Semester. Das ist eine andere Frage als "darf jetzt noch eingetragen werden".
	describe("Prüfungsdatum nach der Frist", () => {
		beforeEach(function () {
			if (!enforced) {
				cy.log("Übersprungen: CIS_GESAMTNOTE_NOTENEINTRAGUNGSFRIST ist aus.");
				this.skip();
			}
			requireDbReset();
		});

		it("lehnt einen Termin ab, der nach der Frist liegt", () => {
			const student = ctx.students[0];
			const jahr = Number(ctx.semKurzbz.slice(2, 6));
			// die Frist des laufenden Semesters liegt in der Zukunft, ein Datum dahinter ist ungültig
			const nachDerFrist = `${jahr + 1}-12-31`;

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: nachDerFrist }).then((response) => {
				expectNotenError(response, "pruefungsdatumNachFrist");
			});
		});

		it("nimmt einen Termin vor der Frist an", () => {
			const student = ctx.students[1];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 1) }).then(
				(response) => expectNotenSuccess(response, "Termin innerhalb der Frist"),
			);
		});
	});

	describe("deadline has passed", () => {
		// A rejected request writes nothing, so these need no DB reset.
		[
			{ type: "SS", label: "Sommersemester (deadline in the same year)" },
			{ type: "WS", label: "Wintersemester (deadline in the following year)" },
		].forEach(({ type, label }) => {
			it(`rejects grade entry for a ${label}`, function () {
				// no flag, no deadline - skip rather than pretend it passed
				if (!enforced) this.skip();

				const sem = semesterWithPastFrist(type);
				const cfg = fristConfig();
				const expectedDeadline = expectedFristString(sem, cfg.ss, cfg.ws);

				expect(fristHasPassed(sem), `${sem} deadline is in the past`).to.be.true;

				notenApi
					.saveStudentPruefung({
						student_uid: ctx.students[0].uid,
						note: ctx.gradeNotes[0],
						punkte: null,
						datum: attemptDate(ctx, 1),
						lva_id: ctx.lvId,
						lehreinheit_id: ctx.students[0].lehreinheit_id,
						sem_kurzbz: sem,
							pruefung_id: null,
					})
					.then((response) => {
						expectNotenError(response, "noteneintragungsfristVorbei");

						const message = response.body.errors.map((e) => e.message).join(" | ");
						expect(
							message,
							`the ${type} deadline the server derived for ${sem}`,
						).to.include(expectedDeadline);
					});
			});
		});

		it("rejects saveNotenvorschlag past the deadline as well", function () {
			if (!enforced) this.skip();

			const sem = semesterWithPastFrist("SS");

			notenApi
				.saveNotenvorschlag(ctx.lvId, sem, ctx.students[0].uid, ctx.gradeNotes[0])
				.then((response) => {
					expectNotenError(response, "noteneintragungsfristVorbei");
				});
		});
	});

	describe("deadline is still ahead", () => {
		before(() => {
			requireDbReset();
		});

		it("accepts grade entry in a semester whose deadline has not passed", function () {
			if (enforced && fristHasPassed(ctx.semKurzbz)) {
				// hier ist kein Schreiben möglich, damit wäre die gesamte mutierende Suite blockiert
				throw new Error(
					`Noteneintragungsfrist enforcement is ON and the deadline for the test semester ` +
						`${ctx.semKurzbz} (${expectedFristString(ctx.semKurzbz)}) has already passed, so no ` +
						`grade can be entered. Point NOTEN_SEM at a semester whose deadline is still ahead.`,
				);
			}

			const student = ctx.students[0];

			givenBaseline(ctx, student);

			addPruefung(ctx, student, {
				note: ctx.gradeNotes[0],
				datum: attemptDate(ctx, 1),
			}).then((response) => {
				expectNotenSuccess(response, `grade entry within the deadline for ${ctx.semKurzbz}`);
			});
		});
	});

	// Die SS/WS-Ableitung wird gegen den SERVER geprüft (die Frist in der Fehlermeldung oben) -
	// ein lokaler Vergleich von expectedFristString() gegen Konstanten testet nur den Helfer.
});
