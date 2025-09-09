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

	private $_UPLOAD_PATH; // temporary directory to store the uploaded file

	private $_ci; // code igniter instance
	private $_who; // who added this document

	/**
	 * Object initialization
	 */
	public function __construct($params = null)
	{
		$this->_ci =& get_instance();

		$this->_UPLOAD_PATH = APPPATH.'tmp/';

		// Set the the _who property
		$this->_who = 'DMS system'; // default
		// It is possible to set it using the who parameter
		if (!isEmptyArray($params) && isset($params['who']) && !isEmptyString($params['who'])) $this->_who = $params['who'];

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
				return success();
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
			return success();
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
			return success();
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
			return success();
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

		if (hasData($dmsVersionResult)) return getData($dmsVersionResult)[0];

		return success();
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

	/**
	 * Perform an upload of a file spcifically for the DMS and returns the uploaded file info
	 */
	public function upload($allowed_types = array('*'))
	{
		// Loads the CI upload library
		$this->_loadUploadLibrary($allowed_types);

		// If the upload was a success then return the uploaded file info
		if ($this->_ci->upload->do_upload(DmsLib::FILE_CONTENT_PROPERTY)) return success($this->_ci->upload->data());

		// If an error occurred then return it
		return error($this->_ci->upload->display_errors('', ''));
	}

	/**
	 * Get info from the DMS to be provided to the FHC_Controller->outputFile
	 */
	public function getOutputFileInfo($dms_id, $file_name = '', $disposition = 'attachment')
	{
		// Loads the last DMS version from database
		$lastVersionResult = $this->getLastVersion($dms_id);

		// If an error occurred then return it
		if (isError($lastVersionResult)) return $lastVersionResult;

		// If has been found
		if (hasData($lastVersionResult))
		{
			$lastVersion = getData(lastVersionResult)[0];

			$fileObj = new stdClass();
			$fileObj->filename = $lastVersion->filename;
			$fileObj->file = DMS_PATH.$lastVersion->filename;
			$fileObj->name = isEmptyString($file_name) ? DMS_PATH.$lastVersion->name : $file_name;
			$fileObj->mimetype = $lastVersion->mimetype;
			$fileObj->disposition = $disposition;

			return success($fileObj);
		}

		return success();
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

	/**
	 * Loads the upload library of CI
	 */
	private function _loadUploadLibrary($allowed_types)
	{
		$this->_ci->load->library(
			'upload',
			array(
				'upload_path' => $this->_UPLOAD_PATH,
				'allowed_types' => $allowed_types,
				'overwrite' => true
			)
		);
	}
}

