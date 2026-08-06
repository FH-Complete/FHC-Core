/** Building attempt histories and reading the resulting state. */

import { notenApi, attemptsOf, countingAttemptsOf, gradesOf, verlaufOf } from "../api/notenApi";
import { expectNotenSuccess } from "./notenErrors";
import { resetNotenState, seedBaseline } from "./notenTestData";

/**
 * Adds an attempt (no pruefung_id -> server runs validatePruefungAdd).
 *
 * No typ: the server derives from the Verlauf which attempt this is. One call writes exactly one
 * Prüfung - the former Termin1 snapshot is gone, the first attempt is created by the Freigabe.
 *
 * punkte stays null: with CIS_GESAMTNOTE_PUNKTE on, a punkte >= 0 makes the controller re-derive
 * the note from the Notenschlüssel and override the grade under test.
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

export const readState = (context) =>
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
	resetNotenState(context).then(() => seedBaseline(context, student.uid, options));
