import { notenAuth } from "../../../../support/api/notenApi";
import { waitForOk } from "../../../../support/helpers/network";
import {
	assistenzAuth,
	assistenzLvWithLehreinheiten,
	requireAssistenz,
} from "../../../../support/helpers/notenAssistenz";
import { loadNotenContext, requireDbReset } from "../../../../support/helpers/notenTestData";

/**
 * The route is /Cis/Benotungstool/:sem_kurzbz?/:lv_id?. The semester comes before the course, so
 * the router no longer interprets a URL without a course as lv_id.
 */
context("Benotungstool UI - Navigation", () => {
	const SEMESTER = "[data-cy='dropdown-semester']";
	const TIMEOUT = 60_000;

	/** The text that appears in the closed dropdown once the component has selected a semester. */
	const selectedSemester = () =>
		cy
			.get(`${SEMESTER} .p-dropdown-label`, { timeout: TIMEOUT })
			.should(($label) => expect($label.text().trim(), "vorausgewähltes Semester").to.not.be.empty)
			.then(($label) => $label.text().trim());

	it("behält ein Semester ohne LV nach einem Reload", () => {
		const { username, password } = notenAuth();
		cy.login(username, password);
		cy.intercept({ method: "GET", url: "**/api/frontend/v1/Noten/getBenotungstoolContext*" }).as("context");
		cy.visit("/cis.php/Cis/Benotungstool");

		selectedSemester()
			.then((preselected) => {
				cy.get(SEMESTER).click();
				return cy
					.get(".p-dropdown-panel .p-dropdown-item")
					.filter((i, el) => el.innerText.trim() !== preselected)
					.first();
			})
			.then(($item) => {
				const sem = $item.text().trim();

				cy.wrap($item).click();
				waitForOk("@context");
				cy.location("pathname").should("match", new RegExp(`/Benotungstool/${sem}$`));

				cy.reload();

				selectedSemester().should("eq", sem);
				cy.get(".p-toast-message-error").should("not.exist");
			});
	});

	// The list contained only the courses that the caller teaches.
	it("zeigt einer Assistenz die Lehreinheiten der LV", function () {
		requireAssistenz(this);

		const { username, password } = assistenzAuth();

		requireDbReset();
		loadNotenContext().then((ctx) => {
			cy.clearAllCookies();

			assistenzLvWithLehreinheiten(ctx.semKurzbz).then((target) => {
				expect(target, "eine LV der Assistenz mit Lehreinheiten").to.not.be.null;

				cy.login(username, password);
				cy.intercept({ method: "GET", url: "**/api/frontend/v1/Noten/getLehreinheitenFuerLv*" }).as(
					"lehreinheiten",
				);
				cy.visit(`/cis.php/Cis/Benotungstool/${target.sem}/${target.lvId}`);

				waitForOk("@lehreinheiten");
				cy.get("[data-cy='dropdown-lehreinheit']", { timeout: TIMEOUT }).click();
				cy.get(".p-dropdown-panel .p-dropdown-item").should("have.length", target.lehreinheiten.length);
			});
		});
	});
});
