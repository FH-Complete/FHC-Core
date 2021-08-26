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

class Entwicklungsteam extends API_Controller
{
	/**
	 * Entwicklungsteam API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Entwicklungsteam' => 'basis/entwicklungsteam:rw'));
		// Load model EntwicklungsteamModel
		$this->load->model('codex/entwicklungsteam_model', 'EntwicklungsteamModel');
	}

	/**
	 * @return void
	 */
	public function getEntwicklungsteam()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		$mitarbeiter_uid = $this->get('mitarbeiter_uid');

		if (isset($studiengang_kz) && isset($mitarbeiter_uid))
		{
			$result = $this->EntwicklungsteamModel->load(array($studiengang_kz, $mitarbeiter_uid));

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
	public function postEntwicklungsteam()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studiengang_kz']) && isset($this->post()['mitarbeiter_uid']))
			{
				$result = $this->EntwicklungsteamModel->update(array($this->post()['entwicklungsteam_id'], $this->post()['mitarbeiter_uid']), $this->post());
			}
			else
			{
				$result = $this->EntwicklungsteamModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($entwicklungsteam = NULL)
	{
		return true;
	}
}
