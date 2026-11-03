<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

class TempusLektorKollision extends PlausiChecker
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
		$uid = isset($params['uid']) ? $params['uid'] : null;
		$oe_kurzbz = isset($params['issue_oe_kurzbz']) ? $params['issue_oe_kurzbz'] : null;
		$kalender_ids = isset($params['kalender_ids']) ? $params['kalender_ids'] : null;
		$von = isset($params['von']) ? $params['von'] : null;
		$bis = isset($params['bis']) ? $params['bis'] : null;

		$kollisionRes = $this->getLektorKollisionen(
			$studiensemester_kurzbz,
			$studiengang_kz,
			$kalender_id_a,
			$kalender_id_b,
			$uid,
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
						'uid' => $kollision->uid,
						'datum' => date('d.m.Y H:i', strtotime($kollision->von))
					),
					'resolution_params' => array(
						'kalender_id_a' => (int)$kollision->kalender_id_a,
						'kalender_id_b' => (int)$kollision->kalender_id_b,
						'uid' => $kollision->uid
					)
				);
			}
		}

		return success($results);
	}

	public function getLektorKollisionen(
		$studiensemester_kurzbz = null,
		$studiengang_kz = null,
		$kalender_id_a = null,
		$kalender_id_b = null,
		$uid = null,
		$oe_kurzbz = null,
		$fallback_oe = null,
		$kalender_ids = null,
		$von = null,
		$bis = null
	) {
		$params = array($fallback_oe);

		$qry = "
			SELECT DISTINCT
				LEAST(current_kalender.kalender_id, other_kalender.kalender_id) AS kalender_id_a,
				GREATEST(current_kalender.kalender_id, other_kalender.kalender_id) AS kalender_id_b,
				current_mitarbeiter.mitarbeiter_uid AS uid,
				GREATEST(current_kalender.von, other_kalender.von) AS von,
				COALESCE(termin_oe.oe_kurzbz, ?) AS oe_kurzbz
			FROM
				lehre.tbl_kalender current_kalender
				JOIN lehre.tbl_kalender_lehreinheit current_kalender_lehreinheit ON current_kalender_lehreinheit.kalender_id = current_kalender.kalender_id
				JOIN lehre.tbl_lehreinheitmitarbeiter current_mitarbeiter ON current_mitarbeiter.lehreinheit_id = current_kalender_lehreinheit.lehreinheit_id
				JOIN lehre.tbl_lehreinheitmitarbeiter other_mitarbeiter ON other_mitarbeiter.mitarbeiter_uid = current_mitarbeiter.mitarbeiter_uid
				JOIN lehre.tbl_kalender_lehreinheit other_kalender_lehreinheit ON other_kalender_lehreinheit.lehreinheit_id = other_mitarbeiter.lehreinheit_id
				JOIN lehre.tbl_kalender other_kalender ON other_kalender.kalender_id = other_kalender_lehreinheit.kalender_id
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
				) termin_oe ON termin_oe.kalender_id IN (current_kalender.kalender_id, other_kalender.kalender_id)
			WHERE
				other_kalender.kalender_id <> current_kalender.kalender_id
				AND other_kalender.von < current_kalender.bis
				AND other_kalender.bis > current_kalender.von
				AND current_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
				AND other_kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
				AND NOT EXISTS (SELECT 1 FROM lehre.tbl_kalender nachfolger WHERE nachfolger.vorgaenger_kalender_id = current_kalender.kalender_id)
				AND NOT EXISTS (SELECT 1 FROM lehre.tbl_kalender nachfolger WHERE nachfolger.vorgaenger_kalender_id = other_kalender.kalender_id)";

		$kollisionsfreie_user = unserialize(KOLLISIONSFREIE_USER);
		if (!isEmptyArray($kollisionsfreie_user))
		{
			$qry .= " AND current_mitarbeiter.mitarbeiter_uid NOT IN ?";
			$params[] = $kollisionsfreie_user;
		}

		if (isset($kalender_id_a) && isset($kalender_id_b))
		{
			$qry .= " AND current_kalender.kalender_id = ? AND other_kalender.kalender_id = ? AND current_mitarbeiter.mitarbeiter_uid = ?";
			$params[] = $kalender_id_a;
			$params[] = $kalender_id_b;
			$params[] = $uid;
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
		else
		{
			$qry .= " AND other_kalender.kalender_id > current_kalender.kalender_id
					AND current_kalender.von >= now()::date AND current_kalender.von < now()::date + 14";
		}

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