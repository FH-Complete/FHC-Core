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

class Orgform extends API_Controller
{
	/**
	 * Orgform API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Orgform' => 'basis/orgform:rw', 'All' => 'basis/orgform:r', 'OrgformLV' => 'basis/orgform:r'));
		// Load model OrgformModel
		$this->load->model('codex/orgform_model', 'OrgformModel');
	}

	/**
	 * @return void
	 */
	public function getOrgform()
	{
		$orgform_kurzbz = $this->get('orgform_kurzbz');

		if (isset($orgform_kurzbz))
		{
			$result = $this->OrgformModel->load($orgform_kurzbz);

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
	public function getAll()
	{
		$result = $this->OrgformModel->load();

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function getOrgformLV()
	{
		$result = $this->OrgformModel->getOrgformLV();

		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function postOrgform()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['orgform_kurzbz']))
			{
				$result = $this->OrgformModel->update($this->post()['orgform_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->OrgformModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($orgform = NULL)
	{
		return true;
	}
}
