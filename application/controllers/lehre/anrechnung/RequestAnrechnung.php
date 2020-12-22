<?php

//if (! defined('BASEPATH')) exit('No direct script access allowed');

class requestAnrechnung extends Auth_Controller
{
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index' => 'admin:rw',
				'uploadFile' => 'admin:rw',
			)
		);
		
		// Load models
		$this->load->model('system/Benutzerrolle_model', 'BenutzerrolleModel');
		
		// Load libraries
		$this->load->library('WidgetLib');
		$this->load->library('PermissionLib');
		
		// Load helpers
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->helper('hlp_sancho_helper');
		
		// Load language phrases
		$this->loadPhrases(
			array(
				'global',
				'ui',
				'anrechnung'
			)
		);
		
		$this->_setAuthUID();
		
		$this->setControllerId();
	}
	
	public function index()
	{
		$this->load->view('lehre/anrechnung/requestAnrechnung.php');
	
	}
	
	public function uploadFile($filename = null)
	{
//		$this->extensionslib->installExtension(urldecode($filename));
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