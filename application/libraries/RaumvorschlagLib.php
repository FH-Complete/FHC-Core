<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

use CI3_Events as Events;

class RaumvorschlagLib
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
		$this->_ci->load->model('ressource/Zeitsperre_model', 'ZeitsperreModel');
		$this->_ci->load->model('ressource/Stunde_model', 'StundeModel');
		$this->_ci->load->model('education/Lehreinheitgruppe_model', 'LehreinheitgruppeModel');

		$this->_ci->load->library('CollisionChecker');
		$this->_ci->load->library('KalenderLib');
		$this->_ci->load->library('PhrasesLib', array('ui'));
		$this->_ci->load->library('VariableLib', array('uid' => getAuthUID()));
	}


	public function getVorschlaege($kalender_id)
	{
		$event = $this->_ci->kalenderlib->getByKalenderId($kalender_id);
		$event = $event[0];
		return $this->_getVorschlaegeForEvent($event);
	}

	public function getVorschlaegeByLehreinheit($lehreinheit_id, $von, $bis)
	{
		$teilnehmer = $this->_getLehreinheitTeilnehmer($lehreinheit_id);

		$event = (object) [
			'kalender_id' => null,
			'lehreinheit_id' => [$lehreinheit_id],
			'isostart' => (new DateTime($von))->format('c'),
			'isoend' => (new DateTime($bis))->format('c'),
			'datum' => (new DateTime($von))->format('Y-m-d'),
			'lektor' => $teilnehmer->lektor_data,
			'gruppe' => $teilnehmer->gruppen_data,
		];

		return $this->_getVorschlaegeForEvent($event);
	}

	public function getVorschlaegeSlots($lehreinheit_id, $start_date, $end_date)
	{
		$lehreinheit_result = $this->_ci->LehreinheitModel->load($lehreinheit_id);
		if (!hasData($lehreinheit_result))
			return error('Lehreinheit nicht gefunden');
		$lehreinheit = getData($lehreinheit_result)[0];

		$block = $lehreinheit->stundenblockung;
		if ($block <= 0)
			return error('Stundenblockung ist ungültig');

		$this->_ci->StundeModel->addOrder('stunde', 'ASC');
		$stunden_result = $this->_ci->StundeModel->load();
		$stunden = hasData($stunden_result) ? array_values(getData($stunden_result)) : [];

		if (empty($stunden))
			return error('Kein Stunden-Raster gefunden');

		$teilnehmer = $this->_getLehreinheitTeilnehmer($lehreinheit_id);
		$grp = $this->_splitGruppen($teilnehmer->gruppen_data);
		$lektor_uids = array_column($teilnehmer->lektor_data, 'mitarbeiter_uid');

		$verplante_events = $this->_ci->kalenderlib->getForRaumvorschlag(
			$start_date,
			$end_date,
			$lektor_uids,
			$grp->gruppen_kurzbz,
			$grp->lehrverband_gruppen
		);

		$start_day = strtotime($start_date);
		$end_day = strtotime($end_date);

		$moegliche_slots = array();

		$verplante_zeitsperren = $this->_getZeitsperen($start_date, $end_date, $lektor_uids);
		while ($start_day <= $end_day)
		{
			$tag = date('Y-m-d', $start_day);

			$verplante_tages_events = array_values(array_filter($verplante_events, function($event) use ($tag)
			{
				return $event->datum === $tag;
			}));

			foreach ($stunden as $index => $start_stunde)
			{
				if (!isset($stunden[$index + $block - 1]))
					continue;

				$end_stunde = $stunden[$index + $block - 1];

				$slot_start = $tag . ' ' . $start_stunde->beginn;
				$slot_end = $tag . ' ' . $end_stunde->ende;

				if (!$this->_isFrei($slot_start, $slot_end, $verplante_zeitsperren, $lektor_uids, 'lektor'))
					continue;

				if (!$this->_isFrei($slot_start, $slot_end, $verplante_tages_events, $lektor_uids, 'lektor'))
					continue;

				if (!$this->_isFrei($slot_start, $slot_end, $verplante_tages_events, $grp->gruppen_kurzbz, 'gruppe'))
					continue;

				if (!$this->_isFrei($slot_start, $slot_end, $verplante_tages_events, $grp->lehrverband_gruppen_bezeichnung, 'lehrverband'))
					continue;

				$moegliche_slots[] = [
					'isostart' => (new DateTime($slot_start))->format('c'),
					'isoend' => (new DateTime($slot_end))->format('c'),
					'marker_isoend' => (new DateTime($tag . ' ' . $start_stunde->ende))->format('c'),
				];
			}

			$start_day = strtotime('+1 day', $start_day);
		}


		$slots = [];
		foreach ($moegliche_slots as $slot)
		{
			$event_obj = (object) [
				'kalender_id' => null,
				'lehreinheit_id' => [$lehreinheit_id],
				'isostart' => $slot['isostart'],
				'isoend' => $slot['isoend'],
			];

			$raumkandidaten = $this->_getRaumkandidaten($event_obj);

			if (empty($raumkandidaten))
				continue;

			$ratings = $this->_getRatings($verplante_events, $slot, $raumkandidaten, $lektor_uids, $grp->gruppen_kurzbz, $grp->lehrverband_gruppen_bezeichnung);
			$best = $ratings[0];
			$anzahl_weitere = count($ratings) - 1;

			$slots[] = [
				'isostart' => $slot['isostart'],
				'isoend' => $slot['isoend'],
				'marker_isoend' => $slot['marker_isoend'],
				'rating' => $this->_scoreToRating($best['score']),
				'label' => $anzahl_weitere > 0 ? $best['ort_kurzbz'] . ' (' . $best['score'] . ') + ' . $anzahl_weitere . ' weitere' : $best['ort_kurzbz'] . ' (' . $best['score'] . ')',
				'raeume' => $ratings,
			];
		}
		return success($slots);
	}

	private function _getZeitsperen($start_date, $end_date, $uids)
	{

		if (empty($uids)) return [];

		if (($this->_ci->variablelib->getVar('ignore_kollision') === 'true') || ($this->_ci->variablelib->getVar('ignore_zeitsperre') === 'true')) return [];


		$this->_ci->ZeitsperreModel->addSelect('mitarbeiter_uid, vondatum, vonstunde_z.beginn as von_beginn, bisdatum, bisstunde_z.ende as bis_ende');
		$this->_ci->ZeitsperreModel->addJoin('lehre.tbl_stunde vonstunde_z', 'vonstunde_z.stunde = tbl_zeitsperre.vonstunde', 'LEFT');
		$this->_ci->ZeitsperreModel->addJoin('lehre.tbl_stunde bisstunde_z', 'bisstunde_z.stunde = tbl_zeitsperre.bisstunde', 'LEFT');
		$this->_ci->ZeitsperreModel->db->where('zeitsperretyp_kurzbz !=', 'ZVerfueg');
		$this->_ci->ZeitsperreModel->db->where('(tbl_zeitsperre.vondatum + COALESCE(vonstunde_z.beginn, \'00:00\'))::timestamp <', $end_date);
		$this->_ci->ZeitsperreModel->db->where('(tbl_zeitsperre.bisdatum + COALESCE(bisstunde_z.ende, \'23:59\'))::timestamp >', $start_date);

		$this->_ci->ZeitsperreModel->db->where_in('mitarbeiter_uid', $uids);
		$result = $this->_ci->ZeitsperreModel->load();

		if (!hasData($result)) return [];

		$events = [];
		foreach (getData($result) as $row)
		{
			$von = new DateTime($row->vondatum . ' ' . ($row->von_beginn ?? '00:00'));
			$bis = new DateTime($row->bisdatum . ' ' . ($row->bis_ende ?? '23:59'));

			$events[] = (object)[
				'isostart' => $von->format('c'),
				'isoend' => $bis->format('c'),
				'lektor' => [['mitarbeiter_uid' => $row->mitarbeiter_uid]]
			];
		}
		return $events;
	}

	private function _getLehreinheitTeilnehmer($lehreinheit_id)
	{
		$lektoren_result = $this->_ci->LehreinheitMitarbeiterModel->loadWhere(['lehreinheit_id' => $lehreinheit_id]);
		$lektor_data = [];
		if (hasData($lektoren_result))
		{
			$lektor_data = getData($lektoren_result);
		}

		$gruppen_result = $this->_ci->LehreinheitgruppeModel->getByLehreinheit($lehreinheit_id);
		$gruppen_data = [];
		if (hasData($gruppen_result))
		{
			$gruppen_rows = getData($gruppen_result);

			foreach ($gruppen_rows as $gruppe)
			{
				$gruppen_data[] = (array) $gruppe;
			}
		}


		return (object)[
			'lektor_data' => $lektor_data,
			'gruppen_data' => $gruppen_data,
		];

	}

	private function _splitGruppen($gruppen_data)
	{
		$gruppen_kurzbz = array_values(array_filter(array_column($gruppen_data, 'gruppe_kurzbz')));
		$lehrverband_gruppen = array_values(array_filter($gruppen_data, function($gruppe)
		{
			return empty($gruppe['gruppe_kurzbz']);
		}));
		$lehrverband_gruppen_bezeichnung = array_column($lehrverband_gruppen, 'bezeichnung');

		return (object)[
			'gruppen_kurzbz' => $gruppen_kurzbz,
			'lehrverband_gruppen' => $lehrverband_gruppen,
			'lehrverband_gruppen_bezeichnung' => $lehrverband_gruppen_bezeichnung
		];

	}
	//TODO (david) umbauen auf CollisionChecks
	private function _isFrei($slot_start, $slot_end, $events, $check_array, $type)
	{
		if (empty($check_array)) return true;

		$slot_start_ts = strtotime($slot_start);
		$slot_end_ts = strtotime($slot_end);

		foreach ($events as $event)
		{
			$event_start_ts = strtotime($event->isostart);
			$event_end_ts = strtotime($event->isoend);

			if ($event_end_ts <= $slot_start_ts || $event_start_ts >= $slot_end_ts)
				continue;

			$event_uids = $this->_getIds($event, $type);

			if (!empty(array_intersect($event_uids, $check_array)))
				return false;
		}

		return true;
	}

	private function _scoreToRating($score)
	{
		if ($score >= 90)
			return 'good';
		if ($score >= 60)
			return 'mid';
		return 'bad';
	}
	private function _getVorschlaegeForEvent($event)
	{
		$raumkandidaten = $this->_getRaumkandidaten($event);
		if (empty($raumkandidaten)) return [];

		$lektor_uids = array_column($event->lektor, 'mitarbeiter_uid');
		$grp = $this->_splitGruppen($event->gruppe);

		$tages_events = $this->_ci->kalenderlib->getForRaumvorschlag(
			$event->datum,
			$event->datum,
			$lektor_uids,
			$grp->gruppen_kurzbz,
			$grp->lehrverband_gruppen
		);

		return $this->_getRatings($tages_events, $event, $raumkandidaten, $lektor_uids, $grp->gruppen_kurzbz, $grp->lehrverband_gruppen_bezeichnung);
	}
	private function _getRatings($events, $event, $raumkandidaten, $lektor_uids, $gruppen_kurzbz, $lehrverband_gruppen_bezeichnung)
	{
		$event = (object)$event;
		$lektor_davor = $this->_getEventDavor($events, $event->isostart, $lektor_uids, 'lektor');
		$gruppen_davor = $this->_getEventDavor($events, $event->isostart, $gruppen_kurzbz, 'gruppe');
		$lehrverband_davor = $this->_getEventDavor($events, $event->isostart, $lehrverband_gruppen_bezeichnung, 'lehrverband');

		$lektor_davor_ort = $lektor_davor ? $this->_getOrtDetails($lektor_davor->ort_kurzbz) : null;
		$gruppen_davor_ort = $gruppen_davor ? $this->_getOrtDetails($gruppen_davor->ort_kurzbz) : null;
		$lehrverband_davor_ort = $lehrverband_davor ? $this->_getOrtDetails($lehrverband_davor->ort_kurzbz) : null;

		$unique = [];

		foreach ($raumkandidaten as $raum)
		{
			$unique[$raum->ort_kurzbz] = $raum;
		}

		$kandidaten = array_values($unique);


		$ratings = [];
		foreach ($kandidaten as $raum)
		{
			$rating = ['ort_kurzbz' => $raum->ort_kurzbz, 'score' => 100, 'details' => [],
				'raumtyp_kurzbz' => $raum->raumtyp_kurzbz, 'max_person' => $raum->max_person];
			$this->_rateLektor($rating, $raum, $lektor_davor_ort);
			$this->_rateGruppen($rating, $raum, $gruppen_davor_ort);
			$this->_rateGruppen($rating, $raum, $lehrverband_davor_ort);

			Events::trigger('room_rating',
				function & () use (&$rating) {
					return $rating;
				},
				$raum,
				$event
			);
			$ratings[] = $rating;
		}

		usort($ratings, function($a, $b)
		{
			return $b['score'] <=> $a['score'];
		});

		return $ratings;

	}

	private function _getOrtDetails($ort_kurzbz_array)
	{
		$ort_kurzbz_array = (array) $ort_kurzbz_array;
		if (empty($ort_kurzbz_array)) return [];
		$this->_ci->OrtModel->addSelect('ort_kurzbz, stockwerk, standort_id');
		$this->_ci->OrtModel->db->where_in('ort_kurzbz', $ort_kurzbz_array);
		$result = $this->_ci->OrtModel->load();

		return hasData($result) ? getData($result) : [];
	}

	private function _rateLektor(&$rating, $raum, $lektor_davor_orte)
	{
		if (empty($lektor_davor_orte)) return;

		foreach ($lektor_davor_orte as $ort)
		{
			if ($ort->ort_kurzbz === $raum->ort_kurzbz)
			{
				$rating['score'] += 20;
				$rating['details'][] = '+20 ' . $this->_ci->phraseslib->t('ui', 'lecturer_already_here');
				return;
			}
		}

		$gleiches_gebaeude = array_filter($lektor_davor_orte, function($ort) use ($raum)
		{
			return $ort->standort_id === $raum->standort_id;
		});

		if (empty($gleiches_gebaeude))
		{
			$rating['score'] -= 20;
			$rating['details'][] = '-20 '. $this->_ci->phraseslib->t('ui', 'lecturer_building_change');
			return;
		}

		$diffs = array_map(function($ort) use ($raum)
		{
			return abs($ort->stockwerk - $raum->stockwerk);
		}, $gleiches_gebaeude);

		$diff = min($diffs);

		if ($diff > 0)
		{
			$rating['score'] -= $diff * 5;
			$rating['details'][] = '-' . ($diff * 5) . ' ' . $this->_ci->phraseslib->t('ui', 'lecturer_floor_change');
		}
	}

	private function _rateGruppen(&$rating, $raum, $gruppen_davor_orte)
	{
		if (empty($gruppen_davor_orte)) return;

		foreach ($gruppen_davor_orte as $ort)
		{
			if ($ort->ort_kurzbz === $raum->ort_kurzbz)
			{
				$rating['score'] += 10;
				$rating['details'][] = '+10 ' . $this->_ci->phraseslib->t('ui', 'student_already_here');
				return;
			}
		}

		$gleiches_gebaeude = array_filter($gruppen_davor_orte, function($ort) use ($raum)
		{
			return $ort->standort_id === $raum->standort_id;
		});

		if (empty($gleiches_gebaeude))
		{
			$rating['score'] -= 20;
			$rating['details'][] = '-20 '. $this->_ci->phraseslib->t('ui', 'student_building_change');
			return;
		}

		$diffs = array_map(function($ort) use ($raum)
		{
			return abs($ort->stockwerk - $raum->stockwerk);
		}, $gleiches_gebaeude);

		$diff = min($diffs);

		if ($diff > 0)
		{
			$rating['score'] -= $diff * 5;
			$rating['details'][] = '-' . ($diff * 5) . ' '. $this->_ci->phraseslib->t('ui', 'student_floor_change');
		}
	}

	private function _getEventDavor($events, $von, $uids, $type)
	{
		$kandidat = null;

		foreach ($events as $event)
		{
			if ($event->isoend > $von)
				continue;

			//Wenn zwischen zwei Events eine 30+ Minuten Pause liegt, wird das Event davor nicht berücksichtigt
			if ((strtotime($von) - strtotime($event->isoend)) > 30 * 60)
				continue;

			if (empty($event->ort_kurzbz))
				continue;

			$event_uids = $this->_getIds($event, $type);

			if (empty(array_intersect($event_uids, $uids)))
				continue;

			if ($kandidat === null || $event->isoend > $kandidat->isoend)
				$kandidat = $event;
		}

		return $kandidat;
	}

	private function _getIds($event, $type)
	{
		$event_uids = array();

		if ($type === 'lektor')
			$event_uids = array_column($event->lektor, 'mitarbeiter_uid');
		else if ($type === 'gruppe')
			$event_uids = array_column($event->gruppe, 'gruppe_kurzbz');
		else if ($type === 'lehrverband')
			$event_uids = array_column($event->gruppe, 'bezeichnung');

		return $event_uids;
	}

	private function _getRaumkandidaten($event)
	{
		$raumtyp = null;
		$raumtypalternativ = null;

		if (!empty($event->lehreinheit_id))
		{
			$lehreinheit = $this->_ci->LehreinheitModel->load($event->lehreinheit_id[0]);
			if (hasData($lehreinheit))
			{
				$lehreinheit = getData($lehreinheit)[0];
				$raumtyp = $lehreinheit->raumtyp ?? null;
				$raumtypalternativ = $lehreinheit->raumtypalternativ ?? null;
			}
		}

		$this->_ci->KalenderModel->addDistinct('tbl_kalender_ort.ort_kurzbz');
		$this->_ci->KalenderModel->addSelect('tbl_kalender_ort.ort_kurzbz');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'tbl_kalender.kalender_id = tbl_kalender_ort.kalender_id');
		$this->_ci->KalenderModel->db->where('tbl_kalender.von <', $event->isoend);
		$this->_ci->KalenderModel->db->where('tbl_kalender.bis >', $event->isostart);
		if (!is_null($event->kalender_id))
		{
			$this->_ci->KalenderModel->db->where('tbl_kalender.kalender_id !=', $event->kalender_id);
		}
		$this->_ci->KalenderModel->db->where_not_in('tbl_kalender.status_kurzbz', ['deleted', 'to_delete', 'to_delete_live', 'to_delete_preview', 'archived']);
		$this->_ci->KalenderModel->db->where('tbl_kalender_ort.ort_kurzbz IS NOT NULL', null, false);
		$this->_ci->KalenderModel->db->where(
			'NOT EXISTS (
				SELECT 1 FROM lehre.tbl_kalender nachfolger
				WHERE nachfolger.vorgaenger_kalender_id = tbl_kalender.kalender_id
			)', null, false
		);

		$belegte = $this->_ci->KalenderModel->load();

		$belegte_orte = hasData($belegte) ? array_column(getData($belegte), 'ort_kurzbz') : [];

		if (empty($raumtyp))
		{
			$raeume = $this->_getFreieRaeume(null, $belegte_orte);
			return hasData($raeume) ? getData($raeume) : [];
		}

		$vorschlaege = [];

		$raeume = $this->_getFreieRaeume($raumtyp, $belegte_orte);
		if (hasData($raeume))
			$vorschlaege = getData($raeume);

		if (count($vorschlaege) < 5 && !empty($raumtypalternativ))
		{
			$bereits_gefunden = array_merge($belegte_orte, array_column($vorschlaege, 'ort_kurzbz'));
			$alternativ = $this->_getFreieRaeume($raumtypalternativ, $bereits_gefunden);

			if (!isError($alternativ) && hasData($alternativ))
				$vorschlaege = array_merge($vorschlaege, getData($alternativ));
		}

		return $vorschlaege;
	}

	private function _getFreieRaeume($raumtyp, $belegte_orte)
	{
		$this->_ci->OrtModel->addSelect('ort_kurzbz, stockwerk, standort_id, max_person, raumtyp_kurzbz');
		$this->_ci->OrtModel->addJoin('public.tbl_ortraumtyp', 'ort_kurzbz');

		if (!empty($raumtyp))
			$this->_ci->OrtModel->db->where('raumtyp_kurzbz', $raumtyp);
		else
			$this->_ci->OrtModel->db->where('reservieren', true);

		$this->_ci->OrtModel->db->where('aktiv', true);
		$this->_ci->OrtModel->db->where("ort_kurzbz NOT LIKE '\_%'", null, false);

		if (!empty($belegte_orte))
			$this->_ci->OrtModel->db->where_not_in('ort_kurzbz', $belegte_orte);
		$this->_ci->OrtModel->addOrder('hierarchie, ort_kurzbz');

		return $this->_ci->OrtModel->load();
	}
}
