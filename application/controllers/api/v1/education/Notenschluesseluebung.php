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

class Notenschluesseluebung extends API_Controller
{
	/**
	 * Notenschluesseluebung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Notenschluesseluebung' => 'basis/notenschluesseluebung:rw'));
		// Load model NotenschluesseluebungModel
		$this->load->model('education/Notenschluesseluebung_model', 'NotenschluesseluebungModel');
	}

	/**
	 * @return void
	 */
	public function getNotenschluesseluebung()
	{
		$note = $this->get('note');
		$uebung_id = $this->get('uebung_id');

		if (isset($note) && isset($uebung_id))
		{
			$result = $this->NotenschluesseluebungModel->load(array($note, $uebung_id));

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
	public function postNotenschluesseluebung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['note']) && isset($this->post()['uebung_id']))
			{
				$result = $this->NotenschluesseluebungModel->update(array($this->post()['note'], $this->post()['uebung_id']), $this->post());
			}
			else
			{
				$result = $this->NotenschluesseluebungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($notenschluesseluebung = NULL)
	{
		return true;
	}
}
