<?php
if (! defined('DB_NAME')) exit('No direct script access allowed');

// lehre.vw_stundenplandev erweitern
if (!$result = @$db->db_query("SELECT mitarbeiter_kurzbz FROM lehre.vw_stundenplandev LIMIT 1"))
{
	$qry = "
		CREATE OR REPLACE VIEW lehre.vw_stundenplandev
					(stundenplandev_id, unr, uid, lehreinheit_id, lehrfach_id, datum, stunde, ort_kurzbz, studiengang_kz,
					 semester, verband, gruppe, gruppe_kurzbz, titel, anmerkung, fix, lehrveranstaltung_id, stg_kurzbz,
					 stg_kurzbzlang, stg_bezeichnung, stg_typ, fachbereich_kurzbz, lehrfach, lehrfach_bez, farbe, lehrform,
					 lektor, updateamum, updatevon, insertamum, insertvon, anmerkung_lehreinheit, mitarbeiter_kurzbz)
		AS
		SELECT tbl_stundenplandev.stundenplandev_id,
			tbl_stundenplandev.unr,
			tbl_stundenplandev.mitarbeiter_uid                                 AS uid,
			tbl_stundenplandev.lehreinheit_id,
			tbl_lehreinheit.lehrfach_id,
			tbl_stundenplandev.datum,
			tbl_stundenplandev.stunde,
			tbl_stundenplandev.ort_kurzbz,
			tbl_stundenplandev.studiengang_kz,
			tbl_stundenplandev.semester,
			tbl_stundenplandev.verband,
			tbl_stundenplandev.gruppe,
			tbl_stundenplandev.gruppe_kurzbz,
			tbl_stundenplandev.titel,
			tbl_stundenplandev.anmerkung,
			tbl_stundenplandev.fix,
			tbl_lehreinheit.lehrveranstaltung_id,
			tbl_studiengang.kurzbz                                             AS stg_kurzbz,
			tbl_studiengang.kurzbzlang                                         AS stg_kurzbzlang,
			tbl_studiengang.bezeichnung                                        AS stg_bezeichnung,
			tbl_studiengang.typ                                                AS stg_typ,
			(SELECT tbl_fachbereich.fachbereich_kurzbz
			FROM tbl_fachbereich
			WHERE tbl_fachbereich.oe_kurzbz::text = lehrfach.oe_kurzbz::text) AS fachbereich_kurzbz,
			lehrfach.kurzbz                                                    AS lehrfach,
			lehrfach.bezeichnung                                               AS lehrfach_bez,
			lehrfach.farbe,
			tbl_lehreinheit.lehrform_kurzbz                                    AS lehrform,
			tbl_mitarbeiter.kurzbz                                             AS lektor,
			tbl_stundenplandev.updateamum,
			tbl_stundenplandev.updatevon,
			tbl_stundenplandev.insertamum,
			tbl_stundenplandev.insertvon,
			tbl_lehreinheit.anmerkung                                          AS anmerkung_lehreinheit,
			tbl_mitarbeiter.kurzbz                                             AS mitarbeiter_kurzbz
		FROM lehre.tbl_stundenplandev
			JOIN tbl_studiengang USING (studiengang_kz)
			JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
			JOIN lehre.tbl_lehrveranstaltung lehrfach ON tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id
			JOIN tbl_mitarbeiter USING (mitarbeiter_uid)
			JOIN tbl_benutzer ON mitarbeiter_uid = uid
			JOIN tbl_person USING(person_id);
		";
	
	if (!$db->db_query($qry))
		echo '<strong>lehre.vw_stundenplandev: ' . $db->db_last_error() . '</strong><br />';
	else
		echo 'lehre.vw_stundenplandev: Neue Spalte mitarbeiter_kurzbz hinzugefuegt<br />';
}

// lehre.vw_stundenplan erweitern
if (!$result = @$db->db_query("SELECT mitarbeiter_kurzbz FROM lehre.vw_stundenplan LIMIT 1"))
{
	$qry = "CREATE OR REPLACE VIEW lehre.vw_stundenplan AS
			SELECT
				tbl_stundenplan.stundenplan_id, tbl_stundenplan.unr, tbl_stundenplan.mitarbeiter_uid AS uid,
				tbl_stundenplan.lehreinheit_id, tbl_lehreinheit.lehrfach_id AS lehrfach_id, tbl_stundenplan.datum,
				tbl_stundenplan.stunde, tbl_stundenplan.ort_kurzbz, tbl_stundenplan.studiengang_kz,
				tbl_stundenplan.semester, tbl_stundenplan.verband, tbl_stundenplan.gruppe, tbl_stundenplan.gruppe_kurzbz,
				tbl_stundenplan.titel, tbl_stundenplan.anmerkung, tbl_stundenplan.fix, tbl_lehreinheit.lehrveranstaltung_id,
				tbl_studiengang.kurzbz AS stg_kurzbz, tbl_studiengang.kurzbzlang AS stg_kurzbzlang,
				tbl_studiengang.bezeichnung AS stg_bezeichnung, tbl_studiengang.typ AS stg_typ,
				(SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE oe_kurzbz=lehrfach.oe_kurzbz) as fachbereich_kurzbz,
				lehrfach.kurzbz AS lehrfach, lehrfach.bezeichnung AS lehrfach_bez, lehrfach.farbe,
				tbl_lehreinheit.lehrform_kurzbz AS lehrform, tbl_mitarbeiter.kurzbz AS lektor,
				tbl_stundenplan.updateamum, tbl_stundenplan.updatevon, tbl_stundenplan.insertamum,
				tbl_stundenplan.insertvon, tbl_lehreinheit.anmerkung AS anmerkung_lehreinheit,
				tbl_mitarbeiter.kurzbz as mitarbeiter_kurzbz
			FROM lehre.tbl_stundenplan
					 JOIN public.tbl_studiengang USING (studiengang_kz)
					 JOIN lehre.tbl_lehreinheit USING (lehreinheit_id)
					 JOIN lehre.tbl_lehrveranstaltung as lehrfach ON (tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
					 JOIN public.tbl_mitarbeiter USING (mitarbeiter_uid)
					 JOIN tbl_benutzer ON mitarbeiter_uid = uid
					 JOIN tbl_person USING(person_id);";
	
	if (!$db->db_query($qry))
		echo '<strong>lehre.vw_stundenplan: ' . $db->db_last_error() . '</strong><br />';
	else
		echo 'lehre.vw_stundenplan: Neue Spalte mitarbeiter_kurzbz hinzugefuegt<br />';
}

// campus.vw_reservierung erweitern
if (!$result = @$db->db_query("SELECT mitarbeiter_kurzbz FROM campus.vw_reservierung LIMIT 1"))
{
	$qry = "CREATE OR REPLACE view campus.vw_reservierung
				(reservierung_id, ort_kurzbz, studiengang_kz, uid, stunde, datum, titel, beschreibung, semester, verband,
				gruppe, gruppe_kurzbz, stg_kurzbz, insertamum, insertvon, mitarbeiter_kurzbz)
			AS
			SELECT tbl_reservierung.reservierung_id,
				tbl_reservierung.ort_kurzbz,
				tbl_reservierung.studiengang_kz,
				tbl_reservierung.uid,
				tbl_reservierung.stunde,
				tbl_reservierung.datum,
				tbl_reservierung.titel,
				tbl_reservierung.beschreibung,
				tbl_reservierung.semester,
				tbl_reservierung.verband,
				tbl_reservierung.gruppe,
				tbl_reservierung.gruppe_kurzbz,
				tbl_studiengang.kurzbz AS stg_kurzbz,
				tbl_reservierung.insertamum,
				tbl_reservierung.insertvon,
				 tbl_mitarbeiter.kurzbz as mitarbeiter_kurzbz
			FROM campus.tbl_reservierung
				JOIN tbl_studiengang USING (studiengang_kz)
				LEFT JOIN tbl_benutzer ON tbl_reservierung.uid = tbl_benutzer.uid
				LEFT JOIN tbl_mitarbeiter ON tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid
				LEFT JOIN tbl_person USING (person_id);";
	
	if (!$db->db_query($qry))
		echo '<strong>campus.vw_reservierung: ' . $db->db_last_error() . '</strong><br />';
	else
		echo 'campus.vw_reservierung: Neue Spalte mitarbeiter_kurzbz hinzugefuegt<br />';
}

