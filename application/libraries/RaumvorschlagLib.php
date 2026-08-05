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

		$this->_ci->load->model('education/Lehreinheitgruppe_model', 'LehreinheitgruppeModel');

		$this->_ci->load->library('CollisionChecker');
		$this->_ci->load->library('KalenderLib');
		$this->_ci->load->library('PhrasesLib', array('ui'));
	}


	public function getVorschlaege($kalender_id)
	{
		$event = $this->_ci->kalenderlib->getByKalenderId($kalender_id);
		$event = $event[0];

		$raumkandidaten = $this->_getRaumkandidaten($event);
		if (empty($kandidaten)) return [];

		$lektor_uids = array_column($event->lektor, 'mitarbeiter_uid');
		$gruppen_kurzbz = array_values(array_filter(array_column($event->gruppe, 'gruppe_kurzbz')));

		$lehrverband_gruppen = array_values(array_filter($event->gruppe, function($gruppe) {
			return empty($gruppe['gruppe_kurzbz']);
		}));

		$lehrverband_gruppen_bezeichnung = array_column($lehrverband_gruppen, 'bezeichnung');

		$tages_events = $this->_ci->kalenderlib->getForRaumvorschlag(
			$event->datum,
			$event->datum,
			$lektor_uids,
			$gruppen_kurzbz,
			$lehrverband_gruppen
		);

		return $this->_getRatings($tages_events, $event, $raumkandidaten, $lektor_uids, $gruppen_kurzbz, $lehrverband_gruppen_bezeichnung);
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

		$lektoren_result = $this->_ci->LehreinheitMitarbeiterModel->loadWhere(['lehreinheit_id' => $lehreinheit_id]);
		$lektor_uids = hasData($lektoren_result) ? array_column(getData($lektoren_result), 'mitarbeiter_uid') : [];

		$gruppen_result = $this->_ci->LehreinheitgruppeModel->getDirectGroup($lehreinheit_id);
		$gruppen = getData($gruppen_result);
		$gruppen_kurzbz = hasData($gruppen_result) ? array_column($gruppen, 'gruppe_kurzbz') : [];

		$lehrverband_result = $this->_ci->LehreinheitgruppeModel->getByLehreinheit($lehreinheit_id);
		$lehrverband_gruppen = hasData($lehrverband_result) ? getData($lehrverband_result) : [];
		$lehrverband_gruppen_bezeichnung = array_column($lehrverband_gruppen, 'bezeichnung');

		$verplante_events = $this->_ci->kalenderlib->getForRaumvorschlag(
			$start_date,
			$end_date,
			$lektor_uids,
			$gruppen_kurzbz,
			$lehrverband_gruppen);

		$start_day = strtotime($start_date);
		$end_day = strtotime($end_date);

		$moegliche_slots = array();
		while ($start_day < $end_day)
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

				if (!$this->_isFrei($slot_start, $slot_end, $verplante_tages_events, $lektor_uids, 'lektor'))
					continue;

				if (!$this->_isFrei($slot_start, $slot_end, $verplante_tages_events, $gruppen_kurzbz, 'gruppe'))
					continue;

				if (!$this->_isFrei($slot_start, $slot_end, $verplante_tages_events, $lehrverband_gruppen_bezeichnung, 'lehrverband'))
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
				'lehreinheit_id' => [$lehreinheit_id],
				'isostart' => $slot['isostart'],
				'isoend' => $slot['isoend'],
			];

			$raumkandidaten = $this->_getRaumkandidaten($event_obj);

			if (empty($raumkandidaten))
				continue;

			$ratings = $this->_getRatings($verplante_events, $slot, $raumkandidaten, $lektor_uids, $gruppen_kurzbz, $lehrverband_gruppen_bezeichnung);
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

	private function _getRatings($events, $event, $raumkandidaten, $lektor_uids, $gruppen_kurzbz, $lehrverband_gruppen_bezeichnung)
	{
		$event = (object)$event;
		$lektor_davor = $this->_getEventDavor($events, $event->isostart, $lektor_uids, 'lektor');
		$gruppen_davor = $this->_getEventDavor($events, $event->isostart, $gruppen_kurzbz, 'gruppe');
		$lehrverband_davor = $this->_getEventDavor($events, $event->isostart, $lehrverband_gruppen_bezeichnung, 'lehrverband');

		$lektor_davor_ort = $lektor_davor ? $this->_getOrtDetails($lektor_davor->ort_kurzbz) : null;
		$gruppen_davor_ort = $gruppen_davor ? $this->_getOrtDetails($gruppen_davor->ort_kurzbz) : null;
		$lehrverband_davor_ort = $lehrverband_davor ? $this->_getOrtDetails($lehrverband_davor->ort_kurzbz) : null;

		$ratings = [];
		foreach ($raumkandidaten as $raum)
		{
			$rating = ['ort_kurzbz' => $raum->ort_kurzbz, 'score' => 100, 'details' => []];
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
			return $b['score'] - $a['score'];
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
		else if ($type === 'gruppen')
			$event_uids = array_column($event->gruppe, 'gruppe_kurzbz');
		else if ($type === 'lehrverband')
			$event_uids = array_column($event->gruppe, 'bezeichnung');

		return $event_uids;
	}

	private function _getRaumkandidaten($event)
	{
		$lehreinheit = $this->_ci->LehreinheitModel->load($event->lehreinheit_id[0]);
		if (!hasData($lehreinheit)) return [];
		$lehreinheit = getData($lehreinheit)[0];

		$this->_ci->KalenderModel->addSelect('tbl_kalender_ort.ort_kurzbz');
		$this->_ci->KalenderModel->addJoin('lehre.tbl_kalender_ort', 'tbl_kalender.kalender_id = tbl_kalender_ort.kalender_id');
		$this->_ci->KalenderModel->db->where('tbl_kalender.von <', $event->isoend);
		$this->_ci->KalenderModel->db->where('tbl_kalender.bis >', $event->isostart);
		$this->_ci->KalenderModel->db->where_not_in('tbl_kalender.status_kurzbz', ['deleted']);
		$this->_ci->KalenderModel->db->where('tbl_kalender_ort.ort_kurzbz IS NOT NULL', null, false);
		$belegte = $this->_ci->KalenderModel->load();

		$belegte_orte = hasData($belegte) ? array_column(getData($belegte), 'ort_kurzbz') : [];

		if (empty($lehreinheit->raumtyp))
		{
			$raeume = $this->_getFreieRaeume(null, $belegte_orte);
			return hasData($raeume) ? getData($raeume) : [];
		}

		$vorschlaege = [];

		$raeume = $this->_getFreieRaeume($lehreinheit->raumtyp, $belegte_orte);
		if (hasData($raeume))
			$vorschlaege = getData($raeume);

		if (count($vorschlaege) < 5 && !empty($lehreinheit->raumtypalternativ))
		{
			$bereits_gefunden = array_merge($belegte_orte, array_column($vorschlaege, 'ort_kurzbz'));
			$alternativ = $this->_getFreieRaeume($lehreinheit->raumtypalternativ, $bereits_gefunden);

			if (!isError($alternativ) && hasData($alternativ))
				$vorschlaege = array_merge($vorschlaege, getData($alternativ));
		}

		return $vorschlaege;
	}

	private function _getFreieRaeume($raumtyp, $belegte_orte)
	{
		$this->_ci->OrtModel->addSelect('ort_kurzbz, stockwerk, standort_id');
		$this->_ci->OrtModel->addJoin('public.tbl_ortraumtyp', 'ort_kurzbz');
		$this->_ci->OrtModel->db->where('raumtyp_kurzbz', $raumtyp);
		$this->_ci->OrtModel->db->where('aktiv', true);
		$this->_ci->OrtModel->db->where("ort_kurzbz NOT LIKE '\_%'", null, false);

		if (!empty($belegte_orte))
			$this->_ci->OrtModel->db->where_not_in('ort_kurzbz', $belegte_orte);
		$this->_ci->OrtModel->addOrder('hierarchie, ort_kurzbz');

		return $this->_ci->OrtModel->load();
	}







}
