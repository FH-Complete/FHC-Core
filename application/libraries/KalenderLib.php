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
		$this->_ci->load->model('ressource/Kalender_Event_model', 'KalenderEventModel');
		$this->_ci->load->model('ressource/Kalender_Event_Teilnehmer_model', 'KalenderEventTeilnehmerModel');
		$this->_ci->load->model('ressource/Kalender_Ort_model', 'KalenderOrtModel');
		$this->_ci->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->_ci->load->model('education/LehreinheitMitarbeiter_model', 'LehreinheitMitarbeiterModel');
		$this->_ci->load->model('ressource/Ort_model', 'OrtModel');
		$this->_ci->load->model('organisation/gruppe_model', 'GruppeModel');
		$this->_ci->load->model('organisation/Lehrverband_model', 'LehrverbandModel');
		$this->_ci->load->model('ressource/Stunde_model', 'StundeModel');
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->_ci->load->model('system/Variable_model', 'VariableModel');
		$this->_ci->load->model('organisation/Ferien_model', 'FerienModel');


		$this->_ci->load->library('CollisionChecker');
		$this->_ci->load->library('PhrasesLib', array('ui'));
		$this->_ci->load->library('VariableLib', array('uid' => getAuthUID()));


	}

	private function _getBasePlan($start_date, $end_date)
	{
		$end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));

		$this->_ci->KalenderModel->addSelect('tbl_kalender.kalender_id,
												tbl_kalender.status_kurzbz,
												tbl_kalender.typ,
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
												COALESCE(orginasator.uid, tbl_lehreinheitmitarbeiter.mitarbeiter_uid) as mitarbeiter_uid,
												COALESCE(reservierung_person.vorname, tbl_person.vorname) as vorname,
												COALESCE(reservierung_person.nachname, tbl_person.nachname) as nachname,
												COALESCE(reservierung_ma.kurzbz, tbl_mitarbeiter.kurzbz) AS ma_kurzbz,
												teilnehmergruppe.gruppe_kurzbz as teilnehmerg_grp,
												COALESCE (
													UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) || 
													COALESCE(verbandgruppe.semester::varchar, \'\') || 
													COALESCE(verbandgruppe.verband::varchar, \'\') || 
													COALESCE(verbandgruppe.gruppe, \'\'), 
												\'\') as verband_grp,
												tbl_kalender_event.beschreibung,
												tbl_kalender_event.titel,
												teilmitarbeiter.kurzbz as teilnehmer_kurzbz,
												teilperson.vorname as teilnehmer_vorname,
												teilperson.nachname as teilnehmer_nachname,
												teilbenutzer.uid as teilnehmer_uid,
												CASE 
													WHEN tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL THEN 
														COALESCE(
															UPPER(le_studiengang.typ || le_studiengang.kurzbz) || 
															COALESCE(tbl_lehreinheitgruppe.semester::varchar, \'\') || 
															COALESCE(tbl_lehreinheitgruppe.verband::varchar, \'\') || 
															COALESCE(tbl_lehreinheitgruppe.gruppe, \'\'), 
														\'\')
													ELSE tbl_lehreinheitgruppe.gruppe_kurzbz
												END AS lehreinheit_gruppe_bezeichnung,
												tbl_lehreinheitgruppe.gruppe_kurzbz as le_gruppe_kurzbz,
												tbl_lehreinheitgruppe.studiengang_kz as le_studiengang_kz,
												tbl_lehreinheitgruppe.semester as le_semester,
												tbl_lehreinheitgruppe.verband as le_verband,
												tbl_lehreinheitgruppe.gruppe as le_gruppe,
												le_gruppe.direktinskription as le_direktinskription,
												
												
												');

		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_lehreinheit', 'tbl_kalender.kalender_id = tbl_kalender_lehreinheit.kalender_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_event', 'tbl_kalender.kalender_id = tbl_kalender_event.kalender_id', 'LEFT');

		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_event_teilnehmer orginasator', 'tbl_kalender_event.kalender_id = orginasator.kalender_id AND orginasator.rolle_kurzbz = \'organisator\'', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_event_teilnehmer teilnehmer', 'tbl_kalender_event.kalender_id = teilnehmer.kalender_id AND teilnehmer.rolle_kurzbz = \'teilnehmer\'', 'LEFT');

		$this->_ci->KalenderModel->addJoin('public.tbl_benutzer teilbenutzer', 'teilnehmer.uid = teilbenutzer.uid', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_mitarbeiter teilmitarbeiter', 'teilmitarbeiter.mitarbeiter_uid = teilbenutzer.uid', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_person teilperson', 'teilperson.person_id = teilbenutzer.person_id', 'LEFT');

		$this->_ci->KalenderModel->addJoin('public.tbl_gruppe teilnehmergruppe', 'teilnehmer.gruppe_kurzbz = teilnehmergruppe.gruppe_kurzbz', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_lehrverband verbandgruppe',
			'teilnehmer.studiengang_kz = verbandgruppe.studiengang_kz 
			AND teilnehmer.semester = verbandgruppe.semester 
			AND TRIM(COALESCE(teilnehmer.verband::text, \'\')) = TRIM(verbandgruppe.verband::text)
			AND TRIM(COALESCE(teilnehmer.gruppe::text, \'\')) = TRIM(verbandgruppe.gruppe::text)', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_studiengang', 'verbandgruppe.studiengang_kz = public.tbl_studiengang.studiengang_kz', 'LEFT' );


		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheit', 'tbl_kalender_lehreinheit.lehreinheit_id = tbl_lehreinheit.lehreinheit_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehrveranstaltung', 'tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehrveranstaltung lehrfach', 'tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'tbl_kalender.kalender_id = tbl_kalender_ort.kalender_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheitmitarbeiter', 'tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id', 'LEFT');

		$this->_ci->KalenderModel->addJoin('public.tbl_mitarbeiter', 'tbl_mitarbeiter.mitarbeiter_uid = tbl_lehreinheitmitarbeiter.mitarbeiter_uid', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_benutzer', 'tbl_mitarbeiter.mitarbeiter_uid = tbl_benutzer.uid', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_person', 'tbl_person.person_id = tbl_benutzer.person_id', 'LEFT');

		$this->_ci->KalenderModel->addJoin('public.tbl_mitarbeiter reservierung_ma', 'reservierung_ma.mitarbeiter_uid = orginasator.uid', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_benutzer resevierung_benutzer', 'reservierung_ma.mitarbeiter_uid = resevierung_benutzer.uid', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_person reservierung_person', 'reservierung_person.person_id = resevierung_benutzer.person_id', 'LEFT');

		$this->_ci->KalenderModel->addJoin('lehre.tbl_lehreinheitgruppe', 'tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_gruppe le_gruppe', 'tbl_lehreinheitgruppe.gruppe_kurzbz = le_gruppe.gruppe_kurzbz', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_lehrverband le_lehrverband',
												'tbl_lehreinheitgruppe.studiengang_kz = le_lehrverband.studiengang_kz
												 AND tbl_lehreinheitgruppe.semester = le_lehrverband.semester
												 AND TRIM(COALESCE(tbl_lehreinheitgruppe.verband::text, \'\')) = TRIM(le_lehrverband.verband::text)
												 AND TRIM(COALESCE(tbl_lehreinheitgruppe.gruppe::text, \'\')) = TRIM(le_lehrverband.gruppe::text)',
			'LEFT'
		);
		$this->_ci->KalenderModel->addJoin('public.tbl_studiengang le_studiengang', 'le_lehrverband.studiengang_kz = le_studiengang.studiengang_kz', 'LEFT');

		$this->_ci->KalenderModel->db->where('tbl_kalender.von >=', $start_date);
		$this->_ci->KalenderModel->db->where('tbl_kalender.bis <', $end_date);
	}

	private function _mapEvents($data, $collisionCheck = true)
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
					'type' => $row->typ,
					'beginn' => $von->format('H:i:s'),
					'ende' => $bis->format('H:i:s'),
					'datum' => $von->format('Y-m-d'),
					'isostart' => $von->format('c'),
					'isoend' => $bis->format('c'),
					'tooltip' => 'tip',
					'status_kurzbz' => $row->status_kurzbz,
					'ort_kurzbz' => [],
					'lehrform' => isset($row->lehrform_kurzbz) ? $row->lehrform_kurzbz : '',
					'lehrfach' => isset($row->lehrfach_kurzbz) ? $row->lehrfach_kurzbz : '',
					'lehrfach_bez' => isset($row->lehrfach_bezeichnung) ? $row->lehrfach_bezeichnung : '',
					'farbe' => isset($row->farbe) ? $row->farbe : '',
					'lehrveranstaltung_id' => $row->lehrveranstaltung_id,
					'organisationseinheit' => isset($row->oe_kurzbz) ? $row->oe_kurzbz : '',
					'kalender_id' => $id,
					'lehreinheit_id' => [],
					'lektor' => [],
					'teilnehmer_gruppe' => [],
					'teilnehmer_person' => [],
					'gruppe' => [],
					'titel' => isset($row->titel) ? $row->titel : '',
					'beschreibung' => isset($row->beschreibung) ? $row->beschreibung : '',
					'topic' => [],
					'collisions' => false
				];
			}

			if ($row->lehreinheit_id && !in_array($row->lehreinheit_id, $events[$id]->lehreinheit_id))
				$events[$id]->lehreinheit_id[] = $row->lehreinheit_id;

			if (isset($row->ort_kurzbz) && $row->ort_kurzbz !== '' && !in_array($row->ort_kurzbz, $events[$id]->ort_kurzbz))
				$events[$id]->ort_kurzbz[] = $row->ort_kurzbz;

			$topic = trim((isset($row->lehrfach_kurzbz) ? $row->lehrfach_kurzbz : '').' '.(isset($row->lehrform_kurzbz) ? $row->lehrform_kurzbz : ''));
			if ($topic !== '' && !in_array($topic, $events[$id]->topic))
				$events[$id]->topic[] = $topic;

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

			if ($row->verband_grp)
			{
				if (!in_array($row->teilnehmerg_grp, array_column($events[$id]->teilnehmer_gruppe, 'gruppe_kurzbz')))
				{
					$events[$id]->teilnehmer_gruppe[] = [
						'gruppe_kurzbz' => $row->verband_grp,
					];
				}
			}

			if ($row->teilnehmerg_grp)
			{
				if (!in_array($row->teilnehmerg_grp, array_column($events[$id]->teilnehmer_gruppe, 'gruppe_kurzbz')))
				{
					$events[$id]->teilnehmer_gruppe[] = [
						'gruppe_kurzbz' => $row->teilnehmerg_grp,
					];
				}
			}

			if ($row->lehreinheit_gruppe_bezeichnung)
			{
				if (!in_array($row->lehreinheit_gruppe_bezeichnung, array_column($events[$id]->gruppe, 'bezeichnung')))
				{
					$events[$id]->gruppe[] = [
						'bezeichnung' => $row->lehreinheit_gruppe_bezeichnung,
						'gruppe_kurzbz' => $row->le_gruppe_kurzbz,
						'studiengang_kz' => $row->le_studiengang_kz,
						'semester' => $row->le_semester,
						'verband' => $row->le_verband,
						'gruppe' => $row->le_gruppe,
						'direktinskription' => $row->le_direktinskription,
					];
				}
			}

			if ($row->teilnehmer_uid)
			{
				if (!in_array($row->teilnehmer_uid, array_column($events[$id]->teilnehmer_person, 'uid')))
				{
					$events[$id]->teilnehmer_person[] = [
						'uid' => $row->teilnehmer_uid,
						'vorname' => $row->teilnehmer_vorname,
						'nachname' => $row->teilnehmer_nachname,
						'kurzbz' => $row->teilnehmer_kurzbz,
					];
				}
			}
		}

		if ($collisionCheck)
		{
			$kalender_ids = array_keys($events);
			$collisions = $this->_ci->collisionchecker->runAll($kalender_ids);

			foreach ($collisions as $kalender_id => $errors)
			{
				if (isset($events[$kalender_id]))
					$events[$kalender_id]->collisions = !empty($errors);
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



	public function getForRaumvorschlag($start_date, $end_date, $lektor_uids = [], $gruppen_kurzbz = [], $lehrverband_gruppen = [])
	{
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));

		$this->_getBasePlan($start_date, $end_date);

		$this->_ci->KalenderModel->db->where('NOT EXISTS (
											SELECT 1 FROM lehre.tbl_kalender nachfolger
											WHERE nachfolger.vorgaenger_kalender_id = tbl_kalender.kalender_id)', null, false);

		$this->_ci->KalenderModel->db->where_not_in('status_kurzbz', ['deleted']);

		if (!empty($lektor_uids) || !empty($gruppen_kurzbz) || !empty($lehrverband_gruppen))
		{
			$this->_ci->KalenderModel->db->group_start();

			if (!empty($lektor_uids))
				$this->_ci->KalenderModel->db->where_in('tbl_lehreinheitmitarbeiter.mitarbeiter_uid', $lektor_uids);

			if (!empty($gruppen_kurzbz))
				$this->_ci->KalenderModel->db->or_where_in('tbl_lehreinheitgruppe.gruppe_kurzbz', $gruppen_kurzbz);

			foreach ($lehrverband_gruppen as $lvg)
			{
				$this->_ci->KalenderModel->db->or_group_start();
				$this->_ci->KalenderModel->db->where('tbl_lehreinheitgruppe.studiengang_kz', $lvg['studiengang_kz']);
				$this->_ci->KalenderModel->db->where('tbl_lehreinheitgruppe.semester', $lvg['semester']);
				$this->_ci->KalenderModel->db->where('tbl_lehreinheitgruppe.verband', $lvg['verband']);
				$this->_ci->KalenderModel->db->where('tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL', null, false);
				$this->_ci->KalenderModel->db->group_end();
			}

			$this->_ci->KalenderModel->db->group_end();
		}

		$data = $this->_ci->KalenderModel->load();
		return $this->_mapEvents($data, false);
	}


	public function getByKalenderId($kalender_id)
	{
		$kalender_entry = $this->_ci->KalenderModel->load($kalender_id);
		if (isError($kalender_entry))
			return $kalender_entry;

		if (!hasData($kalender_entry))
			return error('');

		$kalender_entry = getData($kalender_entry)[0];

		$this->_getBasePlan($kalender_entry->von, $kalender_entry->bis);
		$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id', $kalender_entry->kalender_id);
		$data = $this->_ci->KalenderModel->load();
		return $this->_mapEvents($data);
	}
	public function getPlanForPlanner($start_date, $end_date, $ort = null, $uids = null, $studiengaenge = null)
	{
		$this->_getBasePlan($start_date, $end_date);

		if (!is_null($ort))
		{
			$ort_array = (array) $ort;
			$escaped_orte = array();

			foreach ($ort_array as $ort)
			{
				$escaped_orte[] = $this->_ci->KalenderModel->db->escape($ort);
			}
			$in_list = '(' . implode(',', $escaped_orte) . ')';

			$this->_ci->KalenderModel->db->where(
				"(EXISTS (
					SELECT 1 
					FROM lehre.tbl_kalender_ort filter_ort
					WHERE filter_ort.kalender_id = tbl_kalender.kalender_id
						AND filter_ort.ort_kurzbz IN $in_list
				))"
			);
		}

		if (!is_null($uids))
		{
			$uid_array = (array) $uids;
			$db = $this->_ci->KalenderModel->db;

			$escaped_uids = array();
			foreach ($uid_array as $uid)
				$escaped_uids[] = $db->escape($uid);
			$in_list = '(' . implode(',', $escaped_uids) . ')';

			$this->_ci->KalenderModel->db->where(
				"(EXISTS (
					SELECT 1 
					FROM lehre.tbl_lehreinheitmitarbeiter filter_lem
					WHERE filter_lem.lehreinheit_id = tbl_lehreinheit.lehreinheit_id
					  AND filter_lem.mitarbeiter_uid IN $in_list
				)
				OR EXISTS (
					SELECT 1 
					FROM lehre.tbl_kalender_event_teilnehmer filter_org
					WHERE filter_org.kalender_id = tbl_kalender.kalender_id
					  AND filter_org.rolle_kurzbz = 'organisator'
					  AND filter_org.uid IN $in_list
				)
				OR EXISTS (
					SELECT 1 
					FROM lehre.tbl_kalender_event_teilnehmer filter_teil
					WHERE filter_teil.kalender_id = tbl_kalender.kalender_id
					  AND filter_teil.rolle_kurzbz = 'teilnehmer'
					  AND filter_teil.uid IN $in_list
				))"
			);
		}

		if (!is_null($studiengaenge))
		{
			$db = $this->_ci->KalenderModel->db;
			$or_conditions = array();

			foreach ($studiengaenge as $studiengang)
			{
				$conditions = array();
				$conditions[] = 'filter_lv.studiengang_kz = ' . $db->escape($studiengang['studiengang_kz']);

				if (isset($studiengang['semester']))
					$conditions[] = 'filter_lv.semester = ' . $db->escape($studiengang['semester']);

				if (isset($studiengang['orgform_kurzbz']))
					$conditions[] = 'filter_lv.orgform_kurzbz = ' . $db->escape($studiengang['orgform_kurzbz']);

				$or_conditions[] = '(' . implode(' AND ', $conditions) . ')';
			}

			$or_block = implode(' OR ', $or_conditions);

			$this->_ci->KalenderModel->db->where(
				"(EXISTS (
					SELECT 1
					FROM lehre.tbl_kalender_lehreinheit filter_kl
					JOIN lehre.tbl_lehreinheit filter_le ON filter_le.lehreinheit_id = filter_kl.lehreinheit_id
					JOIN lehre.tbl_lehrveranstaltung filter_lv ON filter_lv.lehrveranstaltung_id = filter_le.lehrveranstaltung_id
					WHERE filter_kl.kalender_id = tbl_kalender.kalender_id
						AND ($or_block)
				))"
			);
		}

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

		$this->_ci->KalenderModel->db->where('status_kurzbz', 'live');

		$data = $this->_ci->KalenderModel->load();

		return $this->_mapEvents($data);
	}


	public function getPlanForLecturer($start_date, $end_date)
	{
		$this->_getBasePlan($start_date, $end_date);

		$this->_ci->KalenderModel->addJoin(
			'lehre.tbl_kalender neuerer',
			"neuerer.vorgaenger_kalender_id = tbl_kalender.kalender_id AND neuerer.status_kurzbz IN ('preview', 'sync_live')",
			'LEFT'
		);
		$this->_ci->KalenderModel->db->where("(
			tbl_kalender.status_kurzbz = 'sync_live'
			OR (tbl_kalender.status_kurzbz = 'preview' AND neuerer.kalender_id IS NULL)
			OR (tbl_kalender.status_kurzbz = 'live' AND neuerer.kalender_id IS NULL)
		)", NULL, FALSE);
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

	public function addToKalenderEvent($target_kalender_id, $lehreinheit_id)
	{
		$check = $this->_ci->KalenderLehreinheitModel->loadWhere(array('kalender_id' => $target_kalender_id, 'lehreinheit_id' => $lehreinheit_id));

		if (hasData($check))
		{
			return error([
				'message' => $this->_ci->phraseslib->t('ui', 'already_present'),
				'errorCode' => 'already_present'
			]);
		}

		$kalenderlehreinheitresult = $this->_ci->KalenderLehreinheitModel->insert(
			array(
				'kalender_id' => $target_kalender_id,
				'lehreinheit_id' => $lehreinheit_id
			)
		);

		if (isError($kalenderlehreinheitresult))
			return $kalenderlehreinheitresult;

		return success($target_kalender_id);
	}

	private function _addWeeks($date_str, $weeks)
	{
		$d = new DateTime($date_str);
		$d->modify('+' . $weeks . ' week');
		return $d->format('Y-m-d');
	}

	//TODO (david) studiengang_kz übergeben
	private function _isFerien($date_str)
	{
		$this->_ci->FerienModel->db->where('vondatum <=', $date_str);
		$this->_ci->FerienModel->db->where('bisdatum >=', $date_str);
		$this->_ci->FerienModel->db->where('studiengang_kz =', 0);

		$ferien_result = $this->_ci->FerienModel->load();

		if (isError($ferien_result))
			return false;

		if (hasData($ferien_result))
			return true;
	}

	private function _insertKalenderEventRaw($start_date, $end_date, $lehreinheit_id, $ort_kurzbz)
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

		if (!isSuccess($kalenderresult) || !hasData($kalenderresult))
			return ['kalender_id' => null, 'result' => $kalenderresult];

		$kalender_id = getData($kalenderresult);

		$kalenderlehreinheitresult = $this->_ci->KalenderLehreinheitModel->insert(
			array (
				'kalender_id' => $kalender_id,
				'lehreinheit_id' => $lehreinheit_id
			)
		);

		if (isSuccess($kalenderlehreinheitresult) && !is_null($ort_kurzbz))
		{
			$ortresult = $this->_addKalenderOrt($kalender_id, $ort_kurzbz);
			if (isError($ortresult))
				return ['kalender_id' => $kalender_id, 'result' => $ortresult];
		}

		$entryResult = $this->_loadKalenderEntry($kalender_id);
		if (isError($entryResult))
			return ['kalender_id' => $kalender_id, 'result' => $entryResult];

		$kalender_entry = getData($entryResult);
		$errors = $this->_ci->collisionchecker->run($kalender_entry);

		if (!empty($errors))
			return ['kalender_id' => $kalender_id, 'result' => error($errors)];

		return ['kalender_id' => $kalender_id, 'result' => $kalenderlehreinheitresult];
	}

	public function calculateMultiWeekPlan($start_date, $end_date, $lehreinheit_id, $ort_kurzbz)
	{
		$this->_ci->load->library('RaumvorschlagLib');
		$le_result = $this->_ci->LehreinheitModel->load($lehreinheit_id);

		if (!hasData($le_result))
			return error('Lehreinheit nicht gefunden');
		$le = getData($le_result)[0];

		$block = $le->stundenblockung;
		$wochenrythmus = $le->wochenrythmus;
		$studiensemester_kurzbz = $le->studiensemester_kurzbz;

		if ($block <= 0)
			return error('Stundenblockung ist ungültig');
		if ($wochenrythmus <= 0)
			return error('Wochenrythmus ist ungültig');

		$offen_result = $this->_ci->LehreinheitModel->getOffeneStunden([$lehreinheit_id]);
		if (!hasData($offen_result))
			return error('Offene Stunden nicht gefunden');

		$rows = getData($offen_result);
		$offenestunden_werte = array_values(array_unique(array_column($rows, 'offenestunden')));

		if (count($offenestunden_werte) > 1)
			return error('Offene Stunden sind nicht eindeutig');

		$rest = $offenestunden_werte[0];

		if ($rest <= 0)
			return error('Es sind bereits alle Stunden verplant');

		$studiensemester = $this->_ci->StudiensemesterModel->loadWhere(array('studiensemester_kurzbz' => $studiensemester_kurzbz));
		if (!hasData($studiensemester))
			return error('Studiensemester nicht gefunden');
		$semesterende = getData($studiensemester)[0]->ende;

		$start_time = (new DateTime($start_date))->format('H:i:s');
		$start_stunde_result = $this->_ci->StundeModel->loadWhere(array('beginn' => $start_time));

		if (!hasData($start_stunde_result))
			return error('Startzeit entspricht keinem gültigen Raster');

		$start_stunde_nr = getData($start_stunde_result)[0]->stunde;

		$current_date = (new DateTime($start_date))->format('Y-m-d');
		$ende = new DateTime($semesterende);

		$plan = [];
		$skipped_weeks = [];
		$errors = [];

		$original_ignore = $this->_ci->variablelib->getVar('ignore_kollision');

		$this->_ci->VariableModel->setVariable(
			getAuthUID(),
			'ignore_kollision',
			'false'
		);

		$this->_ci->KalenderModel->db->trans_start();

		while ($rest > 0 && new DateTime($current_date) < $ende)
		{
			$max_skip = 52;
			$skip_count = 0;
			while ($this->_isFerien($current_date) && $skip_count < $max_skip)
			{
				$skipped_weeks[] = $current_date;
				$current_date = $this->_addWeeks($current_date, $wochenrythmus);
				$skip_count++;
			}

			if ($skip_count >= $max_skip)
			{
				$errors[] = ['message' => 'Keine ferienfreie Woche bis Semesterende gefunden.'];
				break;
			}

			if (new DateTime($current_date) >= $ende)
				break;

			$current_block = ($rest < $block) ? $rest : $block;

			$end_stunde_nr = $start_stunde_nr + $current_block - 1;
			$end_stunde_result = $this->_ci->StundeModel->loadWhere(array('stunde' => $end_stunde_nr));

			if (!hasData($end_stunde_result))
			{
				$errors[] = [
					'datum' => $current_date,
					'message' => 'Endzeit entspricht keinem gültigen Raster',
				];
				$current_date = $this->_addWeeks($current_date, $wochenrythmus);
				continue;
			}

			$end_time = getData($end_stunde_result)[0]->ende;

			$start_str = $current_date . ' ' . $start_time;
			$end_str = $current_date . ' ' . $end_time;

			$insert = $this->_insertKalenderEventRaw($start_str, $end_str, $lehreinheit_id, $ort_kurzbz);
			$kalender_id = $insert['kalender_id'];
			$insert_result = $insert['result'];

			$collisions = isError($insert_result) ? getError($insert_result) : [];

			$raum_vorschlaege = [];
			if ($kalender_id)
			{
				$vorschlaege = $this->_ci->raumvorschlaglib->getVorschlaege($kalender_id);
				$raum_vorschlaege = is_array($vorschlaege) ? $vorschlaege : [];
			}

			$plan[] = [
				'datum' => $current_date,
				'start' => $start_str,
				'ende' => $end_str,
				'block' => $current_block,
				'ort_kurzbz' => $ort_kurzbz,
				'collisions' => $collisions,
				'raum_vorschlaege' => $raum_vorschlaege,
			];

			$rest -= $current_block;
			$current_date = $this->_addWeeks($current_date, $wochenrythmus);
		}

		$this->_ci->KalenderModel->db->trans_rollback();

		$this->_ci->VariableModel->setVariable(
			getAuthUID(),
			'ignore_kollision',
			$original_ignore
		);

		return success([
			'plan' => $plan,
			'skipped_weeks' => $skipped_weeks,
			'errors' => $errors,
			'rest_offen' => max(0, $rest),
			'lehreinheit_id' => $lehreinheit_id,
			'ort_kurzbz' => $ort_kurzbz,
		]);
	}

	public function confirmMultiWeekPlan($plan, $lehreinheit_id)
	{
		$created = [];
		$errors = [];

		$original_ignore = $this->_ci->variablelib->getVar('ignore_kollision');
		$this->_ci->VariableModel->setVariable(
			getAuthUID(),
			'ignore_kollision',
			'true'
		);
		foreach ($plan as $termin)
		{
			$ort_kurzbz = $termin['selected_ort_kurzbz'] ?? $termin['ort_kurzbz'];

			$result = $this->addKalenderEvent($termin['start'], $termin['ende'], $lehreinheit_id, $ort_kurzbz);

			if (isError($result))
			{
				$errors[] = [
					'datum' => $termin['datum'],
					'message' => getError($result),
				];
			}
			else
			{
				$created[] = getData($result);
			}
		}

		$this->_ci->VariableModel->setVariable(
			getAuthUID(),
			'ignore_kollision',
			$original_ignore
		);

		return success([
			'created' => $created,
			'errors' => $errors,
		]);
	}


	public function addKalenderEvent($start_date, $end_date, $lehreinheit_id, $ort_kurzbz)
	{
		$this->_ci->KalenderModel->db->trans_start();

		$insert = $this->_insertKalenderEventRaw($start_date, $end_date, $lehreinheit_id, $ort_kurzbz);
		$result = $insert['result'];

		if (isError($result))
		{
			$this->_ci->KalenderModel->db->trans_rollback();
			return $result;
		}

		$this->_ci->KalenderModel->db->trans_complete();
		return $result;
	}

	public function addReservierung($titel, $beschreibung, $ort_kurzbz, $start_date, $end_date, $teilnehmer, $specialFinalGroups, $specialGroups, $groups)
	{

		if (!is_null($ort_kurzbz))
		{
			$this->_ci->KalenderModel->addSelect('1');
			$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'kalender_id');
			$this->_ci->KalenderModel->db->where_in('ort_kurzbz', $ort_kurzbz);
			$reservierung_vorhanden = $this->_ci->KalenderModel->load(array (
				'von <=' => $end_date,
				'bis >=' => $start_date,
			));

			if (hasData($reservierung_vorhanden) && !$this->_ci->permissionlib->isBerechtigt('lehre/reservierungAdvanced'))
			{
				return error([
						'message' => $this->_ci->phraseslib->t('ui', 'already_reserved'),
						'errorCode' => 'already_reserved'
					]);

			}
		}

		$this->_ci->KalenderModel->db->trans_start();

		$kalenderresult = $this->_ci->KalenderModel->insert(
			array (
				'von' => $start_date,
				'bis' => $end_date,
				'typ' => 'reservierung',
				'status_kurzbz' => 'live',
				'insertvon' => getAuthUID(),
				'insertamum' => date('Y-m-d H:i:s')
			)
		);

		if (isSuccess($kalenderresult) && hasData($kalenderresult))
		{
			$kalender_id = getData($kalenderresult);

			$kalendereventresult = $this->_ci->KalenderEventModel->insert(
				array (
					'kalender_id' => $kalender_id,
					'titel' => $titel,
					'beschreibung' => $beschreibung,
				)
			);

			foreach ($teilnehmer as $teil)
			{
				$teilnehmerresult = $this->_addTeilnehmerToEvent($kalender_id, $teil['uid'], $teil['rolle']);
				if (isError($teilnehmerresult))
				{
					$this->_ci->KalenderModel->db->trans_rollback();
					return $teilnehmerresult;
				}
			}

			foreach ($specialFinalGroups as $group)
			{
				$specialgroupresult = $this->_addFinalGroupToEvent($kalender_id, $group['gid'], !($group['lehrverband'] === 'false'), $group['gruppe_kurzbz'], $group['studiensemester_kurzbz'], $group['rolle']);
				if (isError($specialgroupresult))
				{
					$this->_ci->KalenderModel->db->trans_rollback();
					return $specialgroupresult;
				}
			}

		/*	foreach ($specialGroups as $group)
			{
				$this->_addSpecialGroupToEvent($kalender_id, $group['gruppe_kurzbz'], $group['rolle']);
			}

			foreach ($groups as $group)
			{
				$this->_addGroupToEvent($kalender_id, $group['stg_kz'], $group['semester'], $group['verband'], $group['gruppe'], $group['rolle']);
			}*/

			if (isSuccess($kalendereventresult) && !is_null($ort_kurzbz))
			{
				$ortresult = $this->_addKalenderOrt($kalender_id, $ort_kurzbz);
				if (isError($ortresult))
				{
					$this->_ci->KalenderModel->db->trans_rollback();
					return $ortresult;
				}
			}

			$entryResult = $this->_loadKalenderEntry($kalender_id);
			if (isError($entryResult))
			{
				$this->_ci->KalenderModel->db->trans_rollback();
				return $entryResult;
			}

			$kalender_entry = getData($entryResult);
			$errors = $this->_ci->collisionchecker->run($kalender_entry);

			if (!empty($errors))
			{
				$this->_ci->KalenderModel->db->trans_rollback();
				return error($errors);
			}

			$this->_ci->KalenderModel->db->trans_complete();
			return $kalendereventresult;
		}

		$this->_ci->KalenderModel->db->trans_rollback();
		return $kalenderresult;
	}

	private function _addTeilnehmerToEvent($kalender_id, $uid, $rolle)
	{
		return $this->_ci->KalenderEventTeilnehmerModel->insert(
			array (
				'kalender_id' => $kalender_id,
				'uid' => $uid,
				'rolle_kurzbz' => $rolle
			)
		);
	}
	private function _addFinalGroupToEvent($kalender_id, $gid, $lehrverband, $gruppe_kurzbz, $studiensemester_kurzbz, $rolle)
	{

		if ($lehrverband === false)
		{
			$gruppen_result = $this->_ci->GruppeModel->loadWhere(array('gid' => $gid));

			if (!hasData($gruppen_result))
				return error('No group found for gid ' . $gid);

			return $this->_ci->KalenderEventTeilnehmerModel->insert(
				array (
					'kalender_id' => $kalender_id,
					'gruppe_kurzbz' => $gruppe_kurzbz,
					'studiensemester_kurzbz' => $studiensemester_kurzbz,
					'rolle_kurzbz' => $rolle
				)
			);
		}
		else if ($lehrverband === true)
		{
			$gruppen_result = $this->_ci->LehrverbandModel->loadWhere(array('gid' => $gid));

			if (!hasData($gruppen_result))
				return error('No group found for gid ' . $gid);

			$gruppe = getData($gruppen_result)[0];

			return $this->_ci->KalenderEventTeilnehmerModel->insert(
				array (
					'kalender_id' => $kalender_id,
					'studiengang_kz' => $gruppe->studiengang_kz,
					'studiensemester_kurzbz' => $studiensemester_kurzbz,
					'semester' => isEmptyString($gruppe->semester) ? null : $gruppe->semester,
					'verband' => isEmptyString($gruppe->verband) ? null : $gruppe->verband,
					'gruppe' => isEmptyString($gruppe->gruppe) ? null : $gruppe->gruppe,
					'rolle_kurzbz' => $rolle
				)
			);
		}
	}

	private function _addSpecialGroupToEvent($kalender_id, $gruppe_kurzbz, $rolle)
	{
		return $this->_ci->KalenderEventTeilnehmerModel->insert(
			array (
				'kalender_id' => $kalender_id,
				'gruppe_kurzbz' => $gruppe_kurzbz,
				'rolle_kurzbz' => $rolle
			)
		);
	}
	private function _addGroupToEvent($kalender_id, $stg_kz, $semester, $verband, $gruppe, $rolle)
	{
		return $this->_ci->KalenderEventTeilnehmerModel->insert(
			array (
				'kalender_id' => $kalender_id,
				'studiengang_kz' => $stg_kz,
				'semester' => isEmptyString($semester) ? null : $semester,
				'verband' => isEmptyString($verband) ? null : $verband,
				'gruppe' => isEmptyString($gruppe) ? null : $gruppe,
				'rolle_kurzbz' => $rolle
			)
		);
	}
	private function _addKalenderOrt($kalender_id, $ort_kurzbz)
	{
		foreach ((array) $ort_kurzbz as $ort)
		{
			$ortresult = $this->_ci->KalenderOrtModel->insert(
				array (
					'kalender_id' => $kalender_id,
					'ort_kurzbz' => $ort
				)
			);

			if (!isSuccess($ortresult))
				return $ortresult;
		}

		return success();
	}

	public function updateKalenderEvent($kalender_id, $ort_kurzbz = null, $start_time = null, $end_time = null)
	{
		$entryResult = $this->_loadKalenderEntry($kalender_id);
		if (isError($entryResult)) return $entryResult;

		$kalender_entry = getData($entryResult);

		$kalender_entry->ort_kurzbz = !is_null($ort_kurzbz) ? (array) $ort_kurzbz : $kalender_entry->ort_kurzbz;
		$kalender_entry->von = $start_time ?? $kalender_entry->von;
		$kalender_entry->bis = $end_time ?? $kalender_entry->bis;

		$errors = $this->_ci->collisionchecker->run($kalender_entry);

		if (!empty($errors)) return error($errors);

		if (!is_null($ort_kurzbz) && !empty($ort_kurzbz))
		{
			$result = $this->updateOrt($kalender_id, $ort_kurzbz);
			if (isError($result)) return $result;

			if (hasData($result))
			{
				$kalender_id = (getData($result)['kalender_id']) ?? getData($result);
			}
		}

		if (!is_null($start_time) || !is_null($end_time))
		{
			$result = $this->updateZeit($kalender_id, $start_time, $end_time);
			if (isError($result)) return $result;
		}

		return success('Erfolgreich');
	}

	public function updateZeit($kalender_id, $start_date, $end_date)
	{
		$entryResult = $this->_loadKalenderEntry($kalender_id);
		if (isError($entryResult)) return $entryResult;

		$kalender_entry = getData($entryResult);

		if ($kalender_entry->typ === 'lehreinheit')
		{
			if (in_array($kalender_entry->status_kurzbz, array('todelete', 'deleted')))
			{
				return error([
					'message' => $this->_ci->phraseslib->t('ui', 'entry_not_editable'),
					'errorCode' => 'entry_not_editable'
				]);
			}

			if (in_array($kalender_entry->status_kurzbz, array('live', 'preview')))
				return $this->_createHistoryEntry($kalender_entry, $start_date, $end_date);
		}

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

	public function updateOrt($kalender_id, $ort_kurzbz_array)
	{
		$ort_kurzbz_array = (array)$ort_kurzbz_array;
		$entryResult = $this->_loadKalenderEntry($kalender_id);
		if (isError($entryResult)) return $entryResult;

		$kalender_entry = getData($entryResult);

		if ($kalender_entry->typ === 'lehreinheit')
		{
			if (in_array($kalender_entry->status_kurzbz, array('todelete', 'deleted')))
			{
				return error([
					'message' => $this->_ci->phraseslib->t('ui', 'entry_not_editable'),
					'errorCode' => 'entry_not_editable'
				]);
			}

			if (in_array($kalender_entry->status_kurzbz, array('live', 'preview')))
				return $this->_createHistoryEntry($kalender_entry, null, null, $ort_kurzbz_array);
		}

		$bestehende = $this->_ci->KalenderOrtModel->load(array('kalender_id' => $kalender_id));
		$alte_orte = hasData($bestehende) ? array_column(getData($bestehende), 'ort_kurzbz') : [];

		$zu_loeschen = array_diff($alte_orte, $ort_kurzbz_array);
		$neu_anzulegen = array_diff($ort_kurzbz_array, $alte_orte);

		if (!empty($zu_loeschen))
		{
			$this->_ci->KalenderOrtModel->db->where('kalender_id', $kalender_id);
			$this->_ci->KalenderOrtModel->db->where_in('ort_kurzbz', $zu_loeschen);
			$this->_ci->KalenderOrtModel->db->delete('lehre.tbl_kalender_ort');
		}

		foreach ($neu_anzulegen as $ort_kurzbz)
		{
			$result = $this->_addKalenderOrt($kalender_id, $ort_kurzbz);
			if (isError($result)) return $result;
		}

		return success();
	}

	public function updateStatus($kalender_id, $status_kurzbz)
	{
		$entryResult = $this->_loadKalenderEntry($kalender_id);
		if (isError($entryResult)) return $entryResult;

		$kalender_entry = getData($entryResult);

		$allowed = array(
			'planning' => array('sync_preview', 'sync_live'),
			'sync_preview' => array('sync_live'),
			'sync_live' => array('sync_preview'),
			'preview' => array('sync_live'),
		);

		if (!isset($allowed[$kalender_entry->status_kurzbz]) || !in_array($status_kurzbz, $allowed[$kalender_entry->status_kurzbz]))
		{
			return error([
				'message' => $this->_ci->phraseslib->t('ui', 'status_change_not_allowed'),
				'errorCode' => 'status_change_not_allowed'
			]);
		}

		$result = $this->_ci->KalenderModel->update(
			array('kalender_id' => $kalender_entry->kalender_id),
			array('status_kurzbz' => $status_kurzbz)
		);

		if (isError($result)) return $result;

		return success();
	}

	private function _createHistoryEntry($kalender_entry, $start_date = null, $end_date = null, $ort_kurzbz_array = null)
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

		$lehreinheit_ids = (array) $kalender_entry->lehreinheit_id;

		foreach ($lehreinheit_ids as $lehreinheit_id)
		{
			$kalenderlehreinheitresult = $this->_ci->KalenderLehreinheitModel->insert(
				array(
					'kalender_id' => $new_kalender_id,
					'lehreinheit_id' => $lehreinheit_id
				)
			);

			if (!isSuccess($kalenderlehreinheitresult))
				return $kalenderlehreinheitresult;
		}

		if (!empty($ort_kurzbz_array))
		{
			$new_orte = (array) $ort_kurzbz_array;
			$new_locations = array_fill(0, count($new_orte), null);
		}
		else
		{
			$new_orte = (array) $kalender_entry->ort_kurzbz;
			$new_locations = (array) $kalender_entry->location;
		}

		foreach ($new_orte as $index => $ort)
		{
			$ortresult = $this->_ci->KalenderOrtModel->insert(
				array(
					'kalender_id' => $new_kalender_id,
					'ort_kurzbz' => $ort,
					'location' => $new_locations[$index] ?? null
				)
			);

			if (!isSuccess($ortresult))
				return $ortresult;
		}

		return success($new_kalender_id);
	}

	private function _loadKalenderEntry($kalender_id)
	{
		$this->_ci->KalenderModel->addSelect('
			tbl_kalender.*,
			le.lehreinheit_id,
			orte.ort_kurzbz,
			orte.location
    ');

		$this->_ci->KalenderModel->addJoin(
			'(SELECT kalender_id,
				 array_agg(lehreinheit_id) AS lehreinheit_id
			FROM lehre.tbl_kalender_lehreinheit
			GROUP BY kalender_id) le',
			'tbl_kalender.kalender_id = le.kalender_id',
			'LEFT'
		);

		$this->_ci->KalenderModel->addJoin(
			'(SELECT kalender_id,
				array_agg(ort_kurzbz ORDER BY ort_kurzbz) AS ort_kurzbz,
				array_agg(location ORDER BY ort_kurzbz) AS location
			FROM lehre.tbl_kalender_ort
			GROUP BY kalender_id) orte',
			'tbl_kalender.kalender_id = orte.kalender_id',
			'LEFT'
		);

		$result = $this->_ci->KalenderModel->load(array('tbl_kalender.kalender_id' => $kalender_id));

		if (isError($result)) return $result;
		if (!hasData($result))
			return error(['message' => 'Not found', 'errorCode' => 'not_found']);

		$entry = getData($result)[0];
		$entry->lehreinheit_id = $entry->lehreinheit_id ?? [];
		$entry->ort_kurzbz = $entry->ort_kurzbz ?? [];
		$entry->location = $entry->location ?? [];

		return success($entry);
	}



	public function deleteEntry($kalender_id)
	{
		$result = $this->_ci->KalenderModel->load(array('tbl_kalender.kalender_id' => $kalender_id));

		if (isError($result)) return $result;

		if (!hasData($result))
			return error(['message' => 'Not found', 'errorCode' => 'not_found']);

		$entry = getData($result)[0];

		if ($entry->typ === 'lehreinheit')
			return $this->_deleteTypLehreinheit($entry);
		if ($entry->typ === 'reservierung')
			return $this->_deleteTypReservierung($entry);
	}

	private function _deleteTypReservierung($entry)
	{
		$result_event = $this->_ci->KalenderEventModel->loadWhere(array('kalender_id' => $entry->kalender_id));
		if (isError($result_event)) return $result_event;
		$has_event = hasData($result_event) ? getData($result_event)[0] : false;

		$result_teilnehmer_event = $this->_ci->KalenderEventTeilnehmerModel->loadWhere(array('kalender_id' => $entry->kalender_id));
		if (isError($result_teilnehmer_event))
			return $result_teilnehmer_event;
		$has_teilnehmer_event = hasData($result_teilnehmer_event) ? getData($result_teilnehmer_event)[0] : false;

		$result_ort = $this->_ci->KalenderOrtModel->loadWhere(array('kalender_id' => $entry->kalender_id));
		if (isError($result_ort)) return $result_ort;
		$has_ort = hasData($result_ort) ? getData($result_ort)[0] : false;

		if ($has_ort)
			$this->_deleteOrtEntry($has_ort);

		if ($has_teilnehmer_event)
			$this->_deleteReservierungTeilnehmerEntry($has_teilnehmer_event);

		if ($has_event)
			$this->_deleteReservierungEntry($has_event);

		$this->_ci->KalenderModel->delete(array('tbl_kalender.kalender_id' => $entry->kalender_id));

		return success();
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
		else if ($entry->status_kurzbz === 'sync_preview' || $entry->status_kurzbz === 'sync_live')
		{
			$history = $this->getHistory($entry->kalender_id);
			$history = hasData($history) ? getData($history) : false;

			if ($has_ort)
				$result = $this->_deleteOrtEntry($has_ort);

			if ($has_lehreinheit)
				$result = $this->_deleteLehreinheitEntry($has_lehreinheit);

			$this->_ci->KalenderModel->delete(array('tbl_kalender.kalender_id' => $entry->kalender_id));
		}
		else if ($entry->status_kurzbz === 'preview' || $entry->status_kurzbz === 'live')
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
	private function _deleteReservierungTeilnehmerEntry($entry)
	{
		return $this->_ci->KalenderEventTeilnehmerModel->delete(array('kalender_id' => $entry->kalender_id));
	}

	private function _deleteReservierungEntry($entry)
	{
		return $this->_ci->KalenderEventModel->delete(array('kalender_id' => $entry->kalender_id));
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

		$this->_ci->KalenderModel->addSelect('tbl_kalender.*, tbl_kalender_ort.ort_kurzbz, tbl_kalender_ort.location');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'tbl_kalender.kalender_id = tbl_kalender_ort.kalender_id', 'LEFT');
		$this->_ci->KalenderModel->db->where_in('status_kurzbz', array('sync_live', 'sync_preview', 'todelete', 'planning', 'preview'));
		$this->_ci->KalenderModel->addOrder('tbl_kalender.kalender_id', 'DESC');
		$to_update = $this->_ci->KalenderModel->load();

		if (!hasData($to_update))
			return success();

		$mail_infos = [];

		foreach (getData($to_update) as $entry)
		{
			if ($entry->status_kurzbz === 'todelete')
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'deleted'));
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'deleted', 'notify' => array('lektor', 'student'));
			}

			if ($entry->status_kurzbz === 'sync_preview')
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'preview'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, true);
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'preview', 'notify' => array('lektor'));
			}

			if ($entry->status_kurzbz === 'sync_live' || $entry->status_kurzbz === 'planning')
			{
				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'live'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, false);
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'live', 'notify' => array('lektor', 'student'));
			}

			if ($entry->status_kurzbz === 'preview')
			{
				$current = getData($this->_ci->KalenderModel->load(array('kalender_id' => $entry->kalender_id)))[0];
				if ($current->status_kurzbz === 'archived')
					continue;

				$this->_ci->KalenderModel->update(array('kalender_id' => $entry->kalender_id), array('status_kurzbz' => 'live'));
				$this->_archiveVorgaenger($entry->vorgaenger_kalender_id, false);
				$mail_infos[] = array('entry' => $entry, 'new_status' => 'live', 'notify' => array('lektor', 'student'));
			}
		}

		$this->_ci->load->library('KalenderNotificationLib');
		$this->_ci->kalendernotificationlib->sendMails($mail_infos);
		return success();

	}

	private function _archiveVorgaenger($vorgaenger_kalender_id, $stop_at_live = false)
	{
		if (!$vorgaenger_kalender_id) return;

		$current_id = $vorgaenger_kalender_id;

		while ($current_id)
		{
			$vorgaenger = $this->_ci->KalenderModel->load(array('kalender_id' => $current_id));
			if (!hasData($vorgaenger)) return;

			$vorgaenger = getData($vorgaenger)[0];

			if ($stop_at_live && $vorgaenger->status_kurzbz === 'live')
				return;

			if (in_array($vorgaenger->status_kurzbz, array('preview', 'live')))
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
