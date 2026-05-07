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

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		// Configs
		$this->_ci->load->config('stv');

		// Models
		$this->_ci->load->model('person/Notiz_model', 'NotizModel');
		$this->_ci->load->model('system/Notiztyp_model', 'NotiztypModel');
		$this->_ci->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		$this->_ci->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		// Libraries
		$this->_ci->load->library('PermissionLib');
		$this->_ci->load->library('PrestudentLib');
	}

	public function updateAutomatedTags($paramsTag)
	{
		// ---------------------------------
		// check params
		// ---------------------------------
		$required = ['kurzbz', 'data', 'typeId'];

		foreach ($required as $key) {
			if (!isset($paramsTag[$key])) {
				return error('Missing Parameter: ' . $key);
			}
		}

		$studiensemester_kurzbz = (isset ($paramsTag['studiensemester_kurzbz']) ? $paramsTag['studiensemester_kurzbz'] : null);
		$tag = $paramsTag['kurzbz'];
		$inputData = $paramsTag['data'];
		$typeId = $paramsTag['typeId'];

		// ---------------------------------
		// prepare input
		// ---------------------------------
		$zeitraum = [];
		$arrayIds = [];

		foreach ($inputData as $item) {
			$id = $item['id'];
			$arrayIds[] = $id;

			$zeitraum[$id] = [
				'von' => $item['von'] ?? null,
				'bis' => $item['bis'] ?? null
			];
		}
		$arrayIds = array_unique($arrayIds);

		$result = $this->_ci->StudiensemesterModel->load($studiensemester_kurzbz);
		if (isError($result)) {
			return $result;
		}
		$data = $result->retval[0] ?? null;

		$von = $data->start ?? null;
		$bis = $data->ende ?? null;

		// ---------------------------------
		// load existing tags
		// ---------------------------------
		$allTags = [];
		$resultAllTags = $this->_ci->NotizModel->getAllTags($tag, $von, $bis);
		if (isError($resultAllTags)) {
			return $resultAllTags;
		}
		$allTagsData = getData($resultAllTags);

		if (!empty($allTagsData)) {
			foreach ($allTagsData as $item) {
				$allTags[$item->$typeId] = $item->notiz_id;
			}
		}

		// ---------------------------------
		// map the data
		// ---------------------------------
		$toRecycle = [];
		$toAdd = [];
		$toDelete = [];

		foreach ($arrayIds as $id) {
			if (isset($allTags[$id])) {
				$toRecycle[$id] = $allTags[$id];
			} else {
				$toAdd[] = $id;
			}
		}

		foreach ($allTags as $id => $notizId) {
			if (!in_array($id, $arrayIds)) {
				$toDelete[$id] = $notizId;

			}
		}

		// ---------------------------------
		// recycle (update existing)
		// ---------------------------------
		$countRecycled = 0;
		$retagged = [];

		foreach ($toRecycle as $id => $notizId)
		{
			$this->_updateTag($notizId, $zeitraum[$id]['von'], $zeitraum[$id]['bis']);

			$countRecycled++;
			$retagged[] = $id;
			//$retagged[] = $notizId; //notiz_id
		}

		// ---------------------------------
		// ADD
		// ---------------------------------
		$countAdded = 0;
		$tagged = [];

		foreach ($toAdd as $id)
		{
			$result = $this->_insertTag($typeId, $id, $tag, $zeitraum[$id]['von'], $zeitraum[$id]['bis']);
			if (isError($result)) {
				return $result;
			}
			$countAdded++;
			//$tagged[] = $result->retval[0]->notiz_id; //notiz_id
			$tagged[] = $id;
		}

		// ---------------------------------
		// delete
		// ---------------------------------
		$countDeleted = 0;
		$deleted = [];

		foreach ($toDelete as $id => $notizId)
		{
			$result = $this->_deleteTag($notizId);

			if (isError($result)) {
				return $result;
			}

			$countDeleted++;
			//$deleted[] = $result->retval['deleted']; //notizId
			$deleted[] = $id;
		}

		// ---------------------------------
		// return
		// ---------------------------------
		return success([
			'input' => [
				'tag' => $tag,
				'arrayIds' => $arrayIds
			],
			'summary' => [
				'recycled' => $countRecycled,
				'added' => $countAdded,
				'deleted' => $countDeleted
			],
			'details' => [
				'existingTags' => $allTags,
				'toAdd' => $toAdd,
				'toRecycle' => $toRecycle,
				'toDelete' => $toDelete
			],
			'results' => [
				'retaggedIds' => $retagged,
				'newTags' => $tagged,
				'deletedTagsIds' => $deleted
			]
		]);
	}

	public function updateAutomatedTagsForTypeId(array $params)
	{
		$return = null;
		$notiz_id = null;

		$von = $params['von'];
		$bis = $params['bis'];
		$tag = $params['kurzbz'];
		$id = $params['id'];
		$typeId = $params['typeId'];

		$this->_ci->NotizModel->addSelect('nz.notiz_id');
		$this->_ci->NotizModel->addSelect($typeId);
		$this->_ci->NotizModel->addJoin('public.tbl_notizzuordnung nz', 'notiz_id');
		$resultAllTags = $this->_ci->NotizModel->loadWhere([
			'typ' => $tag,
			$typeId => $id
		]);
		if(hasData($resultAllTags))
		{
			$notiz_id = $resultAllTags->retval[0]->notiz_id;
		}

		//RECYCLE
		if ($notiz_id !== null)
		{
			$resultUpdateNotiz = $this->_updateTag($notiz_id, $von, $bis);
			$return = ['recycled' => $resultUpdateNotiz];
		}
		else
		//ADD
		{
			$resultInsertNotiz = $this->_insertTag($typeId, $id, $tag, $von, $bis);
			$return = ['added' => $resultInsertNotiz];
		}
		return success($return);
	}

	/*
	 * main function for rebuild Tags for typeId
	 * */
	public function rebuildTagsForTypeId($typeId, $id, $studiensemester_kurzbz)
	{
		$automatedTagsRes = $this->_ci->NotiztypModel->loadWhere(array('automatisiert' => true, 'taglib IS NOT NULL' => null));
		$automatedTags = hasData($automatedTagsRes) ? getData($automatedTagsRes) : [];

		$result = $this->_ci->StudiensemesterModel->load($studiensemester_kurzbz);
		if (isError($result))
			return error('Error occurred during retrieving studiensemester');
		if (empty($result->retval) || !isset($result->retval[0])) {
			return error('No studiensemester found');
		}

		$startSem = $result->retval[0]->start ?? null;
		$endeSem = $result->retval[0]->ende ?? null;
		$return = [];

		foreach ($automatedTags as $autoTag)
		{
			// getPath: must not be lost
			$filePath = APPPATH . 'libraries/' . $autoTag->taglib . '.php'; // APPPATH = application/

			if (file_exists($filePath)) {
				require_once($filePath);
			} else {
				echo "File not found: " . $filePath;
				continue;
			}

			$className = basename($autoTag->taglib);
			$kurz_bz = $autoTag->typ_kurzbz;

			$obj = new $className();

			$criteriaIsSet = $obj->isCriteriaSetFor([
				'typeId' => $typeId,
				'id' => $id,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			]);

			if (hasData($criteriaIsSet))
			{
				$von = isset($criteriaIsSet->retval[0]->von) ? $criteriaIsSet->retval[0]->von : '';
				$bis = isset($criteriaIsSet->retval[0]->bis) ? $criteriaIsSet->retval[0]->bis : '';

				$params = [
					'von' => $von,
					'bis' => $bis,
					'kurzbz' => $autoTag->typ_kurzbz,
					'typeId' => $typeId,
					'id' => $id,
				];

				$result = $this->updateAutomatedTagsForTypeId($params);
				if (isError($result))
					return error('Error occurred during updateAutomatedTags' . $kurz_bz);

				$return[$kurz_bz] = $result;
			}
			else
			{
				//CHECK FOR DELETE
				$params = [
						'von' => $startSem,
						'bis' => $endeSem,
						'kurzbz' => $autoTag->typ_kurzbz,
						'typeId' => $typeId,
						'id' => $id,
					];
				$result = $this->_ci->NotizModel->checkIfExistingTag($kurz_bz, $typeId, $id, $startSem, $endeSem);
				if (hasData($result))
				{
					$notizId = $result->retval[0]->notiz_id;
					$this->_deleteTag($notizId);
					$return[$kurz_bz] = ['deleted' => $notizId];
				}
			}
		}
		return success($return);
	}

	public function checkForDeleteDEPR($tag, $typeId, $id, $start, $ende)
	{
		//TODO Zeitbezug
		$return = null;
		$notiz_id = null;

		$_ci = get_instance();
		$_ci->addMeta(
			'in checkForDelete', $tag
		);

		if (!is_numeric($id))
			return error ("id " . $id . "not numeric");

		$result = $this->_ci->NotizModel->checkIfValidTag($tag, $typeId, $id, $start, $ende);
		if (isError($result)) {
			return error('Error checking valid tag for ' .  $typeId . ': ' . $id);
		}

		if(hasData($result))
		{
			$notiz_id = $result->retval[0]->notiz_id;
			$_ci = get_instance();
			$_ci->addMeta(
				'checkDeleteHas DAta', $notiz_id
			);
		}
		else
			return null;

		if($notiz_id)
		{
			$_ci->addMeta(
				'TO DELETE', $notiz_id
			);
			$result = $this->_deleteTag($notiz_id);
		}
		return ['deleted' => $result];
	}

	private function _insertTag($typeId, $id, $tag, $von, $bis)
	{
		$resultInsert = $this->_ci->NotizModel->insert([
			'titel' => 'TAG',
			'text' => 'AUTOMATED TAG',
			'verfasser_uid' => self::BATCHUSER,
			'erledigt' => false,
			'insertamum' => date('Y-m-d H:i:s'),
			'insertvon' => 'BatchJobTagAdd',
			'typ' => $tag,
			'start' => $von,
			'ende' => $bis
		]);

		if (isError($resultInsert)) {
			return error('Error inserting tag for ' .  $typeId . ': ' . $id);
		}

		$notizId = $resultInsert->retval;

		$resultZuordnung = $this->_ci->NotizzuordnungModel->insert([
			'notiz_id' => $notizId,
			$typeId => $id
		]);

		if (isError($resultZuordnung)) {
			return error('Error inserting relation for ' .  $typeId . ': ' . $id);
		}

		return $notizId;
	}

	private function _updateTag($notiz_id, $von, $bis)
	{
		$resultUpdateNotiz = $this->_ci->NotizModel->update(
			[
				'notiz_id' => $notiz_id
			],
			array(
				'updateamum' => date('Y-m-d H:i:s'),
				'updatevon' => 'BatchJobTagUpdate',
				'start' => $von,
				'ende' => $bis
			));


		if (isError($resultUpdateNotiz))
			return error ('Error occurred during Update ' . $notiz_id);

		return $notiz_id;
	}

	private function _deleteTag($notiz_id)
	{
		$result = $this->_ci->NotizzuordnungModel->delete([
			'notiz_id' => $notiz_id
		]);
		if (isError($result)) {
			return error('Error occurred during delete Notizzuordnung ' . $notiz_id);
		}

		$result = $this->_ci->NotizModel->delete([
			'notiz_id' => $notiz_id
		]);
		if (isError($result)) {
			return error('Error occurred during delete Notiz ' . $notiz_id);
		}

		return success([
			'deleted' => $notiz_id
		]);
	}

}
