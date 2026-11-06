// Custom commands: https://on.cypress.io/custom-commands

import { ENTRY, buildApiPath } from "./helpers/paths";

/** Visits a page and fails if any request returned >= 400. */
Cypress.Commands.add("checkPageResources", (url) => {
	const failedRequests = [];

	cy.intercept("**", (req) => {
		req.on("response", (res) => {
			if (res.statusCode >= 400) {
				failedRequests.push({ url: req.url, status: res.statusCode });
			}
		});
	});

	cy.env(["adminusername", "adminpassword"]).then(({ adminusername, adminpassword }) => {
		cy.visit(url, { auth: { username: adminusername, password: adminpassword } });
	});

	cy.then(() => {
		if (failedRequests.length > 0) {
			const message = failedRequests.map((r) => `${r.status} - ${r.url}`).join("\n");
			throw new Error(`Broken resources detected:\n${message}`);
		}
	});
});

/**
 * Session login with HTTP Basic auth. cy.session keeps it across specs, so a test does not log in again.
 * Without arguments it logs in as USER_NAME. The name leaves cy.login free for other suites.
 */
Cypress.Commands.add("loginBasic", (username, password) => {
	const { adminusername, adminpassword } = Cypress.env();
	const auth = { username: username || adminusername, password: password || adminpassword };
	const probe = () =>
		cy
			.request({ url: buildApiPath("AuthInfo/getAuthUID"), auth })
			.its("status")
			.should("eq", 200);

	cy.session(["loginBasic", auth.username], probe, {
		cacheAcrossSpecs: true,
		validate: probe,
	});
});

// The account of the PHP session cookie in the browser. The server prefers that cookie to the Basic auth
// header, so a request as another account drops the cookies first.
let cookieOwner = null;

// testIsolation clears the cookies before each test
beforeEach(() => {
	cookieOwner = null;
});

const defaultAccount = () => {
	const { adminusername, adminpassword } = Cypress.env();
	return { username: adminusername, password: adminpassword };
};

/**
 * Session login for the cy.fhc* commands. cy.session keeps it across specs.
 * Without arguments it logs in as USER_NAME.
 */
Cypress.Commands.add("login", (username, password) => {
	const auth = username ? { username, password } : defaultAccount();
	const probe = () =>
		cy
			.request({ url: buildApiPath("AuthInfo/getAuthUID"), auth })
			.its("status")
			.should("eq", 200);

	cy.session(["login", auth.username], probe, {
		cacheAcrossSpecs: true,
		validate: probe,
	});
	cy.then(() => {
		cookieOwner = auth.username;
	});
});

// account: { username, password }, default USER_NAME; null sends no credentials.
// A failed status does not stop the test: the spec checks it with fhcData or fhcErrors.
const fhcRequest = (method, path, params, account = defaultAccount(), options = {}) => {
	const owner = account ? account.username : "";
	if (owner !== cookieOwner) {
		cy.clearAllCookies();
		cookieOwner = owner;
	}

	// We go through cis.php like the CIS4 pages do. index.ci.php loads the vilesci config, which can
	// point to another database or DB user, so a test there may pass where a real user would fail.
	const url = `/${ENTRY}/${path.replace(/^\/+/, "")}`;

	const request = { method, url, failOnStatusCode: false, ...options };
	if (account) request.auth = account;
	if (method === "GET") request.qs = params;
	else if (params !== undefined) request.body = params;
	return cy.request(request);
};

/** GET against an FHC API. path: the part after the entry point, qs: the query parameters. */
Cypress.Commands.add("fhcGet", (path, qs, account) => fhcRequest("GET", path, qs, account));

/** POST against an FHC API. path: the part after the entry point, body: the JSON body. */
Cypress.Commands.add("fhcPost", (path, body, account) => fhcRequest("POST", path, body, account));

/**
 * Multipart POST, for example a DMS upload. cy.request sends no FormData, so the body is built here.
 * file: { field, name, type, content }; content must be text (ASCII), like a plain PDF fixture.
 */
Cypress.Commands.add("fhcUpload", (path, fields, file, account) => {
	const boundary = `----fhccypress${Date.now()}`;
	const parts = Object.entries(fields).map(
		([name, value]) => `--${boundary}\r\nContent-Disposition: form-data; name="${name}"\r\n\r\n${value}\r\n`,
	);
	if (file) {
		parts.push(
			`--${boundary}\r\nContent-Disposition: form-data; name="${file.field || "files"}"; filename="${file.name}"\r\n` +
				`Content-Type: ${file.type}\r\n\r\n${file.content}\r\n`,
		);
	}
	const body = `${parts.join("")}--${boundary}--\r\n`;
	return fhcRequest("POST", path, body, account, {
		headers: { "Content-Type": `multipart/form-data; boundary=${boundary}` },
	});
});

const responseDetail = (response) => {
	const { body } = response;
	const detail = typeof body === "string" ? body : JSON.stringify(body?.errors ?? body);
	return `${response.status}: ${String(detail).slice(0, 300)}`;
};

/**
 * Checks a successful FHC API response and yields its data.
 * A result object of the server ({ error, retval }) yields its retval.
 */
Cypress.Commands.add("fhcData", { prevSubject: true }, (response) => {
	expect(response.status, responseDetail(response)).to.eq(200);
	expect(response.body?.meta?.status, responseDetail(response)).to.eq("success");

	const { data } = response.body;
	return data && typeof data === "object" && "retval" in data && "error" in data ? data.retval : data;
});

/**
 * Checks a failed FHC API response and yields its errors: [{ type, message }].
 * status: the expected HTTP status, default any status >= 400.
 */
Cypress.Commands.add("fhcErrors", { prevSubject: true }, (response, status) => {
	if (status) expect(response.status, responseDetail(response)).to.eq(status);
	else expect(response.status, responseDetail(response)).to.be.gte(400);
	return response.body?.errors ?? [];
});

// The FHC API layer shows a server error as a toast and then rejects the promise; the page does not
// catch it. That is no crash. Every other error of the page fails the test.
Cypress.on("uncaught:exception", (err) => (err.name === "AxiosError" ? false : undefined));
