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

class Notenschluesselaufteilung extends API_Controller
{
	/**
	 * Notenschluesselaufteilung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Notenschluesselaufteilung' => 'basis/notenschluesselaufteilung:rw'));
		// Load model NotenschluesselaufteilungModel
		$this->load->model('education/Notenschluesselaufteilung_model', 'NotenschluesselaufteilungModel');
	}

	/**
	 * @return void
	 */
	public function getNotenschluesselaufteilung()
	{
		$notenschluesselaufteilung_id = $this->get('notenschluesselaufteilung_id');

		if (isset($notenschluesselaufteilung_id))
		{
			$result = $this->NotenschluesselaufteilungModel->load($notenschluesselaufteilung_id);

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
	public function postNotenschluesselaufteilung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['notenschluesselaufteilung_id']))
			{
				$result = $this->NotenschluesselaufteilungModel->update($this->post()['notenschluesselaufteilung_id'], $this->post());
			}
			else
			{
				$result = $this->NotenschluesselaufteilungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($notenschluesselaufteilung = NULL)
	{
		return true;
	}
}
