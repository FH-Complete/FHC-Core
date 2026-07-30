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

// don't fail tests on uncaught app exceptions
Cypress.on("uncaught:exception", () => false);
