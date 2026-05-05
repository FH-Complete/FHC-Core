<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

use \DateTime as DateTime;

class TagJob extends JOB_Controller
{

	const BATCHUSER = 'sftest';

	/**
	 * API constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Configs
		$this->load->config('stv');

		// Library
		$this->load->library('TagLib');

		// Load Models
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('system/Notiztyp_model', 'NotiztypModel');

		$this->loadPhrases([
			'lehre'
		]);
	}

	public function rebuildAutomatedTags()
	{
		print_r( PHP_EOL . "Start Job rebuild" . PHP_EOL);

		$automatedTagsRes = $this->NotiztypModel->loadWhere(array('automatisiert' => true, 'taglib IS NOT NULL' => null));
		$automatedTags = hasData($automatedTagsRes) ? getData($automatedTagsRes) : [];

		$result = $this->StudiensemesterModel->getAktOrNextSemester();
		if (isError($result))
			return error ('Error occurred during retrieving studiensemester');
		if (empty($result->retval) || !isset($result->retval[0])) {
			return error('No studiensemester found');
		}
		$studiensemester_kurzbz = $result->retval[0]->studiensemester_kurzbz ?? null;
		$params = array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz
		);

		foreach($automatedTags as $autoTag)
		{
			// getPath: must not be lost
			$filePath = APPPATH . 'libraries/' . $autoTag->taglib . '.php'; // APPPATH = application/

			if(file_exists($filePath)) {
				require_once($filePath);
			} else {
				echo "File not found: " . $filePath . PHP_EOL;
				continue;
			}

			$kurz_bz = $autoTag->typ_kurzbz;
			// className without PATH (basename)
			$className = basename($autoTag->taglib);

			$obj = new $className();

			$outputArray = $obj->getZuordnungIds($params);
			$typeId = $outputArray->typeId;

			$paramsTag = array(
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'kurzbz' => $kurz_bz,
				'data' => $outputArray->data,
				'typeId' => $typeId
			);

			$result = $this->taglib->updateAutomatedTags($paramsTag);

			if (isError($result)) {
				return error('Error occurred during updateAutomatedTags');
			}

			$data = is_array($result) ? $result['retval'] : $result->retval;

			//PRINT OUTPUT CONSOLE
			//SUMMARY
			print_r(PHP_EOL . "-- TAG " . $result->retval['input']['tag'] . " | TYPE_ID " . $typeId . " --");

			print_r( PHP_EOL . "Count Recycled: " . $result->retval['summary']['recycled']);
			print_r(PHP_EOL . "Count Added: ".  $result->retval['summary']['added']);
			print_r(PHP_EOL . "Count Deleted: ".  $result->retval['summary']['deleted']);

			//DETAILS
			//print_r(PHP_EOL . "New tag(s) [". $typeId . "]: " .  implode(', ', $result->retval['results']['newTags']));
			//print_r(PHP_EOL . "Deleted tags(s) [". $typeId . "]: " . implode(', ', $result->retval['results']['deletedTagsIds']));
			//print_r(PHP_EOL . "Recycled tag(s) [". $typeId . "]: " . implode(', ', $result->retval['results']['retaggedIds']));
			print_r(PHP_EOL);
		}
		print_r( PHP_EOL . "End Job rebuild" . PHP_EOL);

	}

	public function deleteAllAutomatedTags()
	{
		print_r( PHP_EOL . "Start Job delete ALL Automated Tags" . PHP_EOL);

		$resultToDelete = $this->NotizModel->loadWhere(array('insertvon' => 'BatchJobTagAdd'));

		$data = $resultToDelete->retval;
		$notiz_ids = array_map(function($item) {
			return $item->notiz_id;
		}, $data);

		print_r($notiz_ids);

		foreach ($notiz_ids as $notiz_id)
		{
			$result = $this->NotizzuordnungModel->delete([
				'notiz_id' => $notiz_id
			]);
			if (isError($result))
				return error ('Error occurred delete Notizzuordnung' . $notiz_id);

			$result = $this->NotizModel->delete([
				'notiz_id' => $notiz_id
			]);
			if (isError($result))
				return error ('Error occurred delete Notiz' . $notiz_id);
		}
		print_r( PHP_EOL . "End Job delete Automated Tags" . PHP_EOL);
	}
}
