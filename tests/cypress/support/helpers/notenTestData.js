/**
 * Runtime discovery + fixture helpers.
 *
 * No hardcoded fixture ids: the rule parameters (maxAntritte, the special note PKs) come from
 * server-side config that is not in the repo, and Noten.php resolves the special notes by
 * Bezeichnung. Everything is therefore read back from the API at runtime.
 * Override via NOTEN_SEM / NOTEN_LV_ID.
 */

import { notenApi } from "../api/notenApi";
import { expectNotenSuccess } from "./notenErrors";
// the client rule; it now only reads back what the server derived, so a mismatch is a config bug
import { maxAntrittCount as computeMaxAntritte } from "../../../../public/js/components/Cis/Benotungstool/notenRules.js";
import {
	describeFailure, performRead, performReset, performSeed, performSeedPruefung, resolveResetStrategy,
} from "./notenReset";

const BEZ_ENTSCHULDIGT = "entschuldigt";
const BEZ_NOCH_NICHT = "Noch nicht eingetragen";

let cachedContext = null;

// --- dates ---

export const pad2 = (n) => String(n).padStart(2, "0");

export const toDateString = (date) =>
	`${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;

export const shiftDate = (dateString, days) => {
	const [y, m, d] = dateString.split("-").map(Number);
	const date = new Date(Date.UTC(y, m - 1, d));
	date.setUTCDate(date.getUTCDate() + days);
	return `${date.getUTCFullYear()}-${pad2(date.getUTCMonth() + 1)}-${pad2(date.getUTCDate())}`;
};

/** Deadline the server derives (computeNoteneintragungsfrist): SS -> 15.11.yyyy, WS -> 15.05.yyyy+1. */
export const expectedFristString = (
	semKurzbz,
	ssConfig = { month: 11, day: 15 },
	wsConfig = { month: 5, day: 15 },
) => {
	const type = semKurzbz.slice(0, 2).toUpperCase();
	const year = Number(semKurzbz.slice(2, 6));
	const cfg = type === "SS" ? ssConfig : wsConfig;
	return `${pad2(cfg.day)}.${pad2(cfg.month)}.${type === "SS" ? year : year + 1}`;
};

export const fristHasPassed = (semKurzbz) => {
	const type = semKurzbz.slice(0, 2).toUpperCase();
	const year = Number(semKurzbz.slice(2, 6));
	if (!["SS", "WS"].includes(type) || !year) return false;
	const deadline =
		type === "SS" ? new Date(year, 10, 15, 23, 59, 59) : new Date(year + 1, 4, 15, 23, 59, 59);
	return new Date() > deadline;
};

export const semesterWithPastFrist = (type = "SS") => {
	const year = new Date().getFullYear();
	for (let y = year; y >= year - 6; y -= 1) {
		if (fristHasPassed(`${type}${y}`)) return `${type}${y}`;
	}
	return `${type}${year - 6}`;
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
			auth: { username: Cypress.env("adminusername"), password: Cypress.env("adminpassword") },
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
					: usable.map((n) => n.note).filter((n) => Number(n) !== 0).sort((a, b) => Number(a) - Number(b));

			expect(context.gradeNotes.length, "need two ordinary grades to vary one on edit").to.be.greaterThan(1);

			// administrative note the editor list excludes ("intern angerechnet" / "nicht zugelassen")
			const nichtLehre = noten.find((n) => n.lehre === false);

			// Anrechnungen block every Prüfung for the LV - keyed on the ZEUGNISnote
			const angerechnet = byBezeichnung("angerechnet");
			const internAngerechnet = byBezeichnung("intern angerechnet");

			context.notes = {
				entschuldigt: entschuldigt.note,
				nochNichtEingetragen: nochNicht.note,
				nichtLehre: nichtLehre ? nichtLehre.note : null,
				angerechnet: angerechnet ? angerechnet.note : null,
				internAngerechnet: internAngerechnet ? internAngerechnet.note : null,
			};

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

export const resetNotenState = (context, studentUids) =>
	performReset(context, studentUids || context.studentUids);

/**
 * Seeds the Antritt-1 baseline: a released LV note PLUS the Prüfung row that represents it.
 *
 * The row is what the production flow produces - since the refactor the first Antritt is created
 * by the password-gated Freigabe (Noten::upsertErstantritt), not as a side effect of adding a
 * retake. Without it the LV note only counts while no Prüfung exists at all, so the first added
 * attempt would silently replace Antritt 1 instead of being Antritt 2.
 *
 * `erstantritt: false` seeds the legacy shape (LV note only, no row) for tests that need it.
 * `freigegeben: false` implies it too: no Freigabe, no first Antritt.
 */
export const seedBaseline = (context, studentUid, options = {}) => {
	const note = options.note !== undefined ? options.note : context.gradeNotes[0];
	const freigegeben = options.freigegeben !== undefined ? options.freigegeben : true;

	return performSeed(context, studentUid, {
		note,
		punkte: options.punkte !== undefined ? options.punkte : null,
		benotungsdatum: options.benotungsdatum || baselineBenotungsdatum(context),
		freigegeben,
		freigabedatum: options.freigabedatum || null,
	}).then((seeded) => {
		if (options.erstantritt === false || !freigegeben) return cy.wrap(seeded, { log: false });

		const student = context.students.find((s) => s.uid === studentUid);
		expect(student, `student ${studentUid} in the loaded LV`).to.exist;

		return performSeedPruefung(context, studentUid, {
			lehreinheitId: student.lehreinheit_id,
			note,
			datum: baselineDate(context),
			typ: "Termin1", // legacy projection of Antritt 1; the rules never read it back
		}).then(() => seeded);
	});
};

/** -> { pruefungId, note, datum, typ }. For attempt states the API cannot build. */
export const seedPruefung = (context, student, { note, datum, typ }) =>
	performSeedPruefung(context, student.uid, {
		lehreinheitId: student.lehreinheit_id,
		note,
		datum,
		typ,
	});

/** Raw row, bypassing the freigabedatum filter of getLvGesamtNoten. */
export const readLvGesamtnote = (context, studentUid) => performRead(context, studentUid);

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
