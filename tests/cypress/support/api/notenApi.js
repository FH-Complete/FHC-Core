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

	/** data -> [savedPruefung, lvgesamtnote, extraPruefung(auto-created Termin1)] */
	saveStudentPruefung: ({
		student_uid, note, punkte = null, datum, lva_id, lehreinheit_id, sem_kurzbz, typ, pruefung_id = null,
	}) =>
		apiPost("saveStudentPruefung", {
			student_uid, note, punkte, datum, lva_id, lehreinheit_id, sem_kurzbz, typ, pruefung_id,
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

	createPruefungen: (uids, datum, lva_id, sem_kurzbz) =>
		apiPost("createPruefungen", { uids, datum, lva_id, sem_kurzbz }),
};

// --- selectors over the getStudentenNoten payload ---

export const pruefungenOf = (data, uid) =>
	(data[1] || []).filter((p) => p.student_uid === uid);

export const pruefungenOfTyp = (data, uid, typ) =>
	pruefungenOf(data, uid).filter((p) => p.pruefungstyp_kurzbz === typ);

export const gradesOf = (data, uid) => (data[3] || {})[uid];
