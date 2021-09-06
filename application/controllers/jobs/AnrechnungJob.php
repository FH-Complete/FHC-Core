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
	const APPROVE_ANRECHNUNG_URI = '/lehre/anrechnung/ApproveAnrechnungUebersicht';
	
	const ANRECHNUNGSTATUS_APPROVED = 'approved';
	const ANRECHNUNGSTATUS_REJECTED = 'rejected';
	const ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_STGL = 'AnrechnungNotizSTGL';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');
		$this->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		
		$this->load->helper('url');
		$this->load->helper('hlp_sancho_helper');
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
	
	/**
	 * Deletes Zeugnisnoten 'angerechnet', when Anrechnung is rejected afterwards.
	 * E.g., when STGL first accepts, then withdraws and finally rejects the approvement.
	 */
	public function deleteAnrechnungGrades()
	{
		$this->logInfo('Start AnrechnungJob to delete Grades');
		
		// Get all Zeungisnoten,
		// WHERE note is angerechnet
		// AND Anrechnung was rejected AFTER the Zeugnisnote was created
		$qry = '
			SELECT DISTINCT ON (status.anrechnung_id) anrechnung_id,
				status.status_kurzbz AS "last_anrechnungstatus",
				status.insertamum AS "last_anrechnungstatus_insertamum",
				zeugnisnote.insertamum AS "zeugnisdatum_insertamum",
				student.student_uid,
				zeugnisnote.lehrveranstaltung_id,
				zeugnisnote.studiensemester_kurzbz,
				note
			FROM lehre.tbl_zeugnisnote zeugnisnote
			JOIN public.tbl_student student USING (student_uid)
			JOIN lehre.tbl_anrechnung anrechnung
				ON (zeugnisnote.lehrveranstaltung_id = anrechnung.lehrveranstaltung_id)
					AND (student.prestudent_id = anrechnung.prestudent_id)
					AND (zeugnisnote.studiensemester_kurzbz = anrechnung.studiensemester_kurzbz)
			JOIN lehre.tbl_anrechnung_anrechnungstatus status USING (anrechnung_id)
			WHERE note = 6
			AND status.insertamum > zeugnisnote.insertamum
			AND status.status_kurzbz = '. $this->db->escape(self::ANRECHNUNGSTATUS_REJECTED). '
			ORDER BY status.anrechnung_id, status.insertamum DESC
		';
		
		$db = new DB_Model();
		$result = $db->execReadOnlyQuery($qry);
		$cnt = 0;
		
		if (hasData($result))
		{
			$this->load->model('education/Zeugnisnote_model', 'ZeugnisnoteModel');
			
			foreach (getData($result) as $row)
			{
				// Delete Zeugnisnote
				$this->ZeugnisnoteModel->delete(array(
					'lehrveranstaltung_id' => $row->lehrveranstaltung_id,
					'student_uid' => $row->student_uid,
					'studiensemester_kurzbz' => $row->studiensemester_kurzbz
				));
				
				// Count up
				$cnt++;
			}
		}
		
		$this->logInfo('End AnrechnungJob to delete Grades', array('Number of Grades deleted: ' => $cnt));
	}
	
	// Send Sancho mail to STGL with yesterdays new Anrechnungen
	public function sendMailToSTGL()
	{
		$this->logInfo('Start AnrechnungJob to send emails to STGL about yesterdays new Anrechnungen.');
		
		// Get all yesterdays Anrechnungen, that did not process further than first status
		// (If Anrechnung is new, but STGL already started the process yesterday,
		// he does not need to be informed about this new Anrechnung anymore)
		$this->AnrechnungModel->addSelect('anrechnung_id, studiensemester_kurzbz, lv.studiengang_kz, lv.bezeichnung, vorname, nachname');
		$this->AnrechnungModel->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id');
		$this->AnrechnungModel->addJoin('public.tbl_student student', 'prestudent_id');
		$this->AnrechnungModel->addJoin('public.tbl_benutzer benutzer', 'ON (benutzer.uid = student.student_uid)');
		$this->AnrechnungModel->addJoin('public.tbl_person person', 'person_id');
		$this->AnrechnungModel->addOrder('lv.studiengang_kz, lv.bezeichnung');
		
		$result = $this->AnrechnungModel->loadWhere(
			'(lehre.tbl_anrechnung.insertamum)::date = (NOW() - INTERVAL \'24 HOURS\')::DATE
			AND 1 = (SELECT COUNT(*) FROM lehre.tbl_anrechnung_anrechnungstatus status WHERE status.anrechnung_id = tbl_anrechnung.anrechnung_id)'
		);
		
		// Exit if there are no Anrechnungen
		if (!$anrechnungen = getData($result)) {
			$this->logInfo('ABORTED: Sending emails to STGL about yesterdays new Anrechnungen aborted - No new Anrechnungen found.');
			exit;
		}
		
		$unique_studiengang_kz_arr = array_unique(array_column($anrechnungen, 'studiengang_kz'));
		
		foreach ($unique_studiengang_kz_arr as $studiengang_kz)
		{
			// Get STG bezeichnung
			$this->StudiengangModel->addSelect('UPPER( typ || kurzbz ) AS "stg_bezeichnung"');
			$studiengang_bezeichnung = $this->StudiengangModel->load($studiengang_kz)->retval[0]->stg_bezeichnung;
			
			// Get STGL mail address
			list ($to, $vorname) = self::_getSTGLMailAddress($studiengang_kz);
			
			// Get HTML table with new Anrechnungen of that STG plus amount of them
			list ($anrechnungen_amount, $anrechnungen_table) = self::_getSTGLMailDataTable($studiengang_kz, $anrechnungen);
			
			// Link to Antrag genehmigen dashboard
			$url =
				CIS_ROOT. 'cis/index.php?menu='.
				CIS_ROOT. 'cis/menu.php?content_id=&content='.
				CIS_ROOT. index_page(). self::APPROVE_ANRECHNUNG_URI;
			
			// Prepare mail content
			$body_fields = array(
				'vorname'       => $vorname,
				'studiengang'   => $studiengang_bezeichnung,
				'anzahl'        => $anrechnungen_amount,
				'datentabelle'  => $anrechnungen_table,
				'link'          => anchor($url, 'Anrechnungsanträge Übersicht')
			);
			
			// Send mail
			sendSanchoMail(
				'AnrechnungAntragStellen',
				$body_fields,
				$to,
				'Anerkennung nachgewiesener Kenntnisse: Neuer Antrag wurde gestellt'
			);
		}
		
		$this->logInfo('SUCCEDED: Sending emails to STGL about yesterdays new Anrechnungen succeded.');
	}
	
	/**
	 * Send Sancho mail to students, whose Anrechnungen were approved 24 hours ago.
	 */
	public function sendMailApproved(){
		
		$this->logInfo('Start AnrechnungJob to send emails to students, whose Anrechnungen were approved.');
		
		// Get all yesterdays approvements
		$this->AnrechnungModel->addSelect('student.student_uid, vorname, nachname, geschlecht, lv.bezeichnung');
		$this->AnrechnungModel->addJoin('lehre.tbl_anrechnung_anrechnungstatus status', 'anrechnung_id');
		$this->AnrechnungModel->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id');
		$this->AnrechnungModel->addJoin('public.tbl_student student', 'prestudent_id');
		$this->AnrechnungModel->addJoin('public.tbl_benutzer benutzer', 'ON (benutzer.uid = student.student_uid)');
		$this->AnrechnungModel->addJoin('public.tbl_person person', 'person_id');
		
		$result = $this->AnrechnungModel->loadWhere(
			'(status.insertamum)::date = (NOW() - INTERVAL \'24 HOURS\')::DATE AND
			status.status_kurzbz = '. $this->db->escape(self::ANRECHNUNGSTATUS_APPROVED)
		);
		
		// Exit if there are no approved Anrechnungen
		if (!hasData($result))
		{
			$this->logInfo('ABORTED sending emails to students, whose Anrechnungen were approved. No new approvements found.');
			exit;
		}
		
		// Loop through students
		foreach ($result->retval as $student)
		{
			$to = $student->student_uid. '@'. DOMAIN;
			
			$anrede = $student->geschlecht == 'w' ? 'Sehr geehrte Frau ' : 'Sehr geehrter Herr ';
			
			$text = 'Ihrem Antrag auf Anerkennung nachgewiesener Kenntnisse der Lehrveranstaltung "'.
				$student->bezeichnung. '" wurde stattgegeben.';
			
			// Prepare mail content
			$body_fields = array(
				'anrede_name'   => $anrede. $student->vorname. ' '. $student->nachname,
				'text'          => $text
			);
			
			// Send mail
			sendSanchoMail(
				'AnrechnungGenehmigen',
				$body_fields,
				$to,
				'Anerkennung nachgewiesener Kenntnisse: Ihr Antrag ist abgeschlossen'
			);
		}
	}
	
	/**
	 * Send Sancho mail to students, whose Anrechnungen were rejected 24 hours ago.
	 */
	public function sendMailRejected(){
		
		$this->logInfo('Start AnrechnungJob to send emails to students, whose Anrechnungen were rejected.');
		
		// Get all yesterdays rejections
		$this->AnrechnungModel->addSelect('student.student_uid, vorname, nachname, geschlecht, lv.bezeichnung, notiz.text');
		$this->AnrechnungModel->addJoin('lehre.tbl_anrechnung_anrechnungstatus status', 'anrechnung_id');
		$this->AnrechnungModel->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id');
		$this->AnrechnungModel->addJoin('public.tbl_student student', 'prestudent_id');
		$this->AnrechnungModel->addJoin('public.tbl_benutzer benutzer', 'ON (benutzer.uid = student.student_uid)');
		$this->AnrechnungModel->addJoin('public.tbl_person person', 'person_id');
		$this->AnrechnungModel->addJoin('public.tbl_notizzuordnung', 'anrechnung_id');
		$this->AnrechnungModel->addJoin('public.tbl_notiz notiz', 'notiz_id');
		
		$this->AnrechnungModel->addOrder('notiz.insertamum', 'DESC');
		$this->AnrechnungModel->addLimit(1);
		
		
		$result = $this->AnrechnungModel->loadWhere(
			'(status.insertamum)::date = (NOW() - INTERVAL \'24 HOURS\')::DATE AND
			status.status_kurzbz = '. $this->db->escape(self::ANRECHNUNGSTATUS_REJECTED). ' AND
			notiz.titel = '. $this->db->escape(self::ANRECHNUNG_NOTIZTITEL_NOTIZ_BY_STGL)
		);
		
		// Exit if there are no rejected Anrechnungen
		if (!hasData($result))
		{
			$this->logInfo('ABORTED sending emails to students, whose Anrechnungen were rejected. No new rejectments found.');
			exit;
		}
		
		// Loop through students
		foreach ($result->retval as $student)
		{
			$to = $student->student_uid. '@'. DOMAIN;
			
			$anrede = $student->geschlecht == 'w' ? 'Sehr geehrte Frau ' : 'Sehr geehrter Herr ';
			
			$text = <<<html
				wir haben Ihren Antrag auf Anerkennung nachgewiesener Kenntnisse geprüft und können die Lehrveranstaltung
				"$student->bezeichnung" leider nicht anrechnen, weil die Gleichwertigkeit nicht festgestellt werden konnte.<br><br>
				Begründung: $student->text
html;
			
			// Prepare mail content
			$body_fields = array(
				'anrede_name'   => $anrede. $student->vorname. ' '. $student->nachname,
				'text'          => $text
			);
			
			// Send mail
			sendSanchoMail(
				'AnrechnungGenehmigen',
				$body_fields,
				$to,
				'Anerkennung nachgewiesener Kenntnisse: Ihr Antrag ist abgeschlossen'
			);
		}
	
	}
	
	// Get STGL mail address
	private function _getSTGLMailAddress($studiengang_kz)
	{
		$result = $this->StudiengangModel->getLeitung($studiengang_kz);
		
		// Get STGL mail address
		if (hasData($result))
		{
			return array(
				$result->retval[0]->uid. '@'. DOMAIN,
				$result->retval[0]->vorname
			);
		}
		// If not available, get assistance mail address
		else
		{
			$result = $this->StudiengangModel->load($studiengang_kz);
			
			if (hasData($result))
			{
				return array(
					$result->retval[0]->email,
					''
				);
			}
		}
	}
	
	// Build HTML table with yesterdays new Anrechnungen of the given STG
	private function _getSTGLMailDataTable($studiengang_kz, $anrechnungen)
	{
		$html = '';
		$lv_bezeichnung = '';

		// Filter Anrechnungen of given STG
		$anrechnungen = array_filter(
				$anrechnungen,
				function ($anrechnung) use (&$studiengang_kz) {
					return $anrechnung->studiengang_kz == $studiengang_kz;
		});
		
		// Amount of Anrechnungen
		$amount = count($anrechnungen);

		// HTML table body
		$html .= '
			<table style="width: 60%; border-collapse: collapse;" border="1" cellpadding="5">
			<tbody>
		';
		
		foreach ($anrechnungen as $anrechnung)
		{
			// Head line for each LV bezeichnung
			if ($anrechnung->bezeichnung != $lv_bezeichnung)
			{
				$html .= '<tr><td><span><strong>' . $anrechnung->bezeichnung . '</strong></span></td></tr>';
			}
			
			$lv_bezeichnung = $anrechnung->bezeichnung;
			
			// Row for each Anrechnung / student
			$html .= '<tr><td><span>'. $anrechnung->vorname. ' '. $anrechnung->nachname. '</span></td></tr>';
		}
		
		$html .= '
			</tbody>
			</table>
		';
		
		return array($amount, $html);
	}
}
