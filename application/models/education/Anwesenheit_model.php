<?php
class Anwesenheit_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_anwesenheit';
		$this->pk = 'anwesenheit_id';
	}

	/**
	 * Laedt die Anwesenheiten in Prozent von Studierenden bei Lehrveranstaltungen
	 * Wenn die StudentUID uebergeben wird, werden alle Lehrveranstaltungen zu denen der Studierenden zugeteilt ist inkl Prozent der Anwesenheit
	 * Wenn die LehrveranstaltungID uebergeben wird, werden alle Studierenden geholt die zugeteilt sind inkl Prozent der Anwesenheit
	 * Es werden pro Student die Anwesenheiten berechnet aufgrund der Lehreinheit zu der sie zugeordnet sind
	 *
	 * @param string					$studiensemester_kurzbz
	 * @param string|null				(optional) $student_uid
	 * @param integer|null				(optional) $lehrveranstaltung_id
	 *
	 * @return stdClass
	 */
	public function loadAnwesenheitStudiensemester($studiensemester_kurzbz, $student_uid = null, $lehrveranstaltung_id = null)
	{
		$this->addSelect("vorname");
		$this->addSelect("nachname");
		$this->addSelect("wahlname");
		$this->addSelect("lehrveranstaltung_id");
		$this->addSelect("bezeichnung");
		$this->addSelect("gruppe");
		$this->addSelect("student_uid AS uid");
		$this->addSelect("COUNT(stundenplan_id) AS gesamtstunden");
		$this->addSelect("COALESCE(anwesend.summe, 0) AS anwesend");
		$this->addSelect("COALESCE(nichtanwesend.summe, 0) AS nichtanwesend");
		$this->addSelect("COALESCE(anwesend.summe, 0) + COALESCE(nichtanwesend.summe, 0) AS erfassteanwesenheit");
		$this->addSelect("CASE 
			WHEN COUNT(stundenplan_id) = 0 OR COALESCE(anwesend.summe, 0) + COALESCE(nichtanwesend.summe, 0) = 0 
			THEN 100 
			ELSE TRUNC(100-(100/COUNT(stundenplan_id)*COALESCE(nichtanwesend.summe, 0)), 2)
		END AS prozent");


		$this->db->join("(
			SELECT 
				semester::text AS gruppe, 
				public.tbl_studentlehrverband.studiensemester_kurzbz, 
				student_uid, 
				studiengang_kz
			FROM public.tbl_studentlehrverband
			WHERE studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz) . "

			UNION

			SELECT 
				semester || verband AS gruppe, 
				public.tbl_studentlehrverband.studiensemester_kurzbz, 
				student_uid, 
				studiengang_kz
			FROM public.tbl_studentlehrverband
			WHERE studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz) . "

			UNION

			SELECT 
				semester || verband || gruppe AS gruppe, 
				public.tbl_studentlehrverband.studiensemester_kurzbz, 
				student_uid, 
				studiengang_kz
			FROM public.tbl_studentlehrverband
			WHERE studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz) . "

			UNION

			SELECT 
				gruppe_kurzbz AS gruppe, 
				public.tbl_benutzergruppe.studiensemester_kurzbz, 
				uid AS student_uid, 
				studiengang_kz
			FROM public.tbl_benutzergruppe
			JOIN public.tbl_gruppe USING (gruppe_kurzbz)
			WHERE studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz) . "
		) a", "gruppe,studiensemester_kurzbz,studiengang_kz", "", false);
		$this->addJoin("public.tbl_benutzer b", "b.uid = student_uid");
		$this->addJoin("public.tbl_person p", "person_id");
		$this->db->join("(
			SELECT
				lehrveranstaltung_id, 
				studiensemester_kurzbz, uid AS student_uid, 
				SUM(einheiten) AS summe
			FROM campus.tbl_anwesenheit a
			JOIN lehre.tbl_lehreinheit le USING (lehreinheit_id)
			JOIN lehre.tbl_lehrveranstaltung lv USING (lehrveranstaltung_id)
			WHERE anwesend = TRUE 
				AND studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz) . "
			GROUP BY
				lehrveranstaltung_id, 
				bezeichnung, 
				uid, 
				studiensemester_kurzbz
		) anwesend", "lehrveranstaltung_id,student_uid,studiensemester_kurzbz", "LEFT", false);
		$this->db->join("(
			SELECT 
				lehrveranstaltung_id, 
				studiensemester_kurzbz, 
				uid AS student_uid, 
				SUM(einheiten) AS summe
			FROM campus.tbl_anwesenheit a
			JOIN lehre.tbl_lehreinheit le USING (lehreinheit_id)
			JOIN lehre.tbl_lehrveranstaltung lv USING (lehrveranstaltung_id)
			WHERE anwesend = FALSE 
				AND studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz) . "
			GROUP BY
				lehrveranstaltung_id, bezeichnung, uid, studiensemester_kurzbz
		) nichtanwesend", "lehrveranstaltung_id,student_uid,studiensemester_kurzbz", "LEFT", false); // TODO(chris): use USING

		$this->addGroupBy("vorname");
		$this->addGroupBy("nachname");
		$this->addGroupBy("wahlname");
		$this->addGroupBy("lehrveranstaltung_id");
		$this->addGroupBy("bezeichnung");
		$this->addGroupBy("gruppe");
		$this->addGroupBy("student_uid");
		$this->addGroupBy("anwesend.summe");
		$this->addGroupBy("nichtanwesend.summe");


		$where = [
			"lehrveranstaltung_id >" => 0
		];

		if ($student_uid)
			$where["student_uid"] = $student_uid;

		if ($lehrveranstaltung_id)
			$where["lehrveranstaltung_id"] = $lehrveranstaltung_id;

		if ($lehrveranstaltung_id) {
			$this->addOrder("nachname");
			$this->addOrder("vorname");
		} elseif ($student_uid) {
			$this->addOrder("bezeichnung");
		}


		$tmp = $this->dbTable;

		$this->dbTable = "(
			SELECT
				SUM(stundenplan_id) AS stundenplan_id, 
				datum, 
				stunde, 
				lehrveranstaltung_id, 
				bezeichnung, 
				studiensemester_kurzbz, 
				studiengang_kz, 
				TRIM(
					CASE 
						WHEN stp.gruppe_kurzbz IS NOT NULL 
						THEN stp.gruppe_kurzbz 
						ELSE stp.semester || (
							CASE 
								WHEN verband IS NULL 
								THEN '' 
								ELSE stp.verband 
							END
						) || (
							CASE 
								WHEN stp.gruppe IS NULL 
								THEN '' 
								ELSE stp.gruppe 
							END
						)
					END
				) AS gruppe
			FROM lehre.tbl_lehrveranstaltung lv
			JOIN lehre.tbl_lehreinheit le USING (lehrveranstaltung_id)
			JOIN lehre.tbl_stundenplan stp USING (lehreinheit_id,studiengang_kz)
			WHERE studiensemester_kurzbz = " . $this->escape($studiensemester_kurzbz) . "
				AND (titel NOT LIKE '%Nebenprüfung%' OR titel IS NULL)
			GROUP BY 
				datum, 
				stunde, 
				lehrveranstaltung_id, 
				bezeichnung, 
				studiensemester_kurzbz, 
				studiengang_kz, 
				stp.gruppe_kurzbz, 
				stp.semester, 
				stp.verband, 
				stp.gruppe
		) x";

		$result = $this->loadWhere($where);

		$this->dbTable = $tmp;

		return $result;
	}
}
