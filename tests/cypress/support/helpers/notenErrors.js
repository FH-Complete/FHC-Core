/**
 * Assertions for the Noten error envelope.
 *
 * These errors carry no errorCode, only a localized message (terminateWithError + p->t()), so we
 * match against the de/en phrase templates from system/phrasesupdate.php. Both languages accepted.
 * Adding an errorCode server-side would remove this coupling.
 */

const PHRASES = {
	maxAntritteReached: {
		de: "Für Studierenden {0} sind bereits die maximal zulässigen Prüfungsantritte ({1}) vergeben. Es wurde keine Prüfung angelegt.",
		en: "Student {0} has already used the maximum number of exam attempts ({1}). No exam was created.",
	},
	pruefungDatumBeforeExisting: {
		de: "Das Prüfungsdatum für Studierenden {0} darf nicht vor oder am selben Tag wie ein bereits bestehender Prüfungstermin liegen.",
		en: "The exam date for student {0} must not be on or before an existing exam date.",
	},
	noteOccuranceLimitReached: {
		de: "Die gewählte Note darf für Studierenden {0} nicht so oft vergeben werden. Es wurde keine Prüfung angelegt.",
		en: "The selected grade may not be assigned that many times for student {0}. No exam was created.",
	},
	pruefungNoteLocked: {
		de: "Die Note für Studierenden {0} kann nicht mehr geändert werden, da bereits eine spätere Prüfung oder eine Prüfung mit höherem Antritt existiert. Das Prüfungsdatum kann weiterhin angepasst werden.",
		en: "The grade for student {0} can no longer be changed because a later exam or an exam of a higher attempt already exists. The exam date can still be adjusted.",
	},
	pruefungDatumOutOfRange: {
		de: "Das Prüfungsdatum für Studierenden {0} muss zwischen dem vorherigen und dem nachfolgenden Prüfungstermin liegen.",
		en: "The exam date for student {0} must be between the previous and the following exam date.",
	},
	noteneintragungsfristVorbei: {
		de: "Die Noteneintragungsfrist ({0}) für dieses Studiensemester ist abgelaufen. Es können keine Noten oder Prüfungen mehr eingetragen werden.",
		en: "The grade entry deadline ({0}) for this study semester has passed. Grades and exams can no longer be entered.",
	},
	keineBerechtigungNoten: {
		de: 'Keine Berechtigung, um in der Lehrveranstaltung "{0}" im Semester {1} Noten einzutragen.',
		en: 'No permission to enter grades for the course "{0}" in semester {1}.',
	},
	wrongPassword: { de: "Falsches Passwort", en: "Wrong password" },
};

const escapeRegExp = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

// Split on the {n} placeholders first, then escape - the other order breaks on the braces.
const toPattern = (text) =>
	new RegExp(text.split(/\{\d+\}/).map(escapeRegExp).join("[\\s\\S]*?"));

const patternsFor = (key) => {
	const phrase = PHRASES[key];
	if (!phrase) throw new Error(`Unknown phrase key "${key}". Known: ${Object.keys(PHRASES).join(", ")}`);
	return [toPattern(phrase.de), toPattern(phrase.en)];
};

export const messageMatchesPhrase = (message, key) =>
	typeof message === "string" && patternsFor(key).some((re) => re.test(message));

export const expectNotenError = (response, key) => {
	expect(response.status, `HTTP status for expected "${key}"`).to.eq(500);
	expect(response.body).to.have.nested.property("meta.status", "error");
	expect(response.body.errors).to.be.an("array").and.not.be.empty;

	const messages = response.body.errors.map((e) => e.message);
	expect(
		messages.some((m) => messageMatchesPhrase(m, key)),
		`expected phrase "${key}", got: ${JSON.stringify(messages)}`,
	).to.be.true;
};

export const expectNotenSuccess = (response, context = "request") => {
	// carry the server's message into the assertion, or a 500 says only "expected 200"
	const errors = (response.body && response.body.errors || []).map((e) => e.message).join(" | ");
	expect(response.status, `HTTP status for ${context}${errors ? ` -- ${errors}` : ""}`).to.eq(200);
	expect(response.body, context).to.have.nested.property("meta.status", "success");
	return response.body.data;
};

/** Auth failures short-circuit before any error is added, so the body carries only meta. */
export const expectAuthError = (response) => {
	expect(response.status, "HTTP status for auth rejection").to.eq(401);
	expect(response.body).to.have.nested.property("meta.status", "error");
};

export const expectBulkRowError = (data, uid, key) => {
	expect(data).to.have.property(uid);
	expect(
		messageMatchesPhrase(data[uid], key),
		`expected row "${uid}" to carry "${key}", got: ${JSON.stringify(data[uid])}`,
	).to.be.true;
};

export const expectBulkRowAccepted = (data, uid) => {
	expect(data).to.have.property(uid);
	const value = data[uid];
	const isError =
		typeof value === "string" && Object.keys(PHRASES).some((k) => messageMatchesPhrase(value, k));
	expect(isError, `expected row "${uid}" accepted, got: ${JSON.stringify(value)}`).to.be.false;
};
