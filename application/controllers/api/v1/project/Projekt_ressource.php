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

class Projekt_ressource extends API_Controller
{
	/**
	 * Projekt_ressource API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Projekt_ressource' => 'basis/projekt_ressource:rw'));
		// Load model Projekt_ressourceModel
		$this->load->model('project/projekt_ressource_model', 'Projekt_ressourceModel');


	}

	/**
	 * @return void
	 */
	public function getProjekt_ressource()
	{
		$projekt_ressourceID = $this->get('projekt_ressource_id');

		if (isset($projekt_ressourceID))
		{
			$result = $this->Projekt_ressourceModel->load($projekt_ressourceID);

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
	public function postProjekt_ressource()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['projekt_ressource_id']))
			{
				$result = $this->Projekt_ressourceModel->update($this->post()['projekt_ressource_id'], $this->post());
			}
			else
			{
				$result = $this->Projekt_ressourceModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($projekt_ressource = NULL)
	{
		return true;
	}
}
