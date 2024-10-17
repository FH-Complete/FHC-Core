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

class Vertragstyp extends API_Controller
{
	/**
	 * Vertragstyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Vertragstyp' => 'basis/vertragstyp:rw'));
		// Load model VertragstypModel
		$this->load->model('accounting/vertragstyp_model', 'VertragstypModel');
	}

	/**
	 * @return void
	 */
	public function getVertragstyp()
	{
		$vertragstyp_kurzbz = $this->get('vertragstyp_kurzbz');

		if (isset($vertragstyp_kurzbz))
		{
			$result = $this->VertragstypModel->load($vertragstyp_kurzbz);

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
	public function postVertragstyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['vertragstyp_kurzbz']))
			{
				$result = $this->VertragstypModel->update($this->post()['vertragstyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->VertragstypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($vertragstyp = NULL)
	{
		return true;
	}
}
