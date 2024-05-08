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

class Projekttyp extends API_Controller
{
	/**
	 * Projekttyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Projekttyp' => 'basis/projekttyp:rw'));
		// Load model ProjekttypModel
		$this->load->model('education/Projekttyp_model', 'ProjekttypModel');
	}

	/**
	 * @return void
	 */
	public function getProjekttyp()
	{
		$projekttyp_kurzbz = $this->get('projekttyp_kurzbz');

		if (isset($projekttyp_kurzbz))
		{
			$result = $this->ProjekttypModel->load($projekttyp_kurzbz);

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
	public function postProjekttyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['projekttyp_kurzbz']))
			{
				$result = $this->ProjekttypModel->update($this->post()['projekttyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->ProjekttypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($projekttyp = NULL)
	{
		return true;
	}
}
