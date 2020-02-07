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

class Lehrverband extends API_Controller
{
	/**
	 * Lehrverband API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lehrverband' => 'basis/lehrverband:rw'));
		// Load model LehrverbandModel
		$this->load->model('organisation/lehrverband_model', 'LehrverbandModel');


	}

	/**
	 * @return void
	 */
	public function getLehrverband()
	{
		$gruppe = $this->get('gruppe');
		$verband = $this->get('verband');
		$semester = $this->get('semester');
		$studiengang_kz = $this->get('studiengang_kz');

		if (isset($gruppe) && isset($verband) && isset($semester) && isset($studiengang_kz))
		{
			$result = $this->LehrverbandModel->load(array($gruppe, $verband, $semester, $studiengang_kz));

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
	public function postLehrverband()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['gruppe']) && isset($this->post()['verband']) &&
				isset($this->post()['semester']) && isset($this->post()['studiengang_kz']))
			{
				$pksArray = array($this->post()['gruppe'],
									$this->post()['verband'],
									$this->post()['semester'],
									$this->post()['studiengang_kz']
								);

				$result = $this->LehrverbandModel->update($pksArray, $this->post());
			}
			else
			{
				$result = $this->LehrverbandModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lehrverband = NULL)
	{
		return true;
	}
}
