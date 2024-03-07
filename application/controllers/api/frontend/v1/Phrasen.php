<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Phrasen extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'loadModule' => self::PERM_ANONYMOUS
		]);
	}
	
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @param string $module
	 */
	public function loadModule($module)
	{
		$this->load->library('PhrasesLib', [$module], 'pj');
		$this->terminateWithSuccess(json_decode($this->pj->getJSON()));
	}
}
