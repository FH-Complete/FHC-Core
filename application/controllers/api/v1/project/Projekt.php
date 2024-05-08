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

class Projekt extends API_Controller
{
	/**
	 * Projekt API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Projekt' => 'basis/projekt:rw'));
		// Load model ProjektModel
		$this->load->model('project/projekt_model', 'ProjektModel');


	}

	/**
	 * @return void
	 */
	public function getProjekt()
	{
		$projekt_kurzbz = $this->get('projekt_kurzbz');

		if (isset($projekt_kurzbz))
		{
			$result = $this->ProjektModel->load($projekt_kurzbz);

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
	public function postProjekt()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['projekt_kurzbz']))
			{
				$result = $this->ProjektModel->update($this->post()['projekt_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->ProjektModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($projekt = NULL)
	{
		return true;
	}
}
