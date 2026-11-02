// Custom commands: https://on.cypress.io/custom-commands

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
 * Session login. cy.session keeps it across specs, so a test does not log in again.
 * Without arguments it logs in as USER_NAME.
 */
Cypress.Commands.add("login", (username, password) => {
  const auth = username ? { username, password } : defaultAccount();
  const probe = () =>
    cy
      .request({ url: "/index.ci.php/api/frontend/v1/AuthInfo/getAuthUID", auth })
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
const fhcRequest = (method, url, params, account = defaultAccount(), options = {}) => {
  const owner = account ? account.username : "";
  if (owner !== cookieOwner) {
    cy.clearAllCookies();
    cookieOwner = owner;
  }

  const request = { method, url, failOnStatusCode: false, ...options };
  if (account) request.auth = account;
  if (method === "GET") request.qs = params;
  else if (params !== undefined) request.body = params;
  return cy.request(request);
};

/** GET against an FHC API. qs: the query parameters. */
Cypress.Commands.add("fhcGet", (url, qs, account) => fhcRequest("GET", url, qs, account));

/** POST against an FHC API. body: the JSON body. */
Cypress.Commands.add("fhcPost", (url, body, account) => fhcRequest("POST", url, body, account));

/**
 * Multipart POST, for example a DMS upload. cy.request sends no FormData, so the body is built here.
 * file: { field, name, type, content }; content must be text (ASCII), like a plain PDF fixture.
 */
Cypress.Commands.add("fhcUpload", (url, fields, file, account) => {
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
  return fhcRequest("POST", url, body, account, {
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

// Anwendungsfehler sollen den Test nicht abbrechen, aber sichtbar sein.
Cypress.on("uncaught:exception", (err) => {
  Cypress.log({ name: "app error", message: err.message, consoleProps: () => ({ error: err }) });
  return false;
});
