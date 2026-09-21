/** Building attempt histories and reading the resulting state. */

import { notenApi, attemptsOf, countingAttemptsOf, gradesOf, verlaufOf } from "../api/notenApi";
import { expectNotenSuccess } from "./notenErrors";
import { resetNotenState, seedBaseline } from "./notenTestData";

/**
 * New entry (without prüfung_id -> validatePruefungAdd). No type on the line.
 *
 * Points remain zero: with CIS_GESAMTNOTE_PUNKTE, a points value >= 0 would derive the grade anew from the
 * grading scale and overwrite the grade being tested.
 */
export const addPruefung = (context, student, { note, datum }) =>
	notenApi.saveStudentPruefung({
		student_uid: student.uid,
		note,
		punkte: null,
		datum,
		lva_id: context.lvId,
		lehreinheit_id: student.lehreinheit_id,
		sem_kurzbz: context.semKurzbz,
		pruefung_id: null,
	});

/** Edits an attempt (pruefung_id set -> server runs validatePruefungEdit). */
export const editPruefung = (context, student, { pruefungId, note, datum }) =>
	notenApi.saveStudentPruefung({
		student_uid: student.uid,
		note,
		punkte: null,
		datum,
		lva_id: context.lvId,
		lehreinheit_id: student.lehreinheit_id,
		sem_kurzbz: context.semKurzbz,
		pruefung_id: pruefungId,
	});

/**
 * Reads the status via the API (getStudentenNoten): what the server REPORTS.
 * The counterpart is readLvGesamtnoteViaDb, it reads what is actually stored.
 */
export const readStateViaApi = (context) =>
	notenApi
		.getStudentenNoten(context.lvId, context.semKurzbz)
		.then((response) => expectNotenSuccess(response, "getStudentenNoten"));

/** All attempts of one student, in Verlauf order (position ascending). */
export const attemptsOfStudent = (data, uid) => attemptsOf(data, uid);

/** Attempts that consume an Antritt, in Verlauf order. */
export const countingAttemptsOfStudent = (data, uid) => countingAttemptsOf(data, uid);

/** The server's rule state for one student. */
export const verlaufOfStudent = (data, uid) => verlaufOf(data, uid);

export const lvNoteOf = (data, uid) => gradesOf(data, uid);

/** Clears the suite's rows and re-seeds the Antritt-1 baseline for one student. */
export const givenBaseline = (context, student, options = {}) =>
	resetNotenState(context).then(() => seedBaseline(context, student, options));
