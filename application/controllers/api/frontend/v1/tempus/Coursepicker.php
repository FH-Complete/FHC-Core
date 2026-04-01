<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Coursepicker extends FHCAPI_Controller
{
	private $_ci;
	public function __construct()
	{
		parent::__construct([
			'search' => self::PERM_LOGGED,
			'getByStg' => self::PERM_LOGGED
		]);

		$this->_ci = &get_instance();
		$this->load->library('form_validation');

		$this->_ci->load->model('education/lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');


		$this->loadPhrases(['ui']);
	}

	public function search()
	{
		$query = $this->input->get('query');
		if (is_null($query))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$query_words = explode(' ', $query);

		//TODO Where weiter anpassen z.B. Fachbereich
		$this->_ci->LehreinheitModel->addSelect('tbl_lehreinheit.lehreinheit_id,
												tbl_lehreinheit.unr,
												tbl_lehreinheit.lvnr,
												tbl_lehreinheit.lehrfach_id,
												lehrfach.kurzbz AS lehrfach,
												lehrfach.bezeichnung AS lehrfach_bez,
												lehrfach.farbe AS lehrfach_farbe,
												tbl_lehreinheit.lehrform_kurzbz AS lehrform,
												lema.mitarbeiter_uid AS lektor_uid,
												tbl_mitarbeiter.kurzbz AS lektor,
												tbl_studiengang.studiengang_kz,
												upper(tbl_studiengang.typ::character varying::text || tbl_studiengang.kurzbz::text) AS studiengang,
												lvb.semester,
												lvb.verband,
												lvb.gruppe,
												lvb.gruppe_kurzbz,
												tbl_lehreinheit.raumtyp,
												tbl_lehreinheit.raumtypalternativ,
												tbl_lehreinheit.stundenblockung,
												tbl_lehreinheit.wochenrythmus,
												lema.semesterstunden,
												lema.planstunden,
												tbl_lehreinheit.start_kw,
												tbl_lehreinheit.anmerkung,
												tbl_lehreinheit.studiensemester_kurzbz');

		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehreinheitmitarbeiter lema', 'tbl_lehreinheit.lehreinheit_id = lema.lehreinheit_id');
		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehreinheitgruppe lvb', 'tbl_lehreinheit.lehreinheit_id = lvb.lehreinheit_id');
		$this->_ci->LehreinheitModel->addJoin('public.tbl_studiengang', 'lvb.studiengang_kz = tbl_studiengang.studiengang_kz');
		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehrveranstaltung lehrfach', 'tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id');
		$this->_ci->LehreinheitModel->addJoin('public.tbl_mitarbeiter', 'lema.mitarbeiter_uid = tbl_mitarbeiter.mitarbeiter_uid');
		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehrform', 'tbl_lehrform.lehrform_kurzbz = tbl_lehreinheit.lehrform_kurzbz');

		$this->_ci->MitarbeiterModel->db->group_start();

		foreach ($query_words as $word)
		{
			$this->_ci->LehreinheitModel->db->group_start();
			$this->_ci->LehreinheitModel->db->where('lema.mitarbeiter_uid ILIKE', "%" . $word . "%");
			$this->_ci->LehreinheitModel->db->or_where('lvb.gruppe_kurzbz ILIKE', "%" . $word . "%");
			$this->_ci->LehreinheitModel->db->or_where('tbl_studiengang.kurzbzlang ILIKE', "%" . $word . "%");
			$this->_ci->LehreinheitModel->db->or_where('lvb.verband ILIKE', "%" . $word . "%");
			$this->_ci->LehreinheitModel->db->or_where('lvb.gruppe ILIKE', "%" . $word . "%");
			$this->_ci->LehreinheitModel->db->or_where('lehrfach.bezeichnung ILIKE', "%" . $word . "%");

			if (is_numeric($word))
			{
				$this->_ci->LehreinheitModel->db->or_where('tbl_studiengang.studiengang_kz', $word);
				$this->_ci->LehreinheitModel->db->or_where('lvb.semester', $word);
			}
			$this->_ci->LehreinheitModel->db->group_end();

		}
		$this->_ci->LehreinheitModel->db->group_end();
		$this->_ci->LehreinheitModel->db->where('tbl_lehreinheit.studiensemester_kurzbz = \'SS2025\'');
		$this->_ci->LehreinheitModel->db->where(array('tbl_lehrform.verplanen' => true));

		$result = $this->_ci->LehreinheitModel->load();

		$this->terminateWithSuccess(hasData($result) ? getData($result) : array());
	}

	public function getByStg()
	{
		//TODO check einbauen ob studiensemester und stg vorhanden ist
		$stg = $this->input->get('stg');
		$studiensemester_kurzbz = $this->input->get('studiensemester_kurzbz');
		if (is_null($stg) || is_null($studiensemester_kurzbz))
			$this->terminateWithError($this->p->t('ui', 'ungueltigeParameter'), self::ERROR_TYPE_GENERAL);

		$this->_ci->LehreinheitModel->addSelect('
			tbl_lehreinheit.lehreinheit_id,
			tbl_lehreinheit.unr,
			tbl_lehreinheit.lvnr,
			tbl_lehreinheit.lehrfach_id,
			lehrfach.kurzbz AS lehrfach,
			lehrfach.bezeichnung AS lehrfach_bez,
			lehrfach.farbe AS lehrfach_farbe,
			tbl_lehreinheit.lehrform_kurzbz AS lehrform,
			lema.mitarbeiter_uid AS lektor_uid,
			ma.kurzbz AS lektor,
			tbl_person.vorname,
			tbl_person.nachname,
			tbl_studiengang.studiengang_kz,
			upper(tbl_studiengang.typ::character varying::text || tbl_studiengang.kurzbz::text) AS studiengang,
			lvb.semester,
			lvb.verband,
			lvb.gruppe,
			lvb.gruppe_kurzbz,
			tbl_lehreinheit.raumtyp,
			tbl_lehreinheit.raumtypalternativ,
			tbl_lehreinheit.stundenblockung,
			tbl_lehreinheit.wochenrythmus,
			lema.semesterstunden,
			lema.planstunden,
			tbl_lehreinheit.start_kw,
			tbl_lehreinheit.anmerkung,
			tbl_lehreinheit.studiensemester_kurzbz
		');
		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehreinheitmitarbeiter lema', 'tbl_lehreinheit.lehreinheit_id = lema.lehreinheit_id');
		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehreinheitgruppe lvb', 'tbl_lehreinheit.lehreinheit_id = lvb.lehreinheit_id');
		$this->_ci->LehreinheitModel->addJoin('public.tbl_studiengang', 'lvb.studiengang_kz = tbl_studiengang.studiengang_kz');
		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehrveranstaltung lehrfach', 'tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id');
		$this->_ci->LehreinheitModel->addJoin('public.tbl_mitarbeiter ma', 'lema.mitarbeiter_uid = ma.mitarbeiter_uid');
		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehrform', 'tbl_lehrform.lehrform_kurzbz = tbl_lehreinheit.lehrform_kurzbz');
		$this->_ci->LehreinheitModel->addJoin('public.tbl_benutzer', 'ma.mitarbeiter_uid = tbl_benutzer.uid');
		$this->_ci->LehreinheitModel->addJoin('public.tbl_person', 'tbl_benutzer.person_id = tbl_person.person_id');

		$result = $this->_ci->LehreinheitModel->loadWhere(array(
			'tbl_lehrform.verplanen' => true,
			'tbl_studiengang.studiengang_kz' => $stg,
			'tbl_lehreinheit.studiensemester_kurzbz' => $studiensemester_kurzbz
		));

		$result = hasData($result) ? getData($result) : array();
		$grouped = array();

		foreach ($result as $row)
		{
			$unr = $row->unr;
			if (!isset($grouped[$unr]))
			{
				$grouped[$unr] = (object)array(
					'unr' => $row->unr,
					'lehrfach_id' => $row->lehrfach_id,
					'lehrfach_bez' => $row->lehrfach_bez,
					'lehrfach_farbe' => $row->lehrfach_farbe,
					'studiengang_kz' => $row->studiengang_kz,
					'studiengang' => $row->studiengang,
					'semester' => $row->semester,
					'verband' => $row->verband,
					'gruppe' => $row->gruppe,
					'gruppe_kurzbz' => $row->gruppe_kurzbz,
					'raumtyp' => $row->raumtyp,
					'raumtypalternativ' => $row->raumtypalternativ,
					'anmerkung' => $row->anmerkung,
					'studiensemester_kurzbz' => $row->studiensemester_kurzbz,
					'fachbereich_kurzbz' => isset($row->fachbereich_kurzbz) ? $row->fachbereich_kurzbz : null,
					'lektoren' => array(),
					'lehreinheit_id' => array(),
					'lvnr' => array(),
					'lehrfach' => array(),
					'lehrform' => array(),
					'stundenblockung' => array(),
					'wochenrythmus' => array(),
					'planstunden' => array(),
					'start_kw' => array(),
					'verplant' => array(),
					'offenestunden' => array(),
					'lehrverband' => array(),
					'lem' => array(),
					'verplant_gesamt' => 0,
				);
			}

			$group = $grouped[$unr];

			$group->lektoren[$row->lektor_uid] = (object)array(
				'uid' => $row->lektor_uid,
				'kurzbz' => trim($row->lektor),
				'name' => $row->vorname . ' ' . $row->nachname,
			);

			$group->lehreinheit_id[] = $row->lehreinheit_id;
			$group->lvnr[] = $row->lvnr;
			$group->lehrfach[] = $row->lehrfach;
			$group->lehrform[] = $row->lehrform;
			$group->stundenblockung[] = $row->stundenblockung;
			$group->wochenrythmus[] = $row->wochenrythmus;
			$group->planstunden[] = $row->planstunden;
			$group->start_kw[] = $row->start_kw;
			$group->verplant[] = isset($row->verplant) ? $row->verplant : 0;
			$group->offenestunden[] = isset($row->offenestunden) ? $row->offenestunden : 0;
			$group->verplant_gesamt += isset($row->verplant) ? $row->verplant : 0;

			$lvb = $row->studiengang . '-' . $row->semester;

			if ($row->verband != '' && $row->verband != ' ' && $row->verband != '0' && $row->verband != null)
				$lvb .= $row->verband;

			if ($row->gruppe != '' && $row->gruppe != ' ' && $row->gruppe != '0' && $row->gruppe != null)
				$lvb .= $row->gruppe;

			$group->lehrverband[] = ($row->gruppe_kurzbz != '' && $row->gruppe_kurzbz != null) ? $row->gruppe_kurzbz : $lvb;

			$group->lem[] = array(
				'lehreinheit_id' => $row->lehreinheit_id,
				'mitarbeiter_uid' => $row->lektor_uid,
			);
		}

		foreach ($grouped as $group)
		{
			$group->lektoren = array_values($group->lektoren);
			$group->lehrverband = array_values(array_unique($group->lehrverband));
			$group->lehrfach = $this->_formatArr($group->lehrfach);
			$group->lehrform = $this->_formatArr($group->lehrform);
			$group->stundenblockung = $this->_formatArr($group->stundenblockung);
			$group->wochenrythmus = $this->_formatArr($group->wochenrythmus);
			$group->planstunden = $this->_formatArr($group->planstunden);
			$group->start_kw = $this->_formatArr($group->start_kw);
			$group->verplant = $this->_formatArr($group->verplant);
			$group->offenestunden = $this->_formatArr($group->offenestunden);
		}

		$this->terminateWithSuccess(array_values($grouped));
	}

	private function _formatArr($arr)
	{
		$values = array_values(array_unique($arr));
		$formatted = implode(' ', $values);

		if (count($formatted) > 1)
			$formatted .= ' ?';

		return $formatted;
	}


}
