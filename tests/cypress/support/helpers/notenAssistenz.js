/**
 * The assistant's account: NOTEN_ASSISTENZ_USER / NOTEN_ASSISTENZ_PASSWORD.
 *
 * getByStgs returns a degree program only for a single semester with a curriculum. The assistants therefore
 * search backward from the semester of the suite. Before the first call, the test requires cy.clearAllCookies(); otherwise,
 * the request will run using the suite user’s cookie.
 */

import { expectNotenSuccess } from "./notenErrors";
import { skipIf } from "./notenConfig";

const NOTEN_API = "/index.ci.php/api/frontend/v1/Noten";
const SEMESTER_SEARCH_DEPTH = 6;

export const assistenzConfigured = () =>
	Boolean(Cypress.env("NOTEN_ASSISTENZ_USER") && Cypress.env("NOTEN_ASSISTENZ_PASSWORD"));

/** Skip, wenn kein Konto der Assistenz konfiguriert ist. */
export const requireAssistenz = (testContext) =>
	skipIf(
		testContext,
		!assistenzConfigured(),
		"Übersprungen: NOTEN_ASSISTENZ_USER / NOTEN_ASSISTENZ_PASSWORD fehlen.",
	);

export const assistenzAuth = () => ({
	username: Cypress.env("NOTEN_ASSISTENZ_USER"),
	password: Cypress.env("NOTEN_ASSISTENZ_PASSWORD"),
});

const getAsAssistenz = (path, qs) =>
	cy.request({ method: "GET", url: `${NOTEN_API}/${path}`, qs, auth: assistenzAuth(), failOnStatusCode: false });

/** WS2026 -> [WS2026, SS2026, WS2025, ...] */
const semestersBackwards = (sem) => {
	const semesterList = [sem];
	let type = sem.slice(0, 2);
	let year = Number(sem.slice(2, 6));

	while (semesterList.length < SEMESTER_SEARCH_DEPTH) {
		if (type === "WS") {
			type = "SS";
		} else {
			type = "WS";
			year -= 1;
		}
		semesterList.push(`${type}${year}`);
	}
	return semesterList;
};

/** -> { sem, data } für das neueste Semester, in dem die Assistenz Studiengänge sieht, sonst null. */
export const assistenzContext = (startSem) => {
	const semesterList = semestersBackwards(startSem);

	const trySemester = (i) =>
		i >= semesterList.length
			? cy.wrap(null, { log: false })
			: getAsAssistenz("getBenotungstoolContext", { sem_kurzbz: semesterList[i] }).then((response) => {
					const data = expectNotenSuccess(response, `Kontext der Assistenz in ${semesterList[i]}`);
					return data.studiengaenge.length ? { sem: semesterList[i], data } : trySemester(i + 1);
				});

	return trySemester(0);
};

/** -> { sem, lvId, lehreinheiten } für eine LV der Assistenz mit Lehreinheiten in der Datenbank, sonst null. */
export const assistenzLvWithLehreinheiten = (startSem) =>
	assistenzContext(startSem).then((result) => {
		if (!result) return null;

		const { sem } = result;
		const studiengaenge = result.data.studiengaenge;

		const firstLvWithLehreinheiten = (lvs, j) =>
			j >= lvs.length
				? cy.wrap(null, { log: false })
				: cy
						.task("noten:db:lehreinheitenOfLv", { lvId: lvs[j].lehrveranstaltung_id, semKurzbz: sem })
						.then((ids) =>
							ids.length
								? { sem, lvId: lvs[j].lehrveranstaltung_id, lehreinheiten: ids.map(String).sort() }
								: firstLvWithLehreinheiten(lvs, j + 1),
						);

		const tryStudiengang = (i) =>
			i >= studiengaenge.length
				? cy.wrap(null, { log: false })
				: getAsAssistenz("getLvForStudiengang", {
						studiengang_kz: studiengaenge[i].studiengang_kz,
						sem_kurzbz: sem,
					})
						.then((response) =>
							firstLvWithLehreinheiten(expectNotenSuccess(response, "LVs des Studiengangs"), 0),
						)
						.then((target) => target || tryStudiengang(i + 1));

		return tryStudiengang(0);
	});
