<?php

defined('BASEPATH') || exit('No direct script access allowed');

use CI3_Events as Events;

class CollisionChecker
{
	private $_checks = [];

	private $_ci;

	public function __construct()
	{
		$this->_ci =& get_instance();

		$this->_ci->load->library('collision/checks/RoomCollisionCheck');
		$this->_ci->load->library('collision/checks/LectureCollisionCheck');
		$this->_ci->load->library('collision/checks/VerbandCollisionCheck');
		$this->_ci->load->library('collision/checks/StudentCollisionCheck');
		$this->_ci->load->library('collision/checks/ResourcesCollisionCheck');
		$this->register($this->_ci->roomcollisioncheck);
		$this->register($this->_ci->lecturecollisioncheck);
		$this->register($this->_ci->verbandcollisioncheck);
		$this->register($this->_ci->studentcollisioncheck);
		$this->register($this->_ci->resourcescollisioncheck);
		Events::trigger('collision_register', $this);
	}

	public function register(ICollisionCheck $check)
	{
		$this->_checks[$check->getName()] = $check;
	}

	public function run($data)
	{
		$errors = [];

		foreach ($this->_checks as $check)
		{
			$result = $check->check($data);

			if (!empty($result))
			{
				$errors = array_merge($errors, $result);
			}
		}

		return $errors;
	}

	public function runAll($kalender_ids)
	{
		$results = array_fill_keys($kalender_ids, []);

		foreach ($this->_checks as $check)
		{
			$batchResult = $check->checkAll($kalender_ids);
			foreach ($batchResult as $kalender_id => $errors)
			{
				$results[$kalender_id] = array_merge($results[$kalender_id], $errors);
			}
		}

		return $results;
	}
}