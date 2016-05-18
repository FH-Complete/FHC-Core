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

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Lehrverband extends APIv1_Controller
{
	/**
	 * Lehrverband API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model LehrverbandModel
		$this->load->model('organisation/lehrverband_model', 'LehrverbandModel');
		// Load set the uid of the model to let to check the permissions
		$this->LehrverbandModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getLehrverband()
	{
		$gruppe = $this->get('gruppe');
		$verband = $this->get('verband');
		$semester = $this->get('semester');
		$studiengang_kz = $this->get('studiengang_kz');
		
		if(isset($gruppe) && isset($verband) && isset($semester) && isset($studiengang_kz))
		{
			$result = $this->LehrverbandModel->load(array($gruppe, $verband, $semester, $studiengang_kz));
			
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
	public function postLehrverband()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['lehrverband_id']))
			{
				$result = $this->LehrverbandModel->update($this->post()['lehrverband_id'], $this->post());
			}
			else
			{
				$result = $this->LehrverbandModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($lehrverband = NULL)
	{
		return true;
	}
}