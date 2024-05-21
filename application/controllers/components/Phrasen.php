<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * TODO(chris): deprecated
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
		$this->outputJsonSuccess(json_decode($this->pj->getJSON()));
	}
}
