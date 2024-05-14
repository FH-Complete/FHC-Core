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

class Dokument extends API_Controller
{
	/**
	 * Dokument API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Dokument' => 'basis/dokument:rw'));
		// Load model DokumentModel
		$this->load->model('crm/dokument_model', 'DokumentModel');
	}

	/**
	 * @return void
	 */
	public function getDokument()
	{
		$dokument_kurzbz = $this->get('dokument_kurzbz');

		if (isset($dokument_kurzbz))
		{
			$result = $this->DokumentModel->load($dokument_kurzbz);

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
	public function postDokument()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['dokument_kurzbz']))
			{
				$result = $this->DokumentModel->update($this->post()['dokument_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->DokumentModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($dokument = NULL)
	{
		return true;
	}
}
