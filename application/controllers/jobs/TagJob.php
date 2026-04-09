<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

use \DateTime as DateTime;

class TagJob extends JOB_Controller
{

	const BATCHUSER = 'sftest';
	const SEMESTER = 'WS2025'; //docker
	//TODO semester

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

		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('system/Notiztyp_model', 'NotiztypModel');
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');

		$this->loadPhrases([
			'lehre'
		]);
	}

	public function pocTagLibs()
	{
		$automatedTagsRes = $this->NotiztypModel->loadWhere(array('automatisiert' => true, 'taglib IS NOT NULL' => null));
		//echo $this->NotiztypModel->db->last_query();
		$automatedTags = hasData($automatedTagsRes) ? getData($automatedTagsRes) : [];
		print_r($automatedTags);

		foreach($automatedTags as $autoTag)
		{
			$taglib = strtolower(basename($autoTag->taglib));
			$this->load->library($autoTag->taglib);

			$ids = $this->$taglib->getZuordnungIds(array('studiensemester_kurzbz' => 'SS2026'));
			print_r($ids);
		}
	}

	public function rebuildAutomatedTags()
	{
		print_r( PHP_EOL . "Start Job rebuild" . PHP_EOL);
		$automaticTags = $this->config->item('stv_automatic_tags');
		print_r( implode( ", ", $automaticTags) . PHP_EOL);

		//TODO(Manu) use a loop in library?
		if(in_array('wh_auto', $automaticTags))
		{
			$result = $this->PrestudentstatusModel-> loadWhere(array(
				'statusgrund_id' => 16,
				'studiensemester_kurzbz' => self::SEMESTER
			));
			$data = $result->retval;
			$ids = array_map(function($item) {
				return $item->prestudent_id;
			}, $data);


			$result = $this->taglib->updateAutomatedTags('wh_auto', $ids);
			$data = $result->retval;
			if (isError($result))
				return error ('Error occurred during updateAutomatedTags');

			print_r(PHP_EOL ."ALL TAGS 'wh_auto' " . count($data[0]) . " TO ADD " . count($data[1]) . " TO RECYLE: " . count($data[2]) .  " TO DELETE: " . count($data[3]));


			print_r( PHP_EOL . "Count Recycled: ");
			print_r($data[4]);
			print_r( PHP_EOL . "Count Added: ");
			print_r($data[6]);
			print_r( PHP_EOL . "Count Deleted: ");
			print_r($data[8] . PHP_EOL);
		}

		if(in_array('prewh_auto', $automaticTags))
		{
			$result = $this->PrestudentstatusModel->loadWhere(array(
				'statusgrund_id' => 15,
				'studiensemester_kurzbz' => self::SEMESTER
			));
			$data = $result->retval;
			$ids = array_map(function ($item) {
				return $item->prestudent_id;
			}, $data);

			$result = $this->taglib->updateAutomatedTags('prewh_auto', $ids);
			if (isError($result))
				return error('Error occurred during updateAutomatedTags');

			$data = $result->retval;

			print_r(PHP_EOL . "ALL TAGS 'prewh_auto' " . count($data[0]) . " TO ADD " . count($data[1]) . " TO RECYLE: " . count($data[2]) . " TO DELETE: " . count($data[3]));

			print_r(PHP_EOL . "Count Recycled: ");
			print_r($data[4]);
			print_r(PHP_EOL . "Count Added: ");
			print_r($data[6]);
			print_r(PHP_EOL . "Count Deleted: ");
			print_r($data[8] . PHP_EOL);
		}

		print_r( PHP_EOL . "End Job rebuild" . PHP_EOL);

	}
}
