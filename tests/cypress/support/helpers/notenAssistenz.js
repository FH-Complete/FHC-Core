/**
 * Das Konto der Assistenz: NOTEN_ASSISTENZ_USER / NOTEN_ASSISTENZ_PASSWORD.
 *
 * getByStgs liefert einen Studiengang nur in einem Semester mit Studienplan. Die Helfer suchen deshalb
 * vom Semester der Suite rückwärts. Vor dem ersten Aufruf braucht der Test cy.clearAllCookies(), sonst
 * läuft der Request mit dem Cookie des Suite-Benutzers.
 */

import { expectNotenSuccess } from "./notenErrors";
import { skipWenn } from "./notenConfig";

const NOTEN_API = "/index.ci.php/api/frontend/v1/Noten";
const SEMESTER_SUCHTIEFE = 6;

export const assistenzConfigured = () =>
	Boolean(Cypress.env("NOTEN_ASSISTENZ_USER") && Cypress.env("NOTEN_ASSISTENZ_PASSWORD"));

/** Skip, wenn kein Konto der Assistenz konfiguriert ist. */
export const requireAssistenz = (testContext) =>
	skipWenn(
		testContext,
		!assistenzConfigured(),
		"Übersprungen: NOTEN_ASSISTENZ_USER / NOTEN_ASSISTENZ_PASSWORD fehlen.",
	);

export const assistenzAuth = () => ({
	username: Cypress.env("NOTEN_ASSISTENZ_USER"),
	password: Cypress.env("NOTEN_ASSISTENZ_PASSWORD"),
});

const getAlsAssistenz = (path, qs) =>
	cy.request({ method: "GET", url: `${NOTEN_API}/${path}`, qs, auth: assistenzAuth(), failOnStatusCode: false });

/** WS2026 -> [WS2026, SS2026, WS2025, ...] */
const semesterAbwaerts = (sem) => {
	const liste = [sem];
	let typ = sem.slice(0, 2);
	let jahr = Number(sem.slice(2, 6));

	while (liste.length < SEMESTER_SUCHTIEFE) {
		if (typ === "WS") {
			typ = "SS";
		} else {
			typ = "WS";
			jahr -= 1;
		}
		liste.push(`${typ}${jahr}`);
	}
	return liste;
};

/** -> { sem, data } für das neueste Semester, in dem die Assistenz Studiengänge sieht, sonst null. */
export const assistenzKontext = (startSem) => {
	const liste = semesterAbwaerts(startSem);

	const probiere = (i) =>
		i >= liste.length
			? cy.wrap(null, { log: false })
			: getAlsAssistenz("getBenotungstoolContext", { sem_kurzbz: liste[i] }).then((response) => {
					const data = expectNotenSuccess(response, `Kontext der Assistenz in ${liste[i]}`);
					return data.studiengaenge.length ? { sem: liste[i], data } : probiere(i + 1);
				});

	return probiere(0);
};

/** -> { sem, lvId, lehreinheiten } für eine LV der Assistenz mit Lehreinheiten in der Datenbank, sonst null. */
export const assistenzLvMitLehreinheiten = (startSem) =>
	assistenzKontext(startSem).then((kontext) => {
		if (!kontext) return null;

		const { sem } = kontext;
		const studiengaenge = kontext.data.studiengaenge;

		const ersteLvMitLehreinheiten = (lvs, j) =>
			j >= lvs.length
				? cy.wrap(null, { log: false })
				: cy
						.task("noten:db:lehreinheitenDerLv", { lvId: lvs[j].lehrveranstaltung_id, semKurzbz: sem })
						.then((ids) =>
							ids.length
								? { sem, lvId: lvs[j].lehrveranstaltung_id, lehreinheiten: ids.map(String).sort() }
								: ersteLvMitLehreinheiten(lvs, j + 1),
						);

		const probiereStudiengang = (i) =>
			i >= studiengaenge.length
				? cy.wrap(null, { log: false })
				: getAlsAssistenz("getLvForStudiengang", {
						studiengang_kz: studiengaenge[i].studiengang_kz,
						sem_kurzbz: sem,
					})
						.then((response) =>
							ersteLvMitLehreinheiten(expectNotenSuccess(response, "LVs des Studiengangs"), 0),
						)
						.then((ziel) => ziel || probiereStudiengang(i + 1));

		return probiereStudiengang(0);
	});
