/**
 * The Noten API. The same names and parameters as public/js/api/factory/noten.js.
 *
 * notenApi.* calls an endpoint as NOTEN_LEKTOR_USER. apiGet / apiPost call it as another account.
 */

const NOTEN_API = "/index.ci.php/api/frontend/v1/Noten";

/** The Lektor the suite works as. No fallback to USER_NAME: that user belongs to every suite. */
export const lektorAuth = () => {
	const username = Cypress.env("NOTEN_LEKTOR_USER");
	const password = Cypress.env("NOTEN_LEKTOR_PASSWORD");
	if (!username || !password) {
		throw new Error("NOTEN_LEKTOR_USER / NOTEN_LEKTOR_PASSWORD missing in tests/cypress/suites/.env");
	}
	return { username, password };
};

/** The LDAP password for the Freigabe. NOTEN_FREIGABE_PASSWORD, else the password of NOTEN_LEKTOR_USER. */
export const freigabePassword = () => Cypress.env("NOTEN_FREIGABE_PASSWORD") || lektorAuth().password;

/**
 * Logs in as NOTEN_LEKTOR_USER. Call it in beforeEach: cy.session logs in once per run and restores
 * the session before each test. Without a session cookie the server checks every request against LDAP,
 * and many LDAP binds can block all PHP-FPM workers of the instance.
 */
export const loginAsLektor = () => {
	const { username, password } = lektorAuth();
	cy.login(username, password);
};

/** auth: the account (default NOTEN_LEKTOR_USER), or null for no credentials. A session cookie wins over auth. */
export const apiGet = (path, qs, auth = lektorAuth()) =>
	cy.request({ method: "GET", url: `${NOTEN_API}/${path}`, qs, ...(auth ? { auth } : {}), failOnStatusCode: false });

export const apiPost = (path, body, auth = lektorAuth()) =>
	cy.request({
		method: "POST",
		url: `${NOTEN_API}/${path}`,
		body,
		...(auth ? { auth } : {}),
		failOnStatusCode: false,
	});

export const notenApi = {
	getCisConfig: () => apiGet("getCisConfig"),

	getNoten: () => apiGet("getNoten"),

	getBenotungstoolContext: (sem_kurzbz, lv_id = null) => apiGet("getBenotungstoolContext", { sem_kurzbz, lv_id }),

	getLvForStudiengang: (studiengang_kz, sem_kurzbz) => apiGet("getLvForStudiengang", { studiengang_kz, sem_kurzbz }),

	getLehreinheitenForLv: (lv_id, sem_kurzbz) => apiGet("getLehreinheitenForLv", { lv_id, sem_kurzbz }),

	getLektorenForLehreinheit: (lv_id, sem_kurzbz, lehreinheit_id) =>
		apiGet("getLektorenForLehreinheit", { lv_id, sem_kurzbz, lehreinheit_id }),

	/** data -> { students, domain }. Read it with notenScenario.js. */
	getStudentenNoten: (lv_id, sem_kurzbz) => apiGet("getStudentenNoten", { lv_id, sem_kurzbz }),

	getNoteByPunkte: (lv_id, sem_kurzbz, punkte) => apiPost("getNoteByPunkte", { lv_id, sem_kurzbz, punkte }),

	// Each write answers data[uid] = { lvgesamtnote, verlauf, pruefung }. A bulk write answers HTTP 200
	// also for a rejected row: data[uid] = { error: { code, message } }.

	saveLvNote: (lv_id, sem_kurzbz, student_uid, note, punkte = null, datum = null) =>
		apiPost("saveLvNote", { lv_id, sem_kurzbz, student_uid, note, punkte, datum }),

	/** lv_noten: [{ uid, note, punkte }] */
	importLvNoten: (lv_id, sem_kurzbz, lv_noten) => apiPost("importLvNoten", { lv_id, sem_kurzbz, lv_noten }),

	/** pruefung: { pruefung_id (null = a new Pruefung), lehreinheit_id, datum, note, punkte, mitarbeiter_uid } */
	savePruefung: (lv_id, sem_kurzbz, student_uid, pruefung) =>
		apiPost("savePruefung", { lv_id, sem_kurzbz, student_uid, ...pruefung }),

	/** students: [{ uid, lehreinheit_id }]; pruefung: { datum, note, punkte, mitarbeiter_uid } */
	createPruefungen: (lv_id, sem_kurzbz, students, pruefung) =>
		apiPost("createPruefungen", { lv_id, sem_kurzbz, students, ...pruefung }),

	/** pruefungen: [{ uid, lehreinheit_id, datum, note, punkte }] */
	importPruefungen: (lv_id, sem_kurzbz, pruefungen) => apiPost("importPruefungen", { lv_id, sem_kurzbz, pruefungen }),

	/**
	 * The Freigabe needs the LDAP password. The getCisConfig call first creates a session. Without it,
	 * the Basic-auth login loads ldap.php with require_once, and the password check in the same request
	 * finds no LDAP configuration and answers "Incorrect password".
	 */
	saveFreigabe: (lv_id, sem_kurzbz, password, uids) =>
		apiGet("getCisConfig").then(() => apiPost("saveFreigabe", { lv_id, sem_kurzbz, password, uids })),
};
