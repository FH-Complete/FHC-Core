<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

require_once(FHCPATH.'include/functions.inc.php');

class LvPlanICALSubscription extends FHC_Controller
{
	/**
	 * Encrypted calendar subscription token.
	 *
	 * @var string|null
	 */
	private $cal;

	/**
	 * UID authenticated by the calendar subscription token.
	 *
	 * @var string
	 */
	private $uid;

	/**
	 * First date included in the subscription feed.
	 *
	 * @var int Unix timestamp at local midnight
	 */
	private $begin;

	/**
	 * Last date included in the subscription feed.
	 *
	 * @var int Unix timestamp at local midnight
	 */
	private $ende;

	/**
	 * Object initialization.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->cal = $this->input->get('cal', true);
		$this->uid = $this->authenticateSubscription($this->cal);

		list($this->begin, $this->ende) = $this->determineDateRange();
	}

	/**
	 * Serves the iCalendar subscription feed.
	 *
	 * @return void
	 */
	public function index()
	{
		$this->load->library('LvPlanICALLib');

		$content = $this->lvplanicallib->getContent(
			$this->uid,
			$this->begin,
			$this->ende
		);

		$filename = 'FH-Kalender_'.date('m_Y', $this->begin).'_ical.ics';

		$this->output
			->set_content_type('text/calendar', 'UTF-8')
			->set_header('Content-Disposition: inline; filename="'.$filename.'"')
			->set_header('Pragma: public')
			->set_header('Expires: 0')
			->set_output($content);
	}

	/**
	 * Authenticates an encrypted calendar subscription token.
	 *
	 * @param string|null $cal Encrypted UID from the subscription URL.
	 * @return string Authenticated UID.
	 */
	private function authenticateSubscription($cal)
	{
		if (isEmptyString($cal))
			show_error('Fehlerhafter Parameter', 400);

		if (!defined('LVPLAN_CYPHER_KEY'))
			show_error('LVPLAN_CYPHER_KEY is not configured', 500);

		$uid = decryptData($cal, constant('LVPLAN_CYPHER_KEY'));

		if (!is_string($uid) || $uid === '' || !check_utf8($uid))
			show_error('Fehlerhafter Parameter', 400);

		$this->BenutzerModel->addSelect('uid');
		$user = $this->BenutzerModel->load([$uid]);

		if (isError($user))
			show_error(getError($user), 500);

		if (!hasData($user))
			show_error('Ungueltiger Benutzername', 401);

		return $uid;
	}

	/**
	 * Determines the subscription range from the current or next semester.
	 *
	 * During the final 30 days of the selected semester, the range is
	 * extended through the following semester. The resulting range is
	 * expanded to complete Monday-to-Sunday weeks.
	 *
	 * @return array{0: int, 1: int} Begin and end timestamps.
	 */
	private function determineDateRange()
	{
		$semesterResult = $this->StudiensemesterModel->getAkt();

		if (isError($semesterResult))
			show_error(getError($semesterResult), 500);

		if (!hasData($semesterResult))
		{
			$semesterResult = $this->StudiensemesterModel->getNext();

			if (isError($semesterResult))
				show_error(getError($semesterResult), 500);
		}

		if (!hasData($semesterResult))
			show_error('Studiensemester konnte nicht gefunden werden', 500);

		$semester = current(getData($semesterResult));
		$begin = strtotime($semester->start);
		$ende = strtotime($semester->ende);

		$semesterEnd = new DateTime($semester->ende);
		$daysFromSemesterEnd = (int)$semesterEnd
			->diff(new DateTime())
			->format('%R%a');

		if ($daysFromSemesterEnd >= -30)
		{
			$nextSemesterResult = $this->StudiensemesterModel
				->getNextFrom($semester->studiensemester_kurzbz);

			if (isError($nextSemesterResult))
				show_error(getError($nextSemesterResult), 500);

			if (hasData($nextSemesterResult))
				$ende = strtotime(current(getData($nextSemesterResult))->ende);
		}
 
		$begin = strtotime('monday this week', $begin);
		$ende = strtotime('sunday this week', $ende);
		return [$begin, $ende];
	}
}
