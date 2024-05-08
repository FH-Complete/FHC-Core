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

class Abschlusspruefung extends API_Controller
{
	/**
	 * Abschlusspruefung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Abschlusspruefung' => 'basis/abschlusspruefung:rw'));
		// Load model AbschlusspruefungModel
		$this->load->model('education/Abschlusspruefung_model', 'AbschlusspruefungModel');
	}

	/**
	 * @return void
	 */
	public function getAbschlusspruefung()
	{
		$abschlusspruefung_id = $this->get('abschlusspruefung_id');

		if (isset($abschlusspruefung_id))
		{
			$result = $this->AbschlusspruefungModel->load($abschlusspruefung_id);

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
	public function postAbschlusspruefung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['abschlusspruefung_id']))
			{
				$result = $this->AbschlusspruefungModel->update($this->post()['abschlusspruefung_id'], $this->post());
			}
			else
			{
				$result = $this->AbschlusspruefungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($abschlusspruefung = NULL)
	{
		return true;
	}
}
