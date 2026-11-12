<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class LvPlanCalendarLib
{
	private $_ci;

	public function __construct($loggeduid)
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('ressource/Kalender_model', 'KalenderModel');
		$this->_ci->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
 		$this->_ci->load->model('ressource/Ort_model', 'OrtModel');
 		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
 		$this->_ci->load->model('system/Sprache_model', 'SpracheModel');
		$this->_ci->load->model('organisation/Ferien_model', 'FerienModel');


		if (!(isset($loggeduid['uid']) && !isEmptyString($loggeduid['uid'])))
		{
			$loggeduid["uid"] = getAuthUID();
		}

		$this->_ci->load->library('CollisionChecker', array('uid' => $loggeduid['uid']));
	}
	
	public function getEventsByTeachingUnit($startDate, $endDate, $lehreinheit_id)
	{
		if ($lehreinheit_id === null || $lehreinheit_id === '')
			return [];

		$this->_buildBasePlanQuery();

		$db = $this->_ci->KalenderModel->db;

		$escapedLehreinheitId = $db->escape($lehreinheit_id);
 
		$db->where("EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_lehreinheit kalender_lehreinheit
			JOIN lehre.tbl_lehreinheit lehreinheit
				ON lehreinheit.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
			WHERE kalender_lehreinheit.kalender_id = tbl_kalender.kalender_id
				AND lehreinheit.lehreinheit_id = $escapedLehreinheitId
		)", null, false);

		$data = $this->_loadFilteredBasePlan($startDate, $endDate);
		$mappedEvents = $this->_mapEvents($data);
 
		return $this->_mapCalendarEventsToOldStundenplanFormat($mappedEvents);
	}

	public function getEventsByLV($startDate, $endDate, $lvId)
	{
		if ($lvId === null || $lvId === '')
			return [];

		$this->_buildBasePlanQuery();

		$db = $this->_ci->KalenderModel->db;

		$escapedLvId = $db->escape($lvId);
 
		$db->where("EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_lehreinheit kalender_lehreinheit
			JOIN lehre.tbl_lehreinheit lehreinheit
				ON lehreinheit.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
			WHERE kalender_lehreinheit.kalender_id = tbl_kalender.kalender_id
			AND lehreinheit.lehrveranstaltung_id = $escapedLvId
				
		)", null, false);

		$data = $this->_loadFilteredBasePlan($startDate, $endDate);
		$mappedEvents = $this->_mapEvents($data);
 
		return $this->_mapCalendarEventsToOldStundenplanFormat($mappedEvents);
	}

	public function getEvents($startDate, $endDate, $degreeProgram, $semester = null, $section = null, $group = null)
	{
		if ($degreeProgram === null || $degreeProgram === '')
			return [];

		$this->_buildBasePlanQuery();

		$db = $this->_ci->KalenderModel->db;

		$escapedDegreeProgram = $db->escape($degreeProgram);

		// Optional Lehrverband filters, all applied to the same tbl_lehrverband row
		$lehrverbandFilters = '';
		if ($semester !== null && $semester !== '')
			$lehrverbandFilters .= ' AND lehrverband.semester = ' . $db->escape($semester);
		if ($section !== null && $section !== '')
			$lehrverbandFilters .= ' AND lehrverband.verband = ' . $db->escape($section);
		if ($group !== null && $group !== '')
			$lehrverbandFilters .= ' AND lehrverband.gruppe = ' . $db->escape($group);

		// Checks if the current calendar event is linked to a Lehreinheit whose Lehrveranstaltung
		// (via lehrveranstaltung_id or lehrfach_id) belongs to a degree program that has a Lehrverband
		// matching the given degree program, semester, Verband (section) and group.
		$lehrverbandLehreinheitExists = "EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_lehreinheit kalender_lehreinheit
			JOIN lehre.tbl_lehreinheit lehreinheit
				ON lehreinheit.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
			JOIN lehre.tbl_lehrveranstaltung lehrveranstaltung
				ON lehrveranstaltung.lehrveranstaltung_id IN (lehreinheit.lehrveranstaltung_id, lehreinheit.lehrfach_id)
			JOIN public.tbl_lehrverband lehrverband
				ON lehrverband.studiengang_kz = lehrveranstaltung.studiengang_kz
			WHERE kalender_lehreinheit.kalender_id = tbl_kalender.kalender_id
				AND lehrverband.studiengang_kz = $escapedDegreeProgram
				$lehrverbandFilters
		)";

		$db->where($lehrverbandLehreinheitExists, null, false);

		$data = $this->_loadFilteredBasePlan($startDate, $endDate);
		$mappedEvents = $this->_mapEvents($data);
		
		return $this->_mapCalendarEventsToOldStundenplanFormat($mappedEvents);
	}

	public function getEventsForRoom($start, $end, $roomId = null)
	{
		if (is_null($roomId))
			return error("No roomId");

		$this->_buildBasePlanQuery();

		$escapedRoomId = $this->_ci->KalenderModel->db->escape($roomId);

		// Checks if the current calendar event is associated with a room that has the given room ID.
		$isRoomAssociated = "EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_ort kalender_ort
			WHERE kalender_ort.kalender_id = tbl_kalender.kalender_id
				AND kalender_ort.ort_kurzbz = $escapedRoomId
		)";

		$this->_ci->KalenderModel->db->where('tbl_kalender.status_kurzbz', 'live');
		$this->_ci->KalenderModel->db->where($isRoomAssociated, null, false);

		$data = $this->_loadFilteredBasePlan($start, $end);
		
		return $this->_mapCalendarEventsToOldStundenplanFormat($this->_mapEvents($data));
	}

	public function getEventsForUser($start_date, $end_date, $uid = null, $arePreviewEventsShown = false)
	{
		
		$this->_ci->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		if (!$uid) {
			$uid = getAuthUID();
		}
		
		if (is_null($uid))
			return error("No UID");
		
		$this->_buildBasePlanQuery();
		
		$escapedUserUID = $this->_ci->KalenderModel->db->escape($uid);

		// Checks if the user is assigned as a lecturer to any teaching unit(lehreinheit) associated with the current calendar event.
		$isTeachingUnitStaffMember = "EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_lehreinheit kalender_lehreinheit
			JOIN lehre.tbl_lehreinheitmitarbeiter lehreinheitmitarbeiter
				ON lehreinheitmitarbeiter.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
			WHERE kalender_lehreinheit.kalender_id = tbl_kalender.kalender_id
				AND lehreinheitmitarbeiter.mitarbeiter_uid = $escapedUserUID
		)";

		// Checks if the user is directly assigned to the current calendar reservation event, regardless of their role.
		$isDirectReservationParticipant = "EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_event_teilnehmer reservation_teilnehmer
			WHERE reservation_teilnehmer.kalender_id = tbl_kalender.kalender_id
				AND reservation_teilnehmer.uid = $escapedUserUID
		)";

		// Checks if the user belongs to a group assigned to the current calendar reservation event, matching the study semester if specified.
		$isGroupReservationParticipant = "EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_event_teilnehmer reservation_teilnehmer
			JOIN public.tbl_benutzergruppe benutzergruppe
				ON benutzergruppe.gruppe_kurzbz = reservation_teilnehmer.gruppe_kurzbz
				AND (
					reservation_teilnehmer.studiensemester_kurzbz IS NULL
					OR benutzergruppe.studiensemester_kurzbz = reservation_teilnehmer.studiensemester_kurzbz
				)
			WHERE reservation_teilnehmer.kalender_id = tbl_kalender.kalender_id
				AND reservation_teilnehmer.gruppe_kurzbz IS NOT NULL
				AND benutzergruppe.uid = $escapedUserUID
		)";

		// Checks if the user is directly assigned to the current calendar reservation event, regardless of their role.
		$isDirectReservationParticipant = "EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_event_teilnehmer reservation_teilnehmer
			WHERE reservation_teilnehmer.kalender_id = tbl_kalender.kalender_id
				AND reservation_teilnehmer.uid = $escapedUserUID
		)";

		// Checks if the user is part of a student group (studentlehrverband) that is assigned to the current calendar reservation event, matching the study program, semester, and group if specified.
		$isStudentGroupReservationParticipant = "EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_event_teilnehmer kalender_event_teilnehmer
			JOIN public.tbl_studentlehrverband studentlehrverband
				ON studentlehrverband.student_uid = $escapedUserUID
				AND studentlehrverband.studiengang_kz = kalender_event_teilnehmer.studiengang_kz
				AND (
					kalender_event_teilnehmer.studiensemester_kurzbz IS NULL
					OR studentlehrverband.studiensemester_kurzbz = kalender_event_teilnehmer.studiensemester_kurzbz
				)
				AND (
					kalender_event_teilnehmer.semester IS NULL
					OR studentlehrverband.semester = kalender_event_teilnehmer.semester
				)
				AND (
					kalender_event_teilnehmer.verband = studentlehrverband.verband
					OR kalender_event_teilnehmer.verband IS NULL
					OR btrim(kalender_event_teilnehmer.verband::text) = ''
					OR studentlehrverband.verband IS NULL
				)
				AND (
					kalender_event_teilnehmer.gruppe = studentlehrverband.gruppe
					OR kalender_event_teilnehmer.gruppe IS NULL
					OR btrim(kalender_event_teilnehmer.gruppe::text) = ''
					OR studentlehrverband.gruppe IS NULL
				)
			WHERE kalender_event_teilnehmer.kalender_id = tbl_kalender.kalender_id
				AND kalender_event_teilnehmer.gruppe_kurzbz IS NULL
				AND kalender_event_teilnehmer.studiengang_kz IS NOT NULL
		)";

		// Teaching unit can be assigned to a group (biw-223) or a student group (degree program, semester, section / verband (A,C), group(1,2,3))
		// Checks if the user is a member of a group (benutzergruppe) assigned to a Lehreinheit
		// linked to the current calendar event, in the Lehreinheit's study semester.
		$isGroupAssignedToTeachingUnit = "EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_lehreinheit kalender_lehreinheit
			JOIN lehre.tbl_lehreinheit lehreinheit
				ON lehreinheit.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
			JOIN lehre.tbl_lehreinheitgruppe lehreinheitgruppe
				ON lehreinheitgruppe.lehreinheit_id = lehreinheit.lehreinheit_id
			JOIN public.tbl_gruppe gruppe
				ON gruppe.studiengang_kz = lehreinheitgruppe.studiengang_kz
				AND gruppe.semester = lehreinheitgruppe.semester
				AND gruppe.gruppe_kurzbz = lehreinheitgruppe.gruppe_kurzbz
			JOIN public.tbl_benutzergruppe benutzergruppe
				ON benutzergruppe.gruppe_kurzbz = gruppe.gruppe_kurzbz
				AND benutzergruppe.studiensemester_kurzbz = lehreinheit.studiensemester_kurzbz
			WHERE kalender_lehreinheit.kalender_id = tbl_kalender.kalender_id
				AND lehreinheitgruppe.gruppe_kurzbz IS NOT NULL
				AND benutzergruppe.uid = $escapedUserUID
		)";

		// Checks if the user belongs to a Lehrverband assigned to a Lehreinheit linked to the
		// current calendar event, matching study program, semester and study semester exactly,
		// and Verband and group where specified.
		$isStudentGroupAssignedToTeachingUnit = "EXISTS (
			SELECT 1
			FROM lehre.tbl_kalender_lehreinheit kalender_lehreinheit
			JOIN lehre.tbl_lehreinheit lehreinheit
				ON lehreinheit.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
			JOIN lehre.tbl_lehreinheitgruppe lehreinheitgruppe
				ON lehreinheitgruppe.lehreinheit_id = lehreinheit.lehreinheit_id
			JOIN public.tbl_studentlehrverband studentlehrverband
				ON studentlehrverband.student_uid = $escapedUserUID
				AND studentlehrverband.studiengang_kz = lehreinheitgruppe.studiengang_kz
				AND studentlehrverband.semester = lehreinheitgruppe.semester
				AND studentlehrverband.studiensemester_kurzbz = lehreinheit.studiensemester_kurzbz
				AND (
					lehreinheitgruppe.verband = studentlehrverband.verband
					OR lehreinheitgruppe.verband IS NULL
					OR btrim(lehreinheitgruppe.verband::text) = ''
					OR studentlehrverband.verband IS NULL
				)
				AND (
					lehreinheitgruppe.gruppe = studentlehrverband.gruppe
					OR lehreinheitgruppe.gruppe IS NULL
					OR btrim(lehreinheitgruppe.gruppe::text) = ''
					OR studentlehrverband.gruppe IS NULL
				)
			WHERE kalender_lehreinheit.kalender_id = tbl_kalender.kalender_id
				AND lehreinheitgruppe.gruppe_kurzbz IS NULL
		)";

		if ($arePreviewEventsShown) {
			$this->_ci->KalenderModel->addJoin(
				'lehre.tbl_kalender neuerer',
				"neuerer.vorgaenger_kalender_id = tbl_kalender.kalender_id AND neuerer.status_kurzbz IN ('preview', 'sync_live')",
				'LEFT'
			);
			$this->_ci->KalenderModel->db->where("(
				tbl_kalender.status_kurzbz IN ('sync_live', 'to_delete_live')
				OR (tbl_kalender.status_kurzbz = 'preview' AND neuerer.kalender_id IS NULL)
				OR (tbl_kalender.status_kurzbz = 'live' AND neuerer.kalender_id IS NULL)
			)", NULL, FALSE);
		} else {
			$this->_ci->KalenderModel->db->where('status_kurzbz', 'live');
		}

 		$this->_ci->KalenderModel->db->group_start();
		$this->_ci->KalenderModel->db->where($isTeachingUnitStaffMember, null, false);
		$this->_ci->KalenderModel->db->or_where($isDirectReservationParticipant, null, false);
		$this->_ci->KalenderModel->db->or_where($isGroupReservationParticipant, null, false);
		$this->_ci->KalenderModel->db->or_where($isStudentGroupReservationParticipant, null, false);
		$this->_ci->KalenderModel->db->or_where($isGroupAssignedToTeachingUnit, null, false);
		$this->_ci->KalenderModel->db->or_where($isStudentGroupAssignedToTeachingUnit, null, false);
		$this->_ci->KalenderModel->db->group_end();
		
		$data = $this->_loadFilteredBasePlan($start_date, $end_date);
		
		return $this->_mapCalendarEventsToOldStundenplanFormat($this->_mapEvents($data));
	}

	public function fetchFerienEventsByUser($start_date, $end_date, $uid = null)
	{
		$this->_ci->load->model('education/Studentlehrverband_model', 'StudentLehrverbandModel');

		$currentStudiensemester = $this->_ci->StudiensemesterModel->getByDate($start_date);
		if (!hasData($currentStudiensemester))
			return success([]);
		$currentStudiensemester = getData($currentStudiensemester);

		if ($currentStudiensemester) {
			$studentsemester_kurzbz = current($currentStudiensemester)->studiensemester_kurzbz;

			$studiengang = $this->_ci->StudentLehrverbandModel->loadWhere([
				"student_uid" => $uid ?? getAuthUID(),
				"studiensemester_kurzbz" => $studentsemester_kurzbz
			]);
			$studiengang = getData($studiengang);

			if ($studiengang)
				$studiengang_kz = current($studiengang)->studiengang_kz;
			else
				$studiengang_kz = 0;
		} else {
			$studiengang_kz = 0;
		}

		$ferienEvents = $this->fetchFerienTageEvents($start_date, $end_date, $studiengang_kz);
		if (hasData($ferienEvents))
			$ferienEvents = getData($ferienEvents);
		else
			$ferienEvents = [];

		return $ferienEvents;
	}

	public function fetchFerienTageEvents($start_date, $end_date, $studiengang_kz = 0)
	{
		// Load Config
		$this->_ci->load->config('calendar');

		$this->_ci->load->model('organisation/Ferien_model', 'FerienModel');

		$tz = new DateTimeZone($this->_ci->config->item('timezone'));

		$ferienEvents = $this->_ci->FerienModel->execReadOnlyQuery("
		SELECT * 
		FROM lehre.tbl_ferien
		WHERE (bisdatum >= ? AND vondatum < ?) AND (studiengang_kz = 0 OR studiengang_kz = ?)
		", [$start_date, $end_date, $studiengang_kz]);

		if (isError($ferienEvents))
			return $ferienEvents;
		
		$ferienEvents = getData($ferienEvents);

		if (!$ferienEvents)
			return [];
		
		$ferienEvents = array_map(function ($event) {
			$event_start = new DateTime($event->vondatum);
			$event_end = new DateTime($event->bisdatum);
			$event_end->modify('+1 day');

			$interval = new DateInterval('P1D');
			$period = new DatePeriod($event_start, $interval, $event_end);
			$event->dates = iterator_to_array($period);
			return $event;
		}, $ferienEvents);

		$start_date = new DateTime($start_date);
		$start_date->setTime(0, 0, 0);
		$end_date = new DateTime($end_date);
		$end_date->setTime(23, 59, 59);

		$ferienEventsFlattened = [];
		foreach ($ferienEvents as $ferien_event) {
			foreach ($ferien_event->dates as $date) {
				if ($date < $start_date || $date > $end_date)
					continue;
				$event = new stdClass();
				$event->bezeichnung = $ferien_event->bezeichnung;
				$event->datum = $date->format('Y-m-d');
				$event->type = 'ferien';
				$ferienEventsFlattened[] = $event;
			}
		};

		$today = new DateTime();
		$ferienEventsFlattened = array_map(function ($event) use ($today, $tz) {
			$ferien_event = (object)array(
				'type' => 'ferien',
				'beginn' => $today->format('H:i:s'),
				'ende' => $today->format('H:i:s'),
				'isostart' => (new DateTime($event->datum . ' 00:00:00', $tz))->format('c'),
				'isoend' => (new DateTime($event->datum . ' 23:59:59', $tz))->format('c'),
				'allDayEvent' => true,
				'datum' => $event->datum,
				'topic' => $event->bezeichnung,
				'titel' => $event->bezeichnung,
				'farbe' => '00689E'
			);
			return $ferien_event;
		}, $ferienEventsFlattened);
		
		return $ferienEventsFlattened;
	}

	public function getReservableMap($ort_kurzbz, $start_date, $end_date)
	{
		$this->_ci->load->model('ressource/Ort_model', 'OrtModel');
		$this->_ci->load->library('PermissionLib');

		$berechtigt_begrenzt  = $this->_ci->permissionlib->isBerechtigt('lehre/reservierung:begrenzt', 'suid');
		$berechtigt_erweitert = $this->_ci->permissionlib->isBerechtigt('lehre/reservierung', 'suid');

		$ort_data = $this->_ci->OrtModel->load($ort_kurzbz);
		if (isError($ort_data) || !hasData($ort_data))
			return [];

		$ort_data = getData($ort_data)[0];

		if (!$ort_data->reservieren)
			return [];

		if (!$berechtigt_begrenzt && !$berechtigt_erweitert)
			return [];

		$start_ts = is_numeric($start_date) ? (int)$start_date : strtotime($start_date);
		$end_ts = is_numeric($end_date) ? (int)$end_date : strtotime($end_date);

		if (!$start_ts || !$end_ts)
			return [];

		if ($end_ts < $start_ts)
		{
			$tmp = $start_ts;
			$start_ts = $end_ts;
			$end_ts = $tmp;
		}

		$now = time();
		$tage_min = defined('RES_TAGE_LEKTOR_MIN') ? (int)RES_TAGE_LEKTOR_MIN : 0;
		$tage_bis = defined('RES_TAGE_LEKTOR_BIS') ? (int)RES_TAGE_LEKTOR_BIS : 0;

		$datum_res_lektor_start = $this->jump_day($now, $tage_min - 1);
		$datum_res_lektor_ende = $this->jump_day($now, $tage_bis);

		$start_ymd_allowed = date('Y-m-d', $datum_res_lektor_start);
		$end_ymd_allowed = date('Y-m-d', $datum_res_lektor_ende);

		$result = [];

		$current = strtotime(date('Y-m-d', $start_ts) . ' 00:00:00');
		$end_day = strtotime(date('Y-m-d', $end_ts) . ' 00:00:00');

		while ($current <= $end_day)
		{
			$ymd = date('Y-m-d', $current);

			$result[$ymd] = ($ymd >= $start_ymd_allowed && $ymd <= $end_ymd_allowed) ? true : false;

			$current = $this->jump_day($current, 1);
		}

		return success($result);
	}

	public function getTeachersByCourse($courseId, $studiengang_kz, $studiensemester_kurzbz, $semester)
	{
		$this->_ci->load->model('person/Person_model', 'PersonModel');

		$studiensemester = $this->_ci->StudiensemesterModel->loadWhere(["studiensemester_kurzbz"=>$studiensemester_kurzbz]);
		if(isError($studiensemester))
			return error(getData($studiensemester));

		if (!hasData($studiensemester))
			return error(getData($studiensemester));

		$studiensemester = getData($studiensemester)[0];

		$query = "
			SELECT DISTINCT lehreinheitmitarbeiter.mitarbeiter_uid as uid
			FROM lehre.tbl_lehrveranstaltung lehrveranstaltung
				JOIN lehre.tbl_lehreinheit lehreinheit
					ON lehreinheit.lehrveranstaltung_id = lehrveranstaltung.lehrveranstaltung_id
				JOIN lehre.tbl_kalender_lehreinheit kalender_lehreinheit
					ON kalender_lehreinheit.lehreinheit_id = lehreinheit.lehreinheit_id
				JOIN lehre.tbl_kalender kalender
					ON kalender.kalender_id = kalender_lehreinheit.kalender_id
				JOIN lehre.tbl_lehreinheitmitarbeiter lehreinheitmitarbeiter
					ON lehreinheitmitarbeiter.lehreinheit_id = kalender_lehreinheit.lehreinheit_id
			WHERE lehrveranstaltung.lehrveranstaltung_id = ?
				AND lehrveranstaltung.studiengang_kz = ?
				AND lehrveranstaltung.semester = ?
				AND kalender.von >= ?
				AND kalender.bis <= ?
		";

		$teacherUIDsResponse = $this->_ci->KalenderModel->execReadOnlyQuery($query, [$courseId, $studiengang_kz, $semester, $studiensemester->start, $studiensemester->ende]);
		if (isError($teacherUIDsResponse)) return;

		return getData($teacherUIDsResponse);
	}

	private function _buildBasePlanQuery()
	{
		$language = $this->_getLanguageIndex();
		$index_bezeichnung_mehrsprachig = $language - 1;

		$this->_ci->KalenderModel->addSelect('tbl_kalender.kalender_id,
			tbl_kalender.eindeutige_kalender_gruppen_id,
			tbl_kalender.status_kurzbz,
			tbl_kalender.typ,
			tbl_kalender.von,
			tbl_kalender.bis,
			tbl_kalender.insertvon,
			tbl_kalender_ort.location,
			tbl_kalender_ort.ort_kurzbz,
			tbl_ort.content_id as ort_content_id,
			tbl_lehreinheit.lehreinheit_id,
			tbl_lehreinheit.anmerkung as le_anmerkung,
			tbl_lehreinheit.lehrveranstaltung_id,
			tbl_lehreinheit.lehrfach_id,
			tbl_lehreinheit.studiensemester_kurzbz as le_studiensemester_kurzbz,
			tbl_lehreinheit.lehrform_kurzbz,
			tbl_organisationseinheit.bezeichnung as organisationseinheit,
			lehrfach.kurzbz AS lehrfach_kurzbz,
			lehrfach.bezeichnung AS lehrfach_bezeichnung,
			lehrfach.kurzbz AS lehrfach_kurz_bezeichnung,
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
			teilnehmer.studiensemester_kurzbz AS teilnehmer_studiensemester_kurzbz,
			teilnehmer.rolle_kurzbz AS teilnehmer_rolle_kurzbz,
			teilnehmergruppe.gid AS teilnehmerg_grp_gid,
			verbandgruppe.gid AS verband_grp_gid,
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
			le_gruppe.mailgrp as le_mailgrp,
			CASE
				WHEN vonstunde.stunde IS NOT NULL AND bisstunde.stunde IS NOT NULL
				THEN bisstunde.stunde - vonstunde.stunde + 1
			ELSE ROUND(
					COALESCE(
						(
							SELECT SUM(EXTRACT(EPOCH FROM (LEAST(stunde.ende, tbl_kalender.bis::time) - GREATEST(stunde.beginn, tbl_kalender.von::time))) / 60) / 45
							FROM lehre.tbl_stunde stunde
							WHERE stunde.beginn < tbl_kalender.bis::time AND stunde.ende > tbl_kalender.von::time)
						,0)
				,2
			) END AS verplante_stunden
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

		$this->_ci->KalenderModel->addJoin('public.tbl_organisationseinheit', 'tbl_organisationseinheit.oe_kurzbz = tbl_lehrveranstaltung.oe_kurzbz', 'LEFT');


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
												 AND TRIM(COALESCE(tbl_lehreinheitgruppe.gruppe::text, \'\')) = TRIM(le_lehrverband.gruppe::text)', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_studiengang le_studiengang', 'le_lehrverband.studiengang_kz = le_studiengang.studiengang_kz', 'LEFT');
		$this->_ci->KalenderModel->addJoin('public.tbl_ort', 'tbl_ort.ort_kurzbz = tbl_kalender_ort.ort_kurzbz', 'LEFT');

		$this->_ci->KalenderModel->db->join('lehre.tbl_stunde vonstunde', 'tbl_kalender.von::time = vonstunde.beginn', 'LEFT', FALSE);
		$this->_ci->KalenderModel->db->join('lehre.tbl_stunde bisstunde', 'tbl_kalender.bis::time = bisstunde.ende', 'LEFT', FALSE);

		$this->_ci->KalenderModel->addOrder('tbl_kalender.eindeutige_kalender_gruppen_id', 'DESC');
	}

	private function _loadFilteredBasePlan($start_date, $end_date)
	{
		$query = $this->_buildFilteredKalenderCte($start_date, $end_date) . "\n" .
			$this->_ci->KalenderModel->db->get_compiled_select('filtered_kalender AS tbl_kalender');

		return $this->_ci->KalenderModel->execReadOnlyQuery($query);
	}


	private function _buildFilteredKalenderCte($start_date, $end_date)
	{
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));

		return sprintf(
			'WITH filtered_kalender AS MATERIALIZED (
				SELECT *
				FROM lehre.tbl_kalender
				WHERE von >= timestamp %s
					AND bis < timestamp %s
			)',
			$this->_ci->KalenderModel->db->escape(date('Y-m-d H:i:s', strtotime($start_date))),
			$this->_ci->KalenderModel->db->escape(date('Y-m-d H:i:s', strtotime($end_date)))
		);
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
					'eindeutige_kalender_gruppen_id' => $row->eindeutige_kalender_gruppen_id,
					'type' => $row->typ,
					'beginn' => $von->format('H:i:s'),
					'ende' => $bis->format('H:i:s'),
					'datum' => $von->format('Y-m-d'),
					'isostart' => $von->format('c'),
					'isoend' => $bis->format('c'),
					'status_kurzbz' => $row->status_kurzbz,
					'ort_kurzbz' => [],
					'ort_infos' => [],
					'location' => null,
					'lehrfach' => isset($row->lehrfach_kurzbz) ? $row->lehrfach_kurzbz : '',
					'farbe' => isset($row->farbe) ? $row->farbe : '',
					'lehrform' => [],
					'lehrfach_bez' => [],
					'lehrveranstaltung_id' => [],
					'lehrveranstaltung_infos' => [],
					'verplante_stunden' => $row->verplante_stunden,
					'le_studiensemester_kurzbz' => $row->le_studiensemester_kurzbz,
					'kalender_id' => $id,
					'lehreinheit_id' => [],
					'lehreinheit_infos' => [],
					'lektor' => [],
					'teilnehmer_gruppe' => [],
					'teilnehmer_person' => [],
					'gruppe' => [],
					'titel' => isset($row->titel) ? $row->titel : '',
					'beschreibung' => isset($row->beschreibung) ? $row->beschreibung : '',
					'topic' => [],
					'insertvon' => $row->insertvon
				];
			}

			if ($row->lehreinheit_id && !in_array($row->lehreinheit_id, $events[$id]->lehreinheit_id))
			{
				$events[$id]->lehreinheit_id[] = $row->lehreinheit_id;
				$events[$id]->lehreinheit_infos[] = ['lehreinheit_id' => $row->lehreinheit_id,
					'anmerkung' => (isset($row->le_anmerkung) ? $row->le_anmerkung : ''),
					'lehrfach_bezeichnung' => (isset($row->lehrfach_bezeichnung) ? $row->lehrfach_bezeichnung : ''),
					'lehrfach_kurz_bezeichnung' => (isset($row->lehrfach_kurz_bezeichnung) ? $row->lehrfach_kurz_bezeichnung : ''),
					'studiensemester_kurzbz' => (isset($row->le_studiensemester_kurzbz) ? $row->le_studiensemester_kurzbz : ''),
				];
			}

			if ($row->lehrveranstaltung_id && !in_array($row->lehrveranstaltung_id, $events[$id]->lehrveranstaltung_id))
			{
				$events[$id]->lehrveranstaltung_id[] = $row->lehrveranstaltung_id;
				$events[$id]->lehrveranstaltung_infos[] = ['lehrveranstaltung_id' => $row->lehrveranstaltung_id,
					'lehrform_kurzbz' =>  (isset($row->lehrform_kurzbz) ? $row->lehrform_kurzbz : ''),
					'lehrfach_bezeichnung' => (isset($row->lehrfach_bezeichnung) ? $row->lehrfach_bezeichnung : ''),
					'lehrfach_kurz_bezeichnung' => (isset($row->lehrfach_kurz_bezeichnung) ? $row->lehrfach_kurz_bezeichnung : ''),
					'oe_bezeichnung' => (isset($row->organisationseinheit) ? $row->organisationseinheit : ''),
					'studiensemester_kurzbz' => (isset($row->le_studiensemester_kurzbz) ? $row->le_studiensemester_kurzbz : ''),
				];
			}
			if (isset($row->ort_kurzbz) && $row->ort_kurzbz !== '' && !in_array($row->ort_kurzbz, $events[$id]->ort_kurzbz))
			{
				$events[$id]->ort_kurzbz[] = $row->ort_kurzbz;
				$events[$id]->ort_infos[] = ['ort_kurzbz' => $row->ort_kurzbz, 'ort_content_id' => $row->ort_content_id];
			}

			if (isset($row->location) && $row->location !== '' && is_null($events[$id]->location))
				$events[$id]->location = $row->location;

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
				if (!in_array($row->verband_grp, array_column($events[$id]->teilnehmer_gruppe, 'gruppe_kurzbz')))
				{
					$events[$id]->teilnehmer_gruppe[] = [
						'gruppe_kurzbz' => $row->verband_grp,
						'gid' => $row->verband_grp_gid,
						'lehrverband' => 'true',
						'studiensemester_kurzbz' => $row->teilnehmer_studiensemester_kurzbz,
						'rolle' => $row->teilnehmer_rolle_kurzbz,
					];
				}
			}

			if ($row->teilnehmerg_grp)
			{
				if (!in_array($row->teilnehmerg_grp, array_column($events[$id]->teilnehmer_gruppe, 'gruppe_kurzbz')))
				{
					$events[$id]->teilnehmer_gruppe[] = [
						'gruppe_kurzbz' => $row->teilnehmerg_grp,
						'gid' => $row->teilnehmerg_grp_gid,
						'lehrverband' => 'false',
						'studiensemester_kurzbz' => $row->teilnehmer_studiensemester_kurzbz,
						'rolle' => $row->teilnehmer_rolle_kurzbz,
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
						'mailgrp' => $row->le_mailgrp,
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

		foreach ($events as $event)
		{
			usort($event->ort_kurzbz, 'strnatcasecmp');
		}

		return array_values($events);
	}
 
	private function _getLanguageIndex()
	{
		$this->_ci->SpracheModel->addSelect('index');
		$result = $this->_ci->SpracheModel->loadWhere(array('sprache' => getUserLanguage()));

		return hasData($result) ? getData($result)[0]->index : 1;
	}

	
	private function _mapCalendarEventsToOldStundenplanFormat(array $events): array
	{
		$this->_ci->load->library('PermissionLib');

		return array_map(function ($event) {
			$now = time();
			
			$lvInfo = $event->lehrveranstaltung_infos[0] ?? [];
			$ortInfo = $event->ort_infos[0] ?? [];

			$reservierungId = $event->type === "reservierung" ? $event->kalender_id : null;

			$isEventDeletable = false;

			$hasUserLimitedReservationPermission  = $this->_ci->permissionlib->isBerechtigt('lehre/reservierung:begrenzt', 'sui');

			$allowedDeletionStartDate = $this->jump_day($now, RES_TAGE_LEKTOR_MIN - 1);
			$allowedDeletionStartDate = date('Y-m-d', $allowedDeletionStartDate);
			
			$allowedDeletionEndDate  = mktime(0, 0, 0, date('m', $now), date('d', $now) + RES_TAGE_LEKTOR_BIS, date('Y', $now));
			$allowedDeletionEndDate = date('Y-m-d', $allowedDeletionEndDate);

			$eventStartDate = is_numeric($event->isostart) ? $event->isostart : strtotime($event->isostart);
			if (!date('w', $eventStartDate)) {
				$eventStartDate = $this->jump_day($eventStartDate, 1);
			}
			$eventStartDate = date('Y-m-d', $eventStartDate);

			
			$isEventDeletable = $event->type === "reservierung" &&
				$hasUserLimitedReservationPermission &&
				(
					(isset($event->insertvon) && $event->insertvon === getAuthUID()) //||
					//(isset($event->uids) && in_array(getAuthUID(), $event->uids))
				) &&
				$eventStartDate >= $allowedDeletionStartDate &&
				$eventStartDate <= $allowedDeletionEndDate;

			$participants = $event->lektor ?? [];

			if ($event->type === "reservierung") {
				$formattedReservationParticipants = array_map(function ($item) {
					$item["mitarbeiter_uid"] = $item["uid"];
					unset($item["uid"]);

					return $item;
				}, $event->teilnehmer_person);

				$participants = array_merge($participants, $formattedReservationParticipants);
			}


			$groups = array_map(function ($group) {
				return [
					'gruppe' => $group["gruppe"] ?? ' ',
					'verband' => $group["verband"] ?? ' ',
					'semester' => (string) ($group["semester"] ?? ''),
					'studiengang_kz' => (string) ($group["studiengang_kz"] ?? ''),
					'kuerzel' => $group["gruppe_kurzbz"] ?? null,
				];
			}, $event->gruppe ?? []);

			if ($event->type === "reservierung") {
				
				$formattedReservationGroups = array_map(function ($group) {
					return [
						'gruppe' => $group["gruppe"] ?? ' ',
						'verband' => $group["verband"] ?? ' ',
						'semester' => (string) ($group["semester"] ?? ''),
						'studiengang_kz' => (string) ($group["studiengang_kz"] ?? ''),
						'kuerzel' => (string) ($group["gruppe_kurzbz"] ?? ''),
					];
				}, $event->teilnehmer_gruppe);
				$groups = array_merge($groups, $formattedReservationGroups);
			}

			return [
				'kalender_id' => $event->kalender_id,
				'reservierung_id' => $reservierungId,
				'type' => $event->type,
				'beginn' => $event->beginn,
				'ende' => $event->ende,
				'datum' => $event->datum,
				'topic' => isset($event->topic[0])
					? preg_replace('/\s+/', '-', $event->topic[0])
					: ($event->titel ?: null),
				'lektor' => $participants,
				'gruppe' => $groups,
				'ort_kurzbz' => $event->ort_kurzbz[0] ?? null,
				'lehreinheit_id' => array_map('strval', $event->lehreinheit_id ?? []),
				'titel' => $event->titel ?: null,
				'lehrfach' => $event->lehrfach,
				'lehrform' => $lvInfo["lehrform_kurzbz"] ?? null,
				'lehrfach_bez' => $lvInfo["lehrfach_bezeichnung"] ?? null,
				'organisationseinheit' => $lvInfo["oe_bezeichnung"] ?? null,
				'farbe' => $event->farbe,
				'lehrveranstaltung_id' => $event->lehrveranstaltung_id[0] ?? null,
				'isostart' => $event->isostart,
				'isoend' => $event->isoend,
				'ort_content_id' => $ortInfo->ort_content_id ?? null,
				'deletable' => $isEventDeletable
			];
		}, $events);
	}

	private function jump_day($timestamp, $days)
	{
		$days = (int)$days;
		$prefix = ($days >= 0 ? '+' : '');
		return strtotime($prefix . $days . ' days', $timestamp);
	}
}
