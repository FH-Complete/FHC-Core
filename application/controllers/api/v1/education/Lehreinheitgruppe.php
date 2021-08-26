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

class Lehreinheitgruppe extends API_Controller
{
	/**
	 * Lehreinheitgruppe API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lehreinheitgruppe' => 'basis/lehreinheitgruppe:rw'));
		// Load model LehreinheitgruppeModel
		$this->load->model('education/Lehreinheitgruppe_model', 'LehreinheitgruppeModel');
	}

	/**
	 * @return void
	 */
	public function getLehreinheitgruppe()
	{
		$lehreinheitgruppe_id = $this->get('lehreinheitgruppe_id');

		if (isset($lehreinheitgruppe_id))
		{
			$result = $this->LehreinheitgruppeModel->load($lehreinheitgruppe_id);

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
	public function postLehreinheitgruppe()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lehreinheitgruppe_id']))
			{
				$result = $this->LehreinheitgruppeModel->update($this->post()['lehreinheitgruppe_id'], $this->post());
			}
			else
			{
				$result = $this->LehreinheitgruppeModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lehreinheitgruppe = NULL)
	{
		return true;
	}
}
