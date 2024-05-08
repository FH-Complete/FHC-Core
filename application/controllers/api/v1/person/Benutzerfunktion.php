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

class Benutzerfunktion extends API_Controller
{
	/**
	 * Benutzerfunktion API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Benutzerfunktion' => 'basis/benutzerfunktion:rw'));
		// Load model BenutzerfunktionModel
		$this->load->model('person/benutzerfunktion_model', 'BenutzerfunktionModel');


	}

	/**
	 * @return void
	 */
	public function getBenutzerfunktion()
	{
		$benutzerfunktionID = $this->get('benutzerfunktion_id');

		if (isset($benutzerfunktionID))
		{
			$result = $this->BenutzerfunktionModel->load($benutzerfunktionID);

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
	public function postBenutzerfunktion()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['benutzerfunktion_id']))
			{
				$result = $this->BenutzerfunktionModel->update($this->post()['benutzerfunktion_id'], $this->post());
			}
			else
			{
				$result = $this->BenutzerfunktionModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($benutzerfunktion = NULL)
	{
		return true;
	}
}
