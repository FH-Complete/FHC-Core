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

class Ortraumtyp extends API_Controller
{
	/**
	 * Ortraumtyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Ortraumtyp' => 'basis/ortraumtyp:rw'));
		// Load model OrtraumtypModel
		$this->load->model('ressource/ortraumtyp_model', 'OrtraumtypModel');


	}

	/**
	 * @return void
	 */
	public function getOrtraumtyp()
	{
		$hierarchie = $this->get('hierarchie');
		$ort_kurzbz = $this->get('ort_kurzbz');

		if (isset($hierarchie) && isset($ort_kurzbz))
		{
			$result = $this->OrtraumtypModel->load(array($hierarchie, $ort_kurzbz));

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
	public function postOrtraumtyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['hierarchie']) && isset($this->post()['ort_kurzbz']))
			{
				$result = $this->OrtraumtypModel->update(array($this->post()['hierarchie'], $this->post()['ort_kurzbz']), $this->post());
			}
			else
			{
				$result = $this->OrtraumtypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($ortraumtyp = NULL)
	{
		return true;
	}
}
