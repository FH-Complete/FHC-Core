<?php

/**
 * FH-Complete
 *
 * @package             FHC-Helper
 * @author              FHC-Team
 * @copyright           Copyright (c) 2022 fhcomplete.net
 * @license             GPLv3
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

class AkteLib
{
	const AKTE_KATEGORIE_KURZBZ = 'Akte'; // kategorie_kurzbz of dms when inserting for akte

	private $_ci; // Code igniter instance
	private $_who; // who added this document

	/**
	 * Object initialization
	 */
	public function __construct($params = null)
	{
		$this->_ci =& get_instance();

		// Set the the _who property
		$this->_who = 'Akte system'; // default
		// It is possible to set it using the who parameter
		if (!isEmptyArray($params) && isset($params['who']) && !isEmptyString($params['who'])) $this->_who = $params['who'];

		$this->_ci->load->model('crm/Akte_model', 'AkteModel');
		$this->_ci->load->model('content/DmsFS_model', 'DmsFSModel');

		$this->_ci->load->library('DmsLib');
	}

	/**
	 * Writes a new file, adds a new dms entry with given akte data,
	 * adds a new dms version 0 for the written file, and adds Akte if dms add was successfull
	 * Returns success with inserted akte id or error
	 */
	public function add(
		$person_id, $dokument_kurzbz, $titel, $mimetype, $fileHandle, // Required parameters
		$bezeichnung = null, $archiv = false, $signiert = false, $stud_selfservice = false
	)
	{
		// add new dms entry and new dms version for the Akte, using Akte data (title, mimetype, file content as handle)
		$dmsAddResult = $this->_ci->dmslib->add($titel, $mimetype, $fileHandle, self::AKTE_KATEGORIE_KURZBZ, $dokument_kurzbz, $bezeichnung);

		if (isError($dmsAddResult)) return $dmsAddResult;

		if (!hasData($dmsAddResult)) return error("Dms document could not be added");

		$dmsAddData = getData($dmsAddResult);

		// insert the Akte
		return $this->_ci->AkteModel->insert(
			array(
				'person_id' => $person_id,
				'dokument_kurzbz' => $dokument_kurzbz,
				'titel' => $titel,
				'mimetype' => $mimetype,
				'bezeichnung' => $bezeichnung,
				'erstelltam' => date('Y-m-d'),
				'dms_id' => $dmsAddData->dms_id,
				'archiv' => $archiv,
				'signiert' => $signiert,
				'stud_selfservice' => $stud_selfservice,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => $this->_who
			)
		);
	}

	/**
	 * Writes a new file, adds a new dms version 0 for the written file, and updates Akte if dms version add was successfull
	 * Returns success with updated akte id or error
	 */
	public function update($akte_id, $titel, $mimetype, $fileHandle, $bezeichnung = null, $archiv = false, $signiert = false, $stud_selfservice = false)
	{
		// check if Akte with dms exists
		$this->_ci->AkteModel->addSelect('dms_id');
		$akteResult = $this->_ci->AkteModel->load($akte_id);

		if (isError($akteResult)) return $akteResult;

		if (!hasData($akteResult)) return error("Akte not found");

		$dms_id = getData($akteResult)[0]->dms_id;

		if (isEmptyString($dms_id)) return error("Akte has no dms document");

		// if Akte with dms found, update the last dms version
		$dmsUpdateResult = $this->_ci->dmslib->updateLastVersion($dms_id, $fileHandle, $titel, $mimetype, $bezeichnung);

		if (isError($dmsUpdateResult)) return $dmsUpdateResult;

		if (!hasData($dmsUpdateResult)) return error("Dms document could not be updated");

		// update the Akte
		return $this->_ci->AkteModel->update(
			$akte_id,
			array(
				'titel' => $titel,
				'mimetype' => $mimetype,
				'bezeichnung' => $bezeichnung,
				'erstelltam' => date('Y-m-d'),
				'dms_id' => $dms_id,
				'archiv' => $archiv,
				'signiert' => $signiert,
				'stud_selfservice' => $stud_selfservice,
				'updateamum' => date('Y-m-d H:i:s'),
				'updatevon' => $this->_who
			)
		);
	}

	/**
	 * Gets akte data and associated dms data by akte Id
	 * Returns success with akte and dms data or error
	 */
	public function getByAkteId($akte_id, $archiv = null, $signiert = null, $stud_selfservice = null)
	{
		return $this->_get(
			$akte_id,
			null, // person_id
			null, // dokument_kurzbz
			$archiv,
			$signiert,
			$stud_selfservice
		);
	}

	/**
	 * Gets Akte data and associated dms data by person Id
	 * Returns success with result array with akte and dms data or error
	 */
	public function getByPersonId($person_id, $archiv = null, $signiert = null, $stud_selfservice = null)
	{
		return $this->_get(
			null, // akte_id
			$person_id,
			null, // dokument_kurzbz
			$archiv,
			$signiert,
			$stud_selfservice
		);
	}

	/**
	 * Gets Akte data and associated dms data by person Id and dokument_kurzbz
	 * Returns success with result array with akte and dms data or error
	 */
	public function getByPersonIdAndDocumentType($person_id, $dokument_kurzbz)
	{
		return $this->_get(
			null, // akte_id
			$person_id,
			$dokument_kurzbz,
			$archiv,
			$signiert,
			$stud_selfservice
		);
	}

	/**
	 * Removes Akte by akte Id, removes all associated dms entries and versions, and deletes all associated files
	 * Returns success with removed version numbers or error
	 */
	public function remove($akte_id)
	{
		// get dms_id for akte
		$this->_ci->AkteModel->addSelect('dms_id');
		$akteResult = $this->_ci->AkteModel->load($akte_id);

		if (isError($akteResult)) return $akteResult;

		if (!hasData($akteResult)) return error("Akte not found");

		$dms_id = getData($akteResult)[0]->dms_id;
		$error = null;

		// Start DB transaction to avoid deleting only part of the data
		$this->_ci->db->trans_begin();

		// delete Akte
		$deleteResult = $this->_ci->AkteModel->delete($akte_id);

		if (isError($deleteResult))
		{
			$error = $deleteResult;
		}
		else
		{
			// remove all dms entry for dms of the akte
			$removeAllResult = $this->_ci->dmslib->removeAll($dms_id);

			if (isError($removeAllResult))
				$error = $removeAllResult;
		}

		// Transaction complete!
		$this->_ci->db->trans_complete();

		// Check if everything went ok during the transaction
		if ($this->_ci->db->trans_status() === false || isset($error))
		{
			$this->_ci->db->trans_rollback();

			// return occured error
			if (isset($error))
				return $error;
			else
				return error("Error occured when deleting, rolled back");
		}
		else
		{
			$this->_ci->db->trans_commit();

			// return removed dms entry data
			return $removeAllResult;
		}
	}

	/**
	 *
	 */
	private function _get($akte_id = null, $person_id = null, $dokument_kurzbz = null, $archiv = null, $signiert = null, $stud_selfservice = null)
	{
		// get Akte data
		$this->_ci->AkteModel->addSelect('akte_id');
		$this->_ci->AkteModel->addSelect('person_id');
		$this->_ci->AkteModel->addSelect('dokument_kurzbz');
		$this->_ci->AkteModel->addSelect('inhalt');
		$this->_ci->AkteModel->addSelect('CASE WHEN inhalt is not null THEN true ELSE false END as inhalt_vorhanden', false);
		$this->_ci->AkteModel->addSelect('mimetype');
		$this->_ci->AkteModel->addSelect('erstelltam');
		$this->_ci->AkteModel->addSelect('gedruckt');
		$this->_ci->AkteModel->addSelect('titel');
		$this->_ci->AkteModel->addSelect('bezeichnung');
		$this->_ci->AkteModel->addSelect('updateamum');
		$this->_ci->AkteModel->addSelect('updatevon');
		$this->_ci->AkteModel->addSelect('insertamum');
		$this->_ci->AkteModel->addSelect('insertvon');
		$this->_ci->AkteModel->addSelect('uid');
		$this->_ci->AkteModel->addSelect('dms_id');
		$this->_ci->AkteModel->addSelect('nachgereicht');
		$this->_ci->AkteModel->addSelect('anmerkung');
		$this->_ci->AkteModel->addSelect('titel_intern');
		$this->_ci->AkteModel->addSelect('anmerkung_intern');
		$this->_ci->AkteModel->addSelect('nachgereicht_am');
		$this->_ci->AkteModel->addSelect('ausstellungsnation');
		$this->_ci->AkteModel->addSelect('formal_geprueft_amum');
		$this->_ci->AkteModel->addSelect('archiv');
		$this->_ci->AkteModel->addSelect('signiert');
		$this->_ci->AkteModel->addSelect('stud_selfservice');
		$this->_ci->AkteModel->addSelect('akzeptiertamum');

		// Query parameters
		$paramArray = array();

		// Key to access the table
		if (is_int($akte_id) || is_array($akte_id)) $paramArray['akte_id'] = $akte_id;
		if (is_int($person_id) || is_array($person_id)) $paramArray['person_id'] = $person_id;
		if (!isEmptyString($dokument_kurzbz)) $paramArray['dokument_kurzbz'] = $dokument_kurzbz;

		// If parameters are provided then use them in the query
		if (is_bool($archiv)) $paramArray['archiv'] = $archiv;
		if (is_bool($signiert)) $paramArray['signiert'] = $signiert;
		if (is_bool($stud_selfservice)) $paramArray['stud_selfservice'] = $stud_selfservice;

		// Loads data from DB
		$akteResult = $this->_ci->AkteModel->loadWhere($paramArray);

		// If error or data not found then exit
		if (isError($akteResult)) return $akteResult;
		if (!hasData($akteResult)) return error('Akte not found');

		// For each record from the akte
		foreach (getData($akteResult) as $resultObject)
		{
			// get dms data
			$dmsResult = $this->_ci->dmslib->getLastVersion($resultObject->dms_id);

			if (isError($dmsResult)) return $dmsResult;

			// properties to retrieve from dms
			$dmsProperties = array('version', 'filename', 'mimetype', 'name', 'beschreibung', 'cis_suche', 'schlagworte', DmsLib::FILE_CONTENT_PROPERTY);

			// set dms properties
			if (hasData($dmsResult))
			{
				$dmsData = getData($dmsResult);

				foreach ($dmsProperties as $dmsProperty)
				{
					// If the property is _not_ 'mimetype' _or_
					// If the mimetype from the akte table is null then overwrite it with the one from the DMS
					if ($dmsProperty != 'mimetype' || ($dmsProperty == 'mimetype' && $resultObject->{$dmsProperty} == null))
					{
						$resultObject->{$dmsProperty} = $dmsData->{$dmsProperty};
					}
				}
			}
			else
			{
				// set null if no dms result found
				foreach ($dmsProperties as $dmsProperty)
				{
					if ($dmsProperty != 'mimetype') $resultObject->{$dmsProperty} = null;
				}
			}
		}

		// return the object containing akte and dms data
		return success(getData($akteResult));
	}
}

