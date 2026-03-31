<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

use \DateTime as DateTime;

class TagJob extends JOB_Controller
{

	const BATCHUSER = 'sftest';
	const SEMESTER = 'SS2024'; //docker
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

	/**
	 * Test createTags
	 */
	public function createAutomatedTagsForPrestudents(...$prestudentIds)
	{
		print_r( PHP_EOL . "Start Job create Automated Tags" . PHP_EOL);

		$tag_typ_kurzbz = "wh_auto";
		$notiz = "TEST AUTOMATED TAG";
		$zuordnung_typ = "prestudent_id";
		$count = 0;

		$values = ($prestudentIds != null) ? $prestudentIds :  [ 125239,  167955];

		$checkZuordnungType = $this->NotizzuordnungModel->isValidType($zuordnung_typ);
		if (!isSuccess($checkZuordnungType))
			return error('Error occurred');

		$values = array_unique($values);

		foreach ($values as $value)
		{
			//TODO(uid)
			$resultInsertNotiz = $this->NotizModel->insert(array(
				'titel' => 'TAG',
				'text' => $notiz,
				'verfasser_uid' => self::BATCHUSER,
				'erledigt' => false,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => self::BATCHUSER,
				'typ' => $tag_typ_kurzbz
			));

			if (isError($resultInsertNotiz))
				return error ('Error occurred insert Result ' . $value);

			$resultInsertZuordnung = $this->NotizzuordnungModel->insert(array(
				'notiz_id' => $resultInsertNotiz->retval,
				$zuordnung_typ => $value
			));

			print_r( PHP_EOL . "Tag vom Typ " . $tag_typ_kurzbz . " für " . $zuordnung_typ . " " . $value . " erstellt");

			if (isError($resultInsertZuordnung))
				return error ('Error occurred insert Zuordnung' . $value);

			$count++;
		}

		print_r( PHP_EOL . "Automatically created Tags: " . $count . PHP_EOL);
		print_r( PHP_EOL . "End Job create Automated Tags" . PHP_EOL);

	}

	public function createAutomatedTags()
	{
		print_r( PHP_EOL . "Start Job create Automated Tags: " . PHP_EOL);

		$automaticTags = $this->config->item('stv_automatic_tags');
		print_r( implode( ", ", $automaticTags) . PHP_EOL);

		if(in_array('wh_auto', $automaticTags))
		{
			print_r(PHP_EOL . "create Tags wiederholer " . PHP_EOL);
			$result = $this->PrestudentstatusModel-> loadWhere(array(
				'statusgrund_id' => 16,
				'studiensemester_kurzbz' => self::SEMESTER
			));
			$data = $result->retval;
			$ids = array_map(function($item) {
				return $item->prestudent_id;
			}, $data);

			$result = $this->taglib->getAutomatedTags('wh_auto', $ids);
			if (isError($result))
				return error ('Error occurred getAutomatedTags');

			$data = $result->retval;

			print_r( PHP_EOL . "Automatically created Tags of Type WIEDERHOLER: " . $data[0] . PHP_EOL);
			print_r( "prestudents: " . implode( ", ", $data[1]) . PHP_EOL);
		}

		if(in_array('prewh_auto', $automaticTags))
		{
			print_r(PHP_EOL . "create Tags pre-wiederholer " . PHP_EOL);

			$result = $this->PrestudentstatusModel-> loadWhere(array(
				'statusgrund_id' => 15,
				'studiensemester_kurzbz' => self::SEMESTER
			));
			$data = $result->retval;
			$ids = array_map(function($item) {
				return $item->prestudent_id;
			}, $data);

			$result = $this->taglib->getAutomatedTags('prewh_auto', $ids);
			if (isError($result))
				return error ('Error occurred getAutomatedTags');

			$data = $result->retval;

			print_r( PHP_EOL . "Automatically created Tags of Type PRE-WIEDERHOLER: " . $data[0] . PHP_EOL);
			print_r( "prestudents: " . implode( ", ", $data[1]) . PHP_EOL);
		}

		if(in_array('dd_auto', $automaticTags))
		{
			print_r(PHP_EOL . "create Tags double degree" . PHP_EOL);


			print_r( PHP_EOL . "Automatically created Tags of Type DOUBLE DEGREE: " . $data[0] . PHP_EOL);
			print_r( "prestudents: " . implode( ", ", $data[1]) . PHP_EOL);
		}



/*		$tag_typ_kurzbz = "wh_auto";
		$notiz = "TEST AUTOMATED TAG";
		$zuordnung_typ = "prestudent_id";
		$count = 0;

		$values = ($arrayToTag != null) ? $arrayToTag :  [ 125239,  167955];

		$checkZuordnungType = $this->NotizzuordnungModel->isValidType($zuordnung_typ);
		if (!isSuccess($checkZuordnungType))
			return error('Error occurred');

		$values = array_unique($values);

		foreach ($values as $value)
		{
			//TODO(uid)
			$resultInsertNotiz = $this->NotizModel->insert(array(
				'titel' => 'TAG',
				'text' => $notiz,
				'verfasser_uid' => self::BATCHUSER,
				'erledigt' => false,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => self::BATCHUSER,
				'typ' => $tag_typ_kurzbz
			));

			if (isError($resultInsertNotiz))
				return error ('Error occurred insert Result ' . $value);

			$resultInsertZuordnung = $this->NotizzuordnungModel->insert(array(
				'notiz_id' => $resultInsertNotiz->retval,
				$zuordnung_typ => $value
			));

			print_r( PHP_EOL . "Tag vom Typ " . $tag_typ_kurzbz . " für " . $zuordnung_typ . " " . $value . " erstellt");

			if (isError($resultInsertZuordnung))
				return error ('Error occurred insert Zuordnung' . $value);

			$count++;
		}

		print_r( PHP_EOL . "Automatically created Tags: " . $count . PHP_EOL);*/
		print_r( PHP_EOL . "End Job create Automated Tags" . PHP_EOL);

	}

	/**
	 * delete All Automatic Tags
	 */
	public function deleteAutomatedTags()
	{
		print_r( PHP_EOL . "Start Job delete ALL Automated Tags" . PHP_EOL);
	//	$this->NotizModel->select('notiz_id');
		$resultToDelete = $this->NotizModel->loadWhere(array('insertvon' => self::BATCHUSER));

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
