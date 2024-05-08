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

class Akte extends API_Controller
{
	/**
	 * Akte API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Akte' => 'basis/akte:rw', 'Akten' => 'basis/akte:r', 'AktenAccepted' => 'basis/akte:r'));
		// Load model AkteModel
		$this->load->model('crm/akte_model', 'AkteModel');


	}

	/**
	 * @return void
	 */
	public function getAkte()
	{
		$akteID = $this->get('akte_id');

		if (isset($akteID))
		{
			$result = $this->AkteModel->load($akteID);

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
	public function getAkten()
	{
		$person_id = $this->get('person_id');
		$dokument_kurzbz = $this->get('dokument_kurzbz');
		$stg_kz = $this->get('stg_kz');
		$prestudent_id = $this->get('prestudent_id');

		if (isset($person_id))
		{
			$result = $this->AkteModel->getAkten($person_id, $dokument_kurzbz, $stg_kz, $prestudent_id);

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
	public function getAktenAccepted()
	{
		$person_id = $this->get('person_id');
		$dokument_kurzbz = $this->get('dokument_kurzbz');

		if (isset($person_id))
		{
			$result = $this->AkteModel->getAktenAccepted($person_id, $dokument_kurzbz);

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
	public function postAkte()
	{
		if ($akte = $this->_validate($this->post()))
		{
			if (isset($akte['akte_id']))
			{
				$result = $this->AkteModel->update($akte['akte_id'], $akte);
			}
			else
			{
				$result = $this->AkteModel->insert($akte);
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($akte = null)
	{
		unset($akte['accepted']);

		return $akte;
	}
}
