<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

class TempusStudentKollision extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		$this->_ci->load->config('tempus');
		$fallback_oe = $this->_ci->config->item('tempus_issue_fallback_oe');

		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;
		$studiengang_kz = isset($params['studiengang_kz']) ? $params['studiengang_kz'] : null;
		$kalender_id_a = isset($params['kalender_id_a']) ? $params['kalender_id_a'] : null;
		$kalender_id_b = isset($params['kalender_id_b']) ? $params['kalender_id_b'] : null;
		$oe_kurzbz = isset($params['issue_oe_kurzbz']) ? $params['issue_oe_kurzbz'] : null;
		$kalender_ids = isset($params['kalender_ids']) ? $params['kalender_ids'] : null;
		$von = isset($params['von']) ? $params['von'] : null;
		$bis = isset($params['bis']) ? $params['bis'] : null;

		$kollisionRes = $this->getStudentKollisionen(
			$studiensemester_kurzbz,
			$studiengang_kz,
			$kalender_id_a,
			$kalender_id_b,
			$oe_kurzbz,
			$fallback_oe,
			$kalender_ids,
			$von,
			$bis
		);

		if (isError($kollisionRes)) return $kollisionRes;

		if (hasData($kollisionRes))
		{
			foreach (getData($kollisionRes) as $kollision)
			{
				$results[] = array(
					'oe_kurzbz' => $kollision->oe_kurzbz,
					'fehlertext_params' => array(
						'datum' => date('d.m.Y H:i', strtotime($kollision->von)),
						'anzahl' => $kollision->anzahl,
						'studierende' => $kollision->studierende
					),
					'resolution_params' => array(
						'kalender_id_a' => (int)$kollision->kalender_id_a,
						'kalender_id_b' => (int)$kollision->kalender_id_b
					)
				);
			}
		}

		return success($results);
	}

	public function getStudentKollisionen(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$kalender_id_a = null,
		$kalender_id_b = null,
		$oe_kurzbz = null,
		$fallback_oe = null,
		$kalender_ids = null,
		$von = null,
		$bis = null
	) {
		$params = array();

		$qry = "
			WITH paare AS (
				SELECT
					current_kalender.kalender_id AS current_id,
					other_kalender.kalender_id AS other_id,
					GREATEST(current_kalender.von, other_kalender.von) AS von
				FROM
					lehre.tbl_kalender current_kalender
					JOIN lehre.tbl_kalender other_kalender
						ON other_kalender.kalender_id <> current_kalender.kalender_id
						AND other_kalender.von < current_kalender.bis
						AND other_kalender.bis > current_kalender.von
				WHERE
					current_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
					AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
					AND NOT EXISTS (SELECT 1 FROM lehre.tbl_kalender nachfolger WHERE nachfolger.vorgaenger_kalender_id = current_kalender.kalender_id)
					AND NOT EXISTS (SELECT 1 FROM lehre.tbl_kalender nachfolger WHERE nachfolger.vorgaenger_kalender_id = other_kalender.kalender_id)";

		if (isset($kalender_id_a) && isset($kalender_id_b))
		{
			$qry .= " AND current_kalender.kalender_id = ? AND other_kalender.kalender_id = ?";
			$params[] = $kalender_id_a;
			$params[] = $kalender_id_b;
		}
		elseif (!isEmptyArray($kalender_ids))
		{
			$qry .= " AND current_kalender.kalender_id IN (" . implode(',', array_map('intval', $kalender_ids)) . ")";
		}
		elseif (isset($von) && isset($bis))
		{
			$qry .= " AND other_kalender.kalender_id > current_kalender.kalender_id
			AND current_kalender.von >= ?
			AND current_kalender.von < ?::date + 1";
			$params[] = $von;
			$params[] = $bis;
		}
		elseif (isset($studiensemester_kurzbz))
		{
			$qry .= " AND other_kalender.kalender_id > current_kalender.kalender_id
					AND current_kalender.von >= (SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz = ?)
					AND current_kalender.von < (SELECT ende FROM public.tbl_studiensemester WHERE studiensemester_kurzbz = ?) + interval '1 day'";
			$params[] = $studiensemester_kurzbz;
			$params[] = $studiensemester_kurzbz;
		}

		$qry .= "
			),
			relevant AS (
				SELECT current_id AS kalender_id FROM paare
				UNION
				SELECT other_id FROM paare
			),
			stud AS (
				SELECT kalender_lehreinheit.kalender_id, benutzergruppe.uid
				FROM lehre.tbl_kalender_lehreinheit kalender_lehreinheit
				JOIN lehre.tbl_lehreinheit lehreinheit ON lehreinheit.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
				JOIN lehre.tbl_lehreinheitgruppe lehreinheitgruppe ON lehreinheitgruppe.lehreinheit_id = lehreinheit.lehreinheit_id
				JOIN public.tbl_gruppe spezialgruppe
					ON spezialgruppe.studiengang_kz = lehreinheitgruppe.studiengang_kz
					AND spezialgruppe.semester = lehreinheitgruppe.semester
					AND spezialgruppe.gruppe_kurzbz = lehreinheitgruppe.gruppe_kurzbz
				JOIN public.tbl_benutzergruppe benutzergruppe
					ON benutzergruppe.gruppe_kurzbz = spezialgruppe.gruppe_kurzbz
					AND benutzergruppe.studiensemester_kurzbz = lehreinheit.studiensemester_kurzbz
				WHERE kalender_lehreinheit.kalender_id IN (SELECT kalender_id FROM relevant)
				UNION
				SELECT kalender_lehreinheit.kalender_id, studentlehrverband.student_uid
				FROM lehre.tbl_kalender_lehreinheit kalender_lehreinheit
				JOIN lehre.tbl_lehreinheit lehreinheit ON lehreinheit.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
				JOIN lehre.tbl_lehreinheitgruppe lehreinheitgruppe ON lehreinheitgruppe.lehreinheit_id = lehreinheit.lehreinheit_id
				JOIN public.tbl_studentlehrverband studentlehrverband
					ON studentlehrverband.studiengang_kz = lehreinheitgruppe.studiengang_kz
					AND studentlehrverband.semester = lehreinheitgruppe.semester
					AND studentlehrverband.studiensemester_kurzbz = lehreinheit.studiensemester_kurzbz
					AND (lehreinheitgruppe.verband = studentlehrverband.verband OR lehreinheitgruppe.verband IS NULL OR btrim(lehreinheitgruppe.verband::text) = '' OR studentlehrverband.verband IS NULL)
					AND (lehreinheitgruppe.gruppe = studentlehrverband.gruppe OR lehreinheitgruppe.gruppe IS NULL OR btrim(lehreinheitgruppe.gruppe::text) = '' OR studentlehrverband.gruppe IS NULL)
				WHERE kalender_lehreinheit.kalender_id IN (SELECT kalender_id FROM relevant)
			),
			treffer AS (
				SELECT
					LEAST(paar.current_id, paar.other_id) AS kalender_id_a,
					GREATEST(paar.current_id, paar.other_id) AS kalender_id_b,
					MIN(paar.von) AS von,
					COUNT(DISTINCT current_student.uid) AS anzahl,
					string_agg(DISTINCT current_student.uid, ', ' ORDER BY current_student.uid) AS studierende
				FROM paare paar
				JOIN stud current_student ON current_student.kalender_id = paar.current_id
				JOIN stud other_student ON other_student.kalender_id = paar.other_id AND other_student.uid = current_student.uid
				GROUP BY 1, 2
			)
			SELECT DISTINCT
				treffer.kalender_id_a,
				treffer.kalender_id_b,
				treffer.anzahl,
				treffer.studierende,
				treffer.von,
				COALESCE(termin_oe.oe_kurzbz, ?) AS oe_kurzbz
			FROM treffer treffer
			LEFT JOIN (
				SELECT kalender_lehreinheit.kalender_id, studiengang.oe_kurzbz
				FROM lehre.tbl_kalender_lehreinheit kalender_lehreinheit
				JOIN lehre.tbl_lehreinheit lehreinheit ON lehreinheit.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
				JOIN lehre.tbl_lehrveranstaltung lehrveranstaltung ON lehrveranstaltung.lehrveranstaltung_id = lehreinheit.lehrveranstaltung_id
				JOIN public.tbl_studiengang studiengang ON studiengang.studiengang_kz = lehrveranstaltung.studiengang_kz
				UNION
				SELECT reservierung.kalender_id, NULL
				FROM lehre.tbl_kalender reservierung
				WHERE reservierung.typ = 'reservierung'
			) termin_oe ON termin_oe.kalender_id IN (treffer.kalender_id_a, treffer.kalender_id_b)
			WHERE 1 = 1";
		$params[] = $fallback_oe;

		if (isset($oe_kurzbz))
		{
			$qry .= " AND COALESCE(termin_oe.oe_kurzbz, ?) = ?";
			$params[] = $fallback_oe;
			$params[] = $oe_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND termin_oe.oe_kurzbz = (SELECT oe_kurzbz FROM public.tbl_studiengang WHERE studiengang_kz = ?)";
			$params[] = $studiengang_kz;
		}


		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}