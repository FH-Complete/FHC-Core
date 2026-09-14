-- Demo data for the Benotungstool / Gesamtnoteneingabe test suite.
--
-- campus.vw_student_lehrveranstaltung, the source of getStudentsByLv(), links students to
-- Lehreinheiten through tbl_lehreinheitgruppe.gruppe_kurzbz = tbl_benutzergruppe.gruppe_kurzbz AND
-- a matching Studiensemester.
--
-- RESULT: demolektor1 -> LV 5221 (12 students), demolektor2 -> LV 5121 (6, the foreign LV for the
-- access tests).

BEGIN;

-- Test semester: the running one, else the last started. Same rule as the suite.
CREATE TEMP TABLE sem AS
SELECT studiensemester_kurzbz AS kurzbz
  FROM public.tbl_studiensemester
 WHERE start <= now()
 ORDER BY (ende >= now()) DESC, start DESC
 LIMIT 1;

-- The two LV fixtures. Own Lehreinheiten (511xx) so the tempus fixtures 51001-51006 stay untouched.
-- members_up_to = highest person_id that joins this group.
CREATE TEMP TABLE lv AS
SELECT v.*, l.semester
  FROM (VALUES
          (true,  5221, 51101, 'GRP_CYNOTEN_H', 'Cypress Noten Haupt', 'demolektor1', 512),
          (false, 5121, 51102, 'GRP_CYNOTEN_F', 'Cypress Noten Fremd', 'demolektor2', 506)
       ) AS v(main, lv_id, le_id, grp, grp_text, lektor, members_up_to)
  JOIN lehre.tbl_lehrveranstaltung l ON l.lehrveranstaltung_id = v.lv_id;

-- The 12 students. person_id is the anchor for every derived key.
CREATE TEMP TABLE stud AS
SELECT id AS person_id, 5000 + id AS prestudent_id, 's525b' || id AS uid
  FROM generate_series(501, 512) AS g(id);

DO $$
BEGIN
	IF NOT EXISTS (SELECT 1 FROM sem) THEN
		RAISE EXCEPTION 'No Studiensemester found, the base dump is missing.';
	END IF;
	IF (SELECT count(*) FROM lv) <> 2 THEN
		RAISE EXCEPTION 'LV 5221/5121 missing, apply 013_studiengang_5.sql.';
	END IF;
END $$;

-- Termin3 is absent from the base dump; without it every Termin3 insert hits a foreign key error.
INSERT INTO lehre.tbl_pruefungstyp (pruefungstyp_kurzbz, beschreibung)
VALUES ('Termin3', '3. Termin')
    ON CONFLICT DO NOTHING;

-- ------------------------------------------------------------------------------------- students
INSERT INTO public.tbl_person (person_id, vorname, nachname, gebdatum, geschlecht, aktiv)
SELECT person_id, 'Noten', 'Student ' || (person_id - 500), '2000-01-15', 'm', true
  FROM stud
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_prestudent (prestudent_id, person_id, studiengang_kz)
SELECT prestudent_id, person_id, 5
  FROM stud
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_prestudentstatus
       (prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester)
SELECT s.prestudent_id, 'Student', sem.kurzbz, lv.semester
  FROM stud s, sem, lv
 WHERE lv.main
    ON CONFLICT DO NOTHING;

INSERT INTO public.tbl_benutzer (uid, person_id, aktiv)
SELECT uid, person_id, true
  FROM stud
    ON CONFLICT DO NOTHING;

-- matrikelnr is UNIQUE; scheme of the existing seeders is '251000' + studiengang_kz + person_id
INSERT INTO public.tbl_student
       (student_uid, matrikelnr, prestudent_id, studiengang_kz, semester, verband, gruppe)
SELECT s.uid, '2510005' || s.person_id, s.prestudent_id, 5, lv.semester, '', ''
  FROM stud s, lv
 WHERE lv.main
    ON CONFLICT DO NOTHING;

-- Lehrverband assignment; (5, <semester>, '', '') is created by 013_studiengang_5.sql.
INSERT INTO public.tbl_studentlehrverband
       (student_uid, studiensemester_kurzbz, studiengang_kz, semester, verband, gruppe)
SELECT s.uid, sem.kurzbz, 5, lv.semester, '', ''
  FROM stud s, sem, lv
 WHERE lv.main
    ON CONFLICT DO NOTHING;

-- ------------------------------------------------------------------- Lehreinheiten and Lektoren
INSERT INTO lehre.tbl_lehreinheit
       (lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrform_kurzbz,
        wochenrythmus, raumtyp, raumtypalternativ, sprache, lehrfach_id)
SELECT lv.le_id, lv.lv_id, sem.kurzbz, 'VO', 1, 'Dummy', 'Dummy', 'German', lv.lv_id
  FROM lv, sem
    ON CONFLICT DO NOTHING;

-- Fix the semester if the Lehreinheit is left over from a run in a different one.
UPDATE lehre.tbl_lehreinheit le
   SET studiensemester_kurzbz = sem.kurzbz
  FROM lv, sem
 WHERE le.lehreinheit_id = lv.le_id
   AND le.studiensemester_kurzbz IS DISTINCT FROM sem.kurzbz;

INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz)
SELECT le_id, lektor, 'Lektor'
  FROM lv
    ON CONFLICT DO NOTHING;

-- --------------------------------------------------------------------------------------- groups
INSERT INTO public.tbl_gruppe (gruppe_kurzbz, studiengang_kz, semester, bezeichnung, aktiv)
SELECT grp, 5, semester, grp_text, true
  FROM lv
    ON CONFLICT DO NOTHING;

-- The row the existing seeders lack: attach the group to the Lehreinheit with gruppe_kurzbz set.
-- The primary key is a serial, so NOT EXISTS instead of ON CONFLICT.
INSERT INTO lehre.tbl_lehreinheitgruppe (lehreinheit_id, studiengang_kz, semester, gruppe_kurzbz)
SELECT lv.le_id, 5, lv.semester, lv.grp
  FROM lv
 WHERE NOT EXISTS (SELECT 1 FROM lehre.tbl_lehreinheitgruppe g
                    WHERE g.lehreinheit_id = lv.le_id AND g.gruppe_kurzbz = lv.grp);

-- Membership WITH a semester. The primary key is (uid, gruppe_kurzbz) and excludes the semester,
-- so DO UPDATE repairs a row from an earlier run instead of duplicating it.
INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, studiensemester_kurzbz)
SELECT s.uid, lv.grp, sem.kurzbz
  FROM stud s, lv, sem
 WHERE s.person_id <= lv.members_up_to
    ON CONFLICT (uid, gruppe_kurzbz) DO UPDATE
       SET studiensemester_kurzbz = EXCLUDED.studiensemester_kurzbz;

-- -------------------------------------------------------------------------------- Notenschluessel
INSERT INTO lehre.tbl_notenschluessel (notenschluessel_kurzbz, bezeichnung)
VALUES ('CYNOTEN', 'Cypress Testnotenschluessel')
    ON CONFLICT DO NOTHING;

-- getNote() takes the row with the largest punkte value <= the points given. The 0 threshold makes
-- sure every value maps to a grade. Serial primary key, so NOT EXISTS.
INSERT INTO lehre.tbl_notenschluesselaufteilung (notenschluessel_kurzbz, note, punkte)
SELECT 'CYNOTEN', a.note, a.punkte
  FROM (VALUES (5, 0.0), (4, 51.0), (3, 64.0), (2, 77.0), (1, 90.0)) AS a(note, punkte)
 WHERE NOT EXISTS (SELECT 1 FROM lehre.tbl_notenschluesselaufteilung x
                    WHERE x.notenschluessel_kurzbz = 'CYNOTEN' AND x.punkte = a.punkte);

-- Set the semester explicitly: getKurzbzForLv() builds "... AND studiensemester_kurzbz = ? OR
-- studiensemester_kurzbz IS NULL" without parentheses, so a NULL-semester assignment would apply
-- to every Lehrveranstaltung through AND/OR precedence.
INSERT INTO lehre.tbl_notenschluesselzuordnung
       (notenschluessel_kurzbz, lehrveranstaltung_id, studiensemester_kurzbz)
SELECT 'CYNOTEN', lv.lv_id, sem.kurzbz
  FROM lv, sem
 WHERE lv.main
   AND NOT EXISTS (SELECT 1 FROM lehre.tbl_notenschluesselzuordnung z
                    WHERE z.notenschluessel_kurzbz = 'CYNOTEN'
                      AND z.lehrveranstaltung_id = lv.lv_id
                      AND z.studiensemester_kurzbz = sem.kurzbz);

COMMIT;

-- Fires if the view stays empty anyway, which is the bug this seeder exists to fix.
DO $$
DECLARE v_count integer;
BEGIN
	SELECT count(*) INTO v_count
	  FROM campus.vw_student_lehrveranstaltung v, sem
	 WHERE v.lehrveranstaltung_id = 5221 AND v.studiensemester_kurzbz = sem.kurzbz;

	IF v_count = 0 THEN
		RAISE EXCEPTION 'No students in LV 5221 (%), fixture unusable.', (SELECT kurzbz FROM sem);
	END IF;

	RAISE NOTICE 'OK: % students in LV 5221 (%)', v_count, (SELECT kurzbz FROM sem);
END $$;

SELECT (SELECT kurzbz FROM sem)                                              AS semester,
       (SELECT count(*) FROM campus.vw_student_lehrveranstaltung v, sem
         WHERE v.lehrveranstaltung_id = 5221 AND v.studiensemester_kurzbz = sem.kurzbz) AS lv5221,
       (SELECT count(*) FROM campus.vw_student_lehrveranstaltung v, sem
         WHERE v.lehrveranstaltung_id = 5121 AND v.studiensemester_kurzbz = sem.kurzbz) AS lv5121,
       (SELECT note FROM lehre.tbl_note WHERE bezeichnung = 'entschuldigt')  AS entschuldigt,
       (SELECT note FROM lehre.tbl_note WHERE bezeichnung = 'Noch nicht eingetragen') AS noch_nicht,
       (SELECT count(*) FROM lehre.tbl_notenschluesselaufteilung
         WHERE notenschluessel_kurzbz = 'CYNOTEN')                           AS notenschluessel;
