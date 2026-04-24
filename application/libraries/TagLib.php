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
		$inputData expected pattern

		[
			[
				'prestudent_id' => 123456,
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

		foreach ($inputData as $row) {
			$pid = $row['prestudent_id'];

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
				self::TYP_ZUORDNUNG => $pid
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
					'updatevon' => getAuthUID(),
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
				'verfasser_uid' => getAuthUID(),
				'erledigt' => false,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => getAuthUID(),
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
	 * */
	public function rebuildTagsForPrestudent($prestudent_id)
	{
		$automatedTagsRes = $this->_ci->NotiztypModel->loadWhere(array('automatisiert' => true, 'taglib IS NOT NULL' => null));
		//echo $this->NotiztypModel->db->last_query();
		$automatedTags = hasData($automatedTagsRes) ? getData($automatedTagsRes) : [];

		$result = $this->_ci->StudiensemesterModel->getLastOrAktSemester();
		if (isError($result))
			return error ('Error occurred during retrieving studiensemester');
		if (empty($result->retval) || !isset($result->retval[0])) {
			return error('No studiensemester found');
		}
		$studiensemester_kurzbz = $result->retval[0]->studiensemester_kurzbz ?? null;

		print_r($automatedTags);

		$return = [];

		foreach($automatedTags as $autoTag)
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

			$obj = new $className();
			$criteriaIsSet = $obj->isCriteriaSetFor([
				'prestudent_id' => $prestudent_id,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			]);
			//$return = $this->_ci->PrestudentstatusModel->db->last_query();
			$kurz_bz = $autoTag->typ_kurzbz;

			//	$return[$kurz_bz] = $criteriaIsSet;

			if($criteriaIsSet)
			{
				$result = $this->updateAutomatedTagsForPrestudent($kurz_bz, $prestudent_id);
				if (isError($result))
					return error ('Error occurred during updateAutomatedTags' . $kurz_bz);

				else
					$return[$kurz_bz] = $result;
			}
			else {
				$result = $this->checkForDelete($kurz_bz, $prestudent_id);
				if($result != null)
					$return[$kurz_bz] = $result;
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

}
