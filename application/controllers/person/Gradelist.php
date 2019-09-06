<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * List all grades of a Student
 */
class Gradelist extends Auth_Controller
{
	private $_grades; // Array of Grades

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => array('student:r', 'student/noten:r')
			)
		);

		// Loads models
		$this->load->model('person/person_model', 'PersonModel');
		$this->load->model('organisation/studiengang_model', 'StudiengangModel');
		$this->load->model('crm/student_model', 'StudentModel');
		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('education/zeugnisnote_model', 'ZeugnisnoteModel');
		$this->load->model('education/lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('codex/note_model', 'NoteModel');
		$this->load->model('education/lehrveranstaltung_model', 'LehrveranstaltungModel');

		$this->loadPhrases(
			array(
				'global',
				'person',
				'lehre',
				'ui'
			)
		);

		$result_noten = $this->NoteModel->load();
		foreach ($result_noten->retval as $row)
		{
			$this->_grades[$row->note]['positiv'] = $row->positiv;
			$this->_grades[$row->note]['anmerkung'] = $row->anmerkung;
			$this->_grades[$row->note]['notenwert'] = $row->notenwert;
		}
		$this->_grades['']['positiv'] = false;
		$this->_grades['']['anmerkung'] = '';
		$this->_grades['']['notenwert'] = 0;
	}

	/**
	 * Print all Grades of a person
	 * @param $uid UID of the Person
	 */
	public function index($uid = null)
	{
		if (is_null($uid))
			$uid = getAuthUID();

		// load student
		$student = $this->StudentModel->load(array($uid));
		if (!isSuccess($student) || !hasData($student))
		{
			echo "You have no Permission or User does not exists";
			exit;
		}

		// Check if logged in User has permission to see grades of this person
		$stg = $this->StudiengangModel->load($student->retval[0]->studiengang_kz);
		if (!$this->hasPermission($uid, $stg->retval[0]->oe_kurzbz))
		{
			echo "You have no Permission or User does not exists";
			exit;
		}
		$person = $this->PersonModel->getByUid($uid);

		$courses = $this->loadCourseInformation($student->retval[0]->prestudent_id, $student->retval[0]->student_uid);

		$data = array (
			"user" => $uid,
			"person" => $person->retval[0],
			"courses" => $courses,
			"grades" => $this->_grades
		);

		$this->load->view('person/gradelist/gradelist.php', $data);
	}

	/**
	 * Check if the Logged in User has permission to see the grades of this person
	 * @param $uid UID of the Person we want to see
	 * @param $oe_kurzbz Organisation Unit of the Person we want to see
	 * @return true if the logged in User is allowed to see the content, false if not
	 */
	private function hasPermission($uid, $oe_kurzbz)
	{
		$loggedinUser = getAuthUID();
		if($uid != $loggedinUser)
		{
			$this->load->library('PermissionLib');
			if($this->permissionlib->isBerechtigt('student/noten','s',$oe_kurzbz))
			{
				return true;
			}
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Loads the Courses and Grades of the Student
	 *
	 * @param $prestudent_id of the Student
	 * @return array with the courses
	 */
	private function loadCourseInformation($prestudent_id, $uid)
	{
		$this->load->library('StudienplanLib');

		// Get status of Student
		$result_status = $this->PrestudentstatusModel->getStatusByFilter($prestudent_id);

		if (isError($result_status) || !hasData($result_status))
		{
			return error('No Status Found');
		}

		// Get Courses from studyplan for each semester of the student
		foreach ($result_status->retval as $row_status)
		{
			if (in_array($row_status->status_kurzbz,
				array('Student','Diplomand','Incoming','Abbrecher','Unterbrecher','Absolvent')))
			{
				// Wenn das Semester schon vorhanden ist dann ueberspringen
				// (bei mehreren Statuseintraegen im selben Semester (zB Absolvent / Diplomand)
				if(isset($courses['semester'][$row_status->studiensemester_kurzbz]))
					continue;

				// LVs fuer das Semester holen lt Studienplan
				$lvtree = $this->studienplanlib->getLehrveranstaltungTree(
							$row_status->studienplan_id,
							$row_status->ausbildungssemester,
							true
						);
				$courses['semester'][$row_status->studiensemester_kurzbz]['lvs'] = $lvtree;
				$courses['semester'][$row_status->studiensemester_kurzbz]['lvs_nonstpl'] = array();

				$result_stpl = $this->StudienplanModel->load($row_status->studienplan_id);
				if(isSuccess($result_stpl) && hasData($result_stpl))
				{
					$stpl_bezeichnung = $result_stpl->retval[0]->bezeichnung;
				}
				else
					$stpl_bezeichnung = 'unknown';

				$courses['semester'][$row_status->studiensemester_kurzbz]['data'] = array(
					'status' => $row_status->status_kurzbz,
					'ausbildungssemester' => $row_status->ausbildungssemester,
					'studiensemester_kurzbz' => $row_status->studiensemester_kurzbz,
					'studienplan_id' => $row_status->studienplan_id,
					'studienplan_bezeichnung' => $stpl_bezeichnung,
				);
			}
			$result_zuordnung = $this->LehrveranstaltungModel->getLvsByStudent($uid, $row_status->studiensemester_kurzbz);
			if(isSuccess($result_zuordnung) && hasData($result_zuordnung))
			{
				$this->setZuordnung(
					$result_zuordnung,
					$courses['semester'][$row_status->studiensemester_kurzbz]['lvs']
				);
			}
		}

		// Load Grades and add to studyplan
		$result_zeugnis = $this->ZeugnisnoteModel->loadWhere(array("student_uid" => $uid));

		if (isSuccess($result_zeugnis) && hasData($result_zeugnis))
		{
			foreach ($courses['semester'] as $key=>$value)
			{
				$this->fillNotenPart(
					$result_zeugnis,
					$courses['semester'][$key]['lvs'],
					$value['data']['studiensemester_kurzbz']
				);
			}
		}

		// Build Array of Courses that are not part of the studyplan
		foreach ($result_zeugnis->retval as $row_noten)
		{
			if (!isset($row_noten->found))
			{
				$result_lv = $this->LehrveranstaltungModel->load($row_noten->lehrveranstaltung_id);
				$result_stg = $this->StudiengangModel->load($result_lv->retval[0]->studiengang_kz);
				$courses['semester'][$row_noten->studiensemester_kurzbz]['lvs_nonstpl'][] = array(
					'lehrveranstaltung_id' => $row_noten->lehrveranstaltung_id,
					'lehrtyp_kurzbz' => $result_lv->retval[0]->lehrtyp_kurzbz,
					'lehrform_kurzbz' => $result_lv->retval[0]->lehrform_kurzbz,
					'pflicht' => false,
					'sws' => $result_lv->retval[0]->sws,
					'zeugnis' => $result_lv->retval[0]->zeugnis,
					'bezeichnung' => $result_lv->retval[0]->bezeichnung,
					'kurzbz' => $result_lv->retval[0]->kurzbz,
					'ects' => $result_lv->retval[0]->ects,
					'semester' => $result_lv->retval[0]->semester,
					'note' => $row_noten->note,
					'datum' => $row_noten->benotungsdatum,
					'zugeordnet' => true,
					'studiengang_kurzbz' => $result_stg->retval[0]->kurzbzlang
				);
				if(!isset($courses['semester'][$row_noten->studiensemester_kurzbz]['data']['ectssumme_nonstpl']))
					$courses['semester'][$row_noten->studiensemester_kurzbz]['data']['ectssumme_nonstpl'] = 0;
				if(!isset($courses['semester'][$row_noten->studiensemester_kurzbz]['data']['swssumme_nonstpl']))
					$courses['semester'][$row_noten->studiensemester_kurzbz]['data']['swssumme_nonstpl'] = 0;
				$courses['semester'][$row_noten->studiensemester_kurzbz]['data']['ectssumme_nonstpl'] += $result_lv->retval[0]->ects;
				$courses['semester'][$row_noten->studiensemester_kurzbz]['data']['swssumme_nonstpl'] += $result_lv->retval[0]->sws;
			}
		}

		$sum_gradeweighted_overall = 0;
		$sum_ectsweighted_overall = 0;
		$sum_grades_overall = 0;
		$num_grades_overall = 0;
		$sum_ects_overall = 0;
		$sum_ects_positiv_overall = 0;
		$sum_sws_overall = 0;
		$sum_sws_positiv_overall = 0;

		// Calculate Sum and Average
		foreach ($courses['semester'] as $stsem => $row_lvs)
		{
			$grades = $this->getGrades($row_lvs['lvs']);

			$num_grades = 0;
			$sum_ects = 0;
			$sum_ects_positiv = 0;
			$sum_sws = 0;
			$sum_sws_positiv = 0;
			$sum_grades = 0;
			$notendurchschnitt = 0;
			$sum_gradeweighted = 0;
			$sum_ectsweighted = 0;

			foreach ($grades as $row)
			{
				if ($this->_grades[$row['note']]['notenwert'] != '')
				{
					$num_grades++;
					$sum_grades += $this->_grades[$row['note']]['notenwert'];

					$sum_ectsweighted += $row['ects'];
					$sum_gradeweighted += $row['ects'] * $this->_grades[$row['note']]['notenwert'];
				}
				$sum_ects += $row['ects'];
				$sum_sws += $row['sws'];
				if ($this->_grades[$row['note']]['positiv'])
				{
					$sum_ects_positiv += $row['ects'];
					$sum_sws_positiv += $row['sws'];
				}
			}
			if ($num_grades > 0)
				$notendurchschnitt = $sum_grades / $num_grades;
			else
				$notendurchschnitt = 0;

			if ($sum_ectsweighted > 0)
				$notendurchschnittgewichtet = $sum_gradeweighted / $sum_ectsweighted;
			else
				$notendurchschnittgewichtet = 0;

			$num_grades_overall += $num_grades;
			$sum_grades_overall += $sum_grades;
			$sum_gradeweighted_overall += $sum_gradeweighted;
			$sum_ectsweighted_overall += $sum_ectsweighted;
			$sum_ects_overall += $sum_ects;
			$sum_ects_positiv_overall += $sum_ects_positiv;
			$sum_sws_overall += $sum_sws;
			$sum_sws_positiv_overall += $sum_sws_positiv;

			$courses['semester'][$stsem]['data']['notendurchschnitt'] = number_format($notendurchschnitt, 2);
			$courses['semester'][$stsem]['data']['notendurchschnittgewichtet'] = number_format($notendurchschnittgewichtet, 2);
			$courses['semester'][$stsem]['data']['ectssumme'] = number_format($sum_ects,2);
			$courses['semester'][$stsem]['data']['ectssumme_positiv'] = number_format($sum_ects_positiv,2);
			$courses['semester'][$stsem]['data']['swssumme'] = number_format($sum_sws,2);
			$courses['semester'][$stsem]['data']['swssumme_positiv'] = number_format($sum_sws_positiv,2);
		}

		if ($num_grades_overall > 0)
			$notendurchschnitt = $sum_grades_overall / $num_grades_overall;
		else
			$notendurchschnitt = 0;

		if ($sum_ectsweighted_overall > 0)
			$notendurchschnittgewichtet = $sum_gradeweighted_overall / $sum_ectsweighted_overall;
		else
			$notendurchschnittgewichtet = 0;

		$courses['overall'] = array(
			'notendurchschnitt' => number_format($notendurchschnitt, 2),
			'notendurchschnittgewichtet' => number_format($notendurchschnittgewichtet, 2),
			'ectssumme' => $sum_ects_overall,
			'ectssumme_positiv' => $sum_ects_positiv_overall,
			'swssumme' => $sum_sws_overall,
			'swssumme_positiv' => $sum_sws_positiv_overall
		);
		return $courses;
	}

	/**
	 * Combines the Studyplan Courses recursively with the Grades of the Student
	 * Grades that are found in the Studyplan are marked, the others are added to a separate list
	 * @param $noten reference to array of all grades.
	 * @param $courses reference to array of all courses.
	 * @param $studiensemester_kurzbz Studiensemester of the Course and Grades
	 */
	private function fillNotenPart(&$noten, &$courses, $studiensemester_kurzbz)
	{
		foreach ($courses as $key => $value)
		{
			foreach ($noten->retval as $notenkey => $row_noten)
			{
				if ($row_noten->lehrveranstaltung_id == $value['lehrveranstaltung_id']
					&& $row_noten->studiensemester_kurzbz == $studiensemester_kurzbz)
				{
					$courses[$key]['note'] = $row_noten->note;
					$courses[$key]['datum'] = $row_noten->benotungsdatum;
					$noten->retval[$notenkey]->found = true;
				}
				if (isset($value['childs']))
					$this->fillNotenPart($noten, $courses[$key]['childs'], $studiensemester_kurzbz);
			}
		}
	}

	/**
	 * Checks if the Student is Assigned to this course and marks the course
	 * @param $zuordnung reference to array of all assigned courses.
	 * @param $courses reference to array of all courses.
	 * @param $studiensemester_kurzbz Studiensemester of the Course and Grades
	 */
	private function setZuordnung(&$zuordnung, &$courses)
	{
		$subtree_zugeordnet = false;
		foreach ($courses as $key => $value)
		{
			foreach ($zuordnung->retval as $zuordnungkey => $row_zuordnung)
			{
				if ($row_zuordnung->lehrveranstaltung_id == $value['lehrveranstaltung_id'])
				{
					$courses[$key]['zugeordnet'] = true;
					$subtree_zugeordnet = true;
				}
				if (isset($value['childs']))
				{
					if ($this->setZuordnung($zuordnung, $courses[$key]['childs']) === true)
					{
						$courses[$key]['zugeordnet'] = true;
						$subtree_zugeordnet = true;
					}
				}
			}
		}
		return $subtree_zugeordnet;
	}

	/**
	 * Reads all the Courses recursivly and Returns an Array with the Grades and ECTS
	 * @param $courses array of courses
	 * @return array with grades and ects
	 */
	public function getGrades($courses)
	{
		$grades = array();
		foreach ($courses as $row)
		{
			if (isset($row['note']) && $row['note'] != '')
			{
				$grades[] = array(
					'note' => $row['note'],
					'ects' => $row['ects'],
					'sws' => $row['sws']
				);
			}
			elseif (isset($row['zugeordnet']) && $row['zugeordnet'] == true && $row['lehrtyp_kurzbz']=='lv')
			{
				// ECTS und SWS mitzaehlen wenn die Person zugeordnet ist auch wenn noch keine Noten vorhanden ist.
				$grades[] = array(
					'note' => '',
					'ects' => $row['ects'],
					'sws' => $row['sws']
				);
			}

			if (isset($row['childs']))
			{
				$childgrades = $this->getGrades($row['childs']);
				$grades = array_merge($grades, $childgrades);
			}
		}
		return $grades;
	}

	/**
	 * Helper Function to Display recursive Courses
	 * @param $course array if courses
	 * @param $depth integer defines the number parent elements
	 */
	static function printRow($course, $depth)
	{
		$ci =& get_instance();
		$ci->load->view('person/gradelist/course.php',
			array(
				'course' => $course,
				'depth' => $depth
			)
		);

		if (isset($course['childs']))
		{
			foreach ($course['childs'] as $row_course)
				Gradelist::printRow($row_course, $depth + 1);
		}
	}
}
