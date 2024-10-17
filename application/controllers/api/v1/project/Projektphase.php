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

class Projektphase extends API_Controller
{
	/**
	 * Projektphase API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Projektphase' => 'basis/projektphase:rw'));
		// Load model ProjektphaseModel
		$this->load->model('project/projektphase_model', 'ProjektphaseModel');


	}

	/**
	 * @return void
	 */
	public function getProjektphase()
	{
		$projektphaseID = $this->get('projektphase_id');

		if (isset($projektphaseID))
		{
			$result = $this->ProjektphaseModel->load($projektphaseID);

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
	public function postProjektphase()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['projektphase_id']))
			{
				$result = $this->ProjektphaseModel->update($this->post()['projektphase_id'], $this->post());
			}
			else
			{
				$result = $this->ProjektphaseModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($projektphase = NULL)
	{
		return true;
	}
}
