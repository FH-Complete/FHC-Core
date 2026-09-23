/**
 * The Assistenz account: NOTEN_ASSISTENZ_USER / NOTEN_ASSISTENZ_PASSWORD.
 *
 * getByStgs returns a Studiengang only for a semester with a Studienplan. So the helpers search
 * backwards from the semester of the suite. Call cy.clearAllCookies() before the first request,
 * otherwise the request runs with the session cookie of NOTEN_LEKTOR_USER.
 */

import { apiGet } from "../api/notenApi";
import { expectNotenSuccess } from "./notenErrors";
import { skipIf } from "./notenConfig";

const SEMESTER_SEARCH_DEPTH = 6;

export const assistenzConfigured = () =>
	Boolean(Cypress.env("NOTEN_ASSISTENZ_USER") && Cypress.env("NOTEN_ASSISTENZ_PASSWORD"));

/** Skips if no Assistenz account is configured. */
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

/** WS2026 -> [WS2026, SS2026, WS2025, ...] */
const semestersBackwards = (semKurzbz) => {
	const semesterList = [semKurzbz];
	let type = semKurzbz.slice(0, 2);
	let year = Number(semKurzbz.slice(2, 6));

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

/** -> { semKurzbz, data } for the newest semester in which the Assistenz sees a Studiengang, else null. */
export const assistenzContext = (startSemKurzbz) => {
	const semesterList = semestersBackwards(startSemKurzbz);

	const trySemester = (i) =>
		i >= semesterList.length
			? cy.wrap(null, { log: false })
			: apiGet("getBenotungstoolContext", { sem_kurzbz: semesterList[i] }, assistenzAuth()).then((response) => {
					const data = expectNotenSuccess(response, `Kontext der Assistenz in ${semesterList[i]}`);
					return data.studiengaenge.length ? { semKurzbz: semesterList[i], data } : trySemester(i + 1);
				});

	return trySemester(0);
};

/** -> { semKurzbz, lvId, lehreinheiten } for an LV of the Assistenz with Lehreinheiten in the database, else null. */
export const assistenzLvWithLehreinheiten = (startSemKurzbz) =>
	assistenzContext(startSemKurzbz).then((result) => {
		if (!result) return null;

		const { semKurzbz } = result;
		const studiengaenge = result.data.studiengaenge;

		const firstLvWithLehreinheiten = (lvs, j) =>
			j >= lvs.length
				? cy.wrap(null, { log: false })
				: cy
						.task("noten:db:readLehreinheitenOfLv", { lvId: lvs[j].lehrveranstaltung_id, semKurzbz })
						.then((ids) =>
							ids.length
								? {
										semKurzbz,
										lvId: lvs[j].lehrveranstaltung_id,
										lehreinheiten: ids.map(String).sort(),
									}
								: firstLvWithLehreinheiten(lvs, j + 1),
						);

		const tryStudiengang = (i) =>
			i >= studiengaenge.length
				? cy.wrap(null, { log: false })
				: apiGet(
						"getLvForStudiengang",
						{ studiengang_kz: studiengaenge[i].studiengang_kz, sem_kurzbz: semKurzbz },
						assistenzAuth(),
					)
						.then((response) =>
							firstLvWithLehreinheiten(expectNotenSuccess(response, "LVs des Studiengangs"), 0),
						)
						.then((target) => target || tryStudiengang(i + 1));

		return tryStudiengang(0);
	});
