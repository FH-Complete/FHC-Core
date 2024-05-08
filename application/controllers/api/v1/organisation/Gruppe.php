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

class Gruppe extends API_Controller
{
	/**
	 * Gruppe API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Gruppe' => 'basis/gruppe:rw'));
		// Load model GruppeModel
		$this->load->model('organisation/gruppe_model', 'GruppeModel');


	}

	/**
	 * @return void
	 */
	public function getGruppe()
	{
		$gruppe_kurzbz = $this->get('gruppe_kurzbz');

		if (isset($gruppe_kurzbz))
		{
			$result = $this->GruppeModel->load($gruppe_kurzbz);

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
	public function postGruppe()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['gruppe_kurzbz']))
			{
				$result = $this->GruppeModel->update($this->post()['gruppe_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->GruppeModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($gruppe = NULL)
	{
		return true;
	}
}
