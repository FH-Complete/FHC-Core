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

class Bundesland extends APIv1_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('codex/bundesland_model', 'BundeslandModel');
		// Load set the uid of the model to let to check the permissions
		$this->BundeslandModel->setUID($this->_getUID());
	}
	
	public function getAll()
	{
		$result = $this->BundeslandModel->loadWhole();
		
		$this->response($result, REST_Controller::HTTP_OK);
	}
}