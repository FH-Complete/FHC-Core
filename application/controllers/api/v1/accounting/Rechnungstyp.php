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

class Rechnungstyp extends API_Controller
{
	/**
	 * Rechnungstyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Rechnungstyp' => 'basis/rechnungstyp:rw'));
		// Load model RechnungstypModel
		$this->load->model('accounting/rechnungstyp_model', 'RechnungstypModel');
	}

	/**
	 * @return void
	 */
	public function getRechnungstyp()
	{
		$rechnungstyp_kurzbz = $this->get('rechnungstyp_kurzbz');

		if (isset($rechnungstyp_kurzbz))
		{
			$result = $this->RechnungstypModel->load($rechnungstyp_kurzbz);

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
	public function postRechnungstyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['rechnungstyp_kurzbz']))
			{
				$result = $this->RechnungstypModel->update($this->post()['rechnungstyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->RechnungstypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($rechnungstyp = NULL)
	{
		return true;
	}
}
