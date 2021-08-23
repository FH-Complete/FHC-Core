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

class Personfunktionstandort extends API_Controller
{
	/**
	 * Personfunktionstandort API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Personfunktionstandort' => 'basis/personfunktionstandort:rw'));
		// Load model PersonfunktionstandortModel
		$this->load->model('ressource/personfunktionstandort_model', 'PersonfunktionstandortModel');


	}

	/**
	 * @return void
	 */
	public function getPersonfunktionstandort()
	{
		$personfunktionstandortID = $this->get('personfunktionstandort_id');

		if (isset($personfunktionstandortID))
		{
			$result = $this->PersonfunktionstandortModel->load($personfunktionstandortID);

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
	public function postPersonfunktionstandort()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['personfunktionstandort_id']))
			{
				$result = $this->PersonfunktionstandortModel->update($this->post()['personfunktionstandort_id'], $this->post());
			}
			else
			{
				$result = $this->PersonfunktionstandortModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($personfunktionstandort = NULL)
	{
		return true;
	}
}
