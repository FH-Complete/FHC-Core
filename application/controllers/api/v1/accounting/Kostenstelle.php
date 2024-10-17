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

class Kostenstelle extends API_Controller
{
	/**
	 * Kostenstelle API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Kostenstelle' => 'basis/kostenstelle:rw'));
		// Load model KostenstelleModel
		$this->load->model('accounting/kostenstelle_model', 'KostenstelleModel');
	}

	/**
	 * @return void
	 */
	public function getKostenstelle()
	{
		$kostenstelleID = $this->get('kostenstelle_id');

		if (isset($kostenstelleID))
		{
			$result = $this->KostenstelleModel->load($kostenstelleID);

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
	public function postKostenstelle()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['kostenstelle_id']))
			{
				$result = $this->KostenstelleModel->update($this->post()['kostenstelle_id'], $this->post());
			}
			else
			{
				$result = $this->KostenstelleModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($kostenstelle = NULL)
	{
		return true;
	}
}
