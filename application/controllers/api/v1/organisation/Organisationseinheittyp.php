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

class Organisationseinheittyp extends API_Controller
{
	/**
	 * Organisationseinheittyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Organisationseinheittyp' => 'basis/organisationseinheittyp:rw'));
		// Load model OrganisationseinheittypModel
		$this->load->model('organisation/organisationseinheittyp_model', 'OrganisationseinheittypModel');


	}

	/**
	 * @return void
	 */
	public function getOrganisationseinheittyp()
	{
		$organisationseinheittyp_kurzbz = $this->get('organisationseinheittyp_kurzbz');

		if (isset($organisationseinheittyp_kurzbz))
		{
			$result = $this->OrganisationseinheittypModel->load($organisationseinheittyp_kurzbz);

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
	public function postOrganisationseinheittyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['organisationseinheittyp_kurzbz']))
			{
				$result = $this->OrganisationseinheittypModel->update($this->post()['organisationseinheittyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->OrganisationseinheittypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($organisationseinheittyp = NULL)
	{
		return true;
	}
}
