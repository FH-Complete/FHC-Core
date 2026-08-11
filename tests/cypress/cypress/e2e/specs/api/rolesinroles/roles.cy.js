import { rolesinrolesApi } from "../../../../support/api/rolesinrolesApi";

describe("Roles in roles checks", () => {

  beforeEach(() => {
    cy.login();
  });

  it("checks permission agains the main role", () => {
	rolesinrolesApi.permissionToMainRole();
  });
  it("checks permission agains the basic role", () => {
	rolesinrolesApi.permissionToBasicRole();
  });
  it("checks permission agains the single permission", () => {
	rolesinrolesApi.permissionToUser();
  });
});
