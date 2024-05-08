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

class Projektarbeit extends API_Controller
{
	/**
	 * Projektarbeit API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Projektarbeit' => 'basis/projektarbeit:rw'));
		// Load model ProjektarbeitModel
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
	}

	/**
	 * @return void
	 */
	public function getProjektarbeit()
	{
		$projektarbeit_id = $this->get('projektarbeit_id');

		if (isset($projektarbeit_id))
		{
			$result = $this->ProjektarbeitModel->load($projektarbeit_id);

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
	public function postProjektarbeit()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['projektarbeit_id']))
			{
				$result = $this->ProjektarbeitModel->update($this->post()['projektarbeit_id'], $this->post());
			}
			else
			{
				$result = $this->ProjektarbeitModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($projektarbeit = NULL)
	{
		return true;
	}
}
