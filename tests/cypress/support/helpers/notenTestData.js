/**
 * Runtime discovery + fixture helpers.
 *
 * No fixed IDs: maxAntritte and the special grade PKs are stored in the server configuration, which is not in the repo
 * and are therefore read at runtime via the API. Override: NOTEN_SEM / NOTEN_LV_ID.
 */

import { notenApi, notenAuth } from "../api/notenApi";
import { expectNotenSuccess } from "./notenErrors";
// the client rule; it now only reads back what the server derived, so a mismatch is a config bug
import { maxAntrittCount as computeMaxAntritte } from "../../../../public/js/components/Cis/Benotungstool/notenRules.js";
import {
	describeFailure,
	performRead,
	performReset,
	performSeed,
	performSeedPruefung,
	performSeedZeugnisnote,
	resolveResetStrategy,
} from "./notenReset";
import { assertPunkteMode } from "./notenConfig";

const BEZ_ENTSCHULDIGT = "entschuldigt";
const BEZ_NOCH_NICHT = "Noch nicht eingetragen";

let cachedContext = null;

// --- dates ---

export const pad2 = (n) => String(n).padStart(2, "0");

export const toDateString = (date) => `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;

export const shiftDate = (dateString, days) => {
	const [y, m, d] = dateString.split("-").map(Number);
	const date = new Date(Date.UTC(y, m - 1, d));
	date.setUTCDate(date.getUTCDate() + days);
	return `${date.getUTCFullYear()}-${pad2(date.getUTCMonth() + 1)}-${pad2(date.getUTCDate())}`;
};

/** Deadline the server derives (computeNoteneintragungsfrist): SS -> 15.11.yyyy, WS -> 15.05.yyyy+1. */
export const expectedFristString = (semKurzbz, ssConfig = { month: 11, day: 15 }, wsConfig = { month: 5, day: 15 }) => {
	const type = semKurzbz.slice(0, 2).toUpperCase();
	const year = Number(semKurzbz.slice(2, 6));
	const cfg = type === "SS" ? ssConfig : wsConfig;
	return `${pad2(cfg.day)}.${pad2(cfg.month)}.${type === "SS" ? year : year + 1}`;
};

/** ssConfig/wsConfig: NOTENEINTRAGUNGSFRIST_SS/WS from getCisConfig. */
export const fristHasPassed = (semKurzbz, ssConfig = { month: 11, day: 15 }, wsConfig = { month: 5, day: 15 }) => {
	const type = semKurzbz.slice(0, 2).toUpperCase();
	const year = Number(semKurzbz.slice(2, 6));
	if (!["SS", "WS"].includes(type) || !year) return false;
	const cfg = type === "SS" ? ssConfig : wsConfig;
	const deadline = new Date(type === "SS" ? year : year + 1, cfg.month - 1, cfg.day, 23, 59, 59);
	return new Date() > deadline;
};

/**
 * A semester + course where the logged-in user actually teaches AND the grade deadline has passed.
 *
 * Both are needed: assertLvAccess runs BEFORE the deadline check, so a semester the user does not
 * teach in fails there and never reaches the deadline. Seeder group benotungstool_fixture_erweitert provides the Sommersemester.
 *
 * @param {string} type "SS" or "WS"
 * @returns {Cypress.Chainable<{semKurzbz: string, lvId: number}|null>}
 */
export const teachingSemesterWithExpiredFrist = (type = "SS", ssConfig, wsConfig) => {
	const year = new Date().getFullYear();
	const candidates = [];
	for (let y = year; y >= year - 6; y -= 1) {
		const sem = `${type}${y}`;
		if (fristHasPassed(sem, ssConfig, wsConfig)) candidates.push(sem);
	}

	const trySemester = (i) => {
		if (i >= candidates.length) return cy.wrap(null, { log: false });

		return notenApi.getBenotungstoolContext(candidates[i]).then((response) => {
			const lvs = (response.body && response.body.data && response.body.data.lehrveranstaltungen) || [];
			if (lvs.length > 0) {
				return { semKurzbz: candidates[i], lvId: lvs[0].lehrveranstaltung_id };
			}
			return trySemester(i + 1);
		});
	};

	return trySemester(0);
};

// --- discovery ---

const resolveSemester = () => {
	const configured = Cypress.env("NOTEN_SEM");
	if (configured) return cy.wrap(configured, { log: false });

	return cy
		.request({
			method: "GET",
			url: "/index.ci.php/api/frontend/v1/organisation/Studiensemester/getAll",
			qs: { order: "DESC" },
			auth: notenAuth(),
			failOnStatusCode: false,
		})
		.then((response) => {
			const semesters = expectNotenSuccess(response, "Studiensemester/getAll");
			const today = toDateString(new Date());
			const active = semesters
				.filter((s) => (s.start || "").slice(0, 10) && (s.start || "").slice(0, 10) <= today)
				.sort((a, b) => (b.start || "").localeCompare(a.start || ""))[0];

			expect(active, `an active studiensemester starting on or before ${today}`).to.exist;
			return active.studiensemester_kurzbz;
		});
};

const resolveLehrveranstaltung = (semKurzbz) => {
	const configured = Cypress.env("NOTEN_LV_ID");
	if (configured) return cy.wrap(Number(configured), { log: false });

	return notenApi.getBenotungstoolContext(semKurzbz).then((response) => {
		const lvs = expectNotenSuccess(response, "getBenotungstoolContext").lehrveranstaltungen || [];
		expect(
			lvs.length,
			`No Lehrveranstaltungen for ${semKurzbz}: the logged-in user teaches nothing here ` +
				"(admins usually don't). Set NOTEN_LV_ID.",
		).to.be.greaterThan(0);
		return lvs[0].lehrveranstaltung_id;
	});
};

/** -> { semKurzbz, lvId, cisConfig, maxAntritte, notes, gradeNotes, students, studentUids } */
export const loadNotenContext = () => {
	if (cachedContext) return cy.wrap(cachedContext, { log: false });

	const context = {};

	return resolveSemester()
		.then((sem) => {
			context.semKurzbz = sem;
			return resolveLehrveranstaltung(sem);
		})
		.then((lvId) => {
			context.lvId = lvId;
			return notenApi.getCisConfig();
		})
		.then((response) => {
			context.cisConfig = expectNotenSuccess(response, "getCisConfig");
			context.maxAntritte = computeMaxAntritte(context.cisConfig);
			// ein Lauf, der den falschen Konfigurationsmodus erwartet, soll hier scheitern und nicht
			// alles stillschweigend überspringen
			assertPunkteMode(context);
			return notenApi.getNoten();
		})
		.then((response) => {
			const noten = expectNotenSuccess(response, "getNoten");
			const byBezeichnung = (bez) => noten.find((n) => n.bezeichnung === bez);

			const entschuldigt = byBezeichnung(BEZ_ENTSCHULDIGT);
			const nochNicht = byBezeichnung(BEZ_NOCH_NICHT);

			expect(entschuldigt, `tbl_note needs bezeichnung "${BEZ_ENTSCHULDIGT}"`).to.exist;
			expect(nochNicht, `tbl_note needs bezeichnung "${BEZ_NOCH_NICHT}"`).to.exist;

			// Restricted to the 1..5 scale: the wider lehre-Noten set contains note 0 ("Teilnote"),
			// and saveStudentPruefung's `if($note=='')` guard rewrites a 0 to "Noch nicht eingetragen".
			const specialPks = [entschuldigt.note, nochNicht.note];
			const usable = noten.filter((n) => n.lehre && !specialPks.includes(n.note));
			const ordinary = usable
				.map((n) => n.note)
				.filter((note) => Number(note) >= 1 && Number(note) <= 5)
				.sort((a, b) => Number(a) - Number(b));

			context.gradeNotes =
				ordinary.length > 1
					? ordinary
					: usable
							.map((n) => n.note)
							.filter((n) => Number(n) !== 0)
							.sort((a, b) => Number(a) - Number(b));

			expect(context.gradeNotes.length, "need two ordinary grades to vary one on edit").to.be.greaterThan(1);

			// administrative note the editor list excludes ("intern angerechnet" / "nicht zugelassen")
			const notLehre = noten.find((n) => n.lehre === false);

			// as a ZEUGNISnote this one locks the LV note, in the client and in the server
			const notUeberschreibbar = noten.find((n) => n.lkt_ueberschreibbar === false);

			// Anrechnungen block every Prüfung for the LV - keyed on the ZEUGNISnote
			const angerechnet = byBezeichnung("angerechnet");
			const internAngerechnet = byBezeichnung("intern angerechnet");

			// An attempt chain needs grades by meaning, not by index: a positive grade closes the
			// chain, so a repeat after "Sehr Gut" is not a test case but an impossible flow.
			const abschliessend = (context.cisConfig.NOTEN_ABSCHLIESSEND || []).map(String);
			const inScale = (n) => Number(n.note) >= 1 && Number(n.note) <= 5;
			const negativNoten = usable.filter((n) => !n.positiv && inScale(n)).map((n) => n.note);
			const positivNoten = usable.filter((n) => n.positiv && inScale(n)).map((n) => n.note);
			const verbesserbar = positivNoten.filter((n) => !abschliessend.includes(String(n)));

			expect(negativNoten.length, "need a negative grade to build an attempt chain").to.be.greaterThan(0);

			context.notes = {
				// the only grade a repeat may follow
				negativ: negativNoten[0],
				// positive but not final: allows a repeat when configured
				positiv: verbesserbar.length ? verbesserbar[0] : null,
				// always closes the chain (NOTEN_ABSCHLIESSEND)
				bestnote: positivNoten.find((n) => abschliessend.includes(String(n))) || null,
				entschuldigt: entschuldigt.note,
				nochNichtEingetragen: nochNicht.note,
				notLehre: notLehre ? notLehre.note : null,
				notUeberschreibbar: notUeberschreibbar ? notUeberschreibbar.note : null,
				angerechnet: angerechnet ? angerechnet.note : null,
				internAngerechnet: internAngerechnet ? internAngerechnet.note : null,
			};

			// die GUI-Specs wählen und lesen über die Bezeichnung, nicht über die PK
			context.notenOptions = noten;

			return notenApi.getStudentenNoten(context.lvId, context.semKurzbz);
		})
		.then((response) => {
			const data = expectNotenSuccess(response, `getStudentenNoten(${context.lvId})`);
			const students = data[0] || [];

			expect(students.length, `LV ${context.lvId} needs >=3 enrolled students`).to.be.greaterThan(2);
			students.forEach((s) => expect(s.lehreinheit_id, `lehreinheit_id of ${s.uid}`).to.exist);

			context.students = students;
			context.studentUids = students.map((s) => s.uid);

			cachedContext = context;
			return context;
		});
};

// --- fixture state ---

export const resetNotenState = (context, studentUids) => performReset(context, studentUids || context.studentUids);

/**
 * Baseline for Antritt 1: approved course grade PLUS the corresponding exam row—that is what the
 * approval generates. Without that row, the course grade only counts as long as no date exists, and
 * the first attempt added would replace Attempt 1 instead of becoming Attempt 2.
 *
 * `first_attempt: false` (or `approved: false`) seeds the legacy data form without this line.
 */
export const seedBaseline = (context, student, options = {}) => {
	expect(student, "seedBaseline braucht das Studierenden-Objekt aus context.students, nicht die uid").to.be.an(
		"object",
	);

	// Defaults to a NEGATIVE grade: only after one may another attempt follow. Pass
	// context.notes.bestnote explicitly to close the chain.
	const note = options.note !== undefined ? options.note : context.notes.negativ;
	// With CIS_GESAMTNOTE_FREIGABE_FINAL, a released grade is final. Without an explicit
	// release, the baseline sets the grade to “open” so that a test can create additional dates.
	const freigegeben =
		options.freigegeben !== undefined
			? options.freigegeben
			: context.cisConfig.CIS_GESAMTNOTE_FREIGABE_FINAL !== true;
	// Start 1 is missing only if the initial start is explicitly set to false or enabled: false
	const erstantritt = options.erstantritt !== undefined ? options.erstantritt : options.freigegeben !== false;

	return performSeed(context, student.uid, {
		note,
		punkte: options.punkte !== undefined ? options.punkte : null,
		benotungsdatum: options.benotungsdatum || baselineBenotungsdatum(context),
		freigegeben,
		freigabedatum: options.freigabedatum || null,
	}).then((seeded) => {
		if (!erstantritt) return cy.wrap(seeded, { log: false });

		return performSeedPruefung(context, student.uid, {
			lehreinheitId: student.lehreinheit_id,
			note,
			datum: baselineDate(context),
			type: "Termin1", // legacy projection of Antritt 1; the rules never read it back
		}).then(() => seeded);
	});
};

/** -> { pruefungId, note, datum, typ }. For attempt states the API cannot build. */
export const seedPruefung = (context, student, { note, datum, type }) =>
	performSeedPruefung(context, student.uid, {
		lehreinheitId: student.lehreinheit_id,
		note,
		datum,
		type,
	});

/** Set the grade on the transcript. Only the student administration system (stv) enters it,
 *  this tool does not handle that. */
export const seedZeugnisnote = (context, studentUid, note) => performSeedZeugnisnote(context, studentUid, { note });

/**
 * Reads the course grade directly from the DB: what is actually stored, without the
 * “release date” filter from getLvGesamtNoten. The counterpart is readStateViaApi.
 */
export const readLvGesamtnoteViaDb = (context, studentUid) => performRead(context, studentUid);

/** Anchor date; attempt dates derive from it so ordering is known. */
export const baselineBenotungsdatum = (context) => `${Number(context.semKurzbz.slice(2, 6))}-01-10 08:00:00`;

export const baselineDate = (context) => baselineBenotungsdatum(context).slice(0, 10);

/** attemptDate(ctx,1) < attemptDate(ctx,2) < ... , all after the baseline. */
export const attemptDate = (context, index) => shiftDate(baselineDate(context), 30 * index);

export const requireDbReset = () =>
	resolveResetStrategy().then((state) => {
		expect(
			state.strategy,
			`Fixture reset unavailable.\n\n${describeFailure(state)}\n\n      See tests/cypress/.env.example.\n`,
		).to.not.be.null;
	});
