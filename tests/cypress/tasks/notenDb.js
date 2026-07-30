/**
 * cy.task implementations for the Gesamtnoteneingabe fixture. Connection and guards come from
 * db.js; this file only knows which rows the §1 specs write and how to undo them.
 *
 * Needed because the Noten API has no delete endpoint, while every §1 test writes
 * campus.tbl_lvgesamtnote and lehre.tbl_pruefung. Without a reset the suite is single-shot.
 *
 * Every statement is scoped by an explicit student_uid list plus the test LV and semester.
 * No unscoped DELETE in this file.
 */

const {
	assertWritable, dbConfigured, withClient, inTransaction, checkAvailability, closeDb,
} = require("./db");

const REQUIRED_SCOPE = ["lvId", "semKurzbz", "studentUids"];

const assertScope = (scope) => {
	if (!scope || typeof scope !== "object") throw new Error("noten:db scope object is required");

	REQUIRED_SCOPE.forEach((key) => {
		if (scope[key] === undefined || scope[key] === null || scope[key] === "") {
			throw new Error(`noten:db refused - missing scope key "${key}"`);
		}
	});

	if (!Array.isArray(scope.studentUids) || scope.studentUids.length === 0) {
		throw new Error("noten:db refused - studentUids must be a non-empty array");
	}
	if (scope.studentUids.some((uid) => typeof uid !== "string" || uid.trim() === "")) {
		throw new Error("noten:db refused - studentUids must all be non-empty strings");
	}
};

const resetStudents = async (scope) => {
	assertWritable();
	assertScope(scope);

	const { lvId, semKurzbz, studentUids } = scope;

	return inTransaction(async (client) => {
		// pruefungen hang off lehreinheiten, so scope through the LV's lehreinheiten in this semester
		const pruefungen = await client.query(
			`DELETE FROM lehre.tbl_pruefung
			  WHERE student_uid = ANY($1::varchar[])
			    AND lehreinheit_id IN (
			          SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
			           WHERE lehrveranstaltung_id = $2 AND studiensemester_kurzbz = $3)`,
			[studentUids, lvId, semKurzbz],
		);

		const noten = await client.query(
			`DELETE FROM campus.tbl_lvgesamtnote
			  WHERE student_uid = ANY($1::varchar[])
			    AND lehrveranstaltung_id = $2 AND studiensemester_kurzbz = $3`,
			[studentUids, lvId, semKurzbz],
		);

		return { deletedPruefungen: pruefungen.rowCount, deletedLvGesamtnoten: noten.rowCount };
	});
};

/**
 * Seeds the Antritt-1 baseline. `freigegeben` matters: getLvGesamtNoten() filters
 * `freigabedatum < NOW()`, so an offene note is invisible to validatePruefungAdd.
 */
const seedLvGesamtnote = async (scope) => {
	assertWritable();
	assertScope({ ...scope, studentUids: [scope.studentUid] });

	const {
		lvId, semKurzbz, studentUid, note, punkte = null, mitarbeiterUid,
		benotungsdatum, freigegeben = false, freigabedatum = null,
	} = scope;

	if (note === undefined || note === null) throw new Error("noten:db seed - note is required");
	if (!benotungsdatum) throw new Error("noten:db seed - benotungsdatum is required");
	if (!mitarbeiterUid) throw new Error("noten:db seed - mitarbeiterUid is required");

	// default the Freigabe to the benotungsdatum: earlier would read as "changed"
	const resolvedFreigabe = freigegeben ? freigabedatum || benotungsdatum : null;

	return withClient(async (client) => {
		await client.query(
			`DELETE FROM campus.tbl_lvgesamtnote
			  WHERE student_uid = $1 AND lehrveranstaltung_id = $2 AND studiensemester_kurzbz = $3`,
			[studentUid, lvId, semKurzbz],
		);

		await client.query(
			`INSERT INTO campus.tbl_lvgesamtnote
			     (student_uid, lehrveranstaltung_id, studiensemester_kurzbz, note, punkte,
			      mitarbeiter_uid, benotungsdatum, freigabedatum, freigabevon_uid, insertamum, insertvon)
			 VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, NOW(), $6)`,
			[
				studentUid, lvId, semKurzbz, note, punkte, mitarbeiterUid,
				benotungsdatum, resolvedFreigabe, resolvedFreigabe ? mitarbeiterUid : null,
			],
		);

		return { studentUid, note, benotungsdatum, freigabedatum: resolvedFreigabe };
	});
};

/** Raw row, without the `freigabedatum < NOW()` filter getLvGesamtNoten applies. */
const readLvGesamtnote = async ({ lvId, semKurzbz, studentUid }) => {
	if (!dbConfigured()) throw new Error("noten:db refused - database is not configured");

	return withClient(async (client) => {
		const res = await client.query(
			`SELECT * FROM campus.tbl_lvgesamtnote
			  WHERE student_uid = $1 AND lehrveranstaltung_id = $2 AND studiensemester_kurzbz = $3`,
			[studentUid, lvId, semKurzbz],
		);
		return res.rows[0] || null;
	});
};

const registerNotenDbTasks = (on) => {
	on("task", {
		"noten:db:available": () => checkAvailability(),
		"noten:db:reset": (scope) => resetStudents(scope),
		"noten:db:seedLvGesamtnote": (scope) => seedLvGesamtnote(scope),
		"noten:db:readLvGesamtnote": (scope) => readLvGesamtnote(scope),
		"noten:db:close": () => closeDb(),
	});
};

module.exports = { registerNotenDbTasks };
