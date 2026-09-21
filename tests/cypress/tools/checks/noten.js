/**
 * Fixture preconditions for the Gesamtnoteneingabe suite. Data only -- dbCheck.js runs it.
 *
 * Every precondition of the chain
 *   getBenotungstoolContext -> getStudentenNoten -> saveStudentPruefung
 * is its own query, so a failure names the exact missing link instead of "no students".
 */

const path = require("path");

// tests/cypress/tools/checks -> repo root is four levels up
const SEEDER = path.join(
	__dirname,
	"..",
	"..",
	"..",
	"..",
	"system",
	"seeders",
	"TST60873_000_GesamtnoteneingabeCis4.sql",
);

/** Test semester: the running one, else the last started. Same rule as the suite and seeder group benotungstool_noten. */
const SEM = `(SELECT studiensemester_kurzbz FROM public.tbl_studiensemester
               WHERE start <= now() ORDER BY (ende >= now()) DESC, start DESC LIMIT 1)`;

module.exports = {
	title: "Gesamtnoteneingabe",

	sqlFiles: {
		// the idempotent fixture groups; studiengang_5 inserts without ON CONFLICT and needs a rebuild
		seed: ["benotungstool_noten", "benotungstool_berechtigungen", "benotungstool_fixture_erweitert"].map(
			(group) => ({ file: SEEDER, group }),
		),
	},

	checks: [
		{
			label: "Test semester (latest started)",
			sql: `SELECT ${SEM} AS value`,
			ok: (r) => Boolean(r.value),
			hint: "No studiensemester with start <= now(). Apply the base dump + BS003_kompetenzfeld.sql.",
		},
		{
			label: "demolektor1 exists",
			sql: `SELECT COUNT(*)::int AS value FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid = 'demolektor1'`,
			ok: (r) => r.value > 0,
			hint: "Apply BS002_mitarbeiter.sql.",
		},
		{
			label: "STG5 Lehrveranstaltungen (5121 / 5221)",
			sql: `SELECT COUNT(*)::int AS value FROM lehre.tbl_lehrveranstaltung
			       WHERE lehrveranstaltung_id IN (5121, 5221)`,
			ok: (r) => r.value === 2,
			hint: "Rebuild the database. Seeder group studiengang_5 creates them.",
		},
		{
			label: "Lehreinheiten of demolektor1 in the test semester",
			sql: `SELECT COUNT(*)::int AS value
			        FROM lehre.tbl_lehreinheit le
			        JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)
			       WHERE mitarbeiter_uid = 'demolektor1' AND le.studiensemester_kurzbz = ${SEM}`,
			ok: (r) => r.value > 0,
			hint:
				"demolektor1 teaches nothing in the CURRENT test semester. Seeder group studiengang_5 pins its" +
				"Lehreinheiten to the semester that was active when it ran. Apply seeder group benotungstool_noten.",
		},
		{
			label: "Lehreinheitgruppe rows with a non-NULL gruppe_kurzbz",
			sql: `SELECT COUNT(*)::int AS value FROM lehre.tbl_lehreinheitgruppe
			       WHERE gruppe_kurzbz IS NOT NULL`,
			ok: (r) => r.value > 0,
			hint:
				"campus.vw_student_lehrveranstaltung joins ONLY on gruppe_kurzbz. Lehrverband-style rows " +
				"(verband set, gruppe_kurzbz NULL) never match. Apply seeder group benotungstool_noten.",
		},
		{
			label: "Group memberships carrying a studiensemester",
			sql: `SELECT COUNT(*)::int AS value FROM public.tbl_benutzergruppe
			       WHERE studiensemester_kurzbz IS NOT NULL`,
			ok: (r) => r.value > 0,
			hint:
				"tbl_benutzergruppe.studiensemester_kurzbz must equal tbl_lehreinheit.studiensemester_kurzbz. " +
				"NULL never matches. Apply seeder group benotungstool_noten.",
		},
		{
			label: "Students visible in LV 5221 (the test LV)",
			sql: `SELECT COUNT(*)::int AS value FROM campus.vw_student_lehrveranstaltung
			       WHERE lehrveranstaltung_id = 5221 AND studiensemester_kurzbz = ${SEM}`,
			ok: (r) => r.value >= 3,
			hint: "The suite needs at least 3 enrolled students. Apply seeder group benotungstool_noten.",
		},
		{
			label: "tbl_note 'entschuldigt' (resolved by Bezeichnung)",
			sql: `SELECT COALESCE((SELECT note::text FROM lehre.tbl_note
			                        WHERE bezeichnung = 'entschuldigt'), '') AS value`,
			ok: (r) => r.value !== "",
			hint: "Noten.php resolves this by Bezeichnung, not by the config PK. Apply the base inserts.",
		},
		{
			label: "tbl_note 'Noch nicht eingetragen'",
			sql: `SELECT COALESCE((SELECT note::text FROM lehre.tbl_note
			                        WHERE bezeichnung = 'Noch nicht eingetragen'), '') AS value`,
			ok: (r) => r.value !== "",
			hint: "Missing from the base dump. Rebuild the database with db_setup.sh.",
		},
		{
			label: "Pruefungstypen Termin1/Termin2/Termin3/kommPruef",
			sql: `SELECT COALESCE(string_agg(pruefungstyp_kurzbz, ',' ORDER BY pruefungstyp_kurzbz), '') AS value
			        FROM lehre.tbl_pruefungstyp
			       WHERE pruefungstyp_kurzbz IN ('Termin1','Termin2','Termin3','kommPruef')`,
			ok: (r) => r.value.includes("Termin1") && r.value.includes("Termin2"),
			hint:
				"Termin3 is absent from the base dump. Harmless while CIS_GESAMTNOTE_PRUEFUNG_TERMIN3 is " +
				"off, but a Termin3 insert would hit a foreign key error. Seeder group benotungstool_noten adds it.",
		},
		{
			label: "Notenschluessel assigned to LV 5221",
			sql: `SELECT COUNT(*)::int AS value
			        FROM lehre.tbl_notenschluesselzuordnung z
			        JOIN lehre.tbl_notenschluesselaufteilung a USING (notenschluessel_kurzbz)
			       WHERE z.lehrveranstaltung_id = 5221`,
			ok: (r) => r.value > 0,
			hint:
				"The Notenschluessel tables are empty in the base dump, so getNoteByPunkte always returns " +
				"null. Apply seeder group benotungstool_noten.",
		},
		{
			label: "Students in the second Lehreinheit of LV 5221",
			sql: `SELECT COUNT(*)::int AS value FROM campus.vw_student_lehrveranstaltung
			       WHERE lehreinheit_id = 51103 AND studiensemester_kurzbz = ${SEM}`,
			ok: (r) => r.value > 0,
			hint: "The Lehreinheit check of the write paths needs it. Apply seeder group benotungstool_fixture_erweitert.",
		},
		{
			label: "Lektoren of Lehreinheit 51103",
			sql: "SELECT COUNT(*)::int AS value FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id = 51103",
			ok: (r) => r.value >= 2,
			hint: "The grader rules need two Lektoren. Apply seeder group benotungstool_fixture_erweitert.",
		},
		{
			label: "Sommersemester after its deadline, taught by demolektor1",
			sql: `SELECT COALESCE((SELECT le.studiensemester_kurzbz
			                         FROM lehre.tbl_lehreinheit le
			                         JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id)
			                        WHERE mitarbeiter_uid = 'demolektor1'
			                          AND le.studiensemester_kurzbz ~ '^SS[0-9]{4}$'
			                          AND make_date(substring(le.studiensemester_kurzbz FROM 3 FOR 4)::int, 11, 15) < current_date
			                        ORDER BY le.studiensemester_kurzbz DESC LIMIT 1), '') AS value`,
			ok: (r) => r.value !== "",
			hint: "The deadline tests need it. Apply seeder group benotungstool_fixture_erweitert.",
		},
		{
			label: "Texts of the Vorlagen Notenfreigabe and Sancho_Mail_Template",
			sql: `SELECT COUNT(DISTINCT vorlage_kurzbz)::int AS value FROM public.tbl_vorlagestudiengang
			       WHERE vorlage_kurzbz IN ('Notenfreigabe', 'Sancho_Mail_Template') AND aktiv
			         AND COALESCE(text, '') <> ''`,
			ok: (r) => r.value === 2,
			hint: "Without both texts every release mail has an empty body. Apply seeder group benotungstool_fixture_erweitert.",
		},
	],
};
