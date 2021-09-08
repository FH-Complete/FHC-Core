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

class Veranstaltungskategorie extends API_Controller
{
	/**
	 * Veranstaltungskategorie API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Veranstaltungskategorie' => 'basis/veranstaltungskategorie:rw'));
		// Load model VeranstaltungskategorieModel
		$this->load->model('content/veranstaltungskategorie_model', 'VeranstaltungskategorieModel');
	}

	/**
	 * @return void
	 */
	public function getVeranstaltungskategorie()
	{
		$veranstaltungskategorie_kurzbz = $this->get('veranstaltungskategorie_kurzbz');

		if (isset($veranstaltungskategorie_kurzbz))
		{
			$result = $this->VeranstaltungskategorieModel->load($veranstaltungskategorie_kurzbz);

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
	public function postVeranstaltungskategorie()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['veranstaltungskategorie_kurzbz']))
			{
				$result = $this->VeranstaltungskategorieModel->update($this->post()['veranstaltungskategorie_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->VeranstaltungskategorieModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($veranstaltungskategorie = NULL)
	{
		return true;
	}
}
