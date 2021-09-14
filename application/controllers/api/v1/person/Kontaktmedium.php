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

class Kontaktmedium extends API_Controller
{
	/**
	 * Kontaktmedium API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Kontaktmedium' => 'basis/kontaktmedium:rw'));
		// Load model KontaktmediumModel
		$this->load->model('person/kontaktmedium_model', 'KontaktmediumModel');


	}

	/**
	 * @return void
	 */
	public function getKontaktmedium()
	{
		$kontaktmedium_kurzbz = $this->get('kontaktmedium_kurzbz');

		if (isset($kontaktmedium_kurzbz))
		{
			$result = $this->KontaktmediumModel->load($kontaktmedium_kurzbz);

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
	public function postKontaktmedium()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['kontaktmedium_kurzbz']))
			{
				$result = $this->KontaktmediumModel->update($this->post()['kontaktmedium_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->KontaktmediumModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($kontaktmedium = NULL)
	{
		return true;
	}
}
