<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2021, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

class AnrechnungJob extends JOB_Controller
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');
	}

	/**
	 * Sets the Grades in FAS to angerechnet if the request was successfull
	 * @return void
	 */
	public function setAnrechnungGrades()
	{
		$this->logInfo('Start Anrechnung Grades Job');
		// get all accepted requests that are not in the grades table yet

		// get all placement tests with incorrect studyplan
		$qry = "
		SELECT
			student_uid, lehrveranstaltung_id, studiensemester_kurzbz, genehmigt_von
		FROM
			lehre.tbl_anrechnung
			JOIN public.tbl_student USING(prestudent_id)
		WHERE
			genehmigt_von is not null
			AND EXISTS(
				SELECT 1 FROM lehre.tbl_anrechnung_anrechnungstatus
				WHERE anrechnung_id = tbl_anrechnung.anrechnung_id
				AND status_kurzbz='approved'
				AND datum>=now()-'5 days'::interval
			)
			AND NOT EXISTS(
				SELECT 1 FROM lehre.tbl_zeugnisnote
				WHERE
					lehrveranstaltung_id = tbl_anrechnung.lehrveranstaltung_id
					AND studiensemester_kurzbz = tbl_anrechnung.studiensemester_kurzbz
					AND student_uid = tbl_student.student_uid
			)
		";

		$db = new DB_Model();
		$result_grades = $db->execReadOnlyQuery($qry);

		$cnt = 0;
		if (hasData($result_grades))
		{
			$grades = getData($result_grades);
			foreach ($grades as $anrechnung)
			{
				$cnt++;
				// Set zeugnisnote to angerechnet (= note 6)
				$ret = $this->ZeugnisnoteModel->insert(array(
						'lehrveranstaltung_id' => $anrechnung->lehrveranstaltung_id,
						'student_uid' => $anrechnung->student_uid,
						'studiensemester_kurzbz' => $anrechnung->studiensemester_kurzbz,
						'uebernahmedatum' => (new DateTime())->format('Y-m-d H:m:i'),
						'benotungsdatum' => (new DateTime())->format('Y-m-d H:m:i'),
						'note' => 6,
						'insertvon' => $anrechnung->genehmigt_von,
						'bemerkung' => 'Digitale Anrechnung'
					)
				);
			}
		}
		$this->logInfo('End Anrechnung Grades Job', array('Number of Grades added'=>$cnt));
	}
}
