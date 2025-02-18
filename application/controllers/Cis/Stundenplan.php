<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Stundenplan extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => ['basis/cis:r']
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index($mode = 'Week', $focus_date = null, $lv_id = null)
	{
		// Convert string "null" to actual null values -> ci3 reroute fix
		$mode = ($mode === 'null') ? 'Week' : ucfirst(strtolower($mode));
		$focus_date = ($focus_date === 'null') ? date('Y-m-d') : $focus_date;
		$lv_id = ($lv_id === 'null') ? null : $lv_id;
		
		if($mode) $mode = ucfirst(strtolower($mode));
		
		$viewData = array(
			'mode' => $mode,
			'focus_date' => $focus_date,
			'lv_id' => $lv_id,
			'uid'=>getAuthUID(),
		);
		
		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Stundenplan']);
	}
}
