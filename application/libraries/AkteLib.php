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
	public function get($akte_id)
	{
		// get Akte data
		$this->_ci->AkteModel->addSelect('person_id, dokument_kurzbz, mimetype, erstelltam, titel, bezeichnung,
											gedruckt, uid, dms_id, nachgereicht, nachgereicht_am, anmerkung,
											ausstellungsnation, formal_geprueft_amum, archiv, signiert,
											stud_selfservice, akzeptiertamum, insertvon, insertamum, updatevon, updateamum');
		$this->_ci->AkteModel->load($akte_id);
		$akteResult = $this->_ci->AkteModel->load($akte_id);

		if (isError($akteResult)) return $akteResult;

		if (!hasData($akteResult)) return error("Akte not found");

		$resultObject = getData($akteResult)[0];

		// set properties with same name in Akte and Dms table
		$resultObject->akte_mimetype = $resultObject->mimetype;

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
				$resultObject->{$dmsProperty} = $dmsData->{$dmsProperty};
			}
		}
		else
		{
			// set null if no dms result found
			foreach ($dmsProperties as $dmsProperty)
			{
				$resultObject->{$dmsProperty} = null;
			}
		}

		// return the object containing akte and dms data
		return success($resultObject);
	}

	/**
	 * Gets Akte data and associated dms data by person Id and dokument_kurzbz
	 * Returns success with result array with akte and dms data or error
	 */
	public function getByPersonIdAndDocumentType($person_id, $dokument_kurzbz)
	{
		// load all Akte entries for given person and dokument_kurzbz
		$this->_ci->AkteModel->addSelect('akte_id');
		$akteResult = $this->_ci->AkteModel->loadWhere(
			array(
				'person_id' => $person_id,
				'dokument_kurzbz' => $dokument_kurzbz
			)
		);

		if (!hasData($akteResult)) return error("Akte not found");

		$akteData = getData($akteResult);

		$resultArr = array();

		// for each found akte entry
		foreach ($akteData as $akte)
		{
			// get dms and akte data from akte Id
			$getAkteDmsResult = $this->get($akte->akte_id);

			if (isError($getAkteDmsResult)) return $getAkteDmsResult;

			$resultArr[] = getData($getAkteDmsResult);
		}

		// return all found entries
		return success($resultArr);
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
}
