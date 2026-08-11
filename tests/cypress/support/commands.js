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

/**
 * Session-Login für die UI-Specs.
 * cy.session hält es über Specs hinweg, damit nicht jeder Test neu anmeldet.
 */
Cypress.Commands.add("login", () => {
  const { adminusername, adminpassword } = Cypress.env();
  const auth = { username: adminusername, password: adminpassword };
  const probe = () =>
    cy
      .request({ url: "/index.ci.php/api/frontend/v1/AuthInfo/getAuthUID", auth })
      .its("status")
      .should("eq", 200);

  cy.session(["benotungstool-login", adminusername], probe, {
    cacheAcrossSpecs: true,
    validate: probe,
  });
});

// Anwendungsfehler sollen den Test nicht abbrechen, aber sichtbar sein.
Cypress.on("uncaught:exception", (err) => {
  Cypress.log({ name: "app error", message: err.message, consoleProps: () => ({ error: err }) });
  return false;
});
