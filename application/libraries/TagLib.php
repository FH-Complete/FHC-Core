<?php

/**
 * FH-Complete
 *
 * @package             FHC-Helper
 * @author              FHC-Team
 * @copyright           Copyright (c) 2026 fhcomplete.net
 * @license             GPLv3
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;
use \stdClass as stdClass;

class TagLib
{
	const BATCHUSER = 'sftest';
	const TYP_ZUORDNUNG = 'prestudent_id';
	const SEMESTER = 'WS2025';

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		// Configs
		$this->_ci->load->config('stv');

		// Models
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->_ci->load->model('person/Person_model', 'PersonModel');
		$this->_ci->load->model('person/Notiz_model', 'NotizModel');
		$this->_ci->load->model('system/Notiztyp_model', 'NotiztypModel');
		$this->_ci->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		$this->_ci->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		// Tag-Helper


		// Libraries
		$this->_ci->load->library('PermissionLib');
		$this->_ci->load->library('PrestudentLib');


	}

	public function updateAutomatedTags($tag, $prestudentIds)
	{
		$prestudentIds = array_unique($prestudentIds);

		$toRecycle = [];
		$toAdd = [];
		$toDelete = [];
		$allTags = [];

		$this->_ci->NotizModel->addSelect('nz.notiz_id');
		$this->_ci->NotizModel->addSelect('prestudent_id');
		$this->_ci->NotizModel->addJoin('public.tbl_notizzuordnung nz', 'notiz_id');
		$resultAllTags = $this->_ci->NotizModel->loadWhere([
			'typ' => $tag
		]);

		$allTagsData = getData($resultAllTags);

		if (!empty($allTagsData)) {
			foreach ($allTagsData as $row) {
				$allTags[$row->prestudent_id] = $row->notiz_id;
			}
		}


		foreach ($prestudentIds as $value)
		{
			if (isset($allTags[$value])) {
				// if already existing: recyceln: nothing to do
				$toRecycle[$value] = $allTags[$value] ;
			} else {
				$toAdd[] = $value;
			}
		}

		foreach ($allTags as $prestudent_id => $notiz_id) {
			if (!in_array($prestudent_id, $prestudentIds)) {
				$toDelete[$prestudent_id] = $notiz_id;
			}
		}

		//RECYCLE: Just insert new update Date
		$countRecyled = 0;
		$retagged = [];
		foreach ($toRecycle as $value)
		{
			//TODO(Manu) refactor for recycle, add
			$resultUpdateNotiz = $this->_ci->NotizModel->update(
				[
					'notiz_id' => $value
				],
				array(
				'updateamum' => date('Y-m-d H:i:s'),
				'updatevon' => 'BatchJobTagUpdate',
			));

			if (isError($resultUpdateNotiz))
				return error ('Error occurred update Result ' . $value);

			$countRecyled++;
			$retagged[] = $resultUpdateNotiz->retval;
		}

		//ADD: Create new Tags
		$countAdded = 0;
		$tagged = [];
		foreach ($toAdd as $value)
		{
			//TODO(Manu) refactor for recycle, add
			$resultInsertNotiz = $this->_ci->NotizModel->insert(array(
				'titel' => 'TAG',
				'text' => 'AUTOMATED TAG',
				'verfasser_uid' => self::BATCHUSER, //has to be an uid
				'erledigt' => false,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => 'BatchJobTagAdd',
				'typ' => $tag
			));

			if (isError($resultInsertNotiz))
				return error ('Error occurred insert Result ' . $value);

			$resultInsertZuordnung = $this->_ci->NotizzuordnungModel->insert(array(
				'notiz_id' => $resultInsertNotiz->retval,
				self::TYP_ZUORDNUNG => $value
			));

			if (isError($resultInsertZuordnung))
				return error ('Error occurred insert Zuordnung' . $value);

			$countAdded++;
			$tagged[$resultInsertNotiz->retval] = $value;
		}

		//DELETE OLD Tags, no more valid
		$countDeleted = 0;
		$deleted = [];
		foreach ($toDelete as $value)
		{
			$result = $this->_ci->NotizzuordnungModel->delete([
				'notiz_id' => $value
			]);
			if (isError($result))
				return error ('Error occurred delete Notizzuordnung' . $notiz_id);

			$result = $this->_ci->NotizModel->delete([
				'notiz_id' => $notiz_id
			]);
			if (isError($result))
				return error ('Error occurred delete Notiz' . $notiz_id);

			$countDeleted++;
			$deleted[] = $value;
		}

		return success([$allTags, $toAdd, $toRecycle, $toDelete, $countRecyled, $retagged, $countAdded, $tagged, $countDeleted, $deleted]);

	}

	public function updateAutomatedTagsForPrestudent($tag, $prestudent_id)
	{
		$return = null;
		$notiz_id = null;

		$this->_ci->NotizModel->addSelect('nz.notiz_id');
		$this->_ci->NotizModel->addSelect('prestudent_id');
		$this->_ci->NotizModel->addJoin('public.tbl_notizzuordnung nz', 'notiz_id');
		$resultAllTags = $this->_ci->NotizModel->loadWhere([
			'typ' => $tag,
			'prestudent_id' => $prestudent_id
		]);
		if(hasData($resultAllTags))
		{
			$notiz_id = $resultAllTags->retval[0]->notiz_id;
		}

		//RECYCLE
		if ($notiz_id !== null)
		{
			//TODO(Manu) refactor for recycle, add
			$resultUpdateNotiz = $this->_ci->NotizModel->update(
				[
					'notiz_id' => $notiz_id
				],
				array(
					'updateamum' => date('Y-m-d H:i:s'),
					'updatevon' => 'Manually',
				));

			if (isError($resultUpdateNotiz))
				return error ('Error occurred update Result ' . $notiz_id);

			$return = ['recycled' => $notiz_id];
		}
		else
		{
			//TODO(Manu) refactor for recycle, add
			$resultInsertNotiz = $this->_ci->NotizModel->insert(array(
				'titel' => 'TAG',
				'text' => 'AUTOMATED TAG',
				'verfasser_uid' => self::BATCHUSER, //has to be an uid //TODO (auth UID)
				'erledigt' => false,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => 'Manually',
				'typ' => $tag
			));

			if (isError($resultInsertNotiz))
				return error ('Error occurred insert Result ' . $prestudent_id);

			$resultInsertZuordnung = $this->_ci->NotizzuordnungModel->insert(array(
				'notiz_id' => $resultInsertNotiz->retval,
				self::TYP_ZUORDNUNG => $prestudent_id
			));

			if (isError($resultInsertZuordnung))
				return error ('Error occurred insert Zuordnung' . $prestudent_id);

			$return = ['added' => $resultInsertNotiz->retval];
		}

		return success($return);

	}

/*
 * main function for rebuild Tags for single prestudent
 * manually triggered
 * */
	public function rebuildTagsForPrestudent($prestudent_id)
	{
		$automaticTags = $this->_ci->config->item('stv_automatic_tags');
		$return = [];

		foreach ($automaticTags as $tag)
		{
			if($this->isCriteriaSetFor($tag, $prestudent_id))
			{
				$result = $this->updateAutomatedTagsForPrestudent($tag, $prestudent_id);
				if (isError($result))
					return error ('Error occurred during updateAutomatedTags' . $tag);

				else
					$return[$tag] = $result;
			}
			else {
				$result = $this->checkForDelete($tag, $prestudent_id);
				if($result != null)
					$return[$tag] = $result;
			}
		}


		return success($return);

	}

	public function checkForDelete($tag, $prestudent_id)
	{
		$return = null;
		$notiz_id = null;

		if (!is_numeric($prestudent_id))
			return error ("prestudent_id " . $prestudent_id . "not numeric");

		$this->_ci->NotizModel->addSelect('nz.notiz_id');
		$this->_ci->NotizModel->addSelect('prestudent_id');
		$this->_ci->NotizModel->addJoin('public.tbl_notizzuordnung nz', 'notiz_id');
		$resultAllTags = $this->_ci->NotizModel->loadWhere([
			'typ' => $tag,
			'prestudent_id' => $prestudent_id
		]);
		if(hasData($resultAllTags))
		{
			$notiz_id = $resultAllTags->retval[0]->notiz_id;
		}

		if($notiz_id)
		{
			$result = $this->_ci->NotizzuordnungModel->delete([
				'notiz_id' => $notiz_id
			]);
			if (isError($result))
				return error ('Error occurred during delete Notizzuordnung' . $notiz_id);

			$result = $this->_ci->NotizModel->delete([
				'notiz_id' => $notiz_id
			]);
			if (isError($result))
				return error ('Error occurred during  delete Notiz' . $notiz_id);

			$return = ['deleted' => $notiz_id];
		}
		return $return;
	}

	public function isCriteriaSetFor($tag, $prestudent_id)
	{
		//TODO(finish list)
		if($tag == 'wh_auto')
		{
			$result = $this->_ci->PrestudentstatusModel->loadWhere(array(
				'statusgrund_id' => 16,
				'studiensemester_kurzbz' => self::SEMESTER,
				'prestudent_id' => $prestudent_id
			));
			if(hasData($result))
			{
				return true;
			}
			else
				return false;
		}

		if($tag == 'prewh_auto')
		{
			$result = $this->_ci->PrestudentstatusModel->loadWhere(array(
				'statusgrund_id' => 15,
				'studiensemester_kurzbz' => self::SEMESTER,
				'prestudent_id' => $prestudent_id
			));
			if(hasData($result))
			{
				return true;
			}
			else
				return false;
		}

		return false;

	}
}
