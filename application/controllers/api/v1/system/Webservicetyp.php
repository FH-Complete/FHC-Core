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

class Webservicetyp extends API_Controller
{
	/**
	 * Webservicetyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Webservicetyp' => 'basis/webservicetyp:rw'));
		// Load model WebservicetypModel
		$this->load->model('system/webservicetyp_model', 'WebservicetypModel');


	}

	/**
	 * @return void
	 */
	public function getWebservicetyp()
	{
		$webservicetyp_kurzbz = $this->get('webservicetyp_kurzbz');

		if (isset($webservicetyp_kurzbz))
		{
			$result = $this->WebservicetypModel->load($webservicetyp_kurzbz);

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
	public function postWebservicetyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['webservicetyp_kurzbz']))
			{
				$result = $this->WebservicetypModel->update($this->post()['webservicetyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->WebservicetypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($webservicetyp = NULL)
	{
		return true;
	}
}
