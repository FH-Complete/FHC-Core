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

class Lvinfo extends API_Controller
{
	/**
	 * Lvinfo API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lvinfo' => 'basis/lvinfo:rw'));
		// Load model LvinfoModel
		$this->load->model('education/Lvinfo_model', 'LvinfoModel');
	}

	/**
	 * @return void
	 */
	public function getLvinfo()
	{
		$sprache = $this->get('sprache');
		$lehrveranstaltung_id = $this->get('lehrveranstaltung_id');

		if (isset($sprache) && isset($lehrveranstaltung_id))
		{
			$result = $this->LvinfoModel->load(array($sprache, $lehrveranstaltung_id));

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
	public function postLvinfo()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['sprache']) && isset($this->post()['lehrveranstaltung_id']))
			{
				$result = $this->LvinfoModel->update(array($this->post()['sprache'], $this->post()['lehrveranstaltung_id']), $this->post());
			}
			else
			{
				$result = $this->LvinfoModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lvinfo = NULL)
	{
		return true;
	}
}
