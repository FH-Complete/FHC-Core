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

class Projektbetreuer extends API_Controller
{
	/**
	 * Projektbetreuer API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Projektbetreuer' => 'basis/projektbetreuer:rw'));
		// Load model ProjektbetreuerModel
		$this->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');
	}

	/**
	 * @return void
	 */
	public function getProjektbetreuer()
	{
		$betreuerart_kurzbz = $this->get('betreuerart_kurzbz');
		$projektarbeit_id = $this->get('projektarbeit_id');
		$person_id = $this->get('person_id');

		if (isset($betreuerart_kurzbz) && isset($projektarbeit_id) && isset($person_id))
		{
			$result = $this->ProjektbetreuerModel->load(array($betreuerart_kurzbz, $projektarbeit_id, $person_id));

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
	public function postProjektbetreuer()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['betreuerart_kurzbz']) && isset($this->post()['projektarbeit_id']) && isset($this->post()['person_id']))
			{
				$pksArray = array($this->post()['betreuerart_kurzbz'],
									$this->post()['projektarbeit_id'],
									$this->post()['person_id']
								);

				$result = $this->ProjektbetreuerModel->update($pksArray, $this->post());
			}
			else
			{
				$result = $this->ProjektbetreuerModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($projektbetreuer = NULL)
	{
		return true;
	}
}
