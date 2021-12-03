<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class DmsLib
{
	const FILE_CONTENT_PROPERTY = 'file_content';

	private $_ci; // code igniter instance

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		$this->_ci->load->model('crm/Akte_model', 'AkteModel'); // deprecated, should not be used here!
		$this->_ci->load->model('content/Dms_model', 'DmsModel');
		$this->_ci->load->model('content/DmsVersion_model', 'DmsVersionModel');
		$this->_ci->load->model('content/DmsFS_model', 'DmsFSModel');
	}

	// -----------------------------------------------------------------------------------------------------------
	// Public methods


	// -----------------------------------------------------------------------------------------------------------
	// Private methods


	// -----------------------------------------------------------------------------------------------------------
	// Deprecated methods, not to be used
	
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
			$this->_ci->DmsModel->addJoin('campus.tbl_dms_version', 'dms_id');
			$this->_ci->DmsModel->addOrder('version', 'DESC');
			$this->_ci->DmsModel->addLimit(1);
			
			if (!is_numeric($version))
			{
				return $this->_ci->DmsModel->load($dms_id);
			}
			else
			{
				return $this->_ci->DmsModel->loadWhere(array('dms_id' => $dms_id, 'version' => $version));
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
		$result = error('Wrong dms_id parameter');

		if (isset($dms_id))
		{
			$this->_ci->DmsModel->addJoin('campus.tbl_dms_version', 'dms_id');
			$this->_ci->DmsModel->addOrder('version', 'DESC');
			$this->_ci->DmsModel->addLimit(1);

			if (!isset($version))
			{
				$result = $this->_ci->DmsModel->load($dms_id);
			}
			else
			{
				$result = $this->_ci->DmsModel->loadWhere(array('dms_id' => $dms_id, 'version' => $version));
			}

			// If a dms has been found
			if (hasData($result))
			{
				$resultFS = $this->_ci->DmsFSModel->readBase64(getData($result)[0]->filename);
				if (isError($resultFS)) return $resultFS; // if an error occurred return it

				$result->retval[0]->{self::FILE_CONTENT_PROPERTY} = getData($resultFS);
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
		$result = $this->_ci->AkteModel->getAktenAcceptedDms($person_id, $dokument_kurzbz);

		if (hasData($result) && $no_file == null)
		{
			for ($i = 0; $i < count(getData($result)); $i++)
			{
				$resultFS = $this->_ci->DmsFSModel->readBase64(getData($result)[$i]->filename);
				if (isError($resultFS)) return $resultFS; // if an error occurred return it

				$result->retval[$i]->{self::FILE_CONTENT_PROPERTY} = getData($resultFS);
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

		if (!$this->_ci->upload->do_upload($field_name))
		{
			return error($this->_ci->upload->display_errors());
		}

		$upload_data = $this->_ci->upload->data();  // data about the uploaded file
		$filename = $upload_data['file_name'];

		// Insert to DMS table
		if (!$result = $this->_ci->DmsModel->insert($this->_ci->DmsModel->filterFields($dms)))
		{
			return error('Failed inserting to DMS');
		}
		$upload_data['dms_id'] = getData($result);

		// Insert DMS version
		if (!$result = $this->_ci->DmsVersionModel->insert(
			$this->_ci->DmsVersionModel->filterFields($dms, getData($result), $filename)))
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
		// Retrieves info about the given dms
		$fileInfoResult = $this->getFileInfo($dms_id);
		if (isError($fileInfoResult)) return error(getError($fileInfoResult));

		// If data have been found
		if (hasData($fileInfoResult))
		{
			$fileObj = getData($fileInfoResult);
		
			// Change filename, if filename is provided
			if (!isEmptyString($filename)) $fileObj->name = $filename;

			// Add file disposition if disposition has a valid value
			if ($disposition == 'attachment' || $disposition == 'inline')
			{
				$fileObj->disposition = $disposition;
			}

			return success($fileObj);
		}

		// If no data have been found then return an empty success
		return success();
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
		// Checks the dms_id parameter
		if (!is_numeric($dms_id)) return error('Wrong parameter');
		
		// Load DMS from database
		$result = $this->load($dms_id, $version);
		if (isError($result)) return error(getError($result));

		// If data have been found
		if (hasData($result))
		{
			// Store file information in fileObj
			$fileObj = new StdClass();
			$fileObj->filename = getData($result)[0]->filename;
			$fileObj->file = DMS_PATH.getData($result)[0]->filename;
			$fileObj->name = DMS_PATH.getData($result)[0]->name;   // original user filename
			$fileObj->mimetype = getData($result)[0]->mimetype;

			return success($fileObj);
		}

		// If no data have been found return an empty success
		return success();
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
				$filename = getData($result);
				if (isset($dms['dms_id']) && $dms['dms_id'] != '')
				{
					$result = $this->_ci->DmsVersionModel->insert(
						$this->_ci->DmsVersionModel->filterFields($dms, $dms['dms_id'], $filename)
					);
				}
				else
				{
					$result = $this->_ci->DmsModel->insert($this->_ci->DmsModel->filterFields($dms));
					if (isSuccess($result))
					{
						$result = $this->_ci->DmsVersionModel->insert(
							$this->_ci->DmsVersionModel->filterFields($dms, getData($result), $filename)
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
				$result = $this->_ci->DmsModel->update($dms['dms_id'], $this->_ci->DmsModel->filterFields($dms));
				if (isSuccess($result))
				{
					$result = $this->_ci->DmsVersionModel->update(
						array(
							$dms['dms_id'],
							$dms['version']
						),
						$this->_ci->DmsVersionModel->filterFields($dms)
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
			$this->_ci->db->trans_start(false);

			// Get akte_id from table tbl_akte
			$result = $this->_ci->AkteModel->loadWhere(array('person_id' => $person_id, 'dms_id' => $dms_id));
			if (isSuccess($result))
			{
				// Delete all entries in tbl_akte
				for ($i = 0; $i < count(getData($result)); $i++)
				{
					$this->_ci->AkteModel->delete(getData($result)[$i]->akte_id);
				}

				// Get all filenames related to this dms
				$resultFileNames = $this->_ci->DmsVersionModel->loadWhere(array('dms_id' => $dms_id));
				if (isSuccess($resultFileNames))
				{
					// Delete from tbl_dms_version
					$result = $this->_ci->DmsVersionModel->delete(array('dms_id' => $dms_id));
					if (isSuccess($result))
					{
						// Delete from tbl_dms
						$result = $this->_ci->DmsModel->delete($dms_id);
					}
				}
			}

			// Transaction complete!
			$this->_ci->db->trans_complete();

			// Check if everything went ok during the transaction
			if ($this->_ci->db->trans_status() === false || isError($result))
			{
				$this->_ci->db->trans_rollback();
				$result = error('An error occurred while performing a delete operation', EXIT_ERROR);
			}
			else
			{
				$this->_ci->db->trans_commit();
				$result = success('Dms successfully removed from DB');
			}

			// If everything is ok
			if (isSuccess($result))
			{
				// Remove all files related to this person and dms
				for ($i = 0; $i < count(getData($resultFileNames)); $i++)
				{
					$this->_ci->DmsFSModel->removeBase64(getData($resultFileNames)[$i]->filename);
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
		$akte = $this->_ci->AkteModel->load($akte_id);
		if (hasData($akte))
		{
			if (getData($akte)[0]->inhalt != '')
			{
				return success(base64_decode(getData($akte)[0]->inhalt));
			}
			elseif (getData($akte)[0]->dms_id != '')
			{
				$dmscontent = $this->read(getData($akte)[0]->dms_id);
				if (isSuccess($dmscontent))
				{
					return success(base64_decode(getData($dmscontent)[0]->file_content));
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

		$result = $this->_ci->DmsFSModel->writeBase64($filename, $dms['file_content']);
		if (isSuccess($result))
		{
			$result = success($filename);
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
				$result = $this->_ci->DmsFSModel->writeBase64(getData($result)[0]->filename, $dms['file_content']);
			}
		}

		return $result;
	}

	/**
	 * Loads the upload library of CI
	 */
	private function _loadUploadLibrary($allowed_types)
	{
		$config['upload_path']          = DMS_PATH;
		$config['allowed_types']        = implode('|', $allowed_types);
		$config['overwrite']            = true;
		$config['file_name']				= uniqid().'.pdf';

		$this->_ci->load->library('upload', $config);
		$this->_ci->upload->initialize($config);
	}
}

