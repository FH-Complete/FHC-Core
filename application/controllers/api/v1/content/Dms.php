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

class Dms extends APIv1_Controller
{
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('content/dms_model', 'DmsModel');
		// Load set the uid of the model to let to check the permissions
		$this->DmsModel->setUID($this->_getUID());
	}
	
	/**
	 * 
	 */
	public function getDms()
	{
		$dms_id = $this->get('dms_id');
		$version = $this->get('version');
		
		if (isset($dms_id))
		{
			$result = $this->DmsModel->addJoin('campus.tbl_dms_version', 'dms_id');
			if ($result->error == EXIT_SUCCESS)
			{
				$result = $this->DmsModel->addOrder('version', 'DESC');
				if ($result->error == EXIT_SUCCESS)
				{
					$result = $this->DmsModel->addLimit(1);
					if ($result->error == EXIT_SUCCESS)
					{
						if (!isset($version))
						{
							$result = $this->DmsModel->loadWhere(array('dms_id' => $dms_id));
						}
						else
						{
							$result = $this->DmsModel->loadWhere(array('dms_id' => $dms_id, 'version' => $version));
						}
					}
				}
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	/**
	 * 
	 */
	public function postDms()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['dms_id']))
			{
				$result = $this->DmsModel->update($this->post()['dms_id'], $this->post());
				
				if ($result->error == EXIT_SUCCESS)
				{
					$result = $this->DmsModel->updateDmsVersion($this->post()['dms_id'], $this->post());
				}
			}
			else
			{
				$result = $this->DmsModel->insert($this->post());
				
				if ($result->error == EXIT_SUCCESS)
				{
					$result = $this->DmsModel->insertDmsVersion($this->post());
				}
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($dms = NULL)
	{
		return true;
	}
}