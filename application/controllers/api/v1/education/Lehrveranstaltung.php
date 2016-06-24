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

class Lehrveranstaltung extends APIv1_Controller
{
	/**
	 * Lehrveranstaltung API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model LehrveranstaltungModel
		$this->load->model('education/lehrveranstaltung', 'LehrveranstaltungModel');
		
		
	}

	/**
	 * @return void
	 */
	public function getLehrveranstaltung()
	{
		$lehrveranstaltung_id = $this->get('lehrveranstaltung_id');
		
		if (isset($lehrveranstaltung_id))
		{
			$result = $this->LehrveranstaltungModel->load($lehrveranstaltung_id);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postLehrveranstaltung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lehrveranstaltung_id']))
			{
				$result = $this->LehrveranstaltungModel->update($this->post()['lehrveranstaltung_id'], $this->post());
			}
			else
			{
				$result = $this->LehrveranstaltungModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($lehrveranstaltung = NULL)
	{
		return true;
	}
}