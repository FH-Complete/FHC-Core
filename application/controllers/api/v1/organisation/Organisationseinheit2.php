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

class Organisationseinheit2 extends API_Controller
{
	/**
	 * Organisationseinheit API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Organisationseinheit' => 'basis/organisationseinheit:rw'));
		// Load model OrganisationseinheitModel
		$this->load->model('organisation/organisationseinheit_model', 'OrganisationseinheitModel');


	}

	/**
	 * @return void
	 */
	public function getOrganisationseinheit()
	{
		$oe_kurzbz = $this->get('oe_kurzbz');

		if (isset($oe_kurzbz))
		{
			$result = $this->OrganisationseinheitModel->load($oe_kurzbz);

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
	public function postOrganisationseinheit()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['organisationseinheit_id']))
			{
				$result = $this->OrganisationseinheitModel->update($this->post()['organisationseinheit_id'], $this->post());
			}
			else
			{
				$result = $this->OrganisationseinheitModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($organisationseinheit = NULL)
	{
		return true;
	}
}
