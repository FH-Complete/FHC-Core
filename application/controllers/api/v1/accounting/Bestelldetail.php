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

class Bestelldetail extends API_Controller
{
	/**
	 * Bestelldetail API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Bestelldetail' => 'basis/bestelldetail:rw'));
		// Load model BestelldetailModel
		$this->load->model('accounting/bestelldetail_model', 'BestelldetailModel');
	}

	/**
	 * @return void
	 */
	public function getBestelldetail()
	{
		$bestelldetailID = $this->get('bestelldetail_id');

		if (isset($bestelldetailID))
		{
			$result = $this->BestelldetailModel->load($bestelldetailID);

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
	public function postBestelldetail()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['bestelldetail_id']))
			{
				$result = $this->BestelldetailModel->update($this->post()['bestelldetail_id'], $this->post());
			}
			else
			{
				$result = $this->BestelldetailModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($bestelldetail = NULL)
	{
		return true;
	}
}
