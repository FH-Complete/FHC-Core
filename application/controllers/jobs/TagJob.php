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

		$result = $this->StudiensemesterModel->getLastOrAktSemester();
		if (isError($result))
			return error ('Error occurred during retrieving studiensemester');
		if (empty($result->retval) || !isset($result->retval[0])) {
			return error('No studiensemester found');
		}
		$studiensemester_kurzbz = $result->retval[0]->studiensemester_kurzbz ?? null;

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

			$outputArray = $obj->getZuordnungIds(['studiensemester_kurzbz' => $studiensemester_kurzbz]);
			$data = $outputArray->data;

			print_r($kurz_bz . " " . $autoTag->taglib);

			$result = $this->taglib->updateAutomatedTags($kurz_bz, $data);

			$data = $result->retval;
			if (isError($result))
				return error ('Error occurred during updateAutomatedTags');

			//Output with Summary and Details
			print_r(PHP_EOL . "-- TAG  " . $result->retval['input']['tag'] . " --");
			print_r( PHP_EOL . "Count Recycled: " . $result->retval['summary']['recycled']);
			print_r(PHP_EOL . "Count Added: ".  $result->retval['summary']['added']);
			print_r(PHP_EOL . "Count Deleted: ".  $result->retval['summary']['deleted']);
			print_r(PHP_EOL);
		}
		print_r( PHP_EOL . "End Job rebuild" . PHP_EOL);

	}
}
