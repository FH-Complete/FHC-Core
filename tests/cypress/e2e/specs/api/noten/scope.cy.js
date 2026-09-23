/**
 * The scope of the write endpoints: a Pruefung belongs to the student and the LV of the request.
 *
 * An edit changes only a Pruefung of this student in this LV (else pruefungNichtBearbeitbar). The server
 * picks the Lehreinheit, and a student outside the LV gets no Note (studentNichtInLv). Every test also
 * checks the database.
 *
 * Seeder group `benotungstool_fixture_erweitert` creates a second Lehreinheit with two Lektoren.
 */

import { apiPost, notenApi, loginAsLektor } from "../../../../support/api/notenApi";
import {
	expectBulkRowAccepted,
	expectBulkRowError,
	expectNotenError,
	expectNotenSuccess,
} from "../../../../support/helpers/notenErrors";
import { requireConfig, requireNotenMode, requireRepeat, skipIf } from "../../../../support/helpers/notenConfig";
import { assistenzAuth, requireAssistenz } from "../../../../support/helpers/notenAssistenz";
import {
	antrittDate,
	baselineDate,
	loadNotenContext,
	readAuthUid,
	readLvGesamtnoteViaDb,
	requireDbReset,
	resetNotenState,
	seedBaseline,
	seedPruefung,
	shiftDate,
} from "../../../../support/helpers/notenTestData";
import { pruefungenOf, editPruefung, readStateViaApi } from "../../../../support/helpers/notenScenario";

const dayOf = (value) => String(value).slice(0, 10);

describe("Noten API - Scope der Schreibpfade", () => {
	let ctx;
	let a;
	let b;

	// null: the instance cannot build the case, and the test skips
	let outsider = null;
	let foreign = null;
	let otherLe = null;
	let typeWithoutAntritt = null;

	const lvScope = () => ({ lvId: ctx.lvId, semKurzbz: ctx.semKurzbz });

	/** A student with exactly one Lehreinheit, and a second Lehreinheit of the same LV. */
	const findOtherLe = (lehreinheiten, i = 0) => {
		if (lehreinheiten.length < 2 || i >= ctx.students.length) return cy.wrap(null, { log: false });

		const student = ctx.students[i];
		return cy.task("noten:db:readLehreinheitenOfStudent", { ...lvScope(), studentUid: student.uid }).then((own) => {
			if (own.length !== 1) return findOtherLe(lehreinheiten, i + 1);
			const other = lehreinheiten.find((le) => String(le) !== String(own[0]));
			return { student, ownLe: own[0], otherLe: other };
		});
	};

	/** The first type from PRUEFUNG_TYPEN_OHNE_ANTRITT that tbl_pruefungstyp contains. */
	const findTypeWithoutAntritt = (types, i = 0) => {
		if (i >= types.length) return cy.wrap(null, { log: false });

		return cy
			.task("noten:db:hasPruefungstyp", { type: types[i] })
			.then((found) => (found ? types[i] : findTypeWithoutAntritt(types, i + 1)));
	};

	before(() => {
		requireDbReset();
		loadNotenContext().then((loaded) => {
			ctx = loaded;
			[a, b] = ctx.students;

			cy.task("noten:db:readNonParticipant", lvScope()).then((uid) => {
				outsider = uid;
			});
			cy.task("noten:db:readForeignLehreinheit", lvScope()).then((row) => {
				foreign = row;
			});
			findTypeWithoutAntritt(ctx.cisConfig.PRUEFUNG_TYPEN_OHNE_ANTRITT || []).then((type) => {
				typeWithoutAntritt = type;
			});
			cy.task("noten:db:readLehreinheitenOfLv", lvScope())
				.then((lehreinheiten) => findOtherLe(lehreinheiten))
				.then((found) => {
					otherLe = found;
				});
		});
	});

	beforeEach(() => loginAsLektor());

	describe("ein Edit trifft nur eine eigene Prüfung", () => {
		/** The Pruefung of B that the request for A tries to edit. */
		const seedForeignRow = () =>
			seedPruefung(ctx, b, { note: ctx.noten.negativ, datum: baselineDate(ctx), type: "Termin1" }).then(
				(seeded) => seeded.pruefungId,
			);

		const expectForeignRowUnchanged = (foreignId) =>
			readStateViaApi(ctx).then((data) => {
				const storedPruefung = pruefungenOf(data, b.uid).find(
					(p) => String(p.pruefung_id) === String(foreignId),
				);
				expect(storedPruefung, "die Prüfung von B existiert weiter").to.exist;
				expect(String(storedPruefung.note), "die Prüfung von B behält ihre Note").to.eq(
					String(ctx.noten.negativ),
				);
				expect(dayOf(storedPruefung.datum), "die Prüfung von B behält ihr Datum").to.eq(baselineDate(ctx));
			});

		it("lehnt die Prüfung eines anderen Studierenden ab", () => {
			const otherNote = ctx.notenScale.find((n) => String(n) !== String(ctx.noten.negativ));
			let foreignId;
			let lvNoteBefore;

			resetNotenState(ctx);
			seedBaseline(ctx, a);
			seedBaseline(ctx, b, { erstantritt: false });
			seedForeignRow().then((id) => {
				foreignId = id;
			});
			readLvGesamtnoteViaDb(ctx, a).then((row) => {
				lvNoteBefore = row;
			});

			cy.then(() =>
				editPruefung(ctx, a, {
					pruefungId: foreignId,
					note: otherNote,
					datum: shiftDate(baselineDate(ctx), 3),
				}),
			).then((response) => expectNotenError(response, "pruefungNichtBearbeitbar"));

			cy.then(() => expectForeignRowUnchanged(foreignId));
			readLvGesamtnoteViaDb(ctx, a).then((row) => {
				expect(String(row.note), "die LV-Note von A bleibt").to.eq(String(lvNoteBefore.note));
				expect(String(row.benotungsdatum), "das Benotungsdatum von A bleibt").to.eq(
					String(lvNoteBefore.benotungsdatum),
				);
			});
		});

		it("lehnt eine fremde Prüfung für einen Studierenden ohne Prüfung ab", () => {
			let foreignId;

			resetNotenState(ctx);
			seedForeignRow().then((id) => {
				foreignId = id;
			});

			cy.then(() =>
				editPruefung(ctx, a, {
					pruefungId: foreignId,
					note: ctx.notenScale[0],
					datum: shiftDate(baselineDate(ctx), 3),
				}),
			).then((response) => expectNotenError(response, "pruefungNichtBearbeitbar"));

			readLvGesamtnoteViaDb(ctx, a).then((row) => {
				expect(row, "A bekommt keine LV-Note").to.be.null;
			});
			cy.then(() => expectForeignRowUnchanged(foreignId));
		});

		// The server locks every Pruefungstyp from PRUEFUNG_TYPEN_OHNE_ANTRITT against edits.
		// Today: zusKommPruef, which the StV enters.
		it("lehnt eine Prüfung der Studierendenverwaltung ab", function () {
			skipIf(
				this,
				!typeWithoutAntritt,
				"Übersprungen: kein Typ aus PRUEFUNG_TYPEN_OHNE_ANTRITT in tbl_pruefungstyp.",
			);

			const datum = antrittDate(ctx, 1);
			let pruefungId;

			resetNotenState(ctx);
			seedBaseline(ctx, a);
			seedPruefung(ctx, a, { note: ctx.noten.negativ, datum, type: typeWithoutAntritt }).then((seeded) => {
				pruefungId = seeded.pruefungId;
			});

			cy.then(() =>
				editPruefung(ctx, a, { pruefungId, note: ctx.noten.negativ, datum: shiftDate(datum, 2) }),
			).then((response) => expectNotenError(response, "pruefungNichtBearbeitbar"));

			readStateViaApi(ctx).then((data) => {
				const storedPruefung = pruefungenOf(data, a.uid).find(
					(p) => String(p.pruefung_id) === String(pruefungId),
				);
				expect(storedPruefung, `die Prüfung vom Typ ${typeWithoutAntritt} existiert weiter`).to.exist;
				expect(dayOf(storedPruefung.datum), "sie behält ihr Datum").to.eq(datum);
			});
		});
	});

	describe("die Lehreinheit einer Prüfung", () => {
		const savePruefungFor = (uid, lehreinheitId) =>
			notenApi.savePruefung(ctx.lvId, ctx.semKurzbz, uid, {
				pruefung_id: null,
				lehreinheit_id: lehreinheitId,
				datum: antrittDate(ctx, 1),
				note: ctx.noten.negativ,
				punkte: null,
			});

		// the server replaces the Lehreinheit without an error message
		it("ersetzt eine andere Lehreinheit derselben LV", function () {
			skipIf(this, !otherLe, "Übersprungen: die LV hat keine zweite Lehreinheit.");
			requireRepeat(this, ctx);

			const { student, ownLe } = otherLe;

			resetNotenState(ctx);
			seedBaseline(ctx, student);

			savePruefungFor(student.uid, otherLe.otherLe).then((response) => {
				const { pruefung } = expectNotenSuccess(response, "Termin mit einer anderen Lehreinheit")[student.uid];
				expect(String(pruefung.lehreinheit_id), "die Lehreinheit des Studierenden").to.eq(String(ownLe));
			});
		});

		it("ersetzt die Lehreinheit einer fremden LV", function () {
			skipIf(this, !foreign, "Übersprungen: das Semester hat keine Lehreinheit einer anderen LV.");
			requireRepeat(this, ctx);

			resetNotenState(ctx);
			seedBaseline(ctx, a);

			savePruefungFor(a.uid, foreign.lehreinheit_id)
				.then((response) => {
					// resetNotenState does not reach a row in the foreign LV
					const saved = response.body?.data?.[a.uid]?.pruefung;
					if (saved && String(saved.lehreinheit_id) === String(foreign.lehreinheit_id)) {
						cy.task("noten:db:deletePruefung", { pruefungId: saved.pruefung_id, studentUids: [a.uid] });
					}
					return cy.wrap(response, { log: false });
				})
				.then((response) => {
					const saved = expectNotenSuccess(response, "Termin mit einer fremden Lehreinheit")[a.uid].pruefung;
					return cy
						.task("noten:db:readLehreinheitenOfStudent", { ...lvScope(), studentUid: a.uid })
						.then((own) => {
							expect(own.map(String), "eine Lehreinheit des Studierenden").to.include(
								String(saved.lehreinheit_id),
							);
						});
				});

			readStateViaApi(ctx).then((data) => {
				expect(pruefungenOf(data, a.uid), "Antritt 1 und der neue Termin").to.have.length(2);
			});
		});

		describe("Nicht-Teilnehmer", () => {
			beforeEach(function () {
				skipIf(this, !outsider, "Übersprungen: kein aktiver Studierender ausserhalb der LV.");
				resetNotenState(ctx, [outsider]);
			});

			// the reset is the check: it deletes what the request wrote and counts it
			const expectNothingWritten = () =>
				resetNotenState(ctx, [outsider]).then((deleted) => {
					expect(deleted.deletedLvGesamtnoten, `LV-Noten von ${outsider}`).to.eq(0);
					expect(deleted.deletedPruefungen, `Prüfungen von ${outsider}`).to.eq(0);
				});

			it("savePruefung legt nichts an", () => {
				savePruefungFor(outsider, a.lehreinheit_id).then((response) =>
					expectNotenError(response, "studentNichtInLv"),
				);
				expectNothingWritten();
			});

			it("createPruefungen lehnt nur die Zeile des Nicht-Teilnehmers ab", () => {
				resetNotenState(ctx);

				notenApi
					.createPruefungen(
						ctx.lvId,
						ctx.semKurzbz,
						[
							{ uid: a.uid, lehreinheit_id: a.lehreinheit_id },
							{ uid: outsider, lehreinheit_id: a.lehreinheit_id },
						],
						{ datum: antrittDate(ctx, 1) },
					)
					.then((response) => {
						const data = expectNotenSuccess(response, "createPruefungen");
						expectBulkRowError(data, outsider, "studentNichtInLv");
						expect(data[a.uid].pruefung, `Prüfung für ${a.uid}`).to.exist;
					});
				expectNothingWritten();
			});

			it("importPruefungen lehnt nur die Zeile des Nicht-Teilnehmers ab", function () {
				requireNotenMode(this, ctx);
				requireConfig(this, ctx, "CIS_GESAMTNOTE_PRUEFUNGSIMPORT", true);

				const bulkRow = (uid) => ({
					uid,
					note: ctx.noten.negativ,
					punkte: null,
					datum: antrittDate(ctx, 1),
					lehreinheit_id: a.lehreinheit_id,
				});

				resetNotenState(ctx);

				notenApi
					.importPruefungen(ctx.lvId, ctx.semKurzbz, [bulkRow(a.uid), bulkRow(outsider)])
					.then((response) => {
						const data = expectNotenSuccess(response, "importPruefungen");
						expectBulkRowError(data, outsider, "studentNichtInLv");
						expectBulkRowAccepted(data, a.uid);
					});
				expectNothingWritten();
			});

			it("saveLvNote schreibt keine LV-Note", () => {
				notenApi
					.saveLvNote(ctx.lvId, ctx.semKurzbz, outsider, ctx.noten.negativ)
					.then((response) => expectNotenError(response, "studentNichtInLv"));
				expectNothingWritten();
			});

			it("importLvNoten lehnt nur die Zeile des Nicht-Teilnehmers ab", function () {
				requireNotenMode(this, ctx);
				requireConfig(this, ctx, "CIS_GESAMTNOTE_NOTENIMPORT", true);

				resetNotenState(ctx);

				notenApi
					.importLvNoten(ctx.lvId, ctx.semKurzbz, [
						{ uid: a.uid, note: ctx.notenScale[0], punkte: null },
						{ uid: outsider, note: ctx.notenScale[0], punkte: null },
					])
					.then((response) => {
						const data = expectNotenSuccess(response, "importLvNoten");
						expectBulkRowError(data, outsider, "studentNichtInLv");
						expectBulkRowAccepted(data, a.uid);
					});
				expectNothingWritten();
			});
		});

		describe("getLektorenForLehreinheit", () => {
			it("nennt die Lehrenden einer Lehreinheit der LV", () => {
				notenApi.getLektorenForLehreinheit(ctx.lvId, ctx.semKurzbz, a.lehreinheit_id).then((response) => {
					expect(expectNotenSuccess(response, "eigene Lehreinheit"), "Lehrende").to.be.an("array");
				});
			});

			it("lehnt die Lehreinheit einer fremden LV ab", function () {
				skipIf(this, !foreign, "Übersprungen: das Semester hat keine Lehreinheit einer anderen LV.");

				notenApi
					.getLektorenForLehreinheit(ctx.lvId, ctx.semKurzbz, foreign.lehreinheit_id)
					.then((response) => expectNotenError(response, "wrongParameters"));
			});

			it("lehnt eine nicht numerische Lehreinheit ab", () => {
				notenApi
					.getLektorenForLehreinheit(ctx.lvId, ctx.semKurzbz, "abc")
					.then((response) => expectNotenError(response, "missingParameters"));
			});
		});
	});

	// The Lektor in mitarbeiter_uid of the Pruefung and the LV-Note: a valid selection; else the caller,
	// if the caller teaches the Lehreinheit; else the first Lektor of the Lehreinheit by uid.
	describe("die benotende Person", () => {
		// a student whose Lehreinheit has several Lektoren; NOTEN_LEKTOR_USER is not the first of them
		let target = null;
		let lektorUid = null;

		before(() => {
			const search = (i) =>
				i >= ctx.students.length
					? cy.wrap(null, { log: false })
					: cy
							.task("noten:db:readLektorenOfLehreinheit", {
								lehreinheitId: ctx.students[i].lehreinheit_id,
							})
							.then((lehrende) =>
								lehrende.length > 1 && lehrende.includes(lektorUid) && lehrende[0] !== lektorUid
									? { student: ctx.students[i], lehrende }
									: search(i + 1),
							);

			readAuthUid()
				.then((uid) => {
					lektorUid = uid;
					return search(0);
				})
				.then((found) => {
					target = found;
				});
		});

		beforeEach(() => {
			expect(
				target,
				"eine Lehreinheit mit mehreren Lehrenden, darunter der Suite-Benutzer (Seeder-Gruppe benotungstool_fixture_erweitert)",
			).to.not.be.null;
			requireDbReset();
			resetNotenState(ctx);
		});

		const pruefung = (mitarbeiter_uid) => ({
			pruefung_id: null,
			lehreinheit_id: target.student.lehreinheit_id,
			datum: antrittDate(ctx, 1),
			note: ctx.noten.negativ,
			punkte: null,
			mitarbeiter_uid,
		});

		const savePruefungWithLektor = (mitarbeiter_uid) =>
			notenApi.savePruefung(ctx.lvId, ctx.semKurzbz, target.student.uid, pruefung(mitarbeiter_uid));

		const grader = (response) => {
			const saved = expectNotenSuccess(response, "Termin")[target.student.uid].pruefung;
			return cy.task("noten:db:readPruefungMitarbeiter", {
				pruefungId: saved.pruefung_id,
				studentUid: target.student.uid,
			});
		};

		it("trägt den Aufrufer ein, wenn er die Lehreinheit unterrichtet", () => {
			savePruefungWithLektor()
				.then(grader)
				.then((uid) => expect(uid, "benotende Person der Prüfung").to.eq(lektorUid));

			readLvGesamtnoteViaDb(ctx, target.student).then((row) => {
				expect(row.mitarbeiter_uid, "benotende Person der LV-Note").to.eq(lektorUid);
			});
		});

		it("trägt die gewählte Lehrperson ein", () => {
			const chosen = target.lehrende.find((uid) => uid !== lektorUid);

			savePruefungWithLektor(chosen)
				.then(grader)
				.then((uid) => expect(uid, "benotende Person der Prüfung").to.eq(chosen));
		});

		it("übergeht eine Auswahl ausserhalb der Lehreinheit", () => {
			savePruefungWithLektor("keine-lehrperson")
				.then(grader)
				.then((uid) => expect(uid, "benotende Person der Prüfung").to.eq(lektorUid));
		});

		it("trägt ohne eigene Lehre die erste Lehrperson der Lehreinheit ein", function () {
			requireAssistenz(this);

			// otherwise the request runs with the session cookie of loginAsLektor
			cy.clearAllCookies();

			const body = { lv_id: ctx.lvId, sem_kurzbz: ctx.semKurzbz, student_uid: target.student.uid, ...pruefung() };
			apiPost("savePruefung", body, assistenzAuth())
				.then(grader)
				.then((uid) => expect(uid, "benotende Person der Prüfung").to.eq(target.lehrende[0]));
		});
	});
});
