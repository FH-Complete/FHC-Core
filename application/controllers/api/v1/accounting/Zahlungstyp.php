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

class Zahlungstyp extends API_Controller
{
	/**
	 * Zahlungstyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zahlungstyp' => 'basis/zahlungstyp:rw'));
		// Load model ZahlungstypModel
		$this->load->model('accounting/zahlungstyp_model', 'ZahlungstypModel');
	}

	/**
	 * @return void
	 */
	public function getZahlungstyp()
	{
		$zahlungstyp_kurzbz = $this->get('zahlungstyp_kurzbz');

		if (isset($zahlungstyp_kurzbz))
		{
			$result = $this->ZahlungstypModel->load($zahlungstyp_kurzbz);

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
	public function postZahlungstyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['zahlungstyp_kurzbz']))
			{
				$result = $this->ZahlungstypModel->update($this->post()['zahlungstyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->ZahlungstypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zahlungstyp = NULL)
	{
		return true;
	}
}
