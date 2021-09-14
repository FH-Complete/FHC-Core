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

class Notiz extends API_Controller
{
	/**
	 * Notiz API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Notiz' => 'basis/notiz:rw'));
		// Load model NotizModel
		$this->load->model('person/notiz_model', 'NotizModel');


	}

	/**
	 * @return void
	 */
	public function getNotiz()
	{
		$notizID = $this->get('notiz_id');

		if (isset($notizID))
		{
			$result = $this->NotizModel->load($notizID);

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
	public function postNotiz()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['notiz_id']))
			{
				$result = $this->NotizModel->update($this->post()['notiz_id'], $this->post());
			}
			else
			{
				$result = $this->NotizModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($notiz = NULL)
	{
		return true;
	}
}
