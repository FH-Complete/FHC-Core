<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class LvTemplateUebersicht extends Auth_Controller
{
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index' => 'lehre/lehrveranstaltung:rw',
			)
		);

		// Load libraries
		$this->load->library('AuthLib');


		// Load language phrases
		$this->loadPhrases(
			array(
				'global',
				'lehre'
			)
		);

		$this->_setAuthUID();
	}

	public function index()
	{
		$this->load->view('lehre/lvplanung/lvTemplateUebersicht.php');
	}


	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid) show_error('User authentification failed');
	}
}