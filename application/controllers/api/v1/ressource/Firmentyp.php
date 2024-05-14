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

class Firmentyp extends API_Controller
{
	/**
	 * Firmentyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Firmentyp' => 'basis/firmentyp:rw'));
		// Load model FirmentypModel
		$this->load->model('ressource/firmentyp_model', 'FirmentypModel');


	}

	/**
	 * @return void
	 */
	public function getFirmentyp()
	{
		$firmentyp_kurzbz = $this->get('firmentyp_kurzbz');

		if (isset($firmentyp_kurzbz))
		{
			$result = $this->FirmentypModel->load($firmentyp_kurzbz);

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
	public function postFirmentyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['firmentyp_kurzbz']))
			{
				$result = $this->FirmentypModel->update($this->post()['firmentyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->FirmentypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($firmentyp = NULL)
	{
		return true;
	}
}
