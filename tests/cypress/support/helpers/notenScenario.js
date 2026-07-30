/** Building attempt histories and reading the resulting state. */

import { notenApi, pruefungenOfTyp, gradesOf } from "../api/notenApi";
import { expectNotenSuccess } from "./notenErrors";
import { resetNotenState, seedBaseline } from "./notenTestData";

/** Enabled in-tool retake types, in attempt order. maxAntritte == 1 + this list's length. */
export const enabledRetakeTypes = (context) => {
	const types = [];
	if (context.cisConfig.CIS_GESAMTNOTE_PRUEFUNG_TERMIN2) types.push("Termin2");
	if (context.cisConfig.CIS_GESAMTNOTE_PRUEFUNG_TERMIN3) types.push("Termin3");
	return types;
};

/**
 * Adds an attempt (no pruefung_id -> server runs validatePruefungAdd).
 * punkte stays null: with CIS_GESAMTNOTE_PUNKTE on, a punkte >= 0 makes the controller re-derive
 * the note from the Notenschlüssel and override the grade under test (Noten.php:692).
 */
export const addPruefung = (context, student, { note, datum, typ = "Termin2" }) =>
	notenApi.saveStudentPruefung({
		student_uid: student.uid,
		note,
		punkte: null,
		datum,
		lva_id: context.lvId,
		lehreinheit_id: student.lehreinheit_id,
		sem_kurzbz: context.semKurzbz,
		typ,
		pruefung_id: null,
	});

/** Edits an attempt (pruefung_id set -> server runs validatePruefungEdit). */
export const editPruefung = (context, student, { pruefungId, note, datum, typ = "Termin2" }) =>
	notenApi.saveStudentPruefung({
		student_uid: student.uid,
		note,
		punkte: null,
		datum,
		lva_id: context.lvId,
		lehreinheit_id: student.lehreinheit_id,
		sem_kurzbz: context.semKurzbz,
		typ,
		pruefung_id: pruefungId,
	});

export const readState = (context) =>
	notenApi
		.getStudentenNoten(context.lvId, context.semKurzbz)
		.then((response) => expectNotenSuccess(response, "getStudentenNoten"));

/** Attempts of one student and type, oldest first (the API returns datum DESC). */
export const attemptsOfTyp = (data, uid, typ) =>
	[...pruefungenOfTyp(data, uid, typ)].sort((a, b) =>
		String(a.datum).slice(0, 10).localeCompare(String(b.datum).slice(0, 10)),
	);

export const lvNoteOf = (data, uid) => gradesOf(data, uid);

/** Clears the suite's rows and re-seeds the Antritt-1 baseline for one student. */
export const givenBaseline = (context, student, options = {}) =>
	resetNotenState(context).then(() => seedBaseline(context, student.uid, options));
