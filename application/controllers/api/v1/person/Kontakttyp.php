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

class Kontakttyp extends API_Controller
{
	/**
	 * Kontakttyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Kontakttyp' => 'basis/kontakttyp:rw'));
		// Load model KontakttypModel
		$this->load->model('person/kontakttyp_model', 'KontakttypModel');


	}

	/**
	 * @return void
	 */
	public function getKontakttyp()
	{
		$kontakttyp = $this->get('kontakttyp');

		if (isset($kontakttyp))
		{
			$result = $this->KontakttypModel->load($kontakttyp);

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
	public function postKontakttyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['kontakttyp']))
			{
				$result = $this->KontakttypModel->update($this->post()['kontakttyp'], $this->post());
			}
			else
			{
				$result = $this->KontakttypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($kontakttyp = NULL)
	{
		return true;
	}
}
