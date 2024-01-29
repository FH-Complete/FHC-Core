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

class Abschlussbeurteilung extends API_Controller
{
	/**
	 * Abschlussbeurteilung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Abschlussbeurteilung' => 'basis/abschlussbeurteilung:rw'));
		// Load model AbschlussbeurteilungModel
		$this->load->model('education/Abschlussbeurteilung_model', 'AbschlussbeurteilungModel');
	}

	/**
	 * @return void
	 */
	public function getAbschlussbeurteilung()
	{
		$abschlussbeurteilung_kurzbz = $this->get('abschlussbeurteilung_kurzbz');

		if (isset($abschlussbeurteilung_kurzbz))
		{
			$result = $this->AbschlussbeurteilungModel->load($abschlussbeurteilung_kurzbz);

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
	public function postAbschlussbeurteilung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['$abschlussbeurteilung_kurzbz']))
			{
				$result = $this->AbschlussbeurteilungModel->update($this->post()['$abschlussbeurteilung_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->AbschlussbeurteilungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($abschlussbeurteilung = NULL)
	{
		return true;
	}
}
