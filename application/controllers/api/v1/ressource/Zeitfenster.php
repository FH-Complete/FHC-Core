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

class Zeitfenster extends API_Controller
{
	/**
	 * Zeitfenster API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zeitfenster' => 'basis/zeitfenster:rw'));
		// Load model ZeitfensterModel
		$this->load->model('ressource/zeitfenster_model', 'ZeitfensterModel');


	}

	/**
	 * @return void
	 */
	public function getZeitfenster()
	{
		$wochentag = $this->get('wochentag');
		$studiengang_kz = $this->get('studiengang_kz');
		$ort_kurzbz = $this->get('ort_kurzbz');
		$stunde = $this->get('stunde');

		if (isset($wochentag) && isset($studiengang_kz) && isset($ort_kurzbz) && isset($stunde))
		{
			$result = $this->ZeitfensterModel->load(array($wochentag, $studiengang_kz, $ort_kurzbz, $stunde));

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
	public function postZeitfenster()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['wochentag']) && isset($this->post()['studiengang_kz']) &&
				isset($this->post()['ort_kurzbz']) && isset($this->post()['stunde']))
			{
				$pksArray = array($this->post()['wochentag'],
									$this->post()['studiengang_kz'],
									$this->post()['ort_kurzbz'],
									$this->post()['stunde']
								);

				$result = $this->ZeitfensterModel->update($pksArray, $this->post());
			}
			else
			{
				$result = $this->ZeitfensterModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zeitfenster = NULL)
	{
		return true;
	}
}
