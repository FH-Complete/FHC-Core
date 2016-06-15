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
			$result = $this->_getDms($dms_id, $version);
			if (is_object($result) && $result->error == EXIT_SUCCESS && is_array($result->retval) && count($result->retval) > 0)
			{
				if (($fileContent = $this->_readFile($result->retval[0]->filename)) != false)
				{
					$result->retval[0]->file_content = $fileContent;
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
	private function _getDms($dms_id, $version)
	{
		$result = null;
		
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
		}
		
		return $result;
	}
	
	/**
	 * 
	 */
	public function postDms()
	{
            $dms = $this->_parseData($this->post());
		if ($this->_validate($dms))
		{
			if (isset($dms['dms_id']))
			{
				if ($this->_saveFileOnUpdate($dms))
				{
					$result = $this->DmsModel->update($dms['dms_id'], $this->_dmsFieldsArray($dms));
					if ($result->error == EXIT_SUCCESS)
					{
						$result = $this->DmsModel->updateDmsVersion($dms['dms_id'], $this->_dmsVersionFieldsArray($dms));
					}
				}
			}
			else
			{
				if (($fileName = $this->_saveFileOnInsert($dms)) !== false)
				{
					$result = $this->DmsModel->insert($this->_dmsFieldsArray($dms));
					if ($result->error == EXIT_SUCCESS)
					{
						$result = $this->DmsModel->insertDmsVersion($this->_dmsVersionFieldsArray($dms, $result->retval, $fileName));
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
	private function _dmsFieldsArray($dms)
	{
		$fieldsArray = array('oe_kurzbz', 'dokument_kurzbz', 'kategorie_kurzbz');
		$returnArray = array();
		
		foreach ($fieldsArray as $value)
		{
			if (isset($dms[$value]))
			{
				$returnArray[$value] = $dms[$value];
			}
		}
		
		return $returnArray;
	}
	
	/**
	 * 
	 */
	private function _dmsVersionFieldsArray($dms, $dms_id = null, $fileName = null)
	{
		$fieldsArray = array(
			'version',
			'mimetype',
			'name',
			'beschreibung',
			'letzterzugriff',
			'insertamum',
			'insertvon',
			'updateamum',
			'updatevon'
		);
		$returnArray = array();
		
		foreach ($fieldsArray as $value)
		{
			if (isset($dms[$value]))
			{
				$returnArray[$value] = $dms[$value];
			}
		}
		
		if (isset($dms_id))
		{
			$returnArray['dms_id'] = $dms_id;
		}
		if (isset($fileName))
		{
			$returnArray['filename'] = $fileName;
		}
		
		return $returnArray;
	}
	
	/**
	 * 
	 */
	private function _saveFileOnUpdate($dms)
	{
		$result = $this->_getDms($dms['dms_id'], $dms['version']);
		if (is_object($result) && $result->error == EXIT_SUCCESS && is_array($result->retval) && count($result->retval) > 0)
		{
			$fileName = DMS_PATH . $result->retval[0]->filename;

			if (($fileContent = base64_decode($dms['file_content'])))
			{
				if (file_put_contents($fileName, $fileContent))
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
		$fileName = uniqid() . '.' . pathinfo($dms['name'], PATHINFO_EXTENSION);
		$FileNamePath = DMS_PATH . $fileName;

		if (($fileContent = base64_decode($dms['file_content'])))
		{
			if ($fileHandle = fopen($FileNamePath, 'w'))
			{
				if(fwrite($fileHandle, $fileContent))
				{
					fclose($fileHandle);
					return $fileName;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * 
	 */
	private function _readFile($fileName)
	{
		$fileNamePath = DMS_PATH . $fileName;
		if (file_exists($fileNamePath))
		{
			if ($fileHandle = fopen($fileNamePath, 'r'))
			{
				$cTmpHEX = '';
				while (!feof($fileHandle))
				{
					$cTmpHEX .= fread($fileHandle, 8192);
				}
				fclose($fileHandle);
				return base64_encode($cTmpHEX);
			}
		}

		return false;
	}

	private function _validate($dms = NULL)
	{
		if (!isset($dms['file_content']) || (isset($dms['file_content']) && $dms['file_content'] == ''))
		{
			return false;
		}
		if (!isset($dms['name']) || (isset($dms['name']) && $dms['name'] == ''))
		{
			return false;
		}
		
		return true;
	}
}