/**
 * The cy.task functions of the noten suite. Connection and guards come from
 * db.js; this file only knows which rows the specs write and how to undo them.
 *
 * Needed because the Noten API has no delete endpoint, while every test writes
 * campus.tbl_lvgesamtnote and lehre.tbl_pruefung. Without a reset the suite is single-shot.
 *
 * Every statement is scoped by an explicit student_uid list plus the test LV and semester.
 * No unscoped DELETE in this file.
 */

const { assertWritable, dbConfigured, withClient, inTransaction, checkConnection } = require("./db");

const REQUIRED_SCOPE = ["lvId", "semKurzbz", "studentUids"];

const assertScope = (scope, keys = REQUIRED_SCOPE) => {
	if (!scope || typeof scope !== "object") throw new Error("noten:db scope object is required");

	keys.forEach((key) => {
		if (scope[key] === undefined || scope[key] === null || scope[key] === "") {
			throw new Error(`noten:db refused - missing scope key "${key}"`);
		}
	});

	if (!keys.includes("studentUids")) return;

	if (!Array.isArray(scope.studentUids) || scope.studentUids.length === 0) {
		throw new Error("noten:db refused - studentUids must be a non-empty array");
	}
	if (scope.studentUids.some((uid) => typeof uid !== "string" || uid.trim() === "")) {
		throw new Error("noten:db refused - studentUids must all be non-empty strings");
	}
};

const reset = async (scope) => {
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
 * Seeds the LV-Note of the Antritt-1 baseline. `freigegeben` sets the freigabedatum, and the
 * Freigabe state compares it with the benotungsdatum.
 */
const seedLvGesamtnote = async (scope) => {
	assertWritable();
	assertScope({ ...scope, studentUids: [scope.studentUid] });

	const {
		lvId,
		semKurzbz,
		studentUid,
		note,
		punkte = null,
		mitarbeiterUid,
		benotungsdatum,
		freigegeben = false,
		freigabedatum = null,
	} = scope;

	if (note === undefined || note === null) throw new Error("noten:db seedLvGesamtnote - note is required");
	if (!benotungsdatum) throw new Error("noten:db seedLvGesamtnote - benotungsdatum is required");
	if (!mitarbeiterUid) throw new Error("noten:db seedLvGesamtnote - mitarbeiterUid is required");

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
				studentUid,
				lvId,
				semKurzbz,
				note,
				punkte,
				mitarbeiterUid,
				benotungsdatum,
				resolvedFreigabe,
				resolvedFreigabe ? mitarbeiterUid : null,
			],
		);

		return { studentUid, note, benotungsdatum, freigabedatum: resolvedFreigabe };
	});
};

/**
 * Seeds one Pruefung row, for states that no endpoint can produce: an entschuldigt and a real
 * Termin2 side by side, or a kommPruef (entered in a different tool).
 */
const seedPruefung = async (scope) => {
	assertWritable();
	assertScope({ ...scope, studentUids: [scope.studentUid] });

	const { lvId, semKurzbz, studentUid, lehreinheitId, note, datum, type, mitarbeiterUid } = scope;

	if (note === undefined || note === null) throw new Error("noten:db seedPruefung - note is required");
	if (!datum) throw new Error("noten:db seedPruefung - datum is required");
	if (!type) throw new Error("noten:db seedPruefung - type is required");
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
			throw new Error(
				`noten:db seedPruefung refused - lehreinheit ${lehreinheitId} is outside ${lvId}/${semKurzbz}`,
			);
		}

		const res = await client.query(
			`INSERT INTO lehre.tbl_pruefung
			     (lehreinheit_id, student_uid, mitarbeiter_uid, note, pruefungstyp_kurzbz, datum,
			      anmerkung, insertamum, insertvon)
			 VALUES ($1, $2, $3, $4, $5, $6, '', NOW(), $3)
			 RETURNING pruefung_id`,
			[lehreinheitId, studentUid, mitarbeiterUid, note, type, datum],
		);

		return { pruefungId: res.rows[0].pruefung_id, note, datum, type };
	});
};

/**
 * Seeds the Zeugnisnote. Only the StV writes it; no endpoint of the Benotungstool does.
 * It decides two rules: an Anrechnung blocks every Pruefung, and lkt_ueberschreibbar locks the LV-Note.
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

const LV_SCOPE = ["lvId", "semKurzbz"];

const assertReadable = () => {
	if (!dbConfigured()) throw new Error("noten:db refused - database is not configured");
};

const readRows = async (sql, params) => withClient(async (client) => (await client.query(sql, params)).rows);

/** Same query as Lehrveranstaltung_model::getLeIdsByStudent. Empty = no participant. */
const readLehreinheitenOfStudent = async (scope) => {
	assertReadable();
	assertScope({ ...scope, studentUids: [scope.studentUid] });

	const rows = await readRows(
		`SELECT DISTINCT lehreinheit_id FROM campus.vw_student_lehrveranstaltung
		  WHERE uid = $1 AND lehrveranstaltung_id = $2 AND studiensemester_kurzbz = $3
		  ORDER BY lehreinheit_id`,
		[scope.studentUid, scope.lvId, scope.semKurzbz],
	);
	return rows.map((r) => r.lehreinheit_id);
};

/** Same joins as Lehreinheit_model::getLehreinheitenForLv. */
const readLehreinheitenOfLv = async (scope) => {
	assertReadable();
	assertScope(scope, LV_SCOPE);

	const rows = await readRows(
		`SELECT DISTINCT le.lehreinheit_id FROM lehre.tbl_lehreinheit le
		   JOIN lehre.tbl_lehreinheitgruppe leg USING (lehreinheit_id)
		   JOIN public.tbl_studiengang stg ON stg.studiengang_kz = leg.studiengang_kz
		  WHERE le.lehrveranstaltung_id = $1 AND le.studiensemester_kurzbz = $2
		  ORDER BY 1`,
		[scope.lvId, scope.semKurzbz],
	);
	return rows.map((r) => r.lehreinheit_id);
};

/** A Lehreinheit of another LV in the same semester, or null. */
const readForeignLehreinheit = async (scope) => {
	assertReadable();
	assertScope(scope, LV_SCOPE);

	const rows = await readRows(
		`SELECT lehreinheit_id, lehrveranstaltung_id FROM lehre.tbl_lehreinheit
		  WHERE studiensemester_kurzbz = $1 AND lehrveranstaltung_id <> $2
		  ORDER BY lehreinheit_id LIMIT 1`,
		[scope.semKurzbz, scope.lvId],
	);
	return rows[0] || null;
};

/** An LV of the semester that the uid does not teach, or null. Same condition as getLektorIsTeachingLva. */
const readForeignLv = async ({ semKurzbz, uid } = {}) => {
	assertReadable();
	if (!semKurzbz || !uid) throw new Error("noten:db readForeignLv - semKurzbz and uid are required");

	const rows = await readRows(
		`SELECT le.lehrveranstaltung_id FROM lehre.tbl_lehreinheit le
		  WHERE le.studiensemester_kurzbz = $1
		    AND le.lehrveranstaltung_id NOT IN (
		        SELECT t.lehrveranstaltung_id FROM lehre.tbl_lehreinheit t
		          JOIN lehre.tbl_lehreinheitmitarbeiter m USING (lehreinheit_id)
		         WHERE t.studiensemester_kurzbz = $1 AND m.mitarbeiter_uid = $2)
		  ORDER BY 1 LIMIT 1`,
		[semKurzbz, uid],
	);
	return rows[0] ? rows[0].lehrveranstaltung_id : null;
};

/** An LV and semester that the uid teaches and that has no student, or null. */
const readLvWithoutStudents = async ({ uid } = {}) => {
	assertReadable();
	if (!uid) throw new Error("noten:db readLvWithoutStudents - uid is required");

	const rows = await readRows(
		`SELECT le.lehrveranstaltung_id, le.studiensemester_kurzbz FROM lehre.tbl_lehreinheit le
		   JOIN lehre.tbl_lehreinheitmitarbeiter m USING (lehreinheit_id)
		  WHERE m.mitarbeiter_uid = $1
		    AND NOT EXISTS (
		        SELECT 1 FROM campus.vw_student_lehrveranstaltung v
		         WHERE v.lehrveranstaltung_id = le.lehrveranstaltung_id
		           AND v.studiensemester_kurzbz = le.studiensemester_kurzbz)
		  ORDER BY le.studiensemester_kurzbz DESC LIMIT 1`,
		[uid],
	);
	return rows[0] ? { lvId: rows[0].lehrveranstaltung_id, semKurzbz: rows[0].studiensemester_kurzbz } : null;
};

/** An active student without any Lehreinheit in the LV, or null. */
const readNonParticipant = async (scope) => {
	assertReadable();
	assertScope(scope, LV_SCOPE);

	const rows = await readRows(
		`SELECT s.student_uid FROM public.tbl_student s
		   JOIN public.tbl_benutzer b ON b.uid = s.student_uid
		  WHERE b.aktiv AND NOT EXISTS (
		        SELECT 1 FROM campus.vw_student_lehrveranstaltung v
		         WHERE v.uid = s.student_uid AND v.lehrveranstaltung_id = $1 AND v.studiensemester_kurzbz = $2)
		  ORDER BY s.student_uid LIMIT 1`,
		[scope.lvId, scope.semKurzbz],
	);
	return rows[0] ? rows[0].student_uid : null;
};

/** The LV-Noten of the student in OTHER LVs of the semester; reset never reaches them. */
const countOtherLvNoten = async (scope) => {
	assertReadable();
	assertScope({ ...scope, studentUids: [scope.studentUid] });

	const rows = await readRows(
		`SELECT count(*)::int AS total FROM campus.tbl_lvgesamtnote
		  WHERE student_uid = $1 AND studiensemester_kurzbz = $2 AND lehrveranstaltung_id <> $3`,
		[scope.studentUid, scope.semKurzbz, scope.lvId],
	);
	return rows[0].total;
};

const hasPruefungstyp = async ({ type } = {}) => {
	assertReadable();
	if (!type) throw new Error("noten:db hasPruefungstyp - type is required");

	const rows = await readRows("SELECT 1 FROM lehre.tbl_pruefungstyp WHERE pruefungstyp_kurzbz = $1", [type]);
	return rows.length > 0;
};

/** Deletes one Pruefung outside the LV scope, for example one that old code wrote into a foreign Lehreinheit. */
const deletePruefung = async ({ pruefungId, studentUids } = {}) => {
	assertWritable();
	if (!pruefungId) throw new Error("noten:db deletePruefung - pruefungId is required");
	assertScope({ studentUids }, ["studentUids"]);

	return withClient(async (client) => {
		const res = await client.query(
			"DELETE FROM lehre.tbl_pruefung WHERE pruefung_id = $1 AND student_uid = ANY($2::varchar[])",
			[pruefungId, studentUids],
		);
		return res.rowCount;
	});
};

/** Active text of each Vorlage, newest version. The suite cannot read the mail itself. */
const readVorlagen = async ({ kurzbz } = {}) => {
	assertReadable();
	if (!Array.isArray(kurzbz) || !kurzbz.length) throw new Error("noten:db readVorlagen - kurzbz list is required");

	return readRows(
		`SELECT DISTINCT ON (vorlage_kurzbz) vorlage_kurzbz, text FROM public.tbl_vorlagestudiengang
		  WHERE vorlage_kurzbz = ANY($1::varchar[]) AND aktiv
		  ORDER BY vorlage_kurzbz, version DESC`,
		[kurzbz],
	);
};

/** Lektoren of a Lehreinheit, sorted by uid like Noten::lektorenOfLehreinheit (strcmp). */
const readLektorenOfLehreinheit = async ({ lehreinheitId } = {}) => {
	assertReadable();
	if (!lehreinheitId) throw new Error("noten:db readLektorenOfLehreinheit - lehreinheitId is required");

	const rows = await readRows(
		`SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter
		  WHERE lehreinheit_id = $1 ORDER BY mitarbeiter_uid COLLATE "C"`,
		[lehreinheitId],
	);
	return rows.map((r) => r.mitarbeiter_uid);
};

/** The mitarbeiter_uid of one Pruefung row, or null. */
const readPruefungMitarbeiter = async ({ pruefungId, studentUid } = {}) => {
	assertReadable();
	if (!pruefungId || !studentUid)
		throw new Error("noten:db readPruefungMitarbeiter - pruefungId and studentUid are required");

	const rows = await readRows(
		"SELECT mitarbeiter_uid FROM lehre.tbl_pruefung WHERE pruefung_id = $1 AND student_uid = $2",
		[pruefungId, studentUid],
	);
	return rows[0] ? rows[0].mitarbeiter_uid : null;
};

const registerNotenDbTasks = (on) => {
	on("task", {
		"noten:db:checkConnection": () => checkConnection(),
		"noten:db:reset": reset,
		"noten:db:seedLvGesamtnote": seedLvGesamtnote,
		"noten:db:seedPruefung": seedPruefung,
		"noten:db:seedZeugnisnote": seedZeugnisnote,
		"noten:db:readLvGesamtnote": readLvGesamtnote,
		"noten:db:readLehreinheitenOfStudent": readLehreinheitenOfStudent,
		"noten:db:readLehreinheitenOfLv": readLehreinheitenOfLv,
		"noten:db:readForeignLehreinheit": readForeignLehreinheit,
		"noten:db:readForeignLv": readForeignLv,
		"noten:db:readLvWithoutStudents": readLvWithoutStudents,
		"noten:db:readNonParticipant": readNonParticipant,
		"noten:db:countOtherLvNoten": countOtherLvNoten,
		"noten:db:hasPruefungstyp": hasPruefungstyp,
		"noten:db:deletePruefung": deletePruefung,
		"noten:db:readVorlagen": readVorlagen,
		"noten:db:readLektorenOfLehreinheit": readLektorenOfLehreinheit,
		"noten:db:readPruefungMitarbeiter": readPruefungMitarbeiter,
	});
};

module.exports = { registerNotenDbTasks };
