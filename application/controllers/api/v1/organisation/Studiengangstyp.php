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

class Studiengangstyp extends API_Controller
{
	/**
	 * Studiengangstyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Studiengangstyp' => 'basis/studiengangstyp:rw'));
		// Load model StudiengangstypModel
		$this->load->model('organisation/studiengangstyp_model', 'StudiengangstypModel');


	}

	/**
	 * @return void
	 */
	public function getStudiengangstyp()
	{
		$typ = $this->get('typ');

		if (isset($typ))
		{
			$result = $this->StudiengangstypModel->load($typ);

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
	public function postStudiengangstyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['typ']))
			{
				$result = $this->StudiengangstypModel->update($this->post()['typ'], $this->post());
			}
			else
			{
				$result = $this->StudiengangstypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($studiengangstyp = NULL)
	{
		return true;
	}
}
