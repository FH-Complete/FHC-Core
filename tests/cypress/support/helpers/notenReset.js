/**
 * Fixture reset over a direct DB connection - the only mechanism.
 *
 * A gated HTTP reset endpoint existed briefly and was removed: it deleted grade data, and no
 * gating makes that as safe as not having it. Consequence: the runner needs DB reachability, which
 * from a workstation means the SSH tunnel (cypress/tasks/sshTunnel.js).
 */

let available = null;

/** Probes the DB once per run. Never throws; the caller decides how loudly to fail. */
export const resolveResetStrategy = () => {
	if (available) return cy.wrap(available, { log: false });

	return cy.task("noten:db:available", null, { log: false }).then((status) => {
		available = { strategy: status.available ? "db" : null, reason: status.reason };
		if (status.available) cy.log("Fixture reset via **direct database**");
		return cy.wrap(available, { log: false });
	});
};

export const describeFailure = (state) =>
	`      ${state.reason}\n\n` +
	"      Needs a database connection. From a workstation: NOTEN_SSH_TUNNEL=true plus\n" +
	"      NOTEN_SSH_HOST / NOTEN_SSH_USER (and NOTEN_SSH_KEY, unless an agent holds the key).";

/**
 * uid to stamp as mitarbeiter_uid / freigabevon_uid.
 * Not Cypress.env("adminusername"): LDAP accepts "Demolektor1" but tbl_benutzer.uid is
 * "demolektor1" and the FKs are case sensitive.
 */
let cachedAuthUid = null;

const authUid = () => {
	if (cachedAuthUid) return cy.wrap(cachedAuthUid, { log: false });

	return cy
		.request({
			method: "GET",
			url: "/index.ci.php/api/frontend/v1/AuthInfo/getAuthUID",
			auth: { username: Cypress.env("adminusername"), password: Cypress.env("adminpassword") },
			failOnStatusCode: false,
		})
		.then((response) => {
			const uid = response.body?.data?.uid;
			expect(uid, "AuthInfo/getAuthUID uid").to.be.a("string").and.not.be.empty;
			cachedAuthUid = uid;
			return uid;
		});
};

export const performReset = (context, studentUids) =>
	cy.task("noten:db:reset", {
		lvId: context.lvId,
		semKurzbz: context.semKurzbz,
		studentUids,
	});

export const performSeed = (context, studentUid, payload) =>
	authUid().then((uid) =>
		cy.task("noten:db:seedLvGesamtnote", {
			lvId: context.lvId,
			semKurzbz: context.semKurzbz,
			studentUid,
			mitarbeiterUid: uid,
			...payload,
		}),
	);

export const performRead = (context, studentUid) =>
	cy.task("noten:db:readLvGesamtnote", {
		lvId: context.lvId,
		semKurzbz: context.semKurzbz,
		studentUid,
	});
