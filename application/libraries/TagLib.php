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

	public function updateAutomatedTags($tag, $inputData)
	{
		/*
		$params array expected pattern
		[
			[
				'id' => 123456,
				'typeId' => 123456,
				'von' => '2026-04-01',
				'bis' => '2026-06-30'
			],
			...
		]
		*/

		// ---------------------------------
		// prepare input
		// ---------------------------------
		$zeitraum = [];
		$prestudentIds = [];

		//TODO(Manu) check minimal input:

		foreach ($inputData as $row) {
			$pid = $row['id'];
			$typeId = $row['typeId'];

			$prestudentIds[] = $pid;

			$zeitraum[$pid] = [
				'von' => $row['von'] ?? null,
				'bis' => $row['bis'] ?? null
			];
		}
		$prestudentIds = array_unique($prestudentIds);

		// ---------------------------------
		// load existing tags
		// ---------------------------------
		$allTags = [];

		$this->_ci->NotizModel->addSelect('nz.notiz_id');
		$this->_ci->NotizModel->addSelect($typeId);
		$this->_ci->NotizModel->addJoin('public.tbl_notizzuordnung nz', 'notiz_id');

		$resultAllTags = $this->_ci->NotizModel->loadWhere([
			'typ' => $tag
		]);

		$allTagsData = getData($resultAllTags);

		if (!empty($allTagsData)) {
			foreach ($allTagsData as $row) {
				$allTags[$row->$typeId] = $row->notiz_id;
			}
		}

		// ---------------------------------
		// map the data
		// ---------------------------------
		$toRecycle = [];
		$toAdd = [];
		$toDelete = [];

		foreach ($prestudentIds as $pid) {
			if (isset($allTags[$pid])) {
				$toRecycle[$pid] = $allTags[$pid];
			} else {
				$toAdd[] = $pid;
			}
		}

		foreach ($allTags as $pid => $notizId) {
			if (!in_array($pid, $prestudentIds)) {
				$toDelete[$pid] = $notizId;
			}
		}

		// ---------------------------------
		// recycle (update existing)
		// ---------------------------------
		$countRecycled = 0;
		$retagged = [];

		foreach ($toRecycle as $pid => $notizId) {

			$result = $this->_ci->NotizModel->update(
				['notiz_id' => $notizId],
				[
					'updateamum' => date('Y-m-d H:i:s'),
					'updatevon' => 'BatchJobTagUpdate',
					'start' => $zeitraum[$pid]['von'],
					'ende' => $zeitraum[$pid]['bis']
				]
			);

			if (isError($result)) {
				return error('Error updating tag ' . $notizId);
			}

			$countRecycled++;
			$retagged[] = $notizId;
		}

		// ---------------------------------
		// ADD
		// ---------------------------------
		$countAdded = 0;
		$tagged = [];

		foreach ($toAdd as $pid) {

			$resultInsert = $this->_ci->NotizModel->insert([
				'titel' => 'TAG',
				'text' => 'AUTOMATED TAG',
				'verfasser_uid' => self::BATCHUSER,
				'erledigt' => false,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => 'BatchJobTagAdd',
				'typ' => $tag,
				'start' => $zeitraum[$pid]['von'],
				'ende' => $zeitraum[$pid]['bis']
			]);

			if (isError($resultInsert)) {
				return error('Error inserting tag for prestudent ' . $pid);
			}

			$notizId = $resultInsert->retval;

			$resultZuordnung = $this->_ci->NotizzuordnungModel->insert([
				'notiz_id' => $notizId,
				$typeId => $pid
			]);

			if (isError($resultZuordnung)) {
				return error('Error inserting relation for prestudent ' . $pid);
			}

			$countAdded++;
			$tagged[$notizId] = $pid;
		}

		// ---------------------------------
		// delete
		// ---------------------------------
		$countDeleted = 0;
		$deleted = [];

		foreach ($toDelete as $pid => $notizId) {

			$result = $this->_ci->NotizzuordnungModel->delete([
				'notiz_id' => $notizId
			]);

			if (isError($result)) {
				return error('Error deleting relation ' . $notizId);
			}

			$result = $this->_ci->NotizModel->delete([
				'notiz_id' => $notizId
			]);

			if (isError($result)) {
				return error('Error deleting note ' . $notizId);
			}

			$countDeleted++;
			$deleted[] = $notizId;
		}

		// ---------------------------------
		// return
		// ---------------------------------
		return success([
			'input' => [
				'tag' => $tag,
				'prestudentIds' => $prestudentIds
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
				'retaggedNotizIds' => $retagged,
				'newTags' => $tagged,
				'deletedNotizIds' => $deleted
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
			$resultUpdateNotiz = $this->_ci->NotizModel->update(
				[
					'notiz_id' => $notiz_id
				],
				array(
					'updateamum' => date('Y-m-d H:i:s'),
					'updatevon' => getAuthUID(),
				));

			if (isError($resultUpdateNotiz))
				return error ('Error occurred update Result ' . $notiz_id);

			$return = ['recycled' => $notiz_id];
		}
		else

		{
			$resultInsertNotiz = $this->_ci->NotizModel->insert(array(
				'titel' => 'TAG',
				'text' => 'AUTOMATED TAG',
				'verfasser_uid' => getAuthUID(),
				'erledigt' => false,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => getAuthUID(),
				'typ' => $tag,
				'start' => $von,
				'ende' => $bis
			));

			if (isError($resultInsertNotiz))
				return error ('Error occurred insert Result ' . $prestudent_id);

			$resultInsertZuordnung = $this->_ci->NotizzuordnungModel->insert(array(
				'notiz_id' => $resultInsertNotiz->retval,
				$typeId => $id
			));

			if (isError($resultInsertZuordnung))
				return error ('Error occurred insert Zuordnung ' . $id);
			$return = ['added' => $resultInsertNotiz->retval];
		}
		return success($return);
	}

	/*
	 * main function for rebuild Tags for typeId
	 * */
	public function rebuildTagsForTypeId($typeId, $id)
	{
		$automatedTagsRes = $this->_ci->NotiztypModel->loadWhere(array('automatisiert' => true, 'taglib IS NOT NULL' => null));
		$automatedTags = hasData($automatedTagsRes) ? getData($automatedTagsRes) : [];

		$_ci = get_instance();

		$result = $this->_ci->StudiensemesterModel->getLastOrAktSemester();
		if (isError($result))
			return error('Error occurred during retrieving studiensemester');
		if (empty($result->retval) || !isset($result->retval[0])) {
			return error('No studiensemester found');
		}
		$studiensemester_kurzbz = $result->retval[0]->studiensemester_kurzbz ?? null;
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

			// className without PATH (basename)
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

				return $result;
			}
			else
			{
				$result = $this->checkForDelete($kurz_bz, $typeId, $id);

				if ($result != null)
					$return[$kurz_bz] = $result;
			}
		}
		return success($return);
	}

	public function checkForDelete($tag, $typeId, $id)
	{
		$return = null;
		$notiz_id = null;

		if (!is_numeric($id))
			return error ("id " . $id . "not numeric");

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
		else
			return null;

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

}
