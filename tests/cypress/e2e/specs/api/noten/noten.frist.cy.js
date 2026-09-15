/**
 * Prüfungsordnung §1 - Noteneintragungsfrist.
 *
 * Zwei Fristen beantworten zwei Fragen: CIS_GESAMTNOTE_FRIST_EINGABE sperrt den Zeitpunkt der Eingabe,
 * CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM das Datum der Prüfung. Die Eingabefrist greift in jedem Schreibpfad
 * direkt nach der Zugriffsprüfung, ein abgelehnter Request schreibt also nichts. Die Meldung nennt die Frist
 * und prüft damit ihre Ableitung aus NOTENEINTRAGUNGSFRIST_SS/WS.
 *
 * Seeder 019 legt das Sommersemester an, in dem der Testbenutzer nach der Frist unterrichtet.
 */

import { notenApi } from "../../../../support/api/notenApi";
import {
	expectNotenError,
	expectNotenSuccess,
	messageMatchesPhrase,
} from "../../../../support/helpers/notenErrors";
import {
	attemptDate,
	expectedFristString,
	fristHasPassed,
	loadNotenContext,
	requireDbReset,
	lehrsemesterMitAbgelaufenerFrist,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";
import { requireKonfiguration, requireWiederholung } from "../../../../support/helpers/notenConfig";

describe("Noten API - Noteneintragungsfrist (Prüfungsordnung §1)", () => {
	let ctx;
	// Semester samt Lehrveranstaltung, in dem der Benutzer unterrichtet und die Frist abgelaufen ist
	const abgelaufen = { SS: null, WS: null };

	const fristSS = () => ctx.cisConfig.NOTENEINTRAGUNGSFRIST_SS;
	const fristWS = () => ctx.cisConfig.NOTENEINTRAGUNGSFRIST_WS;

	before(() => {
		loadNotenContext().then((context) => {
			ctx = context;
			cy.log(
				`FRIST_EINGABE=${ctx.cisConfig.CIS_GESAMTNOTE_FRIST_EINGABE} ` +
					`FRIST_PRUEFUNGSDATUM=${ctx.cisConfig.CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM} ` +
					`AUSNAHME_GILT=${ctx.cisConfig.CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT}`,
			);

			["SS", "WS"].forEach((typ) =>
				lehrsemesterMitAbgelaufenerFrist(typ, fristSS(), fristWS()).then((paar) => {
					abgelaufen[typ] = paar;
					cy.log(`${typ} mit abgelaufener Frist: ${JSON.stringify(paar)}`);
				}),
			);
		});
	});

	// ein fehlendes Semester ist ein Fixturefehler und kein übersprungener Test
	const paarFuer = (typ) => {
		expect(abgelaufen[typ], `ein ${typ} mit abgelaufener Frist, in dem der Testbenutzer unterrichtet`).to.not.be.null;
		return abgelaufen[typ];
	};

	// §7 und §11: Die kommissionelle Prüfung muss bis zur Frist STATTFINDEN. Das ist eine andere Frage als
	// "darf jetzt noch eingetragen werden".
	describe("Prüfungsdatum nach der Frist", () => {
		beforeEach(() => requireDbReset());

		// die Frist des laufenden Semesters liegt vor diesem Datum
		const nachDerFrist = () => `${Number(ctx.semKurzbz.slice(2, 6)) + 1}-12-31`;

		it("lehnt einen Termin ab, der nach der Frist liegt", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM", true);

			const student = ctx.students[0];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: nachDerFrist() }).then((response) =>
				expectNotenError(response, "pruefungsdatumNachFrist"),
			);
		});

		it("nimmt einen Termin nach der Frist an, wenn das Prüfungsdatum an keine Frist gebunden ist", function () {
			requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM", false);
			requireWiederholung(this, ctx);

			const student = ctx.students[0];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notes.negativ, datum: nachDerFrist() }).then((response) =>
				expectNotenSuccess(response, "Termin nach der Frist"),
			);
		});

		it("nimmt einen Termin vor der Frist an", function () {
			requireWiederholung(this, ctx);

			const student = ctx.students[1];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.gradeNotes[0], datum: attemptDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Termin innerhalb der Frist"),
			);
		});
	});

	describe("Eingabe nach der Frist", () => {
		const s0 = () => ctx.students[0];

		// Der Studierende gehört nicht zur Lehrveranstaltung des abgelaufenen Semesters. Greift die Eingabefrist
		// nicht, lehnt die Teilnehmerprüfung ab, und der Request schreibt trotzdem nichts.
		const schreibpfade = {
			saveStudentPruefung: (paar) =>
				notenApi.saveStudentPruefung({
					student_uid: s0().uid,
					note: ctx.gradeNotes[0],
					punkte: null,
					datum: attemptDate(ctx, 1),
					lva_id: paar.lvId,
					lehreinheit_id: s0().lehreinheit_id,
					sem_kurzbz: paar.semKurzbz,
					pruefung_id: null,
				}),
			saveNotenvorschlag: (paar) => notenApi.saveNotenvorschlag(paar.lvId, paar.semKurzbz, s0().uid, ctx.gradeNotes[0]),
			saveNotenvorschlagBulk: (paar) =>
				notenApi.saveNotenvorschlagBulk(paar.lvId, paar.semKurzbz, [
					{ uid: s0().uid, note: ctx.gradeNotes[0], punkte: null },
				]),
			createPruefungen: (paar) =>
				notenApi.createPruefungen(
					[{ uid: s0().uid, lehreinheit_id: s0().lehreinheit_id }],
					attemptDate(ctx, 1),
					paar.lvId,
					paar.semKurzbz,
				),
			savePruefungenBulk: (paar) =>
				notenApi.savePruefungenBulk(paar.lvId, paar.semKurzbz, [
					{
						uid: s0().uid,
						note: ctx.gradeNotes[0],
						punkte: null,
						datum: attemptDate(ctx, 1),
						lehreinheit_id: s0().lehreinheit_id,
					},
				]),
		};

		const meldetFrist = (response) =>
			((response.body && response.body.errors) || []).some((e) =>
				messageMatchesPhrase(e.message, "noteneintragungsfristVorbei"),
			);

		Object.entries(schreibpfade).forEach(([pfad, aufruf]) => {
			["SS", "WS"].forEach((typ) => {
				it(`${pfad} lehnt eine Eingabe im ${typ} nach der Frist ab`, function () {
					requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FRIST_EINGABE", true);
					requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT", false);

					const paar = paarFuer(typ);
					expect(fristHasPassed(paar.semKurzbz, fristSS(), fristWS()), `die Frist von ${paar.semKurzbz} ist vorbei`)
						.to.be.true;

					aufruf(paar).then((response) => {
						expectNotenError(response, "noteneintragungsfristVorbei");

						const meldung = response.body.errors.map((e) => e.message).join(" | ");
						expect(meldung, `die Frist, die der Server für ${paar.semKurzbz} ableitet`).to.include(
							expectedFristString(paar.semKurzbz, fristSS(), fristWS()),
						);
					});
				});
			});

			// Die Ausnahme deckt die EINGABE ab, nicht das Prüfungsdatum.
			it(`${pfad} lässt eine Ausnahmerolle nach der Frist eintragen`, function () {
				requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FRIST_EINGABE", true);
				requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT", true);

				aufruf(paarFuer("SS")).then((response) => {
					expect(meldetFrist(response), "die Eingabefrist greift nicht").to.be.false;
				});
			});

			it(`${pfad} nimmt ohne Eingabefrist eine späte Eingabe an`, function () {
				requireKonfiguration(this, ctx, "CIS_GESAMTNOTE_FRIST_EINGABE", false);

				aufruf(paarFuer("SS")).then((response) => {
					expect(meldetFrist(response), "die Eingabefrist greift nicht").to.be.false;
				});
			});
		});
	});
});
