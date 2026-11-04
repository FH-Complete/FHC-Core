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

		// a seeded Zeugnisnote can lock the LV note (lkt_ueberschreibbar), so the reset owns it too
		const zeugnisnoten = await client.query(
			`DELETE FROM lehre.tbl_zeugnisnote
			  WHERE student_uid = ANY($1::varchar[])
			    AND lehrveranstaltung_id = $2 AND studiensemester_kurzbz = $3`,
			[studentUids, lvId, semKurzbz],
		);

		return {
			deletedPruefungen: pruefungen.rowCount,
			deletedLvGesamtnoten: noten.rowCount,
			deletedZeugnisnoten: zeugnisnoten.rowCount,
		};
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

/**
 * Seeds a single Prüfung row. Needed for states no endpoint can produce: an excused and a real
 * Termin2 side by side, or a kommPruef (entered in a different tool).
 */
const seedPruefung = async (scope) => {
	assertWritable();
	assertScope({ ...scope, studentUids: [scope.studentUid] });

	const { lvId, semKurzbz, studentUid, lehreinheitId, note, datum, typ, mitarbeiterUid } = scope;

	if (note === undefined || note === null) throw new Error("noten:db seedPruefung - note is required");
	if (!datum) throw new Error("noten:db seedPruefung - datum is required");
	if (!typ) throw new Error("noten:db seedPruefung - typ is required");
	if (!lehreinheitId) throw new Error("noten:db seedPruefung - lehreinheitId is required");
	if (!mitarbeiterUid) throw new Error("noten:db seedPruefung - mitarbeiterUid is required");

	return withClient(async (client) => {
		// reset scopes through the LV's lehreinheiten, so a row outside them would be unreclaimable
		const owned = await client.query(
			`SELECT 1 FROM lehre.tbl_lehreinheit
			  WHERE lehreinheit_id = $1 AND lehrveranstaltung_id = $2 AND studiensemester_kurzbz = $3`,
			[lehreinheitId, lvId, semKurzbz],
		);
		if (!owned.rowCount) {
			throw new Error(`noten:db seedPruefung refused - lehreinheit ${lehreinheitId} is outside ${lvId}/${semKurzbz}`);
		}

		const res = await client.query(
			`INSERT INTO lehre.tbl_pruefung
			     (lehreinheit_id, student_uid, mitarbeiter_uid, note, pruefungstyp_kurzbz, datum,
			      anmerkung, insertamum, insertvon)
			 VALUES ($1, $2, $3, $4, $5, $6, '', NOW(), $3)
			 RETURNING pruefung_id`,
			[lehreinheitId, studentUid, mitarbeiterUid, note, typ, datum],
		);

		return { pruefungId: res.rows[0].pruefung_id, note, datum, typ };
	});
};

/**
 * Seeds the transcript grade. The student administration writes it, no endpoint of this tool does.
 * It decides two rules: an Anrechnung blocks every exam, and lkt_ueberschreibbar locks the LV note.
 */
const seedZeugnisnote = async (scope) => {
	assertWritable();
	assertScope({ ...scope, studentUids: [scope.studentUid] });

	const { lvId, semKurzbz, studentUid, note, mitarbeiterUid } = scope;

	if (note === undefined || note === null) throw new Error("noten:db seedZeugnisnote - note is required");
	if (!mitarbeiterUid) throw new Error("noten:db seedZeugnisnote - mitarbeiterUid is required");

	return withClient(async (client) => {
		await client.query(
			`DELETE FROM lehre.tbl_zeugnisnote
			  WHERE student_uid = $1 AND lehrveranstaltung_id = $2 AND studiensemester_kurzbz = $3`,
			[studentUid, lvId, semKurzbz],
		);

		await client.query(
			`INSERT INTO lehre.tbl_zeugnisnote
			     (student_uid, lehrveranstaltung_id, studiensemester_kurzbz, note, benotungsdatum,
			      insertamum, insertvon)
			 VALUES ($1, $2, $3, $4, NOW(), NOW(), $5)`,
			[studentUid, lvId, semKurzbz, note, mitarbeiterUid],
		);

		return { studentUid, note };
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
		"noten:db:seedPruefung": (scope) => seedPruefung(scope),
		"noten:db:seedZeugnisnote": (scope) => seedZeugnisnote(scope),
		"noten:db:readLvGesamtnote": (scope) => readLvGesamtnote(scope),
		"noten:db:close": () => closeDb(),
	});
};

module.exports = { registerNotenDbTasks };
