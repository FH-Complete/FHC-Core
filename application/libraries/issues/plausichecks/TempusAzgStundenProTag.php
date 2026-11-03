<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

require_once('PlausiChecker.php');

class TempusAzgStundenProTag extends PlausiChecker
{
	public function executePlausiCheck($params)
	{
		$results = array();

		// AZG-Verletzungen hängen an Person und Tag, nicht an einzelnen Terminen:
		// beim Speichern (kalender_ids) nicht prüfen
		if (!isEmptyArray($params['kalender_ids'] ?? null))
			return success($results);

		$this->_ci->load->config('tempus');
		$azg_oe = $this->_ci->config->item('tempus_azg_oe') ?: $this->_ci->config->item('tempus_issue_fallback_oe');
		$max_stunden = (int)($this->_ci->config->item('tempus_azg_max_stunden_pro_tag') ?: 12);

		$studiensemester_kurzbz = isset($params['studiensemester_kurzbz']) ? $params['studiensemester_kurzbz'] : null;
		$von = isset($params['von']) ? $params['von'] : null;
		$bis = isset($params['bis']) ? $params['bis'] : null;
		$uid = isset($params['uid']) ? $params['uid'] : null;
		$datum = isset($params['datum']) ? $params['datum'] : null;

		$verletzungRes = $this->getStundenProTag(
			$max_stunden,
			$studiensemester_kurzbz,
			$von,
			$bis,
			$uid,
			$datum
		);

		if (isError($verletzungRes)) return $verletzungRes;

		if (hasData($verletzungRes))
		{
			foreach (getData($verletzungRes) as $verletzung)
			{
				$results[] = array(
					'person_id' => $verletzung->person_id,
					'oe_kurzbz' => $azg_oe,
					'fehlertext_params' => array(
						'max_stunden' => $max_stunden,
						'uid' => $verletzung->uid,
						'datum' => date('d.m.Y', strtotime($verletzung->datum)),
						'beginn' => date('H:i', strtotime($verletzung->beginn)),
						'ende' => date('H:i', strtotime($verletzung->ende))
					),
					'resolution_params' => array(
						'uid' => $verletzung->uid,
						'datum' => $verletzung->datum
					)
				);
			}
		}

		return success($results);
	}

	public function getStundenProTag(
		$max_stunden,
		$studiensemester_kurzbz = null,
		$von = null,
		$bis = null,
		$uid = null,
		$datum = null
	) {
		$params = array();

		$ausgenommene_studiengaenge = (array)$this->_ci->config->item('tempus_azg_ausgenommene_studiengaenge');
		$ausgenommene_orte = (array)$this->_ci->config->item('tempus_azg_ausgenommene_orte');
		$ausgenommene_uids = (array)$this->_ci->config->item('tempus_azg_ausgenommene_uids');

		$qry = "
			WITH termine AS (
				SELECT DISTINCT
					kalender.kalender_id,
					mitarbeiter.mitarbeiter_uid AS uid,
					kalender.von,
					kalender.bis
				FROM lehre.tbl_kalender kalender
				JOIN lehre.tbl_kalender_lehreinheit kalender_lehreinheit ON kalender_lehreinheit.kalender_id = kalender.kalender_id
				JOIN lehre.tbl_lehreinheit lehreinheit ON lehreinheit.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
				JOIN lehre.tbl_lehrveranstaltung lehrveranstaltung ON lehrveranstaltung.lehrveranstaltung_id = lehreinheit.lehrveranstaltung_id
				JOIN lehre.tbl_lehreinheitmitarbeiter lehreinheitmitarbeiter ON lehreinheitmitarbeiter.lehreinheit_id = lehreinheit.lehreinheit_id
				JOIN public.tbl_mitarbeiter mitarbeiter
					ON mitarbeiter.mitarbeiter_uid = lehreinheitmitarbeiter.mitarbeiter_uid
					AND mitarbeiter.fixangestellt = true
				WHERE kalender.status_kurzbz NOT IN ('archived', 'deleted', 'to_delete', 'to_delete_live', 'to_delete_preview')
				AND NOT EXISTS (
					SELECT 1 FROM lehre.tbl_kalender nachfolger
					WHERE nachfolger.vorgaenger_kalender_id = kalender.kalender_id
				)
				-- vergangene Tage interessieren nicht, werden beim Beheben dadurch geschlossen
				";

		if (!isEmptyArray($ausgenommene_studiengaenge))
		{
			$qry .= " AND lehrveranstaltung.studiengang_kz NOT IN ?";
			$params[] = $ausgenommene_studiengaenge;
		}

		if (!isEmptyArray($ausgenommene_uids))
		{
			$qry .= " AND mitarbeiter.mitarbeiter_uid NOT IN ?";
			$params[] = $ausgenommene_uids;
		}

		if (!isEmptyArray($ausgenommene_orte))
		{
			$qry .= " AND NOT EXISTS (
						SELECT 1 FROM lehre.tbl_kalender_ort kalender_ort
						WHERE kalender_ort.kalender_id = kalender.kalender_id
						AND kalender_ort.ort_kurzbz IN ?
					)";
			$params[] = $ausgenommene_orte;
		}

		if (isset($uid) && isset($datum))
		{
			$qry .= " AND mitarbeiter.mitarbeiter_uid = ?
					AND kalender.von >= ?::date
					AND kalender.von < ?::date + 1";
			$params[] = $uid;
			$params[] = $datum;
			$params[] = $datum;
		}
		elseif (isset($von) && isset($bis))
		{
			$qry .= " AND kalender.von >= ?::date AND kalender.von < ?::date + 1";
			$params[] = $von;
			$params[] = $bis;
		}
		elseif (isset($studiensemester_kurzbz))
		{
			$qry .= " AND kalender.von >= (SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz = ?)
					AND kalender.von < (SELECT ende FROM public.tbl_studiensemester WHERE studiensemester_kurzbz = ?) + 1";
			$params[] = $studiensemester_kurzbz;
			$params[] = $studiensemester_kurzbz;
		}

		$qry .= "
			),
			tage AS (
				SELECT
					termine.uid,
					termine.von::date AS datum,
					MIN(termine.von) AS beginn,
					MAX(termine.bis) AS ende
				FROM termine
				GROUP BY termine.uid, termine.von::date
			)
			SELECT
				tage.uid,
				benutzer.person_id,
				tage.datum,
				tage.beginn,
				tage.ende
			FROM tage
			JOIN public.tbl_benutzer benutzer ON benutzer.uid = tage.uid
			JOIN public.tbl_studiensemester studiensemester
				ON tage.datum BETWEEN studiensemester.start AND studiensemester.ende
			LEFT JOIN campus.tbl_zeitaufzeichnung_gd zeitaufzeichnung_gd
				ON zeitaufzeichnung_gd.uid = tage.uid
				AND zeitaufzeichnung_gd.studiensemester_kurzbz = studiensemester.studiensemester_kurzbz
			WHERE zeitaufzeichnung_gd.selbstverwaltete_pause IS NOT TRUE
			AND tage.ende - tage.beginn > interval '1 hour' * ?";
		$params[] = $max_stunden;

		$qry .= " ORDER BY tage.datum, tage.uid";

		return $this->_db->execReadOnlyQuery($qry, $params);
	}
}