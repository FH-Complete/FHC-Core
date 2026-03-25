<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Coursepicker extends FHCAPI_Controller
{
	private $_ci;
	public function __construct()
	{
		parent::__construct([
			'search' => self::PERM_LOGGED,
		]);

		$this->_ci = &get_instance();
		$this->load->library('form_validation');

		$this->_ci->load->model('education/lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');


		$this->loadPhrases([
			'ui'
		]);
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
}
