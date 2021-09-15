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

class Kategorie extends API_Controller
{
	/**
	 * Kategorie API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Kategorie' => 'basis/kategorie:rw'));
		// Load model KategorieModel
		$this->load->model('testtool/kategorie_model', 'KategorieModel');


	}

	/**
	 * @return void
	 */
	public function getKategorie()
	{
		$kategorie_kurzbz = $this->get('kategorie_kurzbz');

		if (isset($kategorie_kurzbz))
		{
			$result = $this->KategorieModel->load($kategorie_kurzbz);

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
	public function postKategorie()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['kategorie_kurzbz']))
			{
				$result = $this->KategorieModel->update($this->post()['kategorie_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->KategorieModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($kategorie = NULL)
	{
		return true;
	}
}
