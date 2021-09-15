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

class Benutzer extends API_Controller
{
	/**
	 * Benutzer API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Benutzer' => 'basis/benutzer:rw'));
		// Load model BenutzerModel
		$this->load->model('person/benutzer_model', 'BenutzerModel');


	}

	/**
	 * @return void
	 */
	public function getBenutzer()
	{
		$uid = $this->get('uid');

		if (isset($uid))
		{
			$result = $this->BenutzerModel->load(array('uid' => $uid));

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
	public function postBenutzer()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['uid']))
			{
				$result = $this->BenutzerModel->update($this->post()['uid'], $this->post());
			}
			else
			{
				$result = $this->BenutzerModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($benutzer = NULL)
	{
		return true;
	}
}
