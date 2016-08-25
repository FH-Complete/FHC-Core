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

if (!defined("BASEPATH")) exit("No direct script access allowed");

class Dms extends APIv1_Controller
{
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model("content/Dms_model", "DmsModel");
		$this->load->model("content/DmsVersion_model", "DmsVersionModel");
		$this->load->model("content/DmsFS_model", "DmsFSModel");
	}
	
	/**
	 * 
	 */
	public function getDms()
	{
		$dms_id = $this->get("dms_id");
		$version = $this->get("version");
		
		if (isset($dms_id))
		{
			$result = $this->_getDms($dms_id, $version);
			if (is_object($result) && $result->error == EXIT_SUCCESS && is_array($result->retval) && count($result->retval) > 0)
			{
				$resultFS = $this->DmsFSModel->read($result->retval[0]->filename);
				if (is_object($resultFS) && $resultFS->error == EXIT_SUCCESS)
				{
					$result->retval[0]->file_content = $resultFS->retval;
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
		$dms = $this->_parseData($this->post());
		
		if ($this->_validate($dms))
		{
			$result = null;
			
			if(isset($dms["new"]) && $dms["new"] == true)
			{
				// Remove new parameter to avoid DB insert errors
				unset($dms["new"]);
				
				if (($filename = $this->_saveFileOnInsert($dms)) !== false)
				{
					if(isset($dms["dms_id"]) && $dms["dms_id"] != "")
					{
						$result = $this->DmsVersionModel->insert(
							$this->DmsVersionModel->filterFields($dms, $dms["dms_id"], $filename)
						);
					}
					else
					{
						$result = $this->DmsModel->insert($this->DmsModel->filterFields($dms));
						if ($result->error == EXIT_SUCCESS)
						{
							$result = $this->DmsVersionModel->insert(
								$this->DmsVersionModel->filterFields($dms, $result->retval, $filename)
							);
						}
					}
				}
			}
			else
			{
				if ($this->_saveFileOnUpdate($dms))
				{
					$result = $this->DmsModel->update($dms["dms_id"], $this->DmsModel->filterFields($dms));
					if ($result->error == EXIT_SUCCESS)
					{
						$result = $this->DmsVersionModel->update(
								array(
									$dms["dms_id"],
									$dms["version"]
								),
								$this->DmsVersionModel->filterFields($dms)
						);
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
	private function _getDms($dms_id, $version = null)
	{
		$result = null;
		
		if (isset($dms_id))
		{
			$result = $this->DmsModel->addJoin("campus.tbl_dms_version", "dms_id");
			if ($result->error == EXIT_SUCCESS)
			{
				$result = $this->DmsModel->addOrder("version", "DESC");
				if ($result->error == EXIT_SUCCESS)
				{
					$result = $this->DmsModel->addLimit(1);
					if ($result->error == EXIT_SUCCESS)
					{
						if (!isset($version))
						{
							$result = $this->DmsModel->loadWhere(array("dms_id" => $dms_id));
						}
						else
						{
							$result = $this->DmsModel->loadWhere(array("dms_id" => $dms_id, "version" => $version));
						}
					}
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * 
	 */
	private function _saveFileOnUpdate($dms)
	{
		if(isset($dms["version"]))
		{
			$result = $this->_getDms($dms["dms_id"], $dms["version"]);
		
			if (is_object($result) && $result->error == EXIT_SUCCESS && is_array($result->retval) && count($result->retval) > 0)
			{
				$result = $this->DmsFSModel->write($result->retval[0]->filename, $dms["file_content"]);
				if (is_object($result) && $result->error == EXIT_SUCCESS)
				{
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * 
	 */
	private function _saveFileOnInsert($dms)
	{
		$filename = uniqid() . "." . pathinfo($dms["name"], PATHINFO_EXTENSION);

		$result = $this->DmsFSModel->write($filename, $dms["file_content"]);
		if (is_object($result) && $result->error == EXIT_SUCCESS)
		{
			return $filename;
		}
		
		return false;
	}
	
	private function _validate($dms = NULL)
	{
		if (!isset($dms["file_content"]) || (isset($dms["file_content"]) && $dms["file_content"] == ""))
		{
			return false;
		}
		if (!isset($dms["name"]) || (isset($dms["name"]) && $dms["name"] == ""))
		{
			return false;
		}
		
		return true;
	}
}