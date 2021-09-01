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

class Ressource extends API_Controller
{
	/**
	 * Ressource API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Ressource' => 'basis/ressource:rw'));
		// Load model RessourceModel
		$this->load->model('project/ressource_model', 'RessourceModel');


	}

	/**
	 * @return void
	 */
	public function getRessource()
	{
		$ressourceID = $this->get('ressource_id');

		if (isset($ressourceID))
		{
			$result = $this->RessourceModel->load($ressourceID);

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
	public function postRessource()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['ressource_id']))
			{
				$result = $this->RessourceModel->update($this->post()['ressource_id'], $this->post());
			}
			else
			{
				$result = $this->RessourceModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($ressource = NULL)
	{
		return true;
	}
}
