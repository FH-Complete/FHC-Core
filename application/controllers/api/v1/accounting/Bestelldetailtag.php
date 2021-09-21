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

class Bestelldetailtag extends API_Controller
{
	/**
	 * Bestelldetailtag API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Bestelldetailtag' => 'basis/bestelldetailtag:rw'));
		// Load model BestelldetailtagModel
		$this->load->model('accounting/bestelldetailtag_model', 'BestelldetailtagModel');
	}

	/**
	 * @return void
	 */
	public function getBestelldetailtag()
	{
		$bestelldetail_id = $this->get('bestelldetail_id');
		$tag = $this->get('tag');

		if (isset($bestelldetail_id) && isset($tag))
		{
			$result = $this->BestelldetailtagModel->load(array($bestelldetail_id, $tag));

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
	public function postBestelldetailtag()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['bestelldetailtag_id']))
			{
				$result = $this->BestelldetailtagModel->update($this->post()['bestelldetailtag_id'], $this->post());
			}
			else
			{
				$result = $this->BestelldetailtagModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($bestelldetailtag = NULL)
	{
		return true;
	}
}
