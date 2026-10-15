export const waitForOk = (alias) =>
  cy.wait(alias).its("response.statusCode").should("eq", 200);
