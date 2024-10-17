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

class Notenschluesselzuordnung extends API_Controller
{
	/**
	 * Notenschluesselzuordnung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Notenschluesselzuordnung' => 'basis/notenschluesselzuordnung:rw'));
		// Load model NotenschluesselzuordnungModel
		$this->load->model('education/Notenschluesselzuordnung_model', 'NotenschluesselzuordnungModel');
	}

	/**
	 * @return void
	 */
	public function getNotenschluesselzuordnung()
	{
		$notenschluesselzuordnung_id = $this->get('notenschluesselzuordnung_id');

		if (isset($notenschluesselzuordnung_id))
		{
			$result = $this->NotenschluesselzuordnungModel->load($notenschluesselzuordnung_id);

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
	public function postNotenschluesselzuordnung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['notenschluesselzuordnung_id']))
			{
				$result = $this->NotenschluesselzuordnungModel->update($this->post()['notenschluesselzuordnung_id'], $this->post());
			}
			else
			{
				$result = $this->NotenschluesselzuordnungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($notenschluesselzuordnung = NULL)
	{
		return true;
	}
}
