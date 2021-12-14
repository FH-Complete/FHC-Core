<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class AkteLib
{
	const AKTE_KATEGORIE_KURZBZ = 'Akte';

	private $_ci; // Code igniter instance
	private $_uid;

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_uid = getAuthUID();

		$this->_ci->load->model('crm/Akte_model', 'AkteModel');
		$this->_ci->load->model('content/DmsFS_model', 'DmsFSModel');

		$this->_ci->load->library('DmsLib');
	}

	public function add($person_id, $dokument_kurzbz, $titel, $mimetype, $fileHandle, $bezeichnung = null, $uid = null)
	{
		$dmsAddResult = $this->_ci->dmslib->add($titel, self::AKTE_KATEGORIE_KURZBZ, $mimetype, $fileHandle, $dokument_kurzbz, $bezeichnung);

		if (isError($dmsAddResult)) return $dmsAddResult;

		if (!hasData($dmsAddResult))
			return error("Dms document could not be added");

		$dmsAddData = getData($dmsAddResult);

		return $this->_ci->AkteModel->insert(
			array(
				'person_id' => $person_id,
				'dokument_kurzbz' => $dokument_kurzbz,
				'titel' => $titel,
				'mimetype' => $mimetype,
				'bezeichnung' => $bezeichnung,
				'erstelltam' => date('Y-m-d'),
				'uid' => $uid,
				'dms_id' => $dmsAddData->dms_id,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => $this->_uid
			)
		);
	}

	public function update($akte_id, $titel, $mimetype, $fileHandle, $bezeichnung = null)
	{
		$this->_ci->AkteModel->addSelect('dms_id');
		$akteResult = $this->_ci->AkteModel->load($akte_id);

		if (isError($akteResult)) return $akteResult;

		if (!hasData($akteResult))
			return error("Akte not found");

		$dms_id = getData($akteResult)[0]->dms_id;

		if (isEmptyString($dms_id))
			return error("Akte has no dms document");

		$dmsUpdateResult = $this->_ci->dmslib->updateLastVersion($dms_id, $fileHandle, $titel, $mimetype, $bezeichnung);

		if (isError($dmsUpdateResult)) return $dmsUpdateResult;

		if (!hasData($dmsUpdateResult))
			return error("Dms document could not be updated");

		return $this->_ci->AkteModel->update(
			$akte_id,
			array(
				'titel' => $titel,
				'mimetype' => $mimetype,
				'bezeichnung' => $bezeichnung,
				'erstelltam' => date('Y-m-d'),
				'dms_id' => $dms_id,
				'updateamum' => date('Y-m-d H:i:s'),
				'updatevon' => $this->_uid
			)
		);
	}

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

		if (!hasData($akteResult))
			return error("Akte not found");

		$resultObject = getData($akteResult)[0];

		$resultObject->akte_mimetype = $resultObject->mimetype;

		// get dms data
		$dmsResult = $this->_ci->dmslib->getLastVersion($resultObject->dms_id);
		$dmsProperties = array('version', 'filename', 'mimetype', 'name', 'beschreibung', 'cis_suche', 'schlagworte', DmsLib::FILE_CONTENT_PROPERTY);

		if (isError($dmsResult)) return $dmsResult;

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
			foreach ($dmsProperties as $dmsProperty)
			{
				$resultObject->{$dmsProperty} = null;
			}
		}

		return success($resultObject);
	}

	public function getByPersonIdAndDocumentType($person_id, $dokument_kurzbz)
	{
		$this->_ci->AkteModel->addSelect('akte_id');
		$akteResult = $this->_ci->AkteModel->loadWhere(
			array(
				'person_id' => $person_id,
				'dokument_kurzbz' => $dokument_kurzbz
			)
		);

		if (!hasData($akteResult))
			return error("Akte not found");

		$akteData = getData($akteResult);

		$resultArr = array();

		foreach ($akteData as $akte)
		{
			$getAkteDmsResult = $this->get($akte->akte_id);

			if (isError($getAkteDmsResult))
				return $getAkteDmsResult;

			$resultArr[] = getData($getAkteDmsResult);
		}

		return success($resultArr);
	}

	public function remove($akte_id)
	{
		$this->_ci->AkteModel->addSelect('dms_id');
		$akteResult = $this->_ci->AkteModel->load($akte_id);

		if (isError($akteResult)) return $akteResult;

		if (!hasData($akteResult))
			return error("Akte not found");

		$dms_id = getData($akteResult)[0]->dms_id;
		$error = null;

		// Start DB transaction
		$this->_ci->db->trans_begin();

		// delete Akte
		$deleteResult = $this->_ci->AkteModel->delete($akte_id);

		if (isError($deleteResult))
		{
			$error = $deleteResult;
		}
		else
		{
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

			if (isset($error))
				return $error;
			else
				return error("Error occured when deleting, rolled back");
		}
		else
		{
			$this->_ci->db->trans_commit();
			return $removeAllResult;
		}
	}
}
