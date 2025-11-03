<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');


class PaabgabeUebersicht extends FHCAPI_Controller
{
	const DOWNLOAD_PERMISSION = 'lehre/abgabetool:download';
	const ABGABE_TYPES = ['Bachelor', 'Diplom'];

	/**
	 * PaabgabeUebersicht API constructor.
	 */
	public function __construct()
	{
		parent::__construct([
			'getStudiengaenge' => array('lehre/abgabetool:r'),
			'getPaAbgaben' => array('lehre/abgabetool:r'),
			'getTermine' => array('lehre/abgabetool:r'),
			'getPaAbgabetypen' => array('lehre/abgabetool:r')
			//'downloadProjektarbeit' => array('lehre/abgabetool:r')
		]);

		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');

		$this->load->library('PermissionLib');
	}

	/**
	 *
	 *
	 * @return array|stdClass|null
	 */
	public function getPaAbgaben()
	{
		$studiengang_kz = $this->input->get('studiengang_kz');
		$abgabetyp_kurzbz = $this->input->get('abgabetyp_kurzbz');
		$abgabedatum = $this->input->get('abgabedatum');
		$personSearchString = $this->input->get('personSearchString');


		$result = $this->PaabgabeModel->getPaAbgaben(self::ABGABE_TYPES, $studiengang_kz, $abgabetyp_kurzbz, $abgabedatum, $personSearchString);
		$this->addMeta('res', $result);

		if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_DB);

		$this->terminateWithSuccess(getData($result) ?: []);
	}

	/**
	 *
	 *
	 * @return array|stdClass|null
	 */
	//~ public function searchPaAbgabenByPerson()
	//~ {
		//~ $searchString = $this->input->get('searchString');

		//~ $result = $this->PaabgabeModel->searchPaAbgabenByPerson(self::ABGABE_TYPES, $searchString);
		//~ $this->addMeta('res', $result);

		//~ if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_DB);

		//~ $this->terminateWithSuccess(getData($result) ?: []);
	//~ }

	/**
	 *
	 *
	 * @return array|stdClass|null
	 */
	public function getStudiengaenge()
	{
		$studiengang_kz_arr = $this->permissionlib->getSTG_isEntitledFor(self::DOWNLOAD_PERMISSION);

		if (!$studiengang_kz_arr) $this->terminateWithSuccess([]);

		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->StudiengangModel->addSelect('tbl_studiengang.*, UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) AS kuerzel', $studiengang_kz_arr);
		$this->StudiengangModel->db->where_in('studiengang_kz', $studiengang_kz_arr);
		$this->StudiengangModel->addOrder('typ, kurzbz');
		$result = $this->StudiengangModel->load();

		if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_DB);

		$this->terminateWithSuccess((getData($result) ?: []));
	}


	/**
	 *
	 *
	 * @return array|stdClass|null
	 */
	public function getTermine()
	{
		$studiengang_kz = $this->input->get('studiengang_kz');
		$abgabetyp_kurzbz = $this->input->get('abgabetyp_kurzbz');

		$result = $this->PaabgabeModel->getTermine(self::ABGABE_TYPES, $studiengang_kz, $abgabetyp_kurzbz);

		if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_DB);

		$this->terminateWithSuccess((getData($result) ?: []));
	}

	/**
	 *
	 *
	 * @return array|stdClass|null
	 */
	public function getPaAbgabetypen()
	{
		// Load model PaabgabetypModel
		$this->load->model('education/Paabgabetyp_model', 'PaabgabetypModel');

		$result = $this->PaabgabetypModel->load();

		if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_DB);

		$this->terminateWithSuccess((getData($result) ?: []));
	}

	/**
	 * Download Projektarbeit document.
	 */
	//~ public function downloadProjektarbeit()
	//~ {
		//~ $paabgabe_id = $this->input->get('paabgabe_id');

		//~ if (!is_numeric($paabgabe_id))
			//~ $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id' => 'Abgabe ID']), self::ERROR_TYPE_GENERAL);

		//~ //$abgabeRes = $this->PaabgabeModel->getEndabgabe($projektarbeit_id);
		//~ $this->PaabgabeModel->addSelect("paabgabe_id, student_uid, tbl_paabgabe.datum, tbl_paabgabe.abgabedatum, projekttyp_kurzbz, titel, titel_english,
					//~ paabgabe_id || '_' || student_uid || '.pdf' AS filename");
		//~ $this->PaabgabeModel->addJoin('lehre.tbl_projektarbeit', 'projektarbeit_id');
		//~ $abgabeRes = $this->PaabgabeModel->load($paabgabe_id);

		//~ if (isError($abgabeRes))
			//~ show_error(getError($abgabeRes));

		//~ if (hasData($abgabeRes))
		//~ {
			//~ $endabgabe = getData($abgabeRes)[0];
			//~ $filepath = PAABGABE_PATH.$endabgabe->filename;

			//~ if (file_exists($filepath))
			//~ {
				//~ $this->output
					//~ ->set_status_header(200)
					//~ ->set_content_type('application/pdf', 'utf-8')
					//~ ->set_header('Content-Disposition: attachment; filename="'.$endabgabe->filename.'"')
					//~ ->set_output(file_get_contents($filepath))
					//~ ->_display();
			//~ }
			//~ else
			//~ {
				//~ show_error("File does not exist.");
			//~ }
		//~ }
	//~ }
}
