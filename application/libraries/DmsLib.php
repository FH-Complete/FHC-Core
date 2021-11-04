<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class DmsLib extends FHC_Controller
{
	const FILE_CONTENT_PROPERTY = 'file_content';
	
	private $UPLOAD_PATH = DMS_PATH; // temporary directory to store the upload file

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
	}
	
	/**
	 * Load a DMS Document.
	 * If no version is particularly given, the latest version is loaded.
	 *
	 * @param $dms_id
	 * @param integer $version
	 * @return array
	 */
	public function load($dms_id, $version = null)
	{
		if (is_numeric($dms_id))
		{
			$this->ci->DmsModel->addJoin('campus.tbl_dms_version', 'dms_id');
			$this->ci->DmsModel->addOrder('version', 'DESC');
			$this->ci->DmsModel->addLimit(1);
			
			if (!is_numeric($version))
			{
				return $this->ci->DmsModel->load($dms_id);
			}
			else
			{
				return $this->ci->DmsModel->loadWhere(array('dms_id' => $dms_id, 'version' => $version));
			}
		}
		return error('The parameter DMS ID must be a number');
	}

	/**
	 * Read a DMS Document from the Filesystem
	 * @param int $dms_id ID of the Document.
	 * @param int $version The version of the Document (latest if null).
	 * @return object success or error
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
	 * Get all accepted Documents of a Person
	 *
	 * @param int $person_id ID of the person.
	 * @param string $dokument_kurzbz Type of document.
	 * @param bool $no_file If null then loads also the content.
	 * @return object success or error
	 */
	public function getAktenAcceptedDms($person_id, $dokument_kurzbz = null, $no_file = null)
	{
		$result = $this->ci->AkteModel->getAktenAcceptedDms($person_id, $dokument_kurzbz);

		if (hasData($result) && $no_file == null)
		{
			$cnt = count($result->retval);
			for ($i = 0; $i < $cnt; $i++)
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
	 * Uploads a document and saves it to DMS
	 * @param $dms DMS assoc array
	 * @param $field_name  Name of the HTML uploadfile input name attribute
	 * @param array $allowed_types Default: all. Param example: array(jpg, pdf)
	 * @return array
	 */
	public function upload($dms, $field_name, $allowed_types = array('*'))
	{
		// Init upload configs
		$this->_loadUploadLibrary($allowed_types);

		if (!$this->ci->upload->do_upload($field_name))
		{
			return error($this->ci->upload->display_errors());
		}

		$upload_data = $this->ci->upload->data();  // data about the uploaded file
		$filename = $upload_data['file_name'];

		// Insert to DMS table
		if (!$result = $this->ci->DmsModel->insert($this->ci->DmsModel->filterFields($dms)))
		{
			return error('Failed inserting to DMS');
		}
		$upload_data['dms_id'] = $result->retval;

		// Insert DMS version
		if (!$result = $this->ci->DmsVersionModel->insert(
			$this->ci->DmsVersionModel->filterFields($dms, $result->retval, $filename)))
		{
			return error('Failed inserting DMS version');
		}

		// return result of uploaded data
		return success($upload_data); // data about the uploaded file
	}
	
	/**
	 * Download a document.
	 *
	 * @param $dms_id
	 * @param string $filename $filename If String is given, it will be used as filename on download
	 * @param string $disposition [inline | attachment]
	 *        Inline opens doc in new tab. Attachment displays download dialog box.
	 */
	public function download($dms_id, $filename = null, $disposition = 'inline')
	{
		$result = $this->getFileInfo($dms_id);
		
		if (isError($result))
		{
			return error(getError($result));
		}

		$fileObj = getData($result);
		
		// Change filename, if filename is provided
		if (is_string($filename))
		{
			$fileObj->name = $filename;
		}
		
		// Add file disposition
		if ($disposition == 'attachment')
		{
			$fileObj->disposition = 'attachment';
		}
		else
		{
			$fileObj->disposition = 'inline';
		}
	
		// Output file
		if(!$this->outputFile($fileObj))
		{
			return error('Error on file output');
		}
	}
	
	/**
	 * Get file information.
	 *
	 * @param $dms_id
	 * @param integer $version
	 * @return array with File Object.
	 */
	public function getFileInfo($dms_id, $version = null)
	{
		if (!is_numeric($dms_id))
		{
			return error('Wrong parameter');
		}
		
		// Load file
		$result = $this->load($dms_id, $version);

		if (isError($result))
		{
			return error(getError($result));
		}

		// Store file information in fileObj
		$fileObj = new StdClass();
		$fileObj->filename = getData($result)[0]->filename;
		$fileObj->file = DMS_PATH. getData($result)[0]->filename;
		$fileObj->name = DMS_PATH. getData($result)[0]->name;   // original users filename
		$fileObj->mimetype = DMS_PATH. getData($result)[0]->mimetype;
		
		return success($fileObj);

	}

	/**
	 * Saves a Document
	 * @param object $dms DMS Object ot be saved.
	 * @return object
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
	 * Deletes a Akte of a Person
	 * @param int $person_id ID of the person.
	 * @param int $dms_id Id of the Document.
	 * @return object
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
				$cnt = count($result->retval);
				for ($i = 0; $i < $cnt; $i++)
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
				$result = error('An error occurred while performing a delete operation', EXIT_ERROR);
			}
			else
			{
				$this->ci->db->trans_commit();
				$result = success('Dms successfully removed from DB');
			}

			// If everything is ok
			if (isSuccess($result))
			{
				$cnt = count($resultFileNames->retval);
				// Remove all files related to this person and dms
				for ($i = 0; $i < $cnt; $i++)
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
	 * Loads the Content of an akte
	 * @param int $akte_id Id of the akte.
	 * @return object with document content or error
	 */
	public function getAkteContent($akte_id)
	{
		$akte = $this->ci->AkteModel->load($akte_id);
		if (hasData($akte))
		{
			if ($akte->retval[0]->inhalt != '')
			{
				return success(base64_decode($akte->retval[0]->inhalt));
			}
			elseif ($akte->retval[0]->dms_id != '')
			{
				$dmscontent = $this->read($akte->retval[0]->dms_id);
				if (isSuccess($dmscontent))
				{
					return success(base64_decode($dmscontent->retval[0]->file_content));
				}
				else
				{
					return error(getError($dmscontent));
				}
			}
			else
			{
				return error('No Content available');
			}
		}
		else
		{
			return error(getError($akte));
		}
	}

	/**
	 * Saves the Content of a DMS in the Filesystem
	 * @param object $dms DMS object to be saved.
	 * @return object
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
	 * Updates the File in the Filesystem
	 * @param object $dms DMS object to update.
	 * @return object
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

	/**
	 * Loads the upload library of CI
	 */
	private function _loadUploadLibrary($allowed_types)
	{
		$config['upload_path']          = $this->UPLOAD_PATH;
		$config['allowed_types']        = implode('|', $allowed_types);
		$config['overwrite']            = true;
		$config['file_name']				= uniqid().'.pdf';

		$this->ci->load->library('upload', $config);
		$this->ci->upload->initialize($config);
	}
}
