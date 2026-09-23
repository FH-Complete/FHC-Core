/** Add or change a Pruefung through the API, then read what the server reports. */

import { notenApi } from "../api/notenApi";
import { expectNotenSuccess } from "./notenErrors";
import { resetNotenState, seedBaseline } from "./notenTestData";

/**
 * A new Pruefung: the server runs validateAdd.
 *
 * punkte stays null: in the Punkte mode any value (also 0) makes the server derive the Note from the
 * Notenschluessel, and that Note replaces the Note under test.
 */
export const addPruefung = (ctx, student, { note, datum }) =>
	notenApi.savePruefung(ctx.lvId, ctx.semKurzbz, student.uid, {
		pruefung_id: null,
		lehreinheit_id: student.lehreinheit_id,
		datum,
		note,
		punkte: null,
	});

/** Changes a Pruefung: the server runs validateEdit. */
export const editPruefung = (ctx, student, { pruefungId, note, datum }) =>
	notenApi.savePruefung(ctx.lvId, ctx.semKurzbz, student.uid, {
		pruefung_id: pruefungId,
		lehreinheit_id: student.lehreinheit_id,
		datum,
		note,
		punkte: null,
	});

/** Clears the rows of the suite and seeds the baseline of Antritt 1 for one student. */
export const givenBaseline = (ctx, student, options = {}) =>
	resetNotenState(ctx).then(() => seedBaseline(ctx, student, options));

/**
 * Reads getStudentenNoten: what the server REPORTS. Read the result with the functions below.
 * The counterpart is readLvGesamtnoteViaDb: what the database STORES.
 */
export const readStateViaApi = (ctx) =>
	notenApi
		.getStudentenNoten(ctx.lvId, ctx.semKurzbz)
		.then((response) => expectNotenSuccess(response, "getStudentenNoten"));

/** The row of one student: zeugnisnote, lv_note, lv_punkte, benotungsdatum, freigabedatum, verlauf. */
export const studentOf = (data, uid) => data.students.find((s) => s.uid === uid);

/** The rule state that the server derives: antrittCount, maxAntritte, canAdd, terminal, bestanden, angerechnet. */
export const verlaufOf = (data, uid) => studentOf(data, uid)?.verlauf;

/** All Pruefungen of one student, in Verlauf order. Check position, count and terminal, never the Pruefungstyp. */
export const pruefungenOf = (data, uid) => verlaufOf(data, uid)?.pruefungen ?? [];

/** Only the Pruefungen that use an Antritt. "entschuldigt" and "Noch nicht eingetragen" do not. */
export const antritteOf = (data, uid) => pruefungenOf(data, uid).filter((p) => p.is_antritt);
