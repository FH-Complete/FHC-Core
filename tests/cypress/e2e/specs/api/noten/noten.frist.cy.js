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
	lehrsemesterMitAbgelaufenerFrist,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";

// Deadlines computeNoteneintragungsfrist derives: SS -> 15.11.yyyy, WS -> 15.05.yyyy+1.
const fristConfig = () => ({ ss: { month: 11, day: 15 }, ws: { month: 5, day: 15 } });

describe("Noten API - Noteneintragungsfrist (Prüfungsordnung §1)", () => {
	let ctx;
	let fristDatum; // sperrt das DATUM der Prüfung
	let fristEingabe; // sperrt den ZEITPUNKT der Eingabe
	// Semester samt Lehrveranstaltung, in dem der Benutzer unterrichtet und die Frist abgelaufen ist
	const abgelaufen = { SS: null, WS: null };

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
			// Die zwei Fristen beantworten zwei Fragen. Der Server liefert die Werte, die er
			// wirklich anwendet; der alte Schlüssel ist darin schon aufgelöst.
			fristDatum = Boolean(ctx.cisConfig.CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM);
			// eine Ausnahmerolle hebt die Eingabefrist für diesen Benutzer auf
			fristEingabe =
				Boolean(ctx.cisConfig.CIS_GESAMTNOTE_FRIST_EINGABE) &&
				ctx.cisConfig.CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT !== true;
			cy.log(`FRIST_PRUEFUNGSDATUM=${fristDatum} FRIST_EINGABE=${fristEingabe} AUSNAHME=${ctx.cisConfig.CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT}`);
		});

		lehrsemesterMitAbgelaufenerFrist("SS").then((paar) => {
			abgelaufen.SS = paar;
			cy.log(`SS mit abgelaufener Frist: ${JSON.stringify(paar)}`);
		});

		lehrsemesterMitAbgelaufenerFrist("WS").then((paar) => {
			abgelaufen.WS = paar;
			cy.log(`WS mit abgelaufener Frist: ${JSON.stringify(paar)}`);
		});
	});

	// §7 und §11: nicht nur der Zeitpunkt der Eingabe zählt, sondern auch das Prüfungsdatum selbst.
	// Die kommissionelle Prüfung muss bis zur Frist STATTFINDEN, sonst verliert der Student ein
	// Semester. Das ist eine andere Frage als "darf jetzt noch eingetragen werden".
	describe("Prüfungsdatum nach der Frist", () => {
		beforeEach(function () {
			if (!fristDatum) {
				Cypress.log({ name: "skip", message: "Übersprungen: CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM ist aus." });
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
				if (!fristEingabe) this.skip();

				// assertLvAccess laeuft vor der Frist: das Semester muss eines sein, in dem der
				// Benutzer wirklich unterrichtet, sonst antwortet der Server mit fehlender Berechtigung
				const paar = abgelaufen[type];
				if (!paar) {
					Cypress.log({ name: "skip", message: `Übersprungen: kein ${type} mit abgelaufener Frist, in dem der Benutzer unterrichtet.` });
					this.skip();
				}

				const sem = paar.semKurzbz;
				const cfg = fristConfig();
				const expectedDeadline = expectedFristString(sem, cfg.ss, cfg.ws);

				expect(fristHasPassed(sem), `${sem} deadline is in the past`).to.be.true;

				notenApi
					.saveStudentPruefung({
						student_uid: ctx.students[0].uid,
						note: ctx.gradeNotes[0],
						punkte: null,
						datum: attemptDate(ctx, 1),
						lva_id: paar.lvId,
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

		// Die Ausnahme deckt die EINGABE ab, nicht das Prüfungsdatum. Ohne Ausnahmeweg wird eine Frist
		// beim ersten Sonderfall abgeschaltet, deshalb nennt CIS_GESAMTNOTE_FRIST_AUSNAHME Rollen.
		// Der Test prüft beide Seiten: mit Ausnahme geht die Eingabe durch, ohne wird sie abgelehnt.
		it("lässt eine Ausnahmerolle nach der Frist eintragen", function () {
			if (!ctx.cisConfig.CIS_GESAMTNOTE_FRIST_EINGABE) {
				Cypress.log({ name: "skip", message: "Übersprungen: CIS_GESAMTNOTE_FRIST_EINGABE ist aus." });
				this.skip();
			}

			const ausnahme = ctx.cisConfig.CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT === true;
			const paar = abgelaufen.SS || abgelaufen.WS;
			if (!paar) {
				Cypress.log({ name: "skip", message: "Übersprungen: kein Semester mit abgelaufener Frist, in dem der Benutzer unterrichtet." });
				this.skip();
			}
			const sem = paar.semKurzbz;

			requireDbReset();

			notenApi
				.saveStudentPruefung({
					student_uid: ctx.students[0].uid,
					note: ctx.gradeNotes[0],
					punkte: null,
					datum: attemptDate(ctx, 1),
					lva_id: paar.lvId,
					lehreinheit_id: ctx.students[0].lehreinheit_id,
					sem_kurzbz: sem,
					pruefung_id: null,
				})
				.then((response) => {
					if (ausnahme) {
						// die Eingabefrist gilt nicht; eine andere Regel darf trotzdem greifen
						const meldungen = (response.body.errors || []).map((e) => e.message).join(" | ");
						expect(meldungen, "die Eingabefrist darf nicht mehr greifen").to.not.include(
							"Noteneintragungsfrist",
						);
						return;
					}

					expectNotenError(response, "noteneintragungsfristVorbei");
				});
		});
	});

	describe("deadline is still ahead", () => {
		before(() => {
			requireDbReset();
		});

	});

	// Die SS/WS-Ableitung wird gegen den SERVER geprüft (die Frist in der Fehlermeldung oben) -
	// ein lokaler Vergleich von expectedFristString() gegen Konstanten testet nur den Helfer.
});
