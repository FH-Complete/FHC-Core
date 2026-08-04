/**
 * Prüfungsordnung §1 rules as the client applies them. Extracted from the Vue component so they are
 * testable, and so the server-side mirrors (Noten.php: computeAntrittCount / computeMaxAntritte /
 * getPruefungstypForStudentByAntritt) have something to be compared against.
 */

/** Enabled in-tool retake types, in attempt order. Termin1 is the original note, kommPruef terminal. */
export const retakeTypes = (config) => {
	const types = [];
	if (config?.CIS_GESAMTNOTE_PRUEFUNG_TERMIN2) types.push("Termin2");
	if (config?.CIS_GESAMTNOTE_PRUEFUNG_TERMIN3) types.push("Termin3");
	return types;
};

/** Original note (always 1) plus each enabled retake. kommPruef is entered elsewhere and not capped here. */
export const maxAntrittCount = (config) => 1 + retakeTypes(config).length;

/**
 * Counting Prüfungsantritte. Counted by NOTE, not by typ: notes in NOTEN_OHNE_ANTRITT (entschuldigt,
 * noch nicht eingetragen) never count, which is what lets Termin2 be entered twice. A kommPruef is
 * terminal and returns a sentinel >= any maxAntrittCount. With no counting pruefung the original LV
 * note is the first Antritt, unless it is a non-lehre note ("angerechnet") meaning no participation.
 */
export const antrittCountStudent = (student, config, notenOptions) => {
	if (student["kommPruef"]) return 4;

	const ohneAntritt = config.NOTEN_OHNE_ANTRITT;
	let count = 0;
	for (const p of student.pruefungen) {
		if (!ohneAntritt.find((pk) => pk == p.note)) count++;
	}

	if (count === 0 && student.note) {
		const noteOption = notenOptions.find((n) => n.note == student.note);
		return noteOption.lehre ? 1 : 0;
	}

	return count;
};

/** Next retake type to offer. Attempt 1 is the original note, so retake n sits at index n-1. */
export const pruefungstypForAntritt = (student, config) =>
	retakeTypes(config)[Math.max(0, student.hoechsterAntritt - 1)] ?? "";

/** Grade state from the two timestamps. */
export const checkFreigabe = (freigabedatum, benotungsdatum) => {
	if (!freigabedatum) return "offen";
	return benotungsdatum > freigabedatum ? "changed" : "ok";
};
