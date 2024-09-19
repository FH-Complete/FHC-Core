<?php

/**
 * FH-Complete
 *
 * @package		FHC-Helper
 * @author		FHC-Team
 * @copyright		Copyright (c) 2022 fhcomplete.net
 * @license		GPLv3
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \stdClass as stdClass;

class DmsLib
{
	const FILE_CONTENT_PROPERTY = 'file_content'; // property name for file content

	private $_ci; // code igniter instance
	private $_who; // who added this document

	/**
	 * Object initialization
	 */
	public function __construct($params = null)
	{
		$this->_ci =& get_instance();

		// Set the the _who property
		$this->_who = 'DMS system'; // default
		// It is possible to set it using the who parameter
		if (!isEmptyArray($params) && isset($params['who']) && !isEmptyString($params['who'])) $this->_who = $params['who'];

		$this->_ci->load->model('crm/Akte_model', 'AkteModel'); // deprecated, should not be used here!
		$this->_ci->load->model('content/Dms_model', 'DmsModel');
		$this->_ci->load->model('content/DmsVersion_model', 'DmsVersionModel');
		$this->_ci->load->model('content/DmsFS_model', 'DmsFSModel');
	}

	// -----------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Writes a new file, adds a new dms entry and a new dms version 0 for the written file
	 * Returns success info of added dms entry (dms_id, version, filename) or error
	 */
	public function add(
		$name, $mimetype, $fileHandle, // Required parameters
		$kategorie_kurzbz = null, $dokument_kurzbz = null, $beschreibung = null, $cis_suche = false, $schlagworte = null
	)
	{
			// create unique filename, using original document name to detect file extension
			$filename = $this->_getUniqueFilename($name);

			// copy file from fileHandle to dms folder
			$copyFileResult = $this->_copyFile($fileHandle, $filename);

			if (isError($copyFileResult)) return $copyFileResult;

			// if file written successful, insert dms
			$dmsResult = $this->_ci->DmsModel->insert(
				array(
					'kategorie_kurzbz' => $kategorie_kurzbz,
					'dokument_kurzbz' => $dokument_kurzbz
				)
			);

			if (isError($dmsResult)) return $dmsResult;

			if (hasData($dmsResult))
			{
				$dms_id = getData($dmsResult);
				$version = 0;

				// insert dms version
				$dmsVersion = array(
					'dms_id' => $dms_id,
					'version' => $version,
					'filename' => $filename,
					'mimetype' => $mimetype,
					'name' => $name,
					'beschreibung' => $beschreibung,
					'cis_suche' => $cis_suche,
					'schlagworte' => $schlagworte,
					'insertvon' => $this->_who,
					'insertamum' => date('Y-m-d H:i:s')
				);

				$dmsVersionResult = $this->_ci->DmsVersionModel->insert($dmsVersion);

				if (isError($dmsVersionResult)) return $dmsVersionResult;

				// return dms info
				$resObj = new stdClass();
				$resObj->dms_id = $dms_id;
				$resObj->version = $version;
				$resObj->filename = $filename;

				return success($resObj);
			}
			else
				return error("error when inserting DMS");
	}

	/**
	 * Writes a new file with content of fileHandle, adds a new dms version (max version number + 1) for the written file
	 * Returns success with info of added dms version (version, filename) or error
	 */
	public function addNewVersion($dms_id, $fileHandle, $name = null, $mimetype = null, $beschreibung = null, $cis_suche = false, $schlagworte = null)
	{
		// get the latest version
		$lastVersionResult = $this->getLastVersion($dms_id);

		if (isError($lastVersionResult)) return $lastVersionResult;

		if (hasData($lastVersionResult))
		{
			$lastVersion = getData($lastVersionResult);

			$originalName = isset($name) ? $name : $lastVersion->name;

			// create unique filename, using original document name to detect file extension
			$filename = $this->_getUniqueFilename($originalName);

			// copy file from fileHandle to dms folder
			$copyFileResult = $this->_copyFile($fileHandle, $filename);

			if (isError($copyFileResult)) return $copyFileResult;

			// insert new version
			$newVersionNumber = $lastVersion->version + 1;

			// if new parameters given, use them, otherwise use parameters from last version
			$newVersion = array(
				'dms_id' => $dms_id,
				'name' => $originalName,
				'filename' => $filename,
				'version' => $newVersionNumber,
				'mimetype' => isset($mimetype) ? $mimetype : $lastVersion->mimetype,
				'beschreibung' => isset($beschreibung) ? $beschreibung : $lastVersion->beschreibung,
				'cis_suche' => isset($cis_suche) ? $cis_suche : $lastVersion->cis_suche,
				'schlagworte' => isset($schlagworte) ? $schlagworte : $lastVersion->schlagworte,
				'insertvon' => $this->_who,
				'insertamum' => date('Y-m-d H:i:s')
			);

			$addVersionResult = $this->_ci->DmsVersionModel->insert($newVersion);

			if (isError($addVersionResult)) return $addVersionResult;

			// return dms info
			$resObj = new stdClass();
			$resObj->version = $newVersionNumber;
			$resObj->filename = $filename;

			return success($resObj);
		}
		else
			return error("last version not found");
	}

	/**
	 * Updates the last version (max version number) of a dms entry
	 * Overwrites the file associated with this version with content read from fileHandle
	 * Returns success with info of added dms version (version, filename) or error
	 */
	public function updateLastVersion($dms_id, $fileHandle, $name = null, $mimetype = null, $beschreibung = null, $cis_suche = false, $schlagworte = null)
	{
		// get the latest version
		$lastVersionResult = $this->getLastVersion($dms_id);

		if (isError($lastVersionResult)) return $lastVersionResult;

		if (hasData($lastVersionResult))
		{
			$lastVersion = getData($lastVersionResult);
			$filename = $lastVersion->filename;

			// update file in filesystem
			$copyFileResult = $this->_copyFile($fileHandle, $filename);

			if (isError($copyFileResult)) return $copyFileResult;

			// if new parameters given, use them, otherwise use parameters from last version
			$newVersion = array(
				'name' => isset($name) ? $name : $lastVersion->name,
				'filename' => $filename,
				'mimetype' => isset($mimetype) ? $mimetype : $lastVersion->mimetype,
				'beschreibung' => isset($beschreibung) ? $beschreibung : $lastVersion->beschreibung,
				'cis_suche' => isset($cis_suche) ? $cis_suche : $lastVersion->cis_suche,
				'schlagworte' => isset($schlagworte) ? $schlagworte : $lastVersion->schlagworte,
			);

			// update last dms version
			$addVersionResult = $this->_ci->DmsVersionModel->update(
				array($dms_id, $lastVersion->version),
				$newVersion
			);

			if (isError($addVersionResult)) return $addVersionResult;

			// return dms info
			$resObj = new stdClass();
			$resObj->version = $lastVersion->version;
			$resObj->filename = $filename;

			return success($resObj);
		}
		else
			return error("last version not found");
	}

	/**
	 * Gets dms version with highest number
	 * Returns success with dms data and fileHandle with file content or error
	 */
	public function getLastVersion($dms_id)
	{
		// get the latest version number
		$this->_ci->DmsVersionModel->addSelect('version');
		$this->_ci->DmsVersionModel->addOrder('version', 'DESC');
		$this->_ci->DmsVersionModel->addOrder('insertamum', 'DESC');
		$this->_ci->DmsVersionModel->addLimit(1);
		$lastDmsVersionResult = $this->_ci->DmsVersionModel->loadWhere(
			array('dms_id' => $dms_id)
		);

		if (isError($lastDmsVersionResult)) return $lastDmsVersionResult;

		if (hasData($lastDmsVersionResult))
		{
			$lastDmsVersionData = getData($lastDmsVersionResult)[0];
			$lastDmsVersion = $lastDmsVersionData->version;

			// call get Version with last version number
			return $this->getVersion($dms_id, $lastDmsVersion);
		}
		else
			return error("Dms last version not found");
	}

	/**
	 * Gets specified dms version
	 * Returns success with dms data and fileHandle with file content or error
	 */
	public function getVersion($dms_id, $version)
	{
		$this->_ci->DmsVersionModel->addSelect('dms_id, version, filename, mimetype, name, beschreibung, cis_suche, schlagworte');
		$dmsVersionResult = $this->_ci->DmsVersionModel->loadWhere(
			array(
				'dms_id' => $dms_id,
				'version' => $version
			)
		);

		if (isError($dmsVersionResult)) return $dmsVersionResult;

		if (hasData($dmsVersionResult))
		{
			$dmsVersion = getData($dmsVersionResult)[0];

			// get file content as file pointer
			$fileHandleResult = $this->_ci->DmsFSModel->openRead($dmsVersion->filename);

			if (isError($fileHandleResult)) return $fileHandleResult;

			if (hasData($fileHandleResult))
			{
				$fileHandle = getData($fileHandleResult);
				$dmsVersion->{self::FILE_CONTENT_PROPERTY} = $fileHandle;

				// close file pointer
				$closeResult = $this->_ci->DmsFSModel->close($fileHandle);

				if (isError($closeResult)) return $closeResult;

				return success($dmsVersion);
			}
			else
				return error("File could not be opened");
		}
		else
			return error("Dms version not found");
	}

	/**
	 * Removes dms entry and all its versions, deletes all associated files
	 * Returns success with removed version numbers or error
	 */
	public function removeAll($dms_id)
	{
		$versionsRemoved = array();

		$this->_ci->DmsVersionModel->addSelect('version, filename');
		$allVersionsResult = $this->_ci->DmsVersionModel->loadWhere(array('dms_id' => $dms_id));

		if (hasData($allVersionsResult))
		{
			$allVersionsData = getData($allVersionsResult);

			$error = null;

			// Start DB transaction to avoid deleting only part of the data
			$this->_ci->db->trans_begin();

			// remove all versions of the dms Id
			foreach ($allVersionsData as $version)
			{
				$removeVersionResult = $this->removeVersion($dms_id, $version->version);

				if (isError($removeVersionResult))
				{
					$error = $removeVersionResult;
					break;
				}
				else
					$versionsRemoved[] = $version; // return removed versions
			}

			// Transaction complete!
			$this->_ci->db->trans_complete();

			// Check if everything went ok during the transaction
			if ($this->_ci->db->trans_status() === false || isset($error))
			{
				$this->_ci->db->trans_rollback();

				if (isset($error))
					return $error;
				else
					return error("Error occured when deleting, rolled back");
			}
			else
			{
				$this->_ci->db->trans_commit();
			}
		}

		return success($versionsRemoved);
	}

	/**
	 * Removes latest version and its associated file
	 * Returns success with removed dms version data (dms_id, version, filename) or error
	 */
	public function removeLastVersion($dms_id)
	{
		$lastVersionNumber = 0;
		// get the latest version
		$lastVersionResult = $this->getLastVersion($dms_id);

		if (isError($lastVersionResult)) return $lastVersionResult;

		if (hasData($lastVersionResult))
		{
			$lastVersion = getData($lastVersionResult);
			$lastVersionNumber = $lastVersion->version;
		}

		// call remove method for latest version
		return $this->removeVersion($dms_id, $lastVersionNumber);
	}

	/**
	 * Removes latest version and its associated file
	 * Returns success with removed dms version data (dms_id, version, filename) or error
	 */
	public function removeVersion($dms_id, $version)
	{
		$removeVersionResultObj = new stdClass();
		$removeVersionResultObj->dms_id = null;
		$removeVersionResultObj->version = null;
		$removeVersionResultObj->filename = null;

		// load dms version and check how many versions there are
		$db = new DB_Model();

		$checkDeleteResult = $db->execReadOnlyQuery(
			"SELECT filename,
					(SELECT count(version)
						FROM campus.tbl_dms_version dv_anzahl
						WHERE dv_anzahl.dms_id = dv.dms_id) AS anzahl_versionen
					FROM campus.tbl_dms_version dv
					WHERE dms_id=?
					AND version=?",
			array($dms_id, $version)
		);

		if (isError($checkDeleteResult)) return $checkDeleteResult;

		if (hasData($checkDeleteResult))
		{
			$checkDeleteData = getData($checkDeleteResult)[0];

			// delete version
			$deleteVersionResult = $this->_ci->DmsVersionModel->delete(array($dms_id, $version));

			if (isError($deleteVersionResult)) return $deleteVersionResult;

			$removeVersionResultObj->version = $version;
			$removeVersionResultObj->filename = $checkDeleteData->filename;

			// delete dms too if no versions left
			if ($checkDeleteData->anzahl_versionen <= 1)
			{
				$deleteDmsResult = $this->_ci->DmsModel->delete($dms_id);

				if (isError($deleteDmsResult)) return $deleteDmsResult;

				$removeVersionResultObj->dms_id = $dms_id;
			}

			// delete file from file system
			$removeResult = $this->_ci->DmsFSModel->remove($checkDeleteData->filename);

			if (isError($removeResult)) return $removeResult;
		}

		return success($removeVersionResultObj);
	}

	// -----------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Copies file from sourceFileHandle to destinationFilename in DMS folder
	 * Returns success or error on fail
	 */
	private function _copyFile($sourceFileHandle, $destinationFilename)
	{
		// get file location from file handle
		$metaData = stream_get_meta_data($sourceFileHandle);

		if (isset($metaData['uri']) && !isEmptyString($metaData['uri']))
		{
			// if file location determined, copy file
			$source = $metaData['uri'];

			if (copy($source, DMS_PATH.$destinationFilename))
			{
				return success();
			}
			else
			{
				// error if copy returned false
				return error('error occured while copying file');
			}
		}
		else
		{
			// error when source location could not be determined
			return error('error occured while getting source file name');
		}

		return success($resObj);
	}

	/**
	 * Generates unique filename, appends file extension from document name
	 * Returns the filename string
	 */
	private function _getUniqueFilename($dokname)
	{
		// create unique id
		$uniqueFilename = uniqid();

		// getting extension of file from document name
		$fileExtension = pathinfo($dokname, PATHINFO_EXTENSION);

		// if file extension found, append it
		if (!isEmptyString($fileExtension))
			$uniqueFilename .= '.'.$fileExtension;

		return $uniqueFilename;
	}

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

		// Insert to DMS table
		$insDmsResult = $this->_ci->DmsModel->insert($this->_ci->DmsModel->filterFields($dms));
		if (isError($insDmsResult)) return $insDmsResult;

		$upload_data['dms_id'] = getData($insDmsResult);

		// Insert DMS version
		$insVersionResult = $this->_ci->DmsVersionModel->insert(
			$this->_ci->DmsVersionModel->filterFields(
				$dms,
				$upload_data['dms_id'],
				$upload_data['file_name']
			)
		);
		if (isError($insVersionResult)) return $insVersionResult;

		// Return result of uploaded data
		return success($upload_data);
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
			$fileObj = new stdClass();
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
		$config = array();
		$config['upload_path'] = DMS_PATH;
		$config['allowed_types'] = implode('|', $allowed_types);
		$config['overwrite'] = true;
		$config['file_name'] = uniqid().'.pdf';

		$this->_ci->load->library('upload', $config);
		$this->_ci->upload->initialize($config);
	}
}
