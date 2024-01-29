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

class Bestellungtag extends API_Controller
{
	/**
	 * Bestellungtag API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Bestellungtag' => 'basis/bestellungtag:rw'));
		// Load model BestellungtagModel
		$this->load->model('accounting/bestellungtag_model', 'BestellungtagModel');
	}

	/**
	 * @return void
	 */
	public function getBestellungtag()
	{
		$bestellung_id = $this->get('bestellung_id');
		$tag = $this->get('tag');

		if (isset($bestellung_id) && isset($tag))
		{
			$result = $this->BestellungtagModel->load(array($bestellung_id, $tag));

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
	public function postBestellungtag()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['bestellungtag_id']))
			{
				$result = $this->BestellungtagModel->update($this->post()['bestellungtag_id'], $this->post());
			}
			else
			{
				$result = $this->BestellungtagModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($bestellungtag = NULL)
	{
		return true;
	}
}
