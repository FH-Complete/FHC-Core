/**
 * Prüfungsordnung §1 rules as the client applies them.
 *
 * Die Regeln selbst liegen im Server (PruefungsverlaufLib): er liefert je Student einen `verlauf`
 * mit Antrittszahl, Grenze und der nächsten möglichen Rolle, und je Termin dessen Position sowie
 * ob er einen Antritt verbraucht. Hier wird das nur noch ausgewertet - damit gibt es keine zweite
 * Implementierung der Regeln mehr, die auseinanderlaufen kann.
 *
 * Der lokale Fallback greift nur, solange der Verlauf für eine Zeile noch nicht geladen ist.
 */

/** Maximale Anzahl zählender Antritte in diesem Tool (serverseitig abgeleitet). */
export const maxAntrittCount = (config) => config?.CIS_GESAMTNOTE_MAX_ANTRITTE ?? 1;

/**
 * Zählende Prüfungsantritte. Gezählt wird über die NOTE, nicht über den Termintyp: Noten in
 * NOTEN_OHNE_ANTRITT (entschuldigt, nicht beurteilt, noch nicht eingetragen) verbrauchen keinen
 * Antritt. Ohne zählenden Termin gilt die LV-Note selbst als erster Antritt, sofern sie zählt.
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

/** Ob für den Studenten überhaupt noch ein Antritt angelegt werden darf. */
export const canAddPruefung = (student, config) => {
	if (student.verlauf) return student.verlauf.canAdd;
	return !student.kommPruef && antrittCountStudent(student, config) < maxAntrittCount(config);
};

/**
 * Ob mit der neuen Prüfung auch erst die LV-Note entsteht. Kommt aus dem Verlauf und nicht aus
 * `lv_note`: letzteres kennt nur FREIGEGEBENE Noten, eine erfasste aber nicht freigegebene LV-Note
 * existiert trotzdem. Nur für den Hinweis in der Oberfläche.
 */
export const brauchtNeueLvNote = (student) => student.verlauf ? !student.verlauf.hatLvNote : !student.lv_note;

/** Grade state from the two timestamps. */
export const checkFreigabe = (freigabedatum, benotungsdatum) => {
	if (!freigabedatum) return "offen";
	return benotungsdatum > freigabedatum ? "changed" : "ok";
};
