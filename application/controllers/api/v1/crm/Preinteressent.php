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

class Preinteressent extends API_Controller
{
	/**
	 * Person API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Preinteressent' => 'basis/preinteressent:rw', 'PreinteressentByPersonID' => 'basis/preinteressent:r'));
		// Load model PersonModel
		$this->load->model('crm/preinteressent_model', 'PreinteressentModel');


	}

	/**
	 * @return void
	 */
	public function getPreinteressent()
	{
		$preinteressent_id = $this->get('preinteressent_id');

		if (isset($preinteressent_id))
		{
			$result = $this->PreinteressentModel->load($preinteressent_id);

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
	public function getPreinteressentByPersonID()
	{
		$person_id = $this->get('person_id');

		if (isset($person_id))
		{
			$result = $this->PreinteressentModel->load(array('person_id' => $person_id));

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
	public function postPreinteressent()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['preinteressent_id']))
			{
				$result = $this->PreinteressentModel->update($this->post()['preinteressent_id'], $this->post());
			}
			else
			{
				$result = $this->PreinteressentModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($preinteressent)
	{
		if ($preinteressent['person_id'] == '')
		{
			//$this->errormsg = 'Person_id muss angegeben werden';
			return false;
		}

		if ($preinteressent['aufmerksamdurch_kurzbz'] == '')
		{
			//$this->errormsg = 'Aufmerksamdurch muss angegeben werden';
			return false;
		}

		return true;
	}
}
