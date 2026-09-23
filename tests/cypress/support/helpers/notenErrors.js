/**
 * Assertions for the answers of the Noten API.
 *
 * Each error has a code: the phrase key of its message. The helpers compare the code. A message with
 * "<< PHRASE" means that the phrase is missing in the database: run phrasesync.php on the instance.
 */

const expectTranslated = (message, code) =>
	expect(message, `Meldung von "${code}"`)
		.to.be.a("string")
		.and.not.match(/<<\s*PHRASE/i);

/** The request stops with this error: HTTP 500 and the code in errors[]. */
export const expectNotenError = (response, code) => {
	expect(response.status, `HTTP-Status für den erwarteten Fehler "${code}"`).to.eq(500);
	expect(response.body).to.have.nested.property("meta.status", "error");

	const error = (response.body.errors ?? []).find((e) => e.code === code);
	expect(error, `Fehler "${code}", erhalten: ${JSON.stringify(response.body.errors)}`).to.exist;
	expectTranslated(error.message, code);
};

export const expectNotenSuccess = (response, context = "Anfrage") => {
	// carry the server's message into the assertion, or a 500 says only "expected 200"
	const errors = ((response.body && response.body.errors) || []).map((e) => e.message).join(" | ");
	expect(response.status, `HTTP-Status für ${context}${errors ? ` -- ${errors}` : ""}`).to.eq(200);
	expect(response.body, context).to.have.nested.property("meta.status", "success");
	return response.body.data;
};

/** An auth failure stops the request before any error is added, so the body has only meta. */
export const expectAuthError = (response) => {
	expect(response.status, "HTTP-Status für die abgewiesene Anmeldung").to.eq(401);
	expect(response.body).to.have.nested.property("meta.status", "error");
};

/** A bulk write rejects one row: data[uid] = { error: { code, message } }. */
export const expectBulkRowError = (data, uid, code) => {
	expect(data).to.have.property(uid);
	expect(data[uid].error?.code, `Zeile "${uid}", erhalten: ${JSON.stringify(data[uid])}`).to.eq(code);
	expectTranslated(data[uid].error.message, code);
};

export const expectBulkRowAccepted = (data, uid) => {
	expect(data).to.have.property(uid);
	expect(data[uid].error, `Zeile "${uid}" akzeptiert, erhalten: ${JSON.stringify(data[uid].error)}`).to.be.undefined;
};
