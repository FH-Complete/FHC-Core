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
		$this->logInfo('Start Job rebuild Automated Tags');

		$automatedTagsRes = $this->NotiztypModel->loadWhere(array('automatisiert' => true, 'taglib IS NOT NULL' => null));
		$automatedTags = hasData($automatedTagsRes) ? getData($automatedTagsRes) : [];


		$result = $this->StudiensemesterModel->getAktOrNextSemester();
		if (isError($result))
		{
			$this->logError('Error occurred during retrieving studiensemester');
			return $this->logInfo('End Job rebuild Automated Tags');
		}

		if (empty($result->retval) || !isset($result->retval[0])) {
			$this->logInfo('No Studiensemester found');
			return $this->logInfo('End Job rebuild Automated Tags');
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
				$this->logInfo("File not found: " . $filePath . PHP_EOL);
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
				$this->logError('Error occurred during updateAutomatedTags ' . $kurz_bz);
				continue;
			}

			$data = is_array($result) ? $result['retval'] : $result->retval;

			//PRINT OUTPUT CONSOLE
			//SUMMARY
			$this->logInfo("Tag " . $result->retval['input']['tag'] . " | TYPE_ID " . $typeId . " --"
			. " Count Recycled: " . $result->retval['summary']['recycled']
			. " Count Added: ".  $result->retval['summary']['added']
			. " Count Deleted: ".  $result->retval['summary']['deleted']);

			//DETAILS
			if($result->retval['results']['newTags'])
				$this->logInfo("Tag " . $result->retval['input']['tag'] . "New tag(s): " .  implode(', ', $result->retval['results']['newTags']));
			if($result->retval['results']['deletedTagsIds'])
				$this->logInfo("Tag " . $result->retval['input']['tag'] . "Deleted tags(s: " . implode(', ', $result->retval['results']['deletedTagsIds']));
			if ($result->retval['results']['retaggedIds'])
				$this->logInfo("Tag " . $result->retval['input']['tag'] . "Recycled tag(s): " . implode(', ', $result->retval['results']['retaggedIds']));

		}
		$this->logInfo( PHP_EOL . "End Job rebuild Automated Tags");

	}
}
