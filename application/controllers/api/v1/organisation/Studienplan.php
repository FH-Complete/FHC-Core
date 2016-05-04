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

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Studienplan extends APIv1_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('organisation/studienplan_model', 'StudienplanModel');
		// Load set the uid of the model to let to check the permissions
		$this->StudienplanModel->setUID($this->_getUID());
	}
	
	public function getStudienplaene()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		
		if(isset($studiengang_kz))
		{
			$result = $this->StudienplanModel->addJoin('lehre.tbl_studienordnung', 'studienordnung_id');
			if($result->error == EXIT_SUCCESS)
			{
				$result = $this->StudienplanModel->loadWhere(array('studiengang_kz' => $this->get('studiengang_kz')));
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
}