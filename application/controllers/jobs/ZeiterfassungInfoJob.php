<?php
/**
 * FH-Complete
 *
 * @package            FHC-API
 * @author             FHC-Team
 * @copyright  Copyright (c) 2016, fhcomplete.org
 * @license            GPLv3
 * @link               http://fhcomplete.org
 * @since              Version 1.0
 * @filesource
 *
 * Cronjobs to be run for sending reminder-emails about Zeiterfassung.
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class ZeiterfassungInfoJob extends JOB_Controller
{
	const URLAUBSFREIGABE_PATH = 'cis/private/profile/urlaubsfreigabe.php';
	const MONATSLISTEN_PATH = 'addons/casetime/cis/timesheet_overview.php';
	/*	* Constructor	*/
	public function __construct()
	{
		parent::__construct();

		// Load models
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('ressource/Timesheet_model', 'TimesheetModel');
		$this->load->model('ressource/Zeitaufzeichnung_model', 'ZeitaufzeichnungModel');
		$this->load->model('ressource/Zeitsperre_model', 'ZeitsperreModel');
		$this->load->model('system/Benutzerrolle_model', 'BenutzerrolleModel');
		$this->load->model('person/Person_model', 'PersonModel');

		// Load libraries
		$this->load->library('PermissionLib');

		// Load helpers
		$this->load->helper('hlp_sancho_helper');
	}

/**
 * Send Sancho Reminder eMail to:
 * a) Supervisors, who have to approve vacation
 * b) Supervisors, who have to approve timesheets
 * c) Employees, who did not send last months timesheet yet
 * d) Employees, who have not recorded their working hours last week
 * e) Employees, who do not have a "Zeitmodel" yet
 */
	public function sendMail()
	{
		$allMitarbeiter = $this->_getEmplyeeUids();

		$vorgesetzte_to_approve_vacation = $this->_getVorgesetztetoApproveVacationList();
		$vorgesetzte_to_approve_timesheets = $this->_getVorgesetztetoApproveTimesheetList();

		$mitarbeiter_to_send_timesheet_lastmonth = $this->_getEmployeeTimesheetList();
		$mitarbeiter_to_record_times_lastweek = $this->_getEmployeeLastWeeksTimeList();

		$mitarbeiter_without_zeitmodell = $this->_filterMitarbeiter();

		$cnt_sup_to_approve_vacation = 0;
		$cnt_sup_to_approve_timesheets = 0;
		$cnt_ma_to_send_timesheet = 0;
		$cnt_ma_to_record_times_lastweek = 0;
		$cnt_ma_without_zeitmodell = 0;
		$cnt_mails_total = 0;

		$mailingList = array();

		foreach ($allMitarbeiter as $ma)
		{
			$uid = $ma->uid;
			if(array_key_exists($uid, $vorgesetzte_to_approve_vacation))
			{
				$ma->SupVac = true;
				$cnt_sup_to_approve_vacation++;
			}
			else
			{
				$ma->SupVac = false;
			}
			if(array_key_exists($uid, $vorgesetzte_to_approve_timesheets))
			{
				$ma->SupMonth = true;
				$cnt_sup_to_approve_timesheets++;
			}
			else
			{
				$ma->SupMonth = false;
			}
			if(array_key_exists($uid, $mitarbeiter_to_send_timesheet_lastmonth))
			{
				$ma->EmpMonth = true;
				$cnt_ma_to_send_timesheet++;
			}
			else
			{
				$ma->EmpMonth = false;
			}
			if(array_key_exists($uid, $mitarbeiter_to_record_times_lastweek))
			{
				$ma->EmpWeek = true;
				$cnt_ma_to_record_times_lastweek++;
			}
			else
			{
				$ma->EmpWeek = false;
			}
			if(array_key_exists($uid, $mitarbeiter_without_zeitmodell))
			{
				$ma->EmpZeitMod = true;
				$cnt_ma_without_zeitmodell++;
			}
			else
			{
				$ma->EmpZeitMod = false;
			}

			if($ma->SupVac || $ma->SupMonth || $ma->EmpMonth || $ma->EmpWeek || $ma->EmpZeitMod)
			{
				array_push($mailingList, $ma);
				$cnt_mails_total++;
			}
		}
		$start = date("h:i:sa");

		print_r(date("h:i:sa\n"));
		// Loop through 'container' of mail recipients
		foreach ($mailingList as $ma)
		{
			// Set mail recipient
			$to = $ma->uid.'@'. DOMAIN;

			$ma_name = getData($this->PersonModel->getFullName($ma->uid));
			$supVac ='';
			$SupMonth ='';
			$EmpMonth ='';
			$EmpWeek ='';
			$EmpZeitMod ='';

			//Generate Email Text
			$ma->SupVac ? $supVac = 'Du hast noch Urlaube freizugeben. Du findest die Urlaubsfreigabe unter:
				<a href="'.CIS_ROOT.'cis/index.php?menu='.CIS_ROOT. 'cis/menu.php?content_id=&content='.CIS_ROOT.self::URLAUBSFREIGABE_PATH.'">Urlaubstool</a><br><br>' : '';
			$ma->SupMonth ? $SupMonth = 'Du hast noch Monatslisten freizugeben. Du findest die Monatslistenfreigabe unter:
				<a href="'.CIS_ROOT.'cis/index.php?menu='.CIS_ROOT. 'cis/menu.php?content_id=&content='.CIS_ROOT.self::MONATSLISTEN_PATH.'">Monatslisten</a><br><br>' : '';
			$ma->EmpMonth ? $EmpMonth = 'Du musst noch die Monatsliste von letztem Monat abschicken.<br><br>' : '';
			$ma->EmpWeek ? $EmpWeek = 'Du musst noch Zeiten für letzte Woche eintragen.<br><br>' : '';
			$ma->EmpZeitMod ? $EmpZeitMod = 'Du hast noch kein Zeitmodell hinterlegt.<br><br>' : '';

			// Prepare mail content
			$content_data_arr = array(
				'ma_name'        => $ma_name,
				'SupVac'        => $supVac,
				'SupMonth'      => $SupMonth,
				'EmpMonth'      => $EmpMonth,
				'EmpWeek'       => $EmpWeek,
				'EmpZeitMod'    => $EmpZeitMod
			);

			sendSanchoMail(
				'Sancho_InfoMailZeiterfassung',
				$content_data_arr,
				$to,
				'Zeiterfassung Erinnerung',
				DEFAULT_SANCHO_HEADER_IMG,
				DEFAULT_SANCHO_FOOTER_IMG
			);
		}
		$end = date("h:i:sa");
		print_r($end."\n");
		print_r(($start-$end."\n"));

		print_r("___________________________________________________________________");
		print_r("\n");
		print_r("|");
		print_r("\n");
		print_r("| Job Report: Infomail Zeiterfassung\n");
		print_r("|");
		print_r("\n");
		print_r("| Anzahl Urlaube freizugeben: " . $cnt_sup_to_approve_vacation);
		print_r("\n");
		print_r("| Anzahl Monatslisten zu bestätigen: ". $cnt_sup_to_approve_timesheets);
		print_r("\n");
		print_r("| Anzahl Monatslisten abzuschicken: " . $cnt_ma_to_send_timesheet);
		print_r("\n");
		print_r("| Anzahl fehlende Zeitaufzeichnungen letzte Woche: " . $cnt_ma_to_record_times_lastweek);
		print_r("\n");
		print_r("|");
		print_r("\n");
		print_r("| Anzahl kein hinterlegtes Zeitmodell: " . $cnt_ma_without_zeitmodell);
		print_r("\n");
		print_r("| Anzahl gesendeter Mails Total: " . $cnt_mails_total);
		print_r("\n");
		print_r("|");
		print_r("__________________________________________________________________");
		print_r("\n");

		return true;
	}

//******************************************************************************************************************
//      PRIVATE FUNCTIONS
//******************************************************************************************************************

	/**
	 * Get all Supervisors that have yet to approve Vacations of Emploees
	 * @return array - keys: supervisor name, values: number of emploees with pending vacation approval
	 */
	private function _getVorgesetztetoApproveVacationList()
	{
		$mResult = $this->ZeitsperreModel->getMitarbeiterListWithPendingVacation();
		$mitarbeiterList = getData($mResult);
		$vorgesetzte = array();
		$toSend = array();

		foreach ($mitarbeiterList as $mitarbeiter)
		{
			$mitarbeiter_uid = $mitarbeiter->mitarbeiter_uid;
			$vorgesetzte[] = getData($this->MitarbeiterModel->getVorgesetzte($mitarbeiter_uid));
		}

		foreach ($vorgesetzte as $v)
		{
			if(!(is_null($v)))
			{
				foreach ($v as $obj)
				{
					$name = $obj->vorgesetzter;
					if (!(is_null($name)) && !array_key_exists($name, $toSend))
					{
						$toSend[$name] = 1;
					}
					else
					{
						$toSend[$name] += 1;
					}
				}
			}
		}
		return $toSend;
	}

	/**
	 * Get all Supervisors that have yet to approve Timesheets of Emploees
	 * @return array - keys: supervisor name, values: number of emploees with pending timesheet approval
	 */
	private function _getVorgesetztetoApproveTimesheetList()
	{
		$mResult = $this->TimesheetModel->getPendingTimesheets();
		$mitarbeiterList = getData($mResult);
		$vorgesetzte = array();
		$toSend = array();

		foreach ($mitarbeiterList as $mitarbeiter)
		{
			$uid = $mitarbeiter->uid;
			$vorgesetzte[] = getData($this->MitarbeiterModel->getVorgesetzte($uid));
		}

		foreach ($vorgesetzte as $v)
		{
			if (!is_null($v))
			{
				foreach ($v as $obj)
				{
					$name = $obj->vorgesetzter;
					if (!array_key_exists($name, $toSend))
					{
						$toSend[$name] = 1;
					}
					else
					{
						$toSend[$name] += 1;
					}
				}
			}
		}
		return $toSend;
	}

	/**
	 * Get all Mitarbeiter Names that have yet to send Timesheets of Last Month
	 * @return array - array of Strings (mitarbeiter uids)
	 */
	private function _getEmployeeTimesheetList()
	{
		$mResult = $this->TimesheetModel->getUidofMissingTimesheetsLastMonth();
		$mitarbeiterList = getData($mResult);
		$cnt_timesheetsToSend = 0;

		$names = array();

		foreach ($mitarbeiterList as $mitarbeiter)
		{
			$uid = $mitarbeiter->uid;
			if($this->MitarbeiterModel->isMitarbeiter($uid))
			{
				$names[$uid] = $uid;
				$cnt_timesheetsToSend++;
			}
		}
		return $names;
	}

	/**
	 * Get all Mitarbeiter Names that have yet to record last weeks times
	 * @return array - array of Strings (mitarbeiter uids)
	 */
	private function _getEmployeeLastWeeksTimeList()
	{
		$mitarbeiter = $this->MitarbeiterModel->getPersonal(true, null, true)->retval;
		$zeitaufzeichnungLastWeek = $this->ZeitaufzeichnungModel->zeitaufzeichnungExistsForLastWeekList()->retval;
		$mitarbeiterLastWeekExists = array();
		$uids = array();

		foreach ($zeitaufzeichnungLastWeek as $name)
		{
			$mitarbeiterLastWeekExists[] = $name->uid;
		}

		foreach ($mitarbeiter as $ma)
		{
			$uid = $ma->uid;
			if(!in_array($uid,$mitarbeiterLastWeekExists))
			{
				$uids[$uid] = $uid;
			}
		}
		return $uids;
	}

	private function _getEmplyeeUids()
	{
		$mitarbeiter = $this->MitarbeiterModel->getEmployeesZeitaufzeichnungspflichtig()->retval;
		$mitarbeiterUIDs = array();

		foreach ($mitarbeiter as $ma)
		{
			$mitarbeiterObj = new StdClass();
			$mitarbeiterObj->uid = $ma->mitarbeiter_uid;
			//$mitarbeiterObj->vorname = $ma->vorname;
			$mitarbeiterObj->SupVac = false;
			$mitarbeiterObj->SupMonth = false;
			$mitarbeiterObj->EmpMonth = false;
			$mitarbeiterObj->EmpWeek = false;
			$mitarbeiterObj->EmpZeitMod = false;

			array_push($mitarbeiterUIDs, $mitarbeiterObj);
		}

		return $mitarbeiterUIDs;
	}


	private function _filterMitarbeiter()
	{
		$mitarbeiter = $this->MitarbeiterModel->getPersonal(true, null, true)->retval;
		$mResult = $this->TimesheetModel->getAllMissingZeitmodelle();
		$mResult = $mResult[1];
		$mitarbeiterWithoutZeitmodell = array();
		$uids = array();

		foreach ($mResult as $ma)
		{
			array_push($uids, strtolower($ma[0]));
		}

		foreach ($mitarbeiter as $ma)
		{
			$uid = $ma->uid;
			if(!in_array($uid, $uids))
			{
				$mitarbeiterWithoutZeitmodell[$uid] = $uid;
			}
		}
		return $mitarbeiterWithoutZeitmodell;
	}
}
