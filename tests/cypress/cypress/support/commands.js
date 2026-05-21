// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

require("@4tw/cypress-drag-drop");

Cypress.Commands.add("login", () => {
  const { adminusername, adminpassword, loginAsUser } = Cypress.env();
  const userToImpersonate = loginAsUser || adminusername;
  const auth = {
    username: adminusername,
    password: adminpassword,
  };

  cy.session(
    ["tempus-login", adminusername, userToImpersonate],
    () => {
      cy.request({
        url: "/index.ci.php/tempus",
        method: "POST",
        auth,
        body: {
          searchstr: userToImpersonate,
          types: ["employee"],
        },
      }).its("status").should("eq", 200);
    },
    {
      cacheAcrossSpecs: true,
      validate() {
        cy.request({
          url: "/index.ci.php/tempus",
          auth,
        }).its("status").should("eq", 200);
      },
    },
  );
});