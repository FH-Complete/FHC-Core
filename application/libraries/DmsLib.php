<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class DmsLib
{
	const FILE_CONTENT_PROPERTY = 'file_content';

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->ci =& get_instance();
		
		$this->ci->load->model('crm/Akte_model', 'AkteModel');
		$this->ci->load->model('content/Dms_model', 'DmsModel');
		$this->ci->load->model('content/DmsVersion_model', 'DmsVersionModel');
		$this->ci->load->model('content/DmsFS_model', 'DmsFSModel');
		
		// Loads helper message to manage returning messages
		$this->ci->load->helper('message');
	}

	/**
	 * read
	 */
	public function read($dms_id, $version = null)
	{
		$result = null;

		if (isset($dms_id))
		{
			$this->ci->DmsModel->addJoin('campus.tbl_dms_version', 'dms_id');
			$this->ci->DmsModel->addOrder('version', 'DESC');
			$this->ci->DmsModel->addLimit(1);

			if (!isset($version))
			{
				$result = $this->ci->DmsModel->load($dms_id);
			}
			else
			{
				$result = $this->ci->DmsModel->loadWhere(array('dms_id' => $dms_id, 'version' => $version));
			}
		}
		
		if (hasData($result))
		{
			$resultFS = $this->ci->DmsFSModel->read($result->retval[0]->filename);
			if (isSuccess($resultFS))
			{
				$result->retval[0]->{DmsLib::FILE_CONTENT_PROPERTY} = $resultFS->retval;
			}
			else
			{
				$result = $resultFS;
			}
		}
		
		return $result;
	}
	
	/**
	 * getAktenAcceptedDms
	 */
	public function getAktenAcceptedDms($person_id, $dokument_kurzbz = null, $no_file = null)
	{
		$result = $this->ci->AkteModel->getAktenAcceptedDms($person_id, $dokument_kurzbz);
		
		if (hasData($result) && $no_file == null)
		{
			for ($i = 0; $i < count($result->retval); $i++)
			{
				$resultFS = $this->ci->DmsFSModel->read($result->retval[$i]->filename);
				if (isSuccess($resultFS))
				{
					$result->retval[$i]->{DmsLib::FILE_CONTENT_PROPERTY} = $resultFS->retval;
				}
				else
				{
					$result = $resultFS;
				}
			}
		}
		
		return $result;
	}

	/**
	 * save
	 */
	public function save($dms)
	{
		$result = null;

		if (isset($dms['new']) && $dms['new'] == true)
		{
			// Remove new parameter to avoid DB insert errors
			unset($dms['new']);

			$result = $this->_saveFileOnInsert($dms);
			if (isSuccess($result))
			{
				$filename = $result->retval;
				if (isset($dms['dms_id']) && $dms['dms_id'] != '')
				{
					$result = $this->ci->DmsVersionModel->insert(
						$this->ci->DmsVersionModel->filterFields($dms, $dms['dms_id'], $filename)
					);
				}
				else
				{
					$result = $this->ci->DmsModel->insert($this->ci->DmsModel->filterFields($dms));
					if (isSuccess($result))
					{
						$result = $this->ci->DmsVersionModel->insert(
							$this->ci->DmsVersionModel->filterFields($dms, $result->retval, $filename)
						);
					}
				}
			}
		}
		else
		{
			$result = $this->_saveFileOnUpdate($dms);
			if (isSuccess($result))
			{
				$result = $this->ci->DmsModel->update($dms['dms_id'], $this->ci->DmsModel->filterFields($dms));
				if (isSuccess($result))
				{
					$result = $this->ci->DmsVersionModel->update(
						array(
							$dms['dms_id'],
							$dms['version']
						),
						$this->ci->DmsVersionModel->filterFields($dms)
					);
				}
			}
		}

		return $result;
	}
	
	/**
	 * delete
	 */
	public function delete($person_id, $dms_id)
	{
		$result = null;
		
		// If the parameters are valid
		if (is_numeric($person_id) && is_numeric($dms_id))
		{
			// Start DB transaction
			$this->ci->db->trans_start(false);
			
			// Get akte_id from table tbl_akte
			$result = $this->ci->AkteModel->loadWhere(array('person_id' => $person_id, 'dms_id' => $dms_id));
			if (isSuccess($result))
			{
				// Delete all entries in tbl_akte
				for ($i = 0; $i < count($result->retval); $i++)
				{
					$this->ci->AkteModel->delete($result->retval[$i]->akte_id);
				}
				
				// Get all filenames related to this dms
				$resultFileNames = $this->ci->DmsVersionModel->loadWhere(array('dms_id' => $dms_id));
				if (isSuccess($resultFileNames))
				{
					// Delete from tbl_dms_version
					$result = $this->ci->DmsVersionModel->delete(array('dms_id' => $dms_id));
					if (isSuccess($result))
					{
						// Delete from tbl_dms
						$result = $this->ci->DmsModel->delete($dms_id);
					}
				}
			}
			
			// Transaction complete!
			$this->ci->db->trans_complete();
			
			// Check if everything went ok during the transaction
			if ($this->ci->db->trans_status() === false || isError($result))
			{
				$this->ci->db->trans_rollback();
				$result = error($result->msg, EXIT_ERROR);
			}
			else
			{
				$this->ci->db->trans_commit();
				$result = success('Dms successfully removed from DB');
			}
			
			// If everything is ok
			if (isSuccess($result))
			{
				// Remove all files related to this person and dms
				for ($i = 0; $i < count($resultFileNames->retval); $i++)
				{
					$this->ci->DmsFSModel->remove($resultFileNames->retval[$i]->filename);
				}
			}
		}
		else
		{
			$result = error('Invalid parameters');
		}
		
		return $result;
	}
	
	/**
	 * _saveFileOnInsert
	 */
	private function _saveFileOnInsert($dms)
	{
		$filename = uniqid().'.'.pathinfo($dms['name'], PATHINFO_EXTENSION);
		
		$result = $this->ci->DmsFSModel->write($filename, $dms['file_content']);
		if (isSuccess($result))
		{
			$result->retval = $filename;
		}

		return $result;
	}

	/**
	 * _saveFileOnUpdate
	 */
	private function _saveFileOnUpdate($dms)
	{
		$result = null;

		if (isset($dms['version']))
		{
			$result = $this->read($dms['dms_id'], $dms['version']);

			if (hasData($result))
			{
				$result = $this->ci->DmsFSModel->write($result->retval[0]->filename, $dms['file_content']);
			}
		}

		return $result;
	}
}
