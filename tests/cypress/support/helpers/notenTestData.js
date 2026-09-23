/**
 * Finds the test data at runtime (ctx), and resets and seeds it in the database.
 *
 * No fixed ids: the semester, the LV, the Noten and the rule values differ per installation, so the
 * suite reads them through the API. NOTEN_SEM_KURZBZ / NOTEN_LV_ID pin the semester and the LV.
 * The Noten API has no delete endpoint on purpose, so reset and seed use cy.task (tasks/notenDb.js).
 */

import { notenApi, lektorAuth } from "../api/notenApi";
import { expectNotenSuccess } from "./notenErrors";

const BEZ_ENTSCHULDIGT = "entschuldigt";
const BEZ_NOCH_NICHT = "Noch nicht eingetragen";

// the highest index a spec uses is ctx.students[6]
const MIN_STUDENTS = 7;

let cachedCtx = null;

// --- dates ---

export const pad2 = (n) => String(n).padStart(2, "0");

export const toDateString = (date) => `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;

export const shiftDate = (dateString, days) => {
	const [y, m, d] = dateString.split("-").map(Number);
	const date = new Date(Date.UTC(y, m - 1, d));
	date.setUTCDate(date.getUTCDate() + days);
	return `${date.getUTCFullYear()}-${pad2(date.getUTCMonth() + 1)}-${pad2(date.getUTCDate())}`;
};

/** The Frist the server derives (Noten::computeFrist). ssConfig/wsConfig: NOTENEINTRAGUNGSFRIST_SS/WS. */
export const expectedFristString = (semKurzbz, ssConfig, wsConfig) => {
	const type = semKurzbz.slice(0, 2).toUpperCase();
	const year = Number(semKurzbz.slice(2, 6));
	const cfg = type === "SS" ? ssConfig : wsConfig;
	return `${pad2(cfg.day)}.${pad2(cfg.month)}.${type === "SS" ? year : year + 1}`;
};

/** ssConfig/wsConfig: NOTENEINTRAGUNGSFRIST_SS/WS from getCisConfig. */
export const fristHasPassed = (semKurzbz, ssConfig, wsConfig) => {
	const type = semKurzbz.slice(0, 2).toUpperCase();
	const year = Number(semKurzbz.slice(2, 6));
	if (!["SS", "WS"].includes(type) || !year) return false;
	const cfg = type === "SS" ? ssConfig : wsConfig;
	const frist = new Date(type === "SS" ? year : year + 1, cfg.month - 1, cfg.day, 23, 59, 59);
	return new Date() > frist;
};

/**
 * A semester and LV where the Lektor teaches AND the Frist has passed.
 *
 * Both are necessary: assertLvAccess runs BEFORE the Frist check, so in a semester without teaching
 * the request fails there and never reaches the Frist. Seeder group benotungstool_fixture_erweitert
 * provides the Sommersemester.
 *
 * @param {string} type "SS" or "WS"
 * @returns {Cypress.Chainable<{semKurzbz: string, lvId: number}|null>}
 */
export const teachingSemesterWithExpiredFrist = (type = "SS", ssConfig, wsConfig) => {
	const year = new Date().getFullYear();
	const candidates = [];
	for (let y = year; y >= year - 6; y -= 1) {
		const semKurzbz = `${type}${y}`;
		if (fristHasPassed(semKurzbz, ssConfig, wsConfig)) candidates.push(semKurzbz);
	}

	const trySemester = (i) => {
		if (i >= candidates.length) return cy.wrap(null, { log: false });

		return notenApi.getBenotungstoolContext(candidates[i]).then((response) => {
			const lvs = (response.body && response.body.data && response.body.data.lehrveranstaltungen) || [];
			if (lvs.length > 0) {
				return { semKurzbz: candidates[i], lvId: lvs[0].lehrveranstaltung_id };
			}
			return trySemester(i + 1);
		});
	};

	return trySemester(0);
};

// --- discovery ---

const resolveSemester = () => {
	const configured = Cypress.env("NOTEN_SEM_KURZBZ");
	if (configured) return cy.wrap(configured, { log: false });

	return cy
		.request({
			method: "GET",
			url: "/index.ci.php/api/frontend/v1/organisation/Studiensemester/getAll",
			qs: { order: "DESC" },
			auth: lektorAuth(),
			failOnStatusCode: false,
		})
		.then((response) => {
			const semesters = expectNotenSuccess(response, "Studiensemester/getAll");
			const today = toDateString(new Date());
			const active = semesters
				.filter((s) => (s.start || "").slice(0, 10) && (s.start || "").slice(0, 10) <= today)
				.sort((a, b) => (b.start || "").localeCompare(a.start || ""))[0];

			expect(active, `ein Studiensemester, das am ${today} oder davor beginnt`).to.exist;
			return active.studiensemester_kurzbz;
		});
};

const resolveLehrveranstaltung = (semKurzbz) => {
	const configured = Cypress.env("NOTEN_LV_ID");
	if (configured) return cy.wrap(Number(configured), { log: false });

	return notenApi.getBenotungstoolContext(semKurzbz).then((response) => {
		const lvs = expectNotenSuccess(response, "getBenotungstoolContext").lehrveranstaltungen || [];
		expect(
			lvs.length,
			`Keine Lehrveranstaltung in ${semKurzbz}: der angemeldete Benutzer unterrichtet hier nichts ` +
				"(ein Admin meistens nirgends). Setze NOTEN_LV_ID.",
		).to.be.greaterThan(0);
		return lvs[0].lehrveranstaltung_id;
	});
};

/** -> { semKurzbz, lvId, cisConfig, maxAntritte, noten, notenScale, notenOptions, students, studentUids } */
export const loadNotenContext = () => {
	if (cachedCtx) return cy.wrap(cachedCtx, { log: false });

	const ctx = {};

	return resolveSemester()
		.then((semKurzbz) => {
			ctx.semKurzbz = semKurzbz;
			return resolveLehrveranstaltung(semKurzbz);
		})
		.then((lvId) => {
			ctx.lvId = lvId;
			return notenApi.getCisConfig();
		})
		.then((response) => {
			ctx.cisConfig = expectNotenSuccess(response, "getCisConfig");
			ctx.maxAntritte = ctx.cisConfig.CIS_GESAMTNOTE_MAX_ANTRITTE;
			return notenApi.getNoten();
		})
		.then((response) => {
			const noten = expectNotenSuccess(response, "getNoten");
			const byBezeichnung = (bez) => noten.find((n) => n.bezeichnung === bez);

			const entschuldigt = byBezeichnung(BEZ_ENTSCHULDIGT);
			const nochNicht = byBezeichnung(BEZ_NOCH_NICHT);

			expect(entschuldigt, `tbl_note braucht die Bezeichnung "${BEZ_ENTSCHULDIGT}"`).to.exist;
			expect(nochNicht, `tbl_note braucht die Bezeichnung "${BEZ_NOCH_NICHT}"`).to.exist;

			// Only the 1..5 scale: the Lehre Noten also contain 0 ("Teilnote"), which is no result of an assessment.
			const specialPks = [entschuldigt.note, nochNicht.note];
			const usable = noten.filter((n) => n.lehre && !specialPks.includes(n.note));
			const ordinary = usable
				.map((n) => n.note)
				.filter((note) => Number(note) >= 1 && Number(note) <= 5)
				.sort((a, b) => Number(a) - Number(b));

			ctx.notenScale =
				ordinary.length > 1
					? ordinary
					: usable
							.map((n) => n.note)
							.filter((n) => Number(n) !== 0)
							.sort((a, b) => Number(a) - Number(b));

			expect(
				ctx.notenScale.length,
				"die Skala braucht zwei Noten, sonst ist keine Änderung prüfbar",
			).to.be.greaterThan(1);

			// an administrative Note that the editor list leaves out ("intern angerechnet" / "nicht zugelassen")
			const notLehre = noten.find((n) => n.lehre === false);

			// as a Zeugnisnote, this Note locks the LV-Note, in the client and in the server
			const notUeberschreibbar = noten.find((n) => n.lkt_ueberschreibbar === false);

			// an Anrechnung blocks every Pruefung of the LV; the Zeugnisnote decides
			const angerechnet = byBezeichnung("angerechnet");
			const internAngerechnet = byBezeichnung("intern angerechnet");

			// A test picks a Note by meaning, not by index: a positive Note closes the Antritt chain,
			// so a repeat after "Sehr Gut" is not a test case but an impossible flow.
			const abschliessend = (ctx.cisConfig.NOTEN_ABSCHLIESSEND || []).map(String);
			const inScale = (n) => Number(n.note) >= 1 && Number(n.note) <= 5;
			const negativNoten = usable.filter((n) => !n.positiv && inScale(n)).map((n) => n.note);
			const positivNoten = usable.filter((n) => n.positiv && inScale(n)).map((n) => n.note);
			const verbesserbar = positivNoten.filter((n) => !abschliessend.includes(String(n)));

			expect(negativNoten.length, "ohne negative Note ist keine Antrittskette möglich").to.be.greaterThan(0);

			ctx.noten = {
				// the only Note that allows a repeat
				negativ: negativNoten[0],
				// positive but not final: allows a repeat only with CIS_GESAMTNOTE_NOTENVERBESSERUNG
				positiv: verbesserbar.length ? verbesserbar[0] : null,
				// always closes the chain (NOTEN_ABSCHLIESSEND)
				bestnote: positivNoten.find((n) => abschliessend.includes(String(n))) || null,
				entschuldigt: entschuldigt.note,
				nochNichtEingetragen: nochNicht.note,
				notLehre: notLehre ? notLehre.note : null,
				notUeberschreibbar: notUeberschreibbar ? notUeberschreibbar.note : null,
				angerechnet: angerechnet ? angerechnet.note : null,
				internAngerechnet: internAngerechnet ? internAngerechnet.note : null,
			};

			// the UI specs select and read a Note by its Bezeichnung, not by its id
			ctx.notenOptions = noten;

			return notenApi.getStudentenNoten(ctx.lvId, ctx.semKurzbz);
		})
		.then((response) => {
			const data = expectNotenSuccess(response, `getStudentenNoten(${ctx.lvId})`);
			const students = data.students;

			expect(
				students.length,
				`LV ${ctx.lvId} hat ${students.length} Studierende, die Suite braucht ${MIN_STUDENTS}. Starte: npm run noten:check`,
			).to.be.at.least(MIN_STUDENTS);
			students.forEach((s) => expect(s.lehreinheit_id, `lehreinheit_id von ${s.uid}`).to.exist);

			ctx.students = students;
			ctx.studentUids = students.map((s) => s.uid);

			cachedCtx = ctx;
			return ctx;
		});
};

// --- database ---

let dbConnection = null;

/** Fails the test with the reason if the suite cannot reach the database. Call it in before or beforeEach. */
export const requireDbReset = () => {
	const status = dbConnection
		? cy.wrap(dbConnection, { log: false })
		: cy.task("noten:db:checkConnection", null, { log: false }).then((result) => (dbConnection = result));

	return status.then((result) => {
		expect(
			result.available,
			`Fixture-Reset nicht verfügbar: ${result.reason}\n\n` +
				"Die Suite braucht eine Datenbankverbindung: NOTEN_DB_* in tests/cypress/suites/.env.",
		).to.be.true;
	});
};

let cachedAuthUid = null;

/**
 * The uid of NOTEN_LEKTOR_USER as the database stores it, for mitarbeiter_uid / freigabevon_uid.
 * Not Cypress.env("NOTEN_LEKTOR_USER"): LDAP accepts "Demolektor1", but tbl_benutzer.uid is
 * "demolektor1", and the foreign keys are case sensitive.
 */
export const readAuthUid = () => {
	if (cachedAuthUid) return cy.wrap(cachedAuthUid, { log: false });

	return cy
		.request({
			method: "GET",
			url: "/index.ci.php/api/frontend/v1/AuthInfo/getAuthUID",
			auth: lektorAuth(),
			failOnStatusCode: false,
		})
		.then((response) => {
			const uid = response.body?.data?.uid;
			expect(uid, "uid von AuthInfo/getAuthUID").to.be.a("string").and.not.be.empty;
			cachedAuthUid = uid;
			return uid;
		});
};

/** Runs a seed task in the test LV, with NOTEN_LEKTOR_USER as mitarbeiter_uid. */
const seedTask = (task, ctx, studentUid, payload) =>
	readAuthUid().then((uid) =>
		cy.task(task, { lvId: ctx.lvId, semKurzbz: ctx.semKurzbz, studentUid, mitarbeiterUid: uid, ...payload }),
	);

/** Deletes the LV-Noten, Pruefungen and Zeugnisnoten of the students in the test LV. */
export const resetNotenState = (ctx, studentUids) =>
	cy.task("noten:db:reset", {
		lvId: ctx.lvId,
		semKurzbz: ctx.semKurzbz,
		studentUids: studentUids || ctx.studentUids,
	});

/**
 * The baseline for Antritt 1: an LV-Note PLUS its Pruefung row, the same as a Freigabe creates.
 * Without that row the LV-Note counts only while no Pruefung exists, and the next added Pruefung
 * would replace Antritt 1 instead of becoming Antritt 2.
 *
 * Options: note, punkte, benotungsdatum, freigegeben, freigabedatum, erstantritt.
 * `erstantritt: false` leaves out the Pruefung row: the LV-Note then has no Antritt 1 of its own,
 * which is the state of an old Note and of a Note before its Freigabe.
 */
export const seedBaseline = (ctx, student, options = {}) => {
	expect(student, "seedBaseline braucht das Studierenden-Objekt aus ctx.students, nicht die uid").to.be.an("object");

	// A NEGATIVE Note by default: only a negative Note allows another Antritt.
	// Pass ctx.noten.bestnote to close the chain.
	const note = options.note !== undefined ? options.note : ctx.noten.negativ;
	// With CIS_GESAMTNOTE_FREIGABE_FINAL a freigegeben Note is final. So the baseline leaves the Note
	// open by default, and a test can still add Pruefungen.
	const freigegeben =
		options.freigegeben !== undefined ? options.freigegeben : ctx.cisConfig.CIS_GESAMTNOTE_FREIGABE_FINAL !== true;
	const erstantritt = options.erstantritt !== undefined ? options.erstantritt : true;

	return seedTask("noten:db:seedLvGesamtnote", ctx, student.uid, {
		note,
		punkte: options.punkte !== undefined ? options.punkte : null,
		benotungsdatum: options.benotungsdatum || baselineBenotungsdatum(ctx),
		freigegeben,
		freigabedatum: options.freigabedatum || null,
	}).then((seeded) => {
		if (!erstantritt) return cy.wrap(seeded, { log: false });

		return seedTask("noten:db:seedPruefung", ctx, student.uid, {
			lehreinheitId: student.lehreinheit_id,
			note,
			datum: baselineDate(ctx),
			type: "Termin1", // legacy Pruefungstyp of Antritt 1; no rule reads it
		}).then(() => seeded);
	});
};

/** -> { pruefungId, note, datum, type }. For Pruefung states that the API cannot build. */
export const seedPruefung = (ctx, student, { note, datum, type }) =>
	seedTask("noten:db:seedPruefung", ctx, student.uid, {
		lehreinheitId: student.lehreinheit_id,
		note,
		datum,
		type,
	});

/** Sets the Zeugnisnote. Only the StV enters it; the Benotungstool never writes it. */
export const seedZeugnisnote = (ctx, student, note) => seedTask("noten:db:seedZeugnisnote", ctx, student.uid, { note });

/**
 * Reads the LV-Note from the database: what is STORED, without the freigabedatum filter
 * of getLvGesamtNoten. The counterpart is readStateViaApi.
 */
export const readLvGesamtnoteViaDb = (ctx, student) =>
	cy.task("noten:db:readLvGesamtnote", { lvId: ctx.lvId, semKurzbz: ctx.semKurzbz, studentUid: student.uid });

/** The anchor date. Every Antritt date comes after it, so the order is known. */
export const baselineBenotungsdatum = (ctx) => `${Number(ctx.semKurzbz.slice(2, 6))}-01-10 08:00:00`;

export const baselineDate = (ctx) => baselineBenotungsdatum(ctx).slice(0, 10);

/** antrittDate(ctx, 1) < antrittDate(ctx, 2) < ..., all after the baseline, 30 days apart. */
export const antrittDate = (ctx, index) => shiftDate(baselineDate(ctx), 30 * index);
