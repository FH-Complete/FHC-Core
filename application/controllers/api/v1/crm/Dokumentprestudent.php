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

class Dokumentprestudent extends API_Controller
{
	/**
	 * Dokumentprestudent API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'Dokumentprestudent' => 'basis/dokumentprestudent:rw',
				'SetAccepted' => 'basis/dokumentprestudent:w',
				'SetAcceptedDocuments' => 'basis/dokumentprestudent:w'
			)
		);
		// Load model DokumentprestudentModel
		$this->load->model('crm/Dokumentprestudent_model', 'DokumentprestudentModel');
	}

	/**
	 * @return void
	 */
	public function getDokumentprestudent()
	{
		$prestudent_id = $this->get('prestudent_id');
		$dokument_kurzbz = $this->get('dokument_kurzbz');

		if (isset($prestudent_id) && isset($dokument_kurzbz))
		{
			$result = $this->DokumentprestudentModel->load(array($prestudent_id, $dokument_kurzbz));

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
	public function postDokumentprestudent()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['prestudent_id']) && isset($this->post()['dokument_kurzbz']))
			{
				$result = $this->DokumentprestudentModel->update(array($this->post()['prestudent_id'], $this->post()['dokument_kurzbz']), $this->post());
			}
			else
			{
				$result = $this->DokumentprestudentModel->insert($this->post());
			}

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
	public function postSetAccepted()
	{
		if (isset($this->post()['prestudent_id']) && isset($this->post()['studiengang_kz']))
		{
			$result = $this->DokumentprestudentModel->setAccepted($this->post()['prestudent_id'], $this->post()['studiengang_kz']);

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
	public function postSetAcceptedDocuments()
	{
		if (isset($this->post()['prestudent_id']) && is_array($this->post()['dokument_kurzbz']))
		{
			$result = $this->DokumentprestudentModel->setAcceptedDocuments($this->post()['prestudent_id'], $this->post()['dokument_kurzbz']);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($dokumentprestudent = null)
	{
		return true;
	}
}
