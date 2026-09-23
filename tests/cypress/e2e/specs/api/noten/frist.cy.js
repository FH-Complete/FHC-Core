/**
 * Pruefungsordnung §1: the Noteneintragungsfrist.
 *
 * Two Fristen answer two questions: CIS_GESAMTNOTE_FRIST_EINGABE limits the day of the entry,
 * CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM limits the date of the Pruefung. Every write endpoint checks the
 * entry Frist right after the access check, so a rejected request writes nothing. The message names
 * the Frist, so the test also checks how the server derives it from NOTENEINTRAGUNGSFRIST_SS/WS.
 *
 * Seeder group benotungstool_fixture_erweitert creates a Sommersemester after its Frist in which the Lektor teaches.
 */

import { freigabePassword, notenApi, loginAsLektor } from "../../../../support/api/notenApi";
import { expectNotenError, expectNotenSuccess } from "../../../../support/helpers/notenErrors";
import {
	antrittDate,
	expectedFristString,
	fristHasPassed,
	loadNotenContext,
	requireDbReset,
	teachingSemesterWithExpiredFrist,
} from "../../../../support/helpers/notenTestData";
import { addPruefung, givenBaseline } from "../../../../support/helpers/notenScenario";
import { requireConfig, requireRepeat } from "../../../../support/helpers/notenConfig";

describe("Noten API - Noteneintragungsfrist (Prüfungsordnung §1)", () => {
	let ctx;
	// a semester and LV in which the Lektor teaches and the Frist has passed
	const expired = { SS: null, WS: null };

	const fristSS = () => ctx.cisConfig.NOTENEINTRAGUNGSFRIST_SS;
	const fristWS = () => ctx.cisConfig.NOTENEINTRAGUNGSFRIST_WS;

	before(() => {
		loadNotenContext().then((loaded) => {
			ctx = loaded;
			cy.log(
				`FRIST_EINGABE=${ctx.cisConfig.CIS_GESAMTNOTE_FRIST_EINGABE} ` +
					`FRIST_PRUEFUNGSDATUM=${ctx.cisConfig.CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM} ` +
					`AUSNAHME_GILT=${ctx.cisConfig.CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT}`,
			);

			["SS", "WS"].forEach((type) =>
				teachingSemesterWithExpiredFrist(type, fristSS(), fristWS()).then((pair) => {
					expired[type] = pair;
					cy.log(`${type} mit abgelaufener Frist: ${JSON.stringify(pair)}`);
				}),
			);
		});
	});

	beforeEach(() => loginAsLektor());

	// A missing semester is a fixture error, not a skipped test
	const pairFor = (type) => {
		expect(expired[type], `ein ${type} mit abgelaufener Frist, in dem der Testbenutzer unterrichtet`).to.not.be
			.null;
		return expired[type];
	};

	// §7 and §11: the Pruefung must TAKE PLACE before the Frist. That is a different question from
	// "may the Lektor still enter it now?"
	describe("Prüfungsdatum nach der Frist", () => {
		beforeEach(() => requireDbReset());

		// the Frist of the current semester is before this date
		const afterFrist = () => `${Number(ctx.semKurzbz.slice(2, 6)) + 1}-12-31`;

		it("lehnt einen Termin ab, der nach der Frist liegt", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM", true);

			const student = ctx.students[0];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notenScale[0], datum: afterFrist() }).then((response) =>
				expectNotenError(response, "pruefungsdatumNachFrist"),
			);
		});

		it("nimmt einen Termin nach der Frist an, wenn das Prüfungsdatum an keine Frist gebunden ist", function () {
			requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_PRUEFUNGSDATUM", false);
			requireRepeat(this, ctx);

			const student = ctx.students[0];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.noten.negativ, datum: afterFrist() }).then((response) =>
				expectNotenSuccess(response, "Termin nach der Frist"),
			);
		});

		it("nimmt einen Termin vor der Frist an", function () {
			requireRepeat(this, ctx);

			const student = ctx.students[1];

			givenBaseline(ctx, student);
			addPruefung(ctx, student, { note: ctx.notenScale[0], datum: antrittDate(ctx, 1) }).then((response) =>
				expectNotenSuccess(response, "Termin innerhalb der Frist"),
			);
		});
	});

	describe("Eingabe nach der Frist", () => {
		const s0 = () => ctx.students[0];

		// The student is not in the LV of that earlier semester. Without the Frist check the server would
		// reject the student instead. Either way the request writes nothing.
		const writePaths = {
			savePruefung: (pair) =>
				notenApi.savePruefung(pair.lvId, pair.semKurzbz, s0().uid, {
					pruefung_id: null,
					lehreinheit_id: s0().lehreinheit_id,
					datum: antrittDate(ctx, 1),
					note: ctx.notenScale[0],
					punkte: null,
				}),
			saveLvNote: (pair) => notenApi.saveLvNote(pair.lvId, pair.semKurzbz, s0().uid, ctx.notenScale[0]),
			importLvNoten: (pair) =>
				notenApi.importLvNoten(pair.lvId, pair.semKurzbz, [
					{ uid: s0().uid, note: ctx.notenScale[0], punkte: null },
				]),
			createPruefungen: (pair) =>
				notenApi.createPruefungen(
					pair.lvId,
					pair.semKurzbz,
					[{ uid: s0().uid, lehreinheit_id: s0().lehreinheit_id }],
					{
						datum: antrittDate(ctx, 1),
					},
				),
			importPruefungen: (pair) =>
				notenApi.importPruefungen(pair.lvId, pair.semKurzbz, [
					{
						uid: s0().uid,
						note: ctx.notenScale[0],
						punkte: null,
						datum: antrittDate(ctx, 1),
						lehreinheit_id: s0().lehreinheit_id,
					},
				]),
			saveFreigabe: (pair) => notenApi.saveFreigabe(pair.lvId, pair.semKurzbz, freigabePassword(), [s0().uid]),
		};

		const reportsFrist = (response) =>
			(response.body?.errors ?? []).some((e) => e.code === "noteneintragungsfristVorbei");

		Object.entries(writePaths).forEach(([writePath, call]) => {
			["SS", "WS"].forEach((type) => {
				it(`${writePath} lehnt eine Eingabe im ${type} nach der Frist ab`, function () {
					requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_EINGABE", true);
					requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT", false);

					const pair = pairFor(type);
					expect(
						fristHasPassed(pair.semKurzbz, fristSS(), fristWS()),
						`die Frist von ${pair.semKurzbz} ist vorbei`,
					).to.be.true;

					call(pair).then((response) => {
						expectNotenError(response, "noteneintragungsfristVorbei");

						const errorMessage = response.body.errors.map((e) => e.message).join(" | ");
						expect(errorMessage, `die Frist, die der Server für ${pair.semKurzbz} ableitet`).to.include(
							expectedFristString(pair.semKurzbz, fristSS(), fristWS()),
						);
					});
				});
			});

			// the exception covers the day of the ENTRY, not the date of the Pruefung
			it(`${writePath} lässt eine Ausnahmerolle nach der Frist eintragen`, function () {
				requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_EINGABE", true);
				requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_AUSNAHME_GILT", true);

				call(pairFor("SS")).then((response) => {
					expect(reportsFrist(response), "die Eingabefrist greift nicht").to.be.false;
				});
			});

			it(`${writePath} nimmt ohne Eingabefrist eine späte Eingabe an`, function () {
				requireConfig(this, ctx, "CIS_GESAMTNOTE_FRIST_EINGABE", false);

				call(pairFor("SS")).then((response) => {
					expect(reportsFrist(response), "die Eingabefrist greift nicht").to.be.false;
				});
			});
		});
	});
});
