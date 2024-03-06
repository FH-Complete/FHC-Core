<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Phrasen extends FHC_Controller
{
	
	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @param string $module
	 */
	public function loadModule($module)
	{
		$this->load->library('PhrasesLib', [$module], 'pj');
		$this->outputJson([
			'data' => json_decode($this->pj->getJSON()),
			'meta' => [
				'status' => FHCAPI_Controller::STATUS_SUCCESS
			]
		]);
	}
}
