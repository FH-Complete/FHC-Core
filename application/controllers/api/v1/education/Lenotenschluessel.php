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

class Lenotenschluessel extends API_Controller
{
	/**
	 * LeNotenschluessel API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('LeNotenschluessel' => 'basis/lenotenschluessel:rw'));
		// Load model LeNotenschluesselModel
		$this->load->model('education/LeNotenschluessel_model', 'LeNotenschluesselModel');


	}

	/**
	 * @return void
	 */
	public function getLeNotenschluessel()
	{
		$note = $this->get('note');
		$lehreinheit_id = $this->get('lehreinheit_id');

		if (isset($note) && isset($lehreinheit_id))
		{
			$result = $this->LeNotenschluesselModel->load(array($note, $lehreinheit_id));

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
	public function postLeNotenschluessel()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['note']) && isset($this->post()['lehreinheit_id']))
			{
				$result = $this->LeNotenschluesselModel->update(array($this->post()['note'], $this->post()['lehreinheit_id']), $this->post());
			}
			else
			{
				$result = $this->LeNotenschluesselModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lenotenschluessel = NULL)
	{
		return true;
	}
}
