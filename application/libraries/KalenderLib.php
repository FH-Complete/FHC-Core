<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class KalenderLib
{
	private $_ci;
	public function __construct()
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('ressource/Kalender_model', 'KalenderModel');
		$this->_ci->load->model('ressource/Kalender_Lehreinheit_model', 'KalenderLehreinheitModel');
		$this->_ci->load->model('ressource/Kalender_Ort_model', 'KalenderOrtModel');
		$this->_ci->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->_ci->load->model('education/LehreinheitMitarbeiter_model', 'LehreinheitMitarbeiterModel');
		$this->_ci->load->model('ressource/Ort_model', 'OrtModel');

	}

	private function _getBasePlan($start_date, $end_date)
	{
		$end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));

		$this->_ci->KalenderModel->addSelect('tbl_kalender.kalender_id,
												tbl_kalender.status_kurzbz,
												tbl_kalender.von,
												tbl_kalender.bis,
												tbl_kalender_ort.ort_kurzbz,
												tbl_lehreinheit.lehreinheit_id,
												tbl_lehreinheit.lehrveranstaltung_id,
												tbl_lehreinheit.lehrfach_id,
												tbl_lehreinheit.lehrform_kurzbz,
												tbl_lehrveranstaltung.oe_kurzbz,
												lehrfach.kurzbz AS lehrfach_kurzbz,
												lehrfach.bezeichnung AS lehrfach_bezeichnung,
												lehrfach.farbe,
												tbl_lehreinheitmitarbeiter.mitarbeiter_uid,
												tbl_person.vorname,
												tbl_person.nachname,
												tbl_mitarbeiter.kurzbz AS ma_kurzbz');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_lehreinheit', 'tbl_kalender.kalender_id = tbl_kalender_lehreinheit.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheit', 'tbl_kalender_lehreinheit.lehreinheit_id = tbl_lehreinheit.lehreinheit_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehrveranstaltung', 'tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehrveranstaltung lehrfach', 'tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'tbl_kalender.kalender_id = tbl_kalender_ort.kalender_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheitmitarbeiter', 'tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_mitarbeiter', 'tbl_mitarbeiter.mitarbeiter_uid = tbl_lehreinheitmitarbeiter.mitarbeiter_uid', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_benutzer', 'tbl_mitarbeiter.mitarbeiter_uid = tbl_benutzer.uid', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_person', 'tbl_person.person_id = tbl_benutzer.person_id', 'LEFT');

		$this->_ci->KalenderModel->db->where('tbl_kalender.von >=', $start_date);
		$this->_ci->KalenderModel->db->where('tbl_kalender.bis <', $end_date);
	}

	private function _mapEvents($data)
	{
		$stundenplan_data = [];

		if (!isSuccess($data) || !hasData($data))
			return $stundenplan_data;

		$events = [];

		foreach (getData($data) as $row)
		{
			$id = $row->kalender_id;

			if (!isset($events[$id]))
			{
				$von = new DateTime($row->von);
				$bis = new DateTime($row->bis);

				$events[$id] = (object) [
					'type' => 'lehreinheit',
					'beginn' => $von->format('H:i:s'),
					'ende' => $bis->format('H:i:s'),
					'datum' => $von->format('Y-m-d'),
					'isostart' => $von->format('c'),
					'isoend' => $bis->format('c'),
					'tooltip' => 'tip',
					'status_kurzbz' => $row->status_kurzbz,
					'ort_kurzbz' => isset($row->ort_kurzbz) ? $row->ort_kurzbz : '',
					'lehrform' => isset($row->lehrform_kurzbz) ? $row->lehrform_kurzbz : '',
					'lehrfach' => isset($row->lehrfach_kurzbz) ? $row->lehrfach_kurzbz : '',
					'lehrfach_bez' => isset($row->lehrfach_bezeichnung) ? $row->lehrfach_bezeichnung : '',
					'farbe' => isset($row->farbe) ? $row->farbe : '',
					'lehrveranstaltung_id' => $row->lehrveranstaltung_id,
					'organisationseinheit' => isset($row->oe_kurzbz) ? $row->oe_kurzbz : '',
					'kalender_id' => $id,
					'lehreinheit_id' => [],
					'lektor' => [],
					'gruppe' => [],
					'titel' => '',
					'topic' => (isset($row->lehrfach_kurzbz) ? $row->lehrfach_kurzbz : '').' '.(isset($row->lehrform_kurzbz) ? $row->lehrform_kurzbz : ''),
				];
			}

			if ($row->lehreinheit_id && !in_array($row->lehreinheit_id, $events[$id]->lehreinheit_id))
				$events[$id]->lehreinheit_id[] = $row->lehreinheit_id;

			if ($row->mitarbeiter_uid)
			{
				if (!in_array($row->mitarbeiter_uid, array_column($events[$id]->lektor, 'mitarbeiter_uid')))
				{
					$events[$id]->lektor[] = [
						'mitarbeiter_uid' => $row->mitarbeiter_uid,
						'vorname' => $row->vorname,
						'nachname' => $row->nachname,
						'kurzbz' => $row->ma_kurzbz,
					];
				}
			}
		}

		return array_values($events);
	}
	public function getPlanByOrt($start_date, $end_date, $ort)
	{
		$this->_getBasePlan($start_date, $end_date);

		$this->_ci->KalenderModel->db->where('tbl_kalender_ort.ort_kurzbz', $ort);
		$data = $this->_ci->KalenderModel->load();

		return $this->_mapEvents($data);
	}

	public function getRaumvorschlagByLehreinheitID($start_date, $end_date, $lehreinheit_id)
	{
		$end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));
		$lehreinheit = $this->_ci->LehreinheitModel->load(array('lehreinheit_id' => $lehreinheit_id));

		if (isError($lehreinheit))
			return $lehreinheit;

		if (!hasData($lehreinheit))
			return error("Not found");

		$lehreinheit = getData($lehreinheit)[0];

		$this->_ci->KalenderModel->addDistinct('ort_kurzbz');
		$this->_ci->KalenderModel->addSelect('ort_kurzbz');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'kalender_id');
		$this->_ci->KalenderModel->db->where('tbl_kalender.von >=', $start_date);
		$this->_ci->KalenderModel->db->where('tbl_kalender.bis <', $end_date);
		$belegte_raeume = $this->_ci->KalenderModel->load();

		if (isError($belegte_raeume))
			return $belegte_raeume;

		$belegte_orte = hasData($belegte_raeume) ? array_column(getData($belegte_raeume), 'ort_kurzbz') : [];

		$vorschlaege = $this->_getFreieRaeume($lehreinheit->raumtyp, $belegte_orte);

		if (isError($vorschlaege))
			return $vorschlaege;

		$vorschlaege = hasData($vorschlaege) ? getData($vorschlaege) : [];

		if (count($vorschlaege) < 5 && !empty($lehreinheit->raumtypalternativ))
		{
			$bereits_gefunden = array_merge($belegte_orte, array_column($vorschlaege, 'ort_kurzbz'));

			$alternativ_raeume = $this->_getFreieRaeume($lehreinheit->raumtypalternativ, $bereits_gefunden);

			if (!isError($alternativ_raeume) && hasData($alternativ_raeume))
				$vorschlaege = array_merge($vorschlaege, getData($alternativ_raeume));
		}

		return success($vorschlaege);
	}

	private function _getFreieRaeume($raumtyp, $belegte_orte)
	{
		$this->_ci->OrtModel->addSelect('ort_kurzbz');
		$this->_ci->OrtModel->addJoin('public.tbl_ortraumtyp', 'ort_kurzbz');
		$this->_ci->OrtModel->db->where('raumtyp_kurzbz', $raumtyp);
		$this->_ci->OrtModel->db->where('aktiv', true);
		$this->_ci->OrtModel->db->where("ort_kurzbz NOT LIKE '\_%'", null, false);

		if (!empty($belegte_orte))
			$this->_ci->OrtModel->db->where_not_in('ort_kurzbz', $belegte_orte);
		$this->_ci->OrtModel->addOrder('hierarchie, ort_kurzbz');

		return $this->_ci->OrtModel->load();
	}

	public function getPlanForPlanner($start_date, $end_date, $ort = null, $uids = null, $stg = null)
	{
		$this->_getBasePlan($start_date, $end_date);

		if (!is_null($ort))
			$this->_ci->KalenderModel->db->where('tbl_kalender_ort.ort_kurzbz', $ort);

		if (!is_null($uids))
			$this->_ci->KalenderModel->db->where_in('tbl_lehreinheitmitarbeiter.mitarbeiter_uid', $uids);

		if (!is_null($stg))
			$this->_ci->KalenderModel->db->where('tbl_lehrveranstaltung.studiengang_kz', $stg);

		$this->_ci->KalenderModel->db->where('NOT EXISTS (
				SELECT 1 FROM lehre.tbl_kalender nachfolger
				WHERE nachfolger.vorgaenger_kalender_id = tbl_kalender.kalender_id)', null, false);

		$this->_ci->KalenderModel->db->where_not_in('status_kurzbz', array('deleted'));
		$data = $this->_ci->KalenderModel->load();

		return $this->_mapEvents($data);
	}

	public function getPlanForStudent($start_date, $end_date)
	{
		$this->_getBasePlan($start_date, $end_date);

		$this->_ci->KalenderModel->db->where('status_kurzbz', 'visible_student');

		$data = $this->_ci->KalenderModel->load();

		return $this->_mapEvents($data);
	}


	public function getPlanForLecturer($start_date, $end_date)
	{
		$this->_getBasePlan($start_date, $end_date);

		$this->_ci->KalenderModel->addJoin(
			'lehre.tbl_kalender neuerer',
			"neuerer.vorgaenger_kalender_id = tbl_kalender.kalender_id AND neuerer.status_kurzbz IN ('visible_lektor', 'tosync_student')",
			'LEFT'
		);
		$this->_ci->KalenderModel->db->where("(
			tbl_kalender.status_kurzbz = 'tosync_student'
			OR (tbl_kalender.status_kurzbz = 'visible_lektor' AND neuerer.kalender_id IS NULL)
			OR (tbl_kalender.status_kurzbz = 'visible_student' AND neuerer.kalender_id IS NULL)
		)", NULL, FALSE);
		$data = $this->_ci->KalenderModel->load();

		return $this->_mapEvents($data);
	}
	public function getPlanForLecturer2($start_date, $end_date)
	{
		$this->_getBasePlan($start_date, $end_date);

		$this->_ci->KalenderModel->db->where_in('status_kurzbz', array('visible_student', 'visible_lektor'));

		$data = $this->_ci->KalenderModel->load();

		return $this->_mapEvents($data);
	}
	public function getZeitsperren($start_date, $end_date, $emp)
	{
		$db = new DB_Model();
		$qry = "
			SELECT
				tbl_zeitsperre.zeitsperre_id,
				tbl_zeitsperre.vondatum AS start,
				tbl_zeitsperre.bisdatum AS ende,
				tbl_vonstunde.beginn AS startstunde,
				tbl_bisstunde.ende AS bisstunde,
				tbl_erreichbarkeit.farbe AS erreichbarkeit_farbe,
				tbl_erreichbarkeit.beschreibung AS erreichbarkeit_beschreibung,
				tbl_zeitsperretyp.beschreibung as label
			FROM campus.tbl_zeitsperre
					JOIN campus.tbl_zeitsperretyp ON tbl_zeitsperre.zeitsperretyp_kurzbz = tbl_zeitsperretyp.zeitsperretyp_kurzbz
					LEFT JOIN campus.tbl_erreichbarkeit ON tbl_zeitsperre.erreichbarkeit_kurzbz = tbl_erreichbarkeit.erreichbarkeit_kurzbz
					LEFT JOIN lehre.tbl_stunde tbl_vonstunde ON tbl_zeitsperre.vonstunde = tbl_vonstunde.stunde
					LEFT JOIN lehre.tbl_stunde tbl_bisstunde ON tbl_zeitsperre.bisstunde = tbl_bisstunde.stunde
			WHERE tbl_zeitsperre.mitarbeiter_uid = ?
			  AND tbl_zeitsperre.bisdatum >= ?
			  AND tbl_zeitsperre.vondatum <= ?
			ORDER BY tbl_zeitsperre.vondatum;
		";

		$result = $db->execReadOnlyQuery($qry, array($emp, $start_date, $end_date));

		if (isError($result))
			return $result;

		$zeitsperren_array = array();

		if (hasData($result))
		{
			foreach (getData($result) as $zeitsperre)
			{
				$obj = new stdClass();
				$von = new DateTime($zeitsperre->start . ' '. $zeitsperre->startstunde);
				$bis = new DateTime($zeitsperre->ende . ' '.  $zeitsperre->bisstunde);
				$obj->isostart = $von->format('c');
				$obj->isoend = $bis->format('c');
				$obj->label = $zeitsperre->label;
				$zeitsperren_array[] = $obj;
			}
		}
		return $zeitsperren_array;
	}
	public function getZeitwuensche($start_date, $end_date, $emp)
	{
		$db = new DB_Model();
		$qry = "
			WITH zeitwuensche AS (
				SELECT
					tbl_zeitwunsch.*,
					zw_gueltigkeit.von AS gueltig_von,
					zw_gueltigkeit.bis AS gueltig_bis
				FROM campus.tbl_zeitwunsch
					JOIN campus.tbl_zeitwunsch_gueltigkeit zw_gueltigkeit USING (zeitwunsch_gueltigkeit_id)
				WHERE tbl_zeitwunsch.mitarbeiter_uid = ?
			),
				tage AS (
					SELECT
						tage::date AS tag,
						EXTRACT(DOW FROM tage)::int AS wochentag
					FROM generate_series(?::date, ?::date, interval '1 day') AS tage
				)
			SELECT
				tage.tag,
				zeitwuensche.gewicht,
				tage.tag + s.beginn as start,
				tage.tag + s.ende as ende,
				mitarbeiter_uid as label
			FROM tage
				JOIN zeitwuensche ON tage.wochentag = zeitwuensche.tag
						AND tage.tag >= zeitwuensche.gueltig_von
						AND (zeitwuensche.gueltig_bis IS NULL OR tage.tag <= zeitwuensche.gueltig_bis)
					JOIN lehre.tbl_stunde s ON s.stunde = zeitwuensche.stunde
			ORDER BY tage.tag, start;";

		$result = $db->execReadOnlyQuery($qry, array($emp, $start_date, $end_date));

		if (isError($result))
			return $result;

		$zeitwuensche_array = array();

		if (hasData($result))
		{
			foreach (getData($result) as $zeitwuensch)
			{
				$obj = new stdClass();

				$von = new DateTime($zeitwuensch->start);
				$bis = new DateTime($zeitwuensch->ende);

				$obj->isostart = $von->format('c');
				$obj->isoend = $bis->format('c');
				$obj->gewicht = $zeitwuensch->gewicht;
				$obj->label = $zeitwuensch->label;
				$zeitwuensche_array[] = $obj;
			}
		}

		return $zeitwuensche_array;

	}
	public function addKalenderEvent($start_date, $end_date, $lehreinheit_id, $ort_kurzbz)
	{
		$kalenderresult = $this->_ci->KalenderModel->insert(
			array (
				'von' => $start_date,
				'bis' => $end_date,
				'typ' => 'lehreinheit',
				'status_kurzbz' => 'planning',
				'insertvon' => getAuthUID(),
				'insertamum' => date('Y-m-d H:i:s')
			)
		);

		if(isSuccess($kalenderresult) && hasData($kalenderresult))
		{
			$kalender_id = getData($kalenderresult);

			$kalenderlehreinheitresult = $this->_ci->KalenderLehreinheitModel->insert(
				array (
					'kalender_id' => $kalender_id,
					'lehreinheit_id' => $lehreinheit_id
				)
			);

			if(isSuccess($kalenderlehreinheitresult) && !is_null($ort_kurzbz))
			{
				return $this->_addKalenderOrt($kalender_id, $ort_kurzbz);
			}

			return $kalenderlehreinheitresult;
		}
	}

	private function _addKalenderOrt($kalender_id, $ort_kurzbz)
	{
		return $this->_ci->KalenderOrtModel->insert(
			array (
				'kalender_id' => $kalender_id,
				'ort_kurzbz' => $ort_kurzbz
			)
		);
	}

	public function updateZeit($kalender_id, $start_date, $end_date)
	{
		$entryResult = $this->_loadKalenderEntry($kalender_id);
		if (isError($entryResult)) return $entryResult;

		$kalender_entry = getData($entryResult);

		if (in_array($kalender_entry->status_kurzbz, array('todelete', 'deleted')))
			return error('Eintrag kann nicht mehr bearbeitet werden');

		if (in_array($kalender_entry->status_kurzbz, array('visible_student', 'visible_lektor')))
			return $this->_createHistoryEntry($kalender_entry, $start_date, $end_date);

		return $this->_ci->KalenderModel->update(
			array('kalender_id' => $kalender_id),
			array(
				'von' => $start_date,
				'bis' => $end_date,
				'updateamum' => date('Y-m-d H:i:s'),
				'updatevon' => getAuthUID()
			)
		);
	}

	public function updateOrt($kalender_id, $ort_kurzbz)
	{
		$entryResult = $this->_loadKalenderEntry($kalender_id);
		if (isError($entryResult)) return $entryResult;

		$kalender_entry = getData($entryResult);

		if (in_array($kalender_entry->status_kurzbz, array('todelete', 'deleted')))
			return error('Eintrag kann nicht mehr bearbeitet werden');

		if (in_array($kalender_entry->status_kurzbz, array('visible_student', 'visible_lektor')))
			return $this->_createHistoryEntry($kalender_entry, null, null, $ort_kurzbz);

		$exist = $this->_ci->KalenderOrtModel->load(array('kalender_id' => $kalender_id));
		if (hasData($exist))
		{
			return $this->_ci->KalenderOrtModel->update(
				array('kalender_id' => $kalender_id),
				array('ort_kurzbz' => $ort_kurzbz)
			);
		}
		else
		{
			return $this->_addKalenderOrt($kalender_id, $ort_kurzbz);
		}
	}

	public function updateStatus($kalender_id, $status_kurzbz)
	{
		$entryResult = $this->_loadKalenderEntry($kalender_id);
		if (isError($entryResult)) return $entryResult;

		$kalender_entry = getData($entryResult);

		$allowed = array(
			'planning' => array('tosync_lektor', 'tosync_student'),
			'tosync_lektor' => array('tosync_student'),
			'tosync_student' => array('tosync_lektor'),
			'visible_lektor' => array('tosync_student'),
		);

		if (!isset($allowed[$kalender_entry->status_kurzbz]) || !in_array($status_kurzbz, $allowed[$kalender_entry->status_kurzbz]))
			return error('Statuswechsel nicht erlaubt');

		$result = $this->_ci->KalenderModel->update(
			array('kalender_id' => $kalender_entry->kalender_id),
			array('status_kurzbz' => $status_kurzbz)
		);

		if (isError($result)) return $result;

		return success();
	}

	private function _createHistoryEntry($kalender_entry, $start_date = null, $end_date = null, $ort_kurzbz = null)
	{
		$old_id = $kalender_entry->kalender_id;

		$kalenderresult = $this->_ci->KalenderModel->insert(
			array(
				'von' => $start_date ? $start_date : $kalender_entry->von,
				'bis' => $end_date ? $end_date : $kalender_entry->bis,
				'typ' => 'lehreinheit',
				'status_kurzbz' => 'planning',
				'vorgaenger_kalender_id' => $old_id,
				'insertvon' => getAuthUID(),
				'insertamum' => date('Y-m-d H:i:s')
			)
		);

		if (!isSuccess($kalenderresult) || !hasData($kalenderresult))
			return $kalenderresult;

		$new_kalender_id = getData($kalenderresult);

		$kalenderlehreinheitresult = $this->_ci->KalenderLehreinheitModel->insert(
			array(
				'kalender_id' => $new_kalender_id,
				'lehreinheit_id' => $kalender_entry->lehreinheit_id
			)
		);

		if (!isSuccess($kalenderlehreinheitresult))
			return $kalenderlehreinheitresult;

		$new_ort = $ort_kurzbz ? $ort_kurzbz : $kalender_entry->ort_kurzbz;
		if (!is_null($new_ort) || !is_null($kalender_entry->location))
		{
			$ortresult = $this->_ci->KalenderOrtModel->insert(
				array(
					'kalender_id' => $new_kalender_id,
					'ort_kurzbz' => $new_ort,
					'location' => $kalender_entry->location
				)
			);

			if (!isSuccess($ortresult))
				return $ortresult;
		}

		return success($new_kalender_id);
	}

	private function _loadKalenderEntry($kalender_id)
	{
		$this->_ci->KalenderModel->addSelect('tbl_kalender.*, tbl_kalender_lehreinheit.lehreinheit_id, tbl_kalender_ort.ort_kurzbz, tbl_kalender_ort.location');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_lehreinheit', 'tbl_kalender.kalender_id = tbl_kalender_lehreinheit.kalender_id');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'tbl_kalender.kalender_id = tbl_kalender_ort.kalender_id', 'LEFT');

		$result = $this->_ci->KalenderModel->load(array('tbl_kalender.kalender_id' => $kalender_id));

		if (isError($result)) return $result;
		if (!hasData($result)) return error("Not found");

		return success(getData($result)[0]);
	}



	public function deleteEntry($kalender_id)
	{
		$result = $this->_ci->KalenderModel->load(array('tbl_kalender.kalender_id' => $kalender_id));

		if (isError($result)) return $result;

		if (!hasData($result)) return error("Not found");

		$entry = getData($result)[0];

		if ($entry->typ === 'lehreinheit')
			return $this->_deleteTypLehreinheit($entry);
	}

	private function _deleteTypLehreinheit($entry)
	{
		$result_lehreinheit = $this->_ci->KalenderLehreinheitModel->loadWhere(array('kalender_id' => $entry->kalender_id));
		if (isError($result_lehreinheit)) return $result_lehreinheit;
		$has_lehreinheit = hasData($result_lehreinheit) ? getData($result_lehreinheit)[0] : false;

		$result_ort = $this->_ci->KalenderOrtModel->loadWhere(array('kalender_id' => $entry->kalender_id));
		if (isError($result_ort)) return $result_ort;
		$has_ort = hasData($result_ort) ? getData($result_ort)[0] : false;

		if ($entry->status_kurzbz === 'planning')
		{
			if ($has_ort)
				$this->_deleteOrtEntry($has_ort);

			if ($has_lehreinheit)
				$this->_deleteLehreinheitEntry($has_lehreinheit);

			$this->_ci->KalenderModel->delete(array('tbl_kalender.kalender_id' => $entry->kalender_id));
		}
		else if ($entry->status_kurzbz === 'tosync_lektor' || $entry->status_kurzbz === 'tosync_student')
		{
			$history = $this->getHistory($entry->kalender_id);
			$history = hasData($history) ? getData($history) : false;

			if ($has_ort)
				$result = $this->_deleteOrtEntry($has_ort);

			if ($has_lehreinheit)
				$result = $this->_deleteLehreinheitEntry($has_lehreinheit);

			$this->_ci->KalenderModel->delete(array('tbl_kalender.kalender_id' => $entry->kalender_id));
		}
		else if ($entry->status_kurzbz === 'visible_lektor' || $entry->status_kurzbz === 'visible_student')
		{
			//TODO (david) überprüfen ob sinnvoll, verschwindet direkt in den ansichten (student, lecturer)
			$result = $this->_ci->KalenderModel->update(
				array('kalender_id' => $entry->kalender_id),
				array('status_kurzbz' => 'todelete')
			);
		}

		return success();
	}

	private function _deleteOrtEntry($entry)
	{
		return $this->_ci->KalenderOrtModel->delete(array('kalender_id' => $entry->kalender_id));

	}

	private function _deleteLehreinheitEntry($entry)
	{
		return $this->_ci->KalenderLehreinheitModel->delete(array('kalender_id' => $entry->kalender_id));
	}

	public function getHistory($kalender_id, $only_previous = false)
	{
		$dbModel = new DB_Model();
		$history_entries = $dbModel->execReadOnlyQuery('
				WITH RECURSIVE 
				vorgaenger(kalender_id, vorgaenger_kalender_id) AS (
					SELECT kalender_id, vorgaenger_kalender_id
					FROM lehre.tbl_kalender
					WHERE kalender_id = ?
			
					UNION ALL
			
					SELECT k.kalender_id, k.vorgaenger_kalender_id
					FROM lehre.tbl_kalender k
					JOIN vorgaenger ON k.kalender_id = vorgaenger.vorgaenger_kalender_id
				),
				nachfolger(kalender_id, vorgaenger_kalender_id) AS (
					SELECT kalender_id, vorgaenger_kalender_id
					FROM lehre.tbl_kalender
					WHERE kalender_id = ?
			
					UNION ALL
			
					SELECT k.kalender_id, k.vorgaenger_kalender_id
					FROM lehre.tbl_kalender k
					JOIN nachfolger ON k.vorgaenger_kalender_id = nachfolger.kalender_id
				)
				SELECT tbl_kalender.*,
						TO_CHAR(von, \'DD.MM.YYYY HH24:MI:SS\') as von,
						TO_CHAR(bis, \'DD.MM.YYYY HH24:MI:SS\') as bis,
						COALESCE(tbl_kalender_ort.ort_kurzbz, location) as ort
				FROM (
					SELECT kalender_id FROM vorgaenger
					UNION
					SELECT kalender_id FROM nachfolger
				) combined
				JOIN lehre.tbl_kalender USING(kalender_id)
				LEFT JOIN lehre.tbl_kalender_ort USING(kalender_id)
				ORDER BY kalender_id DESC
				
			' . ($only_previous ? ' LIMIT 1' : ''), array($kalender_id, $kalender_id));

		return $history_entries;
	}

	//TODO in eine eigen Lib?
	public function sync()
	{

		$this->_ci->load->model('ressource/Kalender_model', 'KalenderModel');

		$this->_ci->KalenderModel->db->where_in('status_kurzbz', array('tosync_student', 'tosync_lektor', 'todelete', 'planning', 'visible_lektor'));
		$this->_ci->KalenderModel->addOrder('kalender_id', 'DESC');
		$to_update = $this->_ci->KalenderModel->load();

		if (!hasData($to_update))
			return success();
		foreach (getData($to_update) as $entry)
		{
			if ($entry->status_kurzbz === 'todelete')
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'deleted'));
			}
			if ($entry->status_kurzbz === 'tosync_lektor')
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'visible_lektor'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, true);
			}

			if ($entry->status_kurzbz === 'tosync_student' || $entry->status_kurzbz === 'planning')
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'visible_student'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, false);
			}

			if ($entry->status_kurzbz === 'visible_lektor')
			{
				$current = getData($this->_ci->KalenderModel->load(array('kalender_id' => $entry->kalender_id)))[0];
				if ($current->status_kurzbz === 'archived')
					continue;

				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'visible_student'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, false);
			}
		}

		return success();


	}

	private function _archiveVorgaenger($vorgaenger_kalender_id, $stop_at_visible_student = false)
	{
		if (!$vorgaenger_kalender_id) return;

		$current_id = $vorgaenger_kalender_id;

		while ($current_id)
		{
			$vorgaenger = $this->_ci->KalenderModel->load(array('kalender_id' => $current_id));
			if (!hasData($vorgaenger)) return;

			$vorgaenger = getData($vorgaenger)[0];

			if ($stop_at_visible_student && $vorgaenger->status_kurzbz === 'visible_student')
				return;

			if (in_array($vorgaenger->status_kurzbz, array('visible_lektor', 'visible_student')))
			{
				$this->_ci->KalenderModel->update(
					array('kalender_id' => $current_id),
					array('status_kurzbz' => 'archived')
				);
			}

			$current_id = $vorgaenger->vorgaenger_kalender_id;
		}
	}

}
