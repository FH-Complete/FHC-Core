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

class Notenschluessel extends API_Controller
{
	/**
	 * Notenschluessel API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Notenschluessel' => 'basis/notenschluessel:rw'));
		// Load model NotenschluesselModel
		$this->load->model('education/Notenschluessel_model', 'NotenschluesselModel');
	}

	/**
	 * @return void
	 */
	public function getNotenschluessel()
	{
		$notenschluessel_kurzbz = $this->get('notenschluessel_kurzbz');

		if (isset($notenschluessel_kurzbz))
		{
			$result = $this->NotenschluesselModel->load($notenschluessel_kurzbz);

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
	public function postNotenschluessel()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['notenschluessel_kurzbz']))
			{
				$result = $this->NotenschluesselModel->update($this->post()['notenschluessel_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->NotenschluesselModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($notenschluessel = NULL)
	{
		return true;
	}
}
