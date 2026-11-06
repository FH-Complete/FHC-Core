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

	public function aktualisiereIssues(array $kalender_ids, $akzeptanzZuruecksetzen = true)
	{
		if (isEmptyArray($kalender_ids)) return;
		$ids = implode(',', array_map('intval', $kalender_ids));
		$db = new DB_Model();
		if ($akzeptanzZuruecksetzen)
		{
			$this->_ci->db->query("
				UPDATE system.tbl_issue
				SET status_kurzbz = 'resolved', updateamum = now(), updatevon = ?
				WHERE status_kurzbz = 'accepted'
				AND (behebung_parameter->>'kalender_id_a') IS NOT NULL
				AND (
					(behebung_parameter->>'kalender_id_a')::integer IN (" . $ids . ")
					OR (behebung_parameter->>'kalender_id_b')::integer IN (" . $ids . ")
				)
			", [getAuthUID()]);
		}

		$offeneRes = $db->execReadOnlyQuery("
			SELECT i.issue_id, i.person_id, i.oe_kurzbz, i.fehlercode, f.fehler_kurzbz, i.behebung_parameter
			FROM system.tbl_issue i
			JOIN system.tbl_fehler f ON f.fehlercode = i.fehlercode
			JOIN system.tbl_fehler_app fa ON fa.fehlercode = i.fehlercode AND fa.app = 'tempus'
			WHERE i.verarbeitetamum IS NULL
			AND (i.behebung_parameter->>'kalender_id_a') IS NOT NULL
			AND (
				(i.behebung_parameter->>'kalender_id_a')::integer IN (" . $ids . ")
				OR (i.behebung_parameter->>'kalender_id_b')::integer IN (" . $ids . ")
			)
		");

		if (hasData($offeneRes))
		{
			$this->_ci->load->library('issues/PlausicheckResolverLib', ['apps' => ['tempus']]);
			$result = $this->_ci->plausicheckresolverlib->resolvePlausicheckIssues(getData($offeneRes));

			foreach ($result->errors as $error)
				log_message('error', 'Tempus-Issues beheben: ' . $error);
		}

		$this->_ci->load->library('issues/PlausicheckProducerLib', ['apps' => ['tempus']]);
		$result = $this->_ci->plausicheckproducerlib->producePlausicheckIssues(['kalender_ids' => $kalender_ids]);

		foreach ($result->errors as $error)
			log_message('error', 'Tempus-Issues erzeugen: ' . $error);
	}
}