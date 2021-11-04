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

class Zeitsperretyp extends API_Controller
{
	/**
	 * Zeitsperretyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zeitsperretyp' => 'basis/zeitsperretyp:rw'));
		// Load model ZeitsperretypModel
		$this->load->model('ressource/zeitsperretyp_model', 'ZeitsperretypModel');


	}

	/**
	 * @return void
	 */
	public function getZeitsperretyp()
	{
		$zeitsperretyp_kurzbz = $this->get('zeitsperretyp_kurzbz');

		if (isset($zeitsperretyp_kurzbz))
		{
			$result = $this->ZeitsperretypModel->load($zeitsperretyp_kurzbz);

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
	public function postZeitsperretyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['zeitsperretyp_kurzbz']))
			{
				$result = $this->ZeitsperretypModel->update($this->post()['zeitsperretyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->ZeitsperretypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zeitsperretyp = NULL)
	{
		return true;
	}
}
