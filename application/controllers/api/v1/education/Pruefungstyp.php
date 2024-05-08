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

class Pruefungstyp extends API_Controller
{
	/**
	 * Pruefungstyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Pruefungstyp' => 'basis/pruefungstyp:rw'));
		// Load model PruefungstypModel
		$this->load->model('education/Pruefungstyp_model', 'PruefungstypModel');
	}

	/**
	 * @return void
	 */
	public function getPruefungstyp()
	{
		$pruefungstyp_kurzbz = $this->get('pruefungstyp_kurzbz');

		if (isset($pruefungstyp_kurzbz))
		{
			$result = $this->PruefungstypModel->load($pruefungstyp_kurzbz);

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
	public function postPruefungstyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['pruefungstyp_kurzbz']))
			{
				$result = $this->PruefungstypModel->update($this->post()['pruefungstyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->PruefungstypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($pruefungstyp = NULL)
	{
		return true;
	}
}
