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

class Bundesland extends API_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('All' => 'basis/bundesland:r'));
		// Load model PersonModel
		$this->load->model('codex/bundesland_model', 'BundeslandModel');
	}
	
	public function getAll()
	{
		$result = $this->BundeslandModel->load();
		
		$this->response($result, REST_Controller::HTTP_OK);
	}
}