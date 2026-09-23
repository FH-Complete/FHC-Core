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
 * Session login. cy.session keeps it across specs, so a test does not log in again.
 * Without arguments it logs in as USER_NAME.
 */
Cypress.Commands.add("login", (username, password) => {
	const { adminusername, adminpassword } = Cypress.env();
	const auth = { username: username || adminusername, password: password || adminpassword };
	const probe = () =>
		cy.request({ url: "/index.ci.php/api/frontend/v1/AuthInfo/getAuthUID", auth }).its("status").should("eq", 200);

	cy.session(["login", auth.username], probe, {
		cacheAcrossSpecs: true,
		validate: probe,
	});
});

// The FHC API layer shows a server error as a toast and then rejects the promise; the page does not
// catch it. That is no crash. Every other error of the page fails the test.
Cypress.on("uncaught:exception", (err) => (err.name === "AxiosError" ? false : undefined));
