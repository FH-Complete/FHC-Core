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

class Dokumentstudiengang extends API_Controller
{
	/**
	 * Dokumentstudiengang API constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'Dokumentstudiengang' => 'basis/dokumentstudiengang:rw',
				'DokumentstudiengangByStudiengang_kz' => 'basis/dokumentstudiengang:r'
			)
		);
		// Load model DokumentstudiengangModel
		$this->load->model('crm/Dokumentstudiengang_model', 'DokumentstudiengangModel');
	}

	/**
	 * @return void
	 */
	public function getDokumentstudiengang()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		$dokument_kurzbz = $this->get('dokument_kurzbz');

		if (isset($studiengang_kz) && isset($dokument_kurzbz))
		{
			$result = $this->DokumentstudiengangModel->load(array($studiengang_kz, $dokument_kurzbz));

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
	public function getDokumentstudiengangByStudiengang_kz()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		$onlinebewerbung = $this->get('onlinebewerbung');
		$pflicht = $this->get('pflicht');
		$nachreichbar = $this->get('nachreichbar');

		if (isset($studiengang_kz))
		{
			$result = $this->DokumentstudiengangModel->getDokumentstudiengangByStudiengang_kz(
				$studiengang_kz,
				$onlinebewerbung,
				$pflicht,
				$nachreichbar
			);

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
	public function postDokumentstudiengang()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studiengang_kz']) && isset($this->post()['dokument_kurzbz']))
			{
				$result = $this->DokumentstudiengangModel->update(
					array($this->post()['studiengang_kz'],
					$this->post()['dokument_kurzbz']),
					$this->post()
				);
			}
			else
			{
				$result = $this->DokumentstudiengangModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($dokumentstudiengang = NULL)
	{
		return true;
	}
}
