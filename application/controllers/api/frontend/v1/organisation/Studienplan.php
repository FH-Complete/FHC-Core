<?php
/**
 * FH-Complete
 *
 * @package        FHC-API
 * @author        FHC-Team
 * @copyright    Copyright (c) 2016, fhcomplete.org
 * @license        GPLv3
 * @link        http://fhcomplete.org
 * @since        Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Studienplan extends FHCAPI_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct([
			'getBySemester' => self::PERM_LOGGED
		]);
	}

	public function getBySemester()
	{
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$studiengang_kz = $this->input->get('studiengang_kz');
		$studiensemester_kurzbz = $this->input->get('studiensemester_kurzbz');
		$ausbildungssemester = $this->input->get('ausbildungssemester') ?: null;
		$orgform_kurzbz = $this->input->get('orgform_kurzbz') ?: null;

		if (!$studiengang_kz || !is_numeric($studiengang_kz))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Studiengangskennzahl']), self::ERROR_TYPE_GENERAL);

		if (!$studiensemester_kurzbz)
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Studiensemester']), self::ERROR_TYPE_GENERAL);

		if (isset($ausbildungssemester) && !is_numeric($ausbildungssemester))
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Ausbildungssemester']), self::ERROR_TYPE_GENERAL);


		//~ $this->load->library('form_validation');
		
		//~ $this->form_validation->set_rules('studiengang_kz', 'StudiengangKz', 'required|numeric');
		//~ $this->form_validation->set_rules('studiensemester_kurzbz', 'StudiensemesterKurbz', 'required');
		//~ $this->form_validation->set_rules('ausbildungssemester', 'Ausbildungssemester', 'numeric');

		//~ if (!$this->form_validation->run())
		//~ {
			//~ $this->addMeta('fail2', 'fail2');
			//~ return $this->terminateWithValidationErrors($this->form_validation->error_array());
		//~ }


		$this->addMeta('stg_kz', $studiengang_kz);
		$this->addMeta('sem', $studiensemester_kurzbz);
		$this->addMeta('sem2', $ausbildungssemester);
		$this->addMeta('org', $orgform_kurzbz);

		$result = $this->StudienplanModel->getStudienplaeneBySemester($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester, $orgform_kurzbz);
		if (isError($result)) $this->terminateWithError(getError($result), self::ERROR_TYPE_DB);

		$this->terminateWithSuccess(hasData($result) ? getData($result) : []);
	}
}
