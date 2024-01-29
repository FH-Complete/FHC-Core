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

class Geschaeftsjahr2 extends API_Controller
{
	/**
	 * Geschaeftsjahr API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Geschaeftsjahr' => 'basis/geschaeftsjahr:rw'));
		// Load model GeschaeftsjahrModel
		$this->load->model('organisation/geschaeftsjahr_model', 'GeschaeftsjahrModel');


	}

	/**
	 * @return void
	 */
	public function getGeschaeftsjahr()
	{
		$geschaeftsjahr_kurzbz = $this->get('geschaeftsjahr_kurzbz');

		if (isset($geschaeftsjahr_kurzbz))
		{
			$result = $this->GeschaeftsjahrModel->load($geschaeftsjahr_kurzbz);

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
	public function postGeschaeftsjahr()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['geschaeftsjahr_id']))
			{
				$result = $this->GeschaeftsjahrModel->update($this->post()['geschaeftsjahr_id'], $this->post());
			}
			else
			{
				$result = $this->GeschaeftsjahrModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($geschaeftsjahr = NULL)
	{
		return true;
	}
}
