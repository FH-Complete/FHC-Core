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

class StudiengangEP extends FHCAPI_Controller
{
	/**
	 * StudiengangEP API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'getStudiengangByKz' => self::PERM_LOGGED,
				'getAllowed' => self::PERM_LOGGED,
			)
		);
		// Load model StudiengangModel
		$this->load->model('organisation/studiengang_model', 'StudiengangModel');
	}

	/**
	 * @return void
	 */
	public function getStudiengangByKz()
	{
		$studiengang_kz = intval($this->input->get('studiengang_kz'));

		$this->StudiengangModel->addSelect('studiengang_kz, kurzbz, kurzbzlang, '
			. 'typ, bezeichnung, english, aktiv, orgform_kurzbz, sprache, '
			. 'oe_kurzbz');
		$result = $this->StudiengangModel->load($studiengang_kz);

		if (isError($result)) 
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		}
		
		$stg = null;
		if(hasData($result)) 
		{
			$stg = (getData($result))[0];
		}
		$this->terminateWithSuccess($stg);
	}

	/**
	 * @return void
	 */
	public function getAllowed()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('permissions[]', 'Permissions', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$permissions = $this->input->post('permissions');

		$permittedStgs = [];

		foreach ($permissions as $permission)
			$permittedStgs = array_merge($permittedStgs, $this->permissionlib->getSTG_isEntitledFor($permission) ?: []);

		$this->terminateWithSuccess($permittedStgs);
	}
}
