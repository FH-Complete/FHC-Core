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

class Lehrmittel extends API_Controller
{
	/**
	 * Lehrmittel API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lehrmittel' => 'basis/lehrmittel:rw'));
		// Load model LehrmittelModel
		$this->load->model('ressource/lehrmittel_model', 'LehrmittelModel');


	}

	/**
	 * @return void
	 */
	public function getLehrmittel()
	{
		$lehrmittel_kurzbz = $this->get('lehrmittel_kurzbz');

		if (isset($lehrmittel_kurzbz))
		{
			$result = $this->LehrmittelModel->load($lehrmittel_kurzbz);

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
	public function postLehrmittel()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lehrmittel_kurzbz']))
			{
				$result = $this->LehrmittelModel->update($this->post()['lehrmittel_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->LehrmittelModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lehrmittel = NULL)
	{
		return true;
	}
}
