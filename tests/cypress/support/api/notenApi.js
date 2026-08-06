/**
 * Wrappers over the Benotungstool API. Mirrors public/js/api/factory/noten.js.
 */

const NOTEN_API = "/index.ci.php/api/frontend/v1/Noten";

const authOptions = () => ({
	username: Cypress.env("adminusername"),
	password: Cypress.env("adminpassword"),
});

// Basic auth on every call rather than relying on the session cookie, so requests stay independent.
const apiGet = (path, qs) =>
	cy.request({ method: "GET", url: `${NOTEN_API}/${path}`, qs, auth: authOptions(), failOnStatusCode: false });

const apiPost = (path, body) =>
	cy.request({ method: "POST", url: `${NOTEN_API}/${path}`, body, auth: authOptions(), failOnStatusCode: false });

export const notenApi = {
	getCisConfig: () => apiGet("getCisConfig"),

	getNoten: () => apiGet("getNoten"),

	getBenotungstoolContext: (sem_kurzbz, lv_id = null) =>
		apiGet("getBenotungstoolContext", { sem_kurzbz, lv_id }),

	getLvForStudiengang: (studiengang_kz, sem_kurzbz) =>
		apiGet("getLvForStudiengang", { studiengang_kz, sem_kurzbz }),

	/**
	 * data is a POSITIONAL array (Noten.php:431):
	 * [studenten, pruefungen, DOMAIN, grades-by-uid, anwesenheiten]
	 */
	getStudentenNoten: (lv_id, sem_kurzbz) => apiGet("getStudentenNoten", { lv_id, sem_kurzbz }),

	/** Reads through the UNFILTERED getter, so it sees notes getStudentenNoten hides. uid -> caller. */
	getNotenvorschlagStudent: (lv_id, sem_kurzbz, uid = null) =>
		apiGet("getNotenvorschlagStudent", { lv_id, sem_kurzbz, uid }),

	/** data -> [lvgesamtnote] */
	saveNotenvorschlag: (lv_id, sem_kurzbz, student_uid, note, punkte = null) =>
		apiPost("saveNotenvorschlag", { lv_id, sem_kurzbz, student_uid, note, punkte }),

	/**
	 * data -> [savedPruefung, lvgesamtnote, verlauf]
	 * No `typ` on the wire: which attempt a Prüfung becomes is derived server-side from the
	 * existing Prüfungsverlauf (PruefungsverlaufLib).
	 */
	saveStudentPruefung: ({
		student_uid, note, punkte = null, datum, lva_id, lehreinheit_id, sem_kurzbz, pruefung_id = null,
	}) =>
		apiPost("saveStudentPruefung", {
			student_uid, note, punkte, datum, lva_id, lehreinheit_id, sem_kurzbz, pruefung_id,
		}),

	/** LDAP-password gated. data -> [{uid, freigabedatum, benotungsdatum}] */
	saveStudentenNoten: (password, noten, lv_id, sem_kurzbz) =>
		apiPost("saveStudentenNoten", { password, noten, lv_id, sem_kurzbz }),

	getNoteByPunkte: (punkte, lv_id, sem_kurzbz) =>
		apiPost("getNoteByPunkte", { punkte, lv_id, sem_kurzbz }),

	// Bulk paths answer 200 and put the error string in data[uid] per row - they never fail the
	// whole request (Noten.php:1600/:1656).
	saveNotenvorschlagBulk: (lv_id, sem_kurzbz, noten) =>
		apiPost("saveNotenvorschlagBulk", { lv_id, sem_kurzbz, noten }),

	savePruefungenBulk: (lv_id, sem_kurzbz, pruefungen) =>
		apiPost("savePruefungenBulk", { lv_id, sem_kurzbz, pruefungen }),

	// note/punkte are optional; without them the Prüfung is created as "Noch nicht eingetragen"
	createPruefungen: (uids, datum, lva_id, sem_kurzbz, note = null, punkte = null) =>
		apiPost("createPruefungen", { uids, datum, lva_id, sem_kurzbz, note, punkte }),
};

// --- selectors over the getStudentenNoten payload ---

export const pruefungenOf = (data, uid) =>
	(data[1] || []).filter((p) => p.student_uid === uid);

/**
 * Attempts in Verlauf order. position/zaehlt/antritt_nr/terminal are derived by the server; the
 * specs assert on those and never on pruefungstyp_kurzbz, which is only a legacy projection.
 */
export const attemptsOf = (data, uid) =>
	[...pruefungenOf(data, uid)].sort((a, b) => Number(a.position) - Number(b.position));

/** Only the attempts that consume one - excused / "noch nicht eingetragen" / nicht beurteilt do not. */
export const countingAttemptsOf = (data, uid) => attemptsOf(data, uid).filter((p) => p.zaehlt);

/** Legacy projection written for old reports. Asserted in exactly one spec, never used as a rule. */
export const pruefungenOfTyp = (data, uid, typ) =>
	pruefungenOf(data, uid).filter((p) => p.pruefungstyp_kurzbz === typ);

export const gradesOf = (data, uid) => (data[3] || {})[uid];

/** Server-derived rule state per student: antrittCount, maxAntritte, canAdd, terminal, angerechnet. */
export const verlaufOf = (data, uid) => (gradesOf(data, uid) || {}).verlauf;
