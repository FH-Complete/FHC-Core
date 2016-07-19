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

class Dokumentstudiengang extends APIv1_Controller
{
	/**
	 * Dokumentstudiengang API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model DokumentstudiengangModel
		$this->load->model('crm/dokumentstudiengang_model', 'DokumentstudiengangModel');
		
		
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
	public function getDokumentstudiengangByStudiengang_bz()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		$onlinebewerbung = $this->get('onlinebewerbung');
		$pflicht = $this->get('pflicht');
		
		if (isset($studiengang_kz))
		{
			$result = $this->DokumentstudiengangModel->addJoin("public.tbl_dokument", "dokument_kurzbz");
			if(is_object($result) && $result->error == EXIT_SUCCESS)
			{
				$parameterArray = array("studiengang_kz" => $studiengang_kz);
			
				if( isset($onlinebewerbung))
				{
					$parameterArray["onlinebewerbung"] = $onlinebewerbung;
				}

				if( isset($pflicht))
				{
					$parameterArray["pflicht"] = $pflicht;
				}
				
				$result = $this->DokumentstudiengangModel->loadWhere($parameterArray);
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
	public function postDokumentstudiengang()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studiengang_kz']) && isset($this->post()['dokument_kurzbz']))
			{
				$result = $this->DokumentstudiengangModel->update(array($this->post()['studiengang_kz'], $this->post()['dokument_kurzbz']), $this->post());
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