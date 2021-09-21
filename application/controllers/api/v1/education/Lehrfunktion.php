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

class Lehrfunktion extends API_Controller
{
	/**
	 * Lehrfunktion API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lehrfunktion' => 'basis/lehrfunktion:rw'));
		// Load model LehrfunktionModel
		$this->load->model('education/Lehrfunktion_model', 'LehrfunktionModel');
	}

	/**
	 * @return void
	 */
	public function getLehrfunktion()
	{
		$lehrfunktion_kurzbz = $this->get('lehrfunktion_kurzbz');

		if (isset($lehrfunktion_kurzbz))
		{
			$result = $this->LehrfunktionModel->load($lehrfunktion_kurzbz);

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
	public function postLehrfunktion()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lehrfunktion_kurzbz']))
			{
				$result = $this->LehrfunktionModel->update($this->post()['lehrfunktion_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->LehrfunktionModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lehrfunktion = NULL)
	{
		return true;
	}
}
