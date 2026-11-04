/**
 * The examination rules as the client applies them.
 *
 * PruefungsverlaufLib sends one `verlauf` for each student. It contains the attempt count, the
 * limit and the next possible role. For each exam it contains the position and a flag that tells
 * you if the exam uses an attempt.
 *
 * The local fallback applies only while the client has no `verlauf` for a row.
 */

/** The maximum number of attempts that count in this tool. The server derives this value. */
export const maxAntrittCount = (config) => config?.CIS_GESAMTNOTE_MAX_ANTRITTE ?? 1;

/**
 * The attempts that count. The NOTE decides, not the exam type: the grades in NOTEN_OHNE_ANTRITT
 * (excused, not assessed, not entered yet) use no attempt. If no exam counts, the course grade
 * itself is the first attempt, but only if that grade counts.
 */
export const antrittCountStudent = (student, config, notenOptions) => {
	if (student.verlauf) return student.verlauf.antrittCount;

	const ohneAntritt = config?.NOTEN_OHNE_ANTRITT ?? [];
	let count = 0;
	for (const p of student.pruefungen ?? []) {
		if (!ohneAntritt.find((pk) => pk == p.note)) count++;
	}

	if (count === 0 && student.note && !ohneAntritt.find((pk) => pk == student.note)) {
		const noteOption = notenOptions?.find((n) => n.note == student.note);
		return noteOption?.lehre ? 1 : 0;
	}

	return count;
};

/** Tells you if the student can get one more attempt. */
export const canAddPruefung = (student, config) => {
	if (student.verlauf) return student.verlauf.canAdd;
	return !student.kommPruef && antrittCountStudent(student, config) < maxAntrittCount(config);
};

/**
 * Tells you if the new exam also creates the course grade. The value comes from the `verlauf` and
 * not from `lv_note`, because `lv_note` contains RELEASED grades only. An entered grade that is
 * not released still exists. The user interface uses this for a hint only.
 */
export const brauchtNeueLvNote = (student) => student.verlauf ? !student.verlauf.hatLvNote : !student.lv_note;

/**
 * The grade state from the two timestamps: offen = no grade entered (an empty row), changed = a
 * grade is entered but not released, ok = released and not changed since the release.
 */
export const checkFreigabe = (freigabedatum, benotungsdatum) => {
	if (!benotungsdatum) return "offen";
	if (!freigabedatum) return "changed";
	return benotungsdatum > freigabedatum ? "changed" : "ok";
};
