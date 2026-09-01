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
		return $this->_getVorschlaegeForEvent($event);
	}

	public function getVorschlaegeByLehreinheit($lehreinheit_id, $von, $bis)
	{
		$lektoren_result = $this->_ci->LehreinheitMitarbeiterModel->loadWhere(['lehreinheit_id' => $lehreinheit_id]);
		$lektor_uids = hasData($lektoren_result) ? array_column(getData($lektoren_result), 'mitarbeiter_uid') : [];

		$lektor_data = [];
		foreach ($lektor_uids as $uid)
		{
			$lektor_data[] = ['mitarbeiter_uid' => $uid];
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

		$event = (object) [
			'kalender_id' => null,
			'lehreinheit_id' => [$lehreinheit_id],
			'isostart' => (new DateTime($von))->format('c'),
			'isoend' => (new DateTime($bis))->format('c'),
			'datum' => (new DateTime($von))->format('Y-m-d'),
			'lektor' => $lektor_data,
			'gruppe' => $gruppen_data,
		];

		return $this->_getVorschlaegeForEvent($event);
	}

	private function _getVorschlaegeForEvent($event)
	{
		$raumkandidaten = $this->_getRaumkandidaten($event);
		if (empty($raumkandidaten)) return [];

		$lektor_uids = array_column($event->lektor, 'mitarbeiter_uid');
		$gruppen_kurzbz = array_values(array_filter(array_column($event->gruppe, 'gruppe_kurzbz')));

		$lehrverband_gruppen = array_values(array_filter($event->gruppe, function($gruppe)
		{
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
			$rating = ['ort_kurzbz' => $raum->ort_kurzbz, 'score' => 100, 'details' => [],
				'raumtyp_kurzbz' => $raum->raumtyp_kurzbz, 'max_person' => $raum->max_person, 'ausstattung' => $raum->ausstattung];
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
