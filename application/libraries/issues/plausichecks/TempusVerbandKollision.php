<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

class TempusVerbandKollision extends PlausiChecker
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

		$kollisionRes = $this->getVerbandKollisionen(
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
						'gruppen' => $kollision->gruppen,
						'datum' => date('d.m.Y H:i', strtotime($kollision->von))
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

	public function getVerbandKollisionen(
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
			grp AS (
				SELECT kalender_lehreinheit.kalender_id, lehreinheitgruppe.studiengang_kz, lehreinheitgruppe.semester, lehreinheitgruppe.verband, lehreinheitgruppe.gruppe, lehreinheitgruppe.gruppe_kurzbz
				FROM lehre.tbl_kalender_lehreinheit kalender_lehreinheit
				JOIN lehre.tbl_lehreinheitgruppe lehreinheitgruppe ON lehreinheitgruppe.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
				WHERE kalender_lehreinheit.kalender_id IN (SELECT kalender_id FROM relevant)
				UNION
				SELECT event_teilnehmer.kalender_id, event_teilnehmer.studiengang_kz, event_teilnehmer.semester, event_teilnehmer.verband, event_teilnehmer.gruppe, event_teilnehmer.gruppe_kurzbz
				FROM lehre.tbl_kalender_event_teilnehmer event_teilnehmer
				WHERE event_teilnehmer.kalender_id IN (SELECT kalender_id FROM relevant)
			),
			treffer AS (
				SELECT
					LEAST(paar.current_id, paar.other_id) AS kalender_id_a,
					GREATEST(paar.current_id, paar.other_id) AS kalender_id_b,
					MIN(paar.von) AS von,
					string_agg(DISTINCT COALESCE(
						other_gruppe.gruppe_kurzbz,
						UPPER(studiengang.typ::text || studiengang.kurzbz::text) || '-' || other_gruppe.semester
							|| COALESCE(other_gruppe.verband::text, '') || COALESCE(other_gruppe.gruppe::text, '')
					), ', ') AS gruppen
				FROM paare paar
				JOIN grp current_gruppe ON current_gruppe.kalender_id = paar.current_id
				JOIN grp other_gruppe
					ON other_gruppe.kalender_id = paar.other_id
					AND other_gruppe.studiengang_kz = current_gruppe.studiengang_kz
					AND other_gruppe.semester = current_gruppe.semester
				LEFT JOIN public.tbl_gruppe current_gruppe_stamm ON current_gruppe_stamm.gruppe_kurzbz = current_gruppe.gruppe_kurzbz
				LEFT JOIN public.tbl_gruppe other_gruppe_stamm ON other_gruppe_stamm.gruppe_kurzbz = other_gruppe.gruppe_kurzbz
				LEFT JOIN public.tbl_studiengang studiengang ON studiengang.studiengang_kz = other_gruppe.studiengang_kz
				WHERE
					(
						current_gruppe.gruppe_kurzbz IS NULL AND other_gruppe.gruppe_kurzbz IS NULL
						AND (
							current_gruppe.verband IS NULL OR other_gruppe.verband IS NULL
							OR (current_gruppe.verband = other_gruppe.verband AND (current_gruppe.gruppe IS NULL OR other_gruppe.gruppe IS NULL OR current_gruppe.gruppe = other_gruppe.gruppe))
						)
					)
					OR (current_gruppe.gruppe_kurzbz IS NOT NULL AND other_gruppe.gruppe_kurzbz IS NOT NULL
						AND current_gruppe_stamm.direktinskription IS NOT TRUE AND other_gruppe_stamm.direktinskription IS NOT TRUE)
					OR (current_gruppe.gruppe_kurzbz IS NULL AND other_gruppe.gruppe_kurzbz IS NOT NULL AND other_gruppe_stamm.direktinskription IS NOT TRUE)
					OR (current_gruppe.gruppe_kurzbz IS NOT NULL AND other_gruppe.gruppe_kurzbz IS NULL AND current_gruppe_stamm.direktinskription IS NOT TRUE)
				GROUP BY kalender_id_a, kalender_id_b
			)
			SELECT DISTINCT
				treffer.kalender_id_a,
				treffer.kalender_id_b,
				treffer.gruppen,
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