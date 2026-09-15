/**
 * Wrappers over the Benotungstool API. Mirrors public/js/api/factory/noten.js.
 */

const NOTEN_API = "/index.ci.php/api/frontend/v1/Noten";

// One session for the whole run. Without a session cookie the server checks each Basic-auth request
// against LDAP, and many LDAP binds can block all PHP-FPM workers of the instance. cy.login creates the
// session once (cy.session, cacheAcrossSpecs) and restores it before each test of every importing spec.
beforeEach(() => cy.login());

const authOptions = () => ({
	username: Cypress.env("adminusername"),
	password: Cypress.env("adminpassword"),
});

// Basic auth stays on every call as the fallback; with a session cookie the server uses the cookie.
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

	getLehrendeFuerLehreinheit: (lehreinheit_id, lv_id, sem_kurzbz) =>
		apiGet("getLehrendeFuerLehreinheit", { lehreinheit_id, lv_id, sem_kurzbz }),

	getLehreinheitenFuerLv: (lv_id, sem_kurzbz) => apiGet("getLehreinheitenFuerLv", { lv_id, sem_kurzbz }),

	/** data ist POSITIONAL: [studenten, pruefungen, DOMAIN, grades-by-uid, anwesenheiten] */
	getStudentenNoten: (lv_id, sem_kurzbz) => apiGet("getStudentenNoten", { lv_id, sem_kurzbz }),

	/** Single-student read through the unfiltered getter. uid -> caller. */
	getNotenvorschlagStudent: (lv_id, sem_kurzbz, uid = null) =>
		apiGet("getNotenvorschlagStudent", { lv_id, sem_kurzbz, uid }),

	/** data -> [lvgesamtnote] */
	saveNotenvorschlag: (lv_id, sem_kurzbz, student_uid, note, punkte = null, datum = null) =>
		apiPost("saveNotenvorschlag", { lv_id, sem_kurzbz, student_uid, note, punkte, datum }),

	/** data -> [savedPruefung, lvgesamtnote, verlauf]. Kein `typ` auf der Leitung. Ohne mitarbeiter_uid fehlt das Feld. */
	saveStudentPruefung: ({
		student_uid, note, punkte = null, datum, lva_id, lehreinheit_id, sem_kurzbz, pruefung_id = null, mitarbeiter_uid,
	}) =>
		apiPost("saveStudentPruefung", {
			student_uid, note, punkte, datum, lva_id, lehreinheit_id, sem_kurzbz, pruefung_id, mitarbeiter_uid,
		}),

	/**
	 * LDAP-password gated. data -> [{uid, freigabedatum, benotungsdatum}]
	 *
	 * Zuerst eine Sitzung: AuthLDAPLib lädt ldap.php mit require_once. Prüft die Basic-Anmeldung desselben
	 * Requests schon LDAP, sieht die Passwortprüfung der Freigabe keine Konfiguration und meldet
	 * "Falsches Passwort". Mit dem Sitzungscookie prüft die Anmeldung kein LDAP.
	 */
	saveStudentenNoten: (password, noten, lv_id, sem_kurzbz) =>
		apiGet("getCisConfig").then(() => apiPost("saveStudentenNoten", { password, noten, lv_id, sem_kurzbz })),

	getNoteByPunkte: (punkte, lv_id, sem_kurzbz) =>
		apiPost("getNoteByPunkte", { punkte, lv_id, sem_kurzbz }),

	// Bulk-Pfade antworten 200 und melden Fehler je Zeile in data[uid]
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

/** Antritte in Verlaufsreihenfolge. Specs prüfen position/zaehlt/antritt_nr/terminal, nie den Typ. */
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
