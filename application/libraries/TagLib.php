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

		$this->batchUser = $this->_ci->config->item('userAutomatedTags');
	}

	public function updateAutomatedTagsSingle($paramsTag)
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
		}

		// ---------------------------------
		// ADD
		// ---------------------------------
		$countAdded = 0;
		$tagged = [];

		foreach ($toAdd as $id)
		{
			$this->_insertTag($typeId, $id, $tag, $zeitraum[$id]['von'], $zeitraum[$id]['bis']);
			$countAdded++;

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
/*			'details' => [
				'existingTags' => $allTags,
				'toAdd' => $toAdd,
				'toRecycle' => $toRecycle,
				'toDelete' => $toDelete
			],
			'results' => [
				'retaggedIds' => $retagged,
				'newTags' => $tagged,
				'deletedTagsIds' => $deleted
			]*/
		]);
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
		$typeId = $paramsTag['typeId'];
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
		//$arrayNotizids = [];
		$resultAllTags = $this->_ci->NotizModel->getAllTags($tag, $von, $bis);
		if (isError($resultAllTags)) {
			return $resultAllTags;
		}
		$allTagsData = getData($resultAllTags);
		//return success("count Data " . count($allTagsData)); //21

		//new version: for each prestudent_id nun mehrere notiz_id mit von und bis
		if (!empty($allTagsData)) {
			foreach ($allTagsData as $item) {
				//$prestudentId = $item->prestudent_id;

				//if (!isset($allTags[$prestudentId])) {
				//	$allTags[$prestudentId] = [];
				//}

				$allTags[$item->prestudent_id][] = [ //betreffende Zeile, die in PHP error log angezeigt wird
					'notiz_id' => $item->notiz_id,
					'von' => $item->start,
					'bis' => $item->ende
				];
			}
		}

		//speichersparende Version
/*		if (!empty($allTagsData)) {
			foreach ($allTagsData as $item) {
				$key = $item->prestudent_id . '|' . $item->start . '|' . $item->ende;

				$allTags[$key][] = [
					'notiz_id' => $item->notiz_id,
					'prestudent_id' => $item->prestudent_id,
					'von' => $item->start,
					'bis' => $item->ende
				];
			}
		}*/


		unset($allTagsData); //TODO Check manu

		//return success($allTags);

		// ---------------------------------
		// prepare input
		// ---------------------------------
		$inputTags = [];

		foreach ($paramsTag['data'] as $item) {
			$inputTags[$item['id']][] = [
				'von' => $item['von'],
				'bis' => $item['bis']
			];
		}

		$toRecycle = [];
		$toAdd = [];

		foreach ($paramsTag['data'] as $item) {

			$prestudentId = $item['id'];
			$von = $item['von'];
			$bis = $item['bis'];

			$found = false;

			if (isset($allTags[$prestudentId])) {
				foreach ($allTags[$prestudentId] as $tag) {

					if (
						$tag['von'] === $von
						&& $tag['bis'] === $bis
					) {
						$toRecycle[] = [
							'notiz_id' => $tag['notiz_id'],
							'prestudent_id' => $prestudentId,
							'von' => $von,
							'bis' => $bis
						];

						$found = true;
						break;
					}
				}
			}

			if (!$found) {
				$toAdd[] = [
					'prestudent_id' => $prestudentId,
					'von' => $von,
					'bis' => $bis
				];
			}
		}
		//return success($toRecycle);
		//return success($toAdd);


		$toDelete = [];
		$recycleIds = [];

		foreach ($toRecycle as $item) {
			$recycleIds[] = $item['notiz_id'];
		}

		// compare existing tags with recycled ones
		foreach ($allTags as $prestudentId => $tags) {
			foreach ($tags as $tag) {

				if (!in_array($tag['notiz_id'], $recycleIds)) {
					$toDelete[] = [
						'notiz_id' => $tag['notiz_id'],
						'prestudent_id' => $prestudentId,
					/*	'von' => $tag['von'],
						'bis' => $tag['bis']*/
					];
				}
			}
		}

		// ---------------------------------
		// recycle (update existing)
		// ---------------------------------
		$countRecycled = 0;
		$retagged = [];

		foreach ($toRecycle as $recycleItem)
		{
			$this->_updateTag($recycleItem['notiz_id'], $recycleItem['von'], $recycleItem['bis']);

			$countRecycled++;
			$retagged[] = $recycleItem['prestudent_id'];
		}

		//return success($retagged);

		// ---------------------------------
		// ADD
		// ---------------------------------
		$countAdded = 0;
		$tagged = [];

		foreach ($toAdd as $addItem)
		{
			$this->_insertTag($typeId, $addItem['prestudent_id'], $paramsTag['kurzbz'], $addItem['von'], $addItem['bis']);
/*			if (isError($resultAdd)) {
				return $resultAdd;
			}*/
			$countAdded++;
			//$idsFromData[] = $resultAdd;

			$tagged[] = $addItem['prestudent_id'];
		}
		//return success($tagged);


		// ---------------------------------
		// delete
		// ---------------------------------
		$countDeleted = 0;
		$deleted = [];

		//alle notizIds, die nicht in idsToCheck sind, sollen in $toDelete gespeichert werden

		foreach ($toDelete as $deleteItem)
		{
			$this->_deleteTag($deleteItem['notiz_id']);
			$countDeleted++;
			$deleted[] = $deleteItem['notiz_id'];
		}
		//return success($deleted);

		// ---------------------------------
		// return
		// ---------------------------------
		return success([
			'input' => [
				'tag' => $paramsTag['kurzbz']
			],
			'summary' => [
				'recycled' => $countRecycled,
				'added' => $countAdded,
				'deleted' => $countDeleted
			],
/*			'details' => [
				'existingTags' => $allTags,
				'toAdd' => $toAdd,
				'toRecycle' => $toRecycle,
				'toDelete' => $toDelete
			],*/
/*			'results' => [
				'retaggedIds' => $retagged,
				'newTags' => $tagged,
				'deletedTagsIds' => $deleted
			]*/
		]);
	}

	public function updateAutomatedTagsForTypeId(array $params)
	{
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
			$typeId => $id,
			'ende' => $bis,
			'start' => $von
		]);
		if(hasData($resultAllTags))
		{
			$this->_ci->addMeta('manu_update ', $resultAllTags);
			$notiz_id = $resultAllTags->retval[0]->notiz_id;
		}

		//RECYCLE
		if ($notiz_id !== null)
		{
			$this->_ci->addMeta('recycled ', $notiz_id);
			$notiz_id = $this->_updateTag($notiz_id, $von, $bis);
			$return = ['recycled' => $notiz_id];
			//return success($return);
		}
		else
		//ADD
		{
			$resultInsertNotiz = $this->_insertTag($typeId, $id, $tag, $von, $bis);
			$return = ['added' => $resultInsertNotiz];
			$notiz_id = $resultInsertNotiz;
			$this->_ci->addMeta('added ', $resultInsertNotiz);
			//return success($return);
		}

		//Here now checkDelete
		return success($notiz_id);
	}

	/*
	 * main function for rebuild Tags for typeId
	 * */
	public function rebuildTagsForTypeId($typeId, $id, $studiensemester_kurzbz)
	{
		$automatedTagsRes = $this->_ci->NotiztypModel->loadWhere(array('automatisiert' => true, 'taglib IS NOT NULL' => null));
		$automatedTags = hasData($automatedTagsRes) ? getData($automatedTagsRes) : [];
		$ids_toCheck = [];

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

				$this->_ci->addMeta("criteriaIsSet", $criteriaIsSet);
				foreach ($criteriaIsSet->retval as $item)
				{
					$von = isset($item->von) ? $item->von : '';
					$bis = isset($item->bis) ? $item->bis : '';

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

					$return[$kurz_bz] = $result; //hier besser alle notiz_Ids speichern, die zurückkommen,
					//können neu hinzugefügt oder recycelt sein
					$ids_toCheck[] = $result->retval;
				}
				$this->_ci->addMeta('ids_to_check', $ids_toCheck);
			}

			if(count($ids_toCheck) !== 0)
			{
				$resultToDelete = $this->_ci->NotizModel->checkIfTagToDelete($autoTag->typ_kurzbz, $typeId, $id, $ids_toCheck, $startSem, $endeSem);
				if (hasData($resultToDelete))
				{
					$this->_ci->addMeta('ids_to_delete', $resultToDelete->retval);
					foreach($resultToDelete->retval as $item)
					{
						$this->_deleteTag($item->notiz_id);
					}
				}
			}
		}
		return success($ids_toCheck);
	}

	private function _insertTag($typeId, $id, $tag, $von, $bis)
	{
		$resultInsert = $this->_ci->NotizModel->insert([
			'titel' => 'TAG',
			'text' => 'AUTOMATED TAG',
			'verfasser_uid' => $this->batchUser,
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
