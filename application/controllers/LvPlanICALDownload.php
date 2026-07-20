<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

require_once(FHCPATH.'include/functions.inc.php');

class LvPlanICALDownload extends Auth_Controller
{
	const MAX_DATE_RANGE = 34560000; // 400 days

	/**
	 * Object initialization.
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => ['basis/cis:r']
		]);

		$this->load->model('person/Benutzer_model', 'BenutzerModel');
	}

	/**
	 * Serves an authenticated iCalendar file download.
	 *
	 * @return void
	 */
	public function index()
	{
		$type = $this->getType();
		$uid = $this->getUid();
		$version = $this->getVersion();
		list($begin, $ende) = $this->getDateRange();

		$this->authorizeUid($uid);
		$this->validateUid($uid);

		$this->load->library('LvPlanICALLib');
		$content = $this->lvplanicallib->getContent(
			$uid,
			$begin,
			$ende,
			$type,
			$version
		);

		$filename = 'FH-Kalender_'.date('m_Y', $begin).'_ical'.$version.'.ics';

		$this->output
			->set_content_type('text/calendar', 'UTF-8')
			->set_header('Content-Disposition: attachment; filename="'.$filename.'"')
			->set_header('Pragma: public')
			->set_header('Expires: 0')
			->set_output($content);
	}

	/**
	 * @return string Requested timetable type.
	 */
	private function getType()
	{
		$type = $this->input->get('type', true);

		if (!in_array($type, array('student', 'lektor'), true))
			show_error('Ungueltiger Parameter: type', 400);

		return $type;
	}

	/**
	 * @return string Requested user UID.
	 */
	private function getUid()
	{
		$uid = $this->input->get('pers_uid', true);

		if (!is_string($uid) || $uid === '' || !check_utf8($uid))
			show_error('Ungueltiger Parameter: pers_uid', 400);

		return $uid;
	}

	/**
	 * @return int Requested iCalendar major version.
	 */
	private function getVersion()
	{
		$version = $this->input->get('version', true);

		if (!in_array($version, array('1', '2'), true))
			show_error('Ungueltiger Parameter: version', 400);

		return (int)$version;
	}

	/**
	 * Validates and expands the requested range to complete weeks.
	 *
	 * @return array{0: int, 1: int} Begin and end timestamps.
	 */
	private function getDateRange()
	{
		$begin = $this->getTimestamp('begin');
		$ende = $this->getTimestamp('ende');

		if ($ende < $begin)
			show_error('Ungueltiger Datumsbereich', 400);

		if ($ende - $begin > self::MAX_DATE_RANGE)
			show_error('Datumsbereich zu gross', 400);

		$begin = strtotime('monday this week', $begin);
		$ende = strtotime('sunday this week', $ende);

		return array($begin, $ende);
	}

	/**
	 * @param string $name GET parameter name.
	 * @return int Unix timestamp.
	 */
	private function getTimestamp($name)
	{
		$value = $this->input->get($name, true);

		if (!is_string($value) || !ctype_digit($value))
			show_error('Ungueltiger Parameter: '.$name, 400);

		$timestamp = filter_var(
			$value,
			FILTER_VALIDATE_INT,
			array('options' => array('min_range' => 0))
		);

		if ($timestamp === false)
			show_error('Ungueltiger Parameter: '.$name, 400);

		return $timestamp;
	}

	/**
	 * Ensures access to another user's timetable is explicitly permitted.
	 *
	 * @param string $uid Requested user UID.
	 * @return void
	 */
	private function authorizeUid($uid)
	{
		if ($uid !== getAuthUID()
			&& !$this->permissionlib->isBerechtigt('basis/other_lv_plan'))
		{
			show_error('Forbidden', 403);
		}
	}

	/**
	 * Ensures the requested user exists.
	 *
	 * @param string $uid Requested user UID.
	 * @return void
	 */
	private function validateUid($uid)
	{
		$this->BenutzerModel->addSelect('uid');
		$user = $this->BenutzerModel->load(array($uid));

		if (isError($user))
			show_error(getError($user), 500);

		if (!hasData($user))
			show_error('Ungueltiger Parameter: pers_uid', 400);
	}
}
