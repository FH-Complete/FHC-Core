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
	const PROJEKTLISTE_PATH = 'addons/reports/cis/index.php?reportgruppe_id=32';
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
		$this->load->model('project/Projekt_ressource_model', 'ProjektRessourceModel');

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
 * f) Emplyoyees, who are projectleaders
 */
	public function sendMail()
	{
		$allMitarbeiter = $this->_getEmplyeeUids();

		$vorgesetzte_to_approve_vacation = $this->_getVorgesetztetoApproveVacationList();
		$vorgesetzte_to_approve_timesheets = $this->_getVorgesetztetoApproveTimesheetList();

		$mitarbeiter_to_send_timesheet_lastmonth = $this->_getEmployeeTimesheetList();
		$mitarbeiter_to_record_times_lastweek = $this->_getEmployeeLastWeeksTimeList();

		$mitarbeiter_without_zeitmodell = $this->_filterMitarbeiter();
		$mitarbeiter_projektleiter = $this->_getProjektleiter();

		$cnt_sup_to_approve_vacation = 0;
		$cnt_sup_to_approve_timesheets = 0;
		$cnt_ma_to_send_timesheet = 0;
		$cnt_ma_to_record_times_lastweek = 0;
		$cnt_ma_without_zeitmodell = 0;
		$cnt_ma_projektleitend = 0;
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
			//projektleiter
			if(array_key_exists($uid, $mitarbeiter_projektleiter))
			{
				$ma->EmpProLei = true;
				$cnt_ma_projektleitend++;
			}
			else
			{
				$ma->EmpProLei = false;
			}

			if($ma->SupVac || $ma->SupMonth || $ma->EmpMonth || $ma->EmpWeek || $ma->EmpZeitMod || $ma->EmpProLei)
			{
				array_push($mailingList, $ma);
				$cnt_mails_total++;
			}
		}
		$start = date("h:i:sa");

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
			$EmpProLei ='';

			if(array_key_exists($ma->uid, $mitarbeiter_projektleiter))
			{
				$projekteMa = implode(', ', $mitarbeiter_projektleiter[$ma->uid]);
			}

			//Generate Email Text
			$ma->SupVac ? $supVac = 'Du hast noch Urlaube freizugeben. Du findest die Urlaubsfreigabe unter:
				<a href="'.CIS_ROOT.self::URLAUBSFREIGABE_PATH.'">Urlaubstool</a><br><br>' : '';
			$ma->SupMonth ? $SupMonth = 'Du hast noch Monatslisten freizugeben. Du findest die Monatslistenfreigabe unter:
				<a href="'.CIS_ROOT.self::MONATSLISTEN_PATH.'">Monatslisten</a><br><br>' : '';
			$ma->EmpMonth ? $EmpMonth = 'Du musst noch die Monatsliste von letztem Monat abschicken.<br><br>' : '';
			$ma->EmpWeek ? $EmpWeek = 'Du musst noch Zeiten für letzte Woche eintragen.<br><br>' : '';
			$ma->EmpZeitMod ? $EmpZeitMod = 'Du hast noch kein Zeitmodell hinterlegt.<br><br>' : '';
			$ma->EmpProLei ? $EmpProLei = 'Bitte kontrolliere die Aufzeichnungen deiner Projekte ('. $projekteMa. '):
				<a href="'.CIS_ROOT.self::PROJEKTLISTE_PATH.'">Projektaufzeichnungen</a><br><br>' : '';


			// Prepare mail content
			$content_data_arr = array(
				'ma_name'       => $ma_name,
				'SupVac'        => $supVac,
				'SupMonth'      => $SupMonth,
				'EmpMonth'      => $EmpMonth,
				'EmpWeek'       => $EmpWeek,
				'EmpZeitMod'    => $EmpZeitMod,
				'EmpProLei'		=> $EmpProLei
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

		//Logs Viewer
		$this->logInfo('START Job Report: Infomail Zeiterfassung (' . $start . ')');
		$this->logInfo($cnt_sup_to_approve_vacation . " Urlaub(e) freizugeben");
		$this->logInfo($cnt_sup_to_approve_timesheets . " Monatsliste(n) zu bestaetigen");
		$this->logInfo($cnt_ma_to_send_timesheet ." Monatsliste(n) abzuschicken");
		$this->logInfo($cnt_ma_to_record_times_lastweek ." fehlende Zeitaufzeichnung(en) letzte Woche");
		$this->logInfo($cnt_ma_without_zeitmodell . " Zeitmodell(e) nicht hinterlegt ");
		$this->logInfo($cnt_ma_projektleitend . " projektleitende(r) Mitarbeiter*in");
		$this->logInfo($cnt_mails_total . " gesendete Mails Total");
		$this->logInfo("ENDE Job Report: Infomail Zeiterfassung (" . $end . ")");

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
		$toSend = array();
		if (hasData($mResult))
		{
			$mitarbeiterList = getData($mResult);
			$vorgesetzte = array();

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
		}
		elseif (isError($mResult))
		{
			show_error(getError($mResult));
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
		if (hasData($mResult))
		{
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
		}
		elseif (isError($mResult))
		{
			show_error(getError($mResult));
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
		$names = array();
		if (hasData($mResult))
		{
			$mitarbeiterList = getData($mResult);
			$cnt_timesheetsToSend = 0;

			foreach ($mitarbeiterList as $mitarbeiter)
			{
				$uid = $mitarbeiter->uid;
				if($this->MitarbeiterModel->isMitarbeiter($uid))
				{
					$names[$uid] = $uid;
					$cnt_timesheetsToSend++;
				}
			}
		}
		elseif (isError($mResult))
		{
			show_error(getError($mResult));
		}
		return $names;
	}

	/**
	 * Get all Mitarbeiter Names that have yet to record last weeks times
	 * @return array - array of Strings (mitarbeiter uids)
	 */
	private function _getEmployeeLastWeeksTimeList()
	{
		$mResult = $this->MitarbeiterModel->getPersonal(true, null, true);
		$mitarbeiter = getData($mResult);
		$zResult = $this->ZeitaufzeichnungModel->zeitaufzeichnungExistsForLastWeekList();
		$zeitaufzeichnungLastWeek = getData($zResult);
		$mitarbeiterLastWeekExists = array();
		$uids = array();

		if (hasData($zResult))
		{
			foreach ($zeitaufzeichnungLastWeek as $name)
			{
				$mitarbeiterLastWeekExists[] = $name->uid;
			}
		}
		elseif (isError($zResult))
		{
			show_error(getError($zResult));
		}

		if (hasData($mResult))
		{
			foreach ($mitarbeiter as $ma)
			{
				$uid = $ma->uid;
				if(!in_array($uid, $mitarbeiterLastWeekExists))
				{
					$uids[$uid] = $uid;
				}
			}
		}
		elseif (isError($mResult))
		{
			show_error(getError($mResult));
		}
		return $uids;
	}

	private function _getEmplyeeUids()
	{
		$mResult = $this->MitarbeiterModel->getEmployeesZeitaufzeichnungspflichtig();
		$mitarbeiter = getData($mResult);
		$mitarbeiterUIDs = array();

		if (hasData($mResult))
		{
			foreach ($mitarbeiter as $ma)
			{
				$mitarbeiterObj = new StdClass();
				$mitarbeiterObj->uid = $ma->mitarbeiter_uid;
				$mitarbeiterObj->SupVac = false;
				$mitarbeiterObj->SupMonth = false;
				$mitarbeiterObj->EmpMonth = false;
				$mitarbeiterObj->EmpWeek = false;
				$mitarbeiterObj->EmpZeitMod = false;

				array_push($mitarbeiterUIDs, $mitarbeiterObj);
			}
		}
		elseif (isError($mResult))
		{
			show_error(getError($mResult));
		}
		return $mitarbeiterUIDs;
	}


	private function _filterMitarbeiter()
	{
		$mResult = $this->MitarbeiterModel->getPersonal(true, null, true);
		$mitarbeiter = getData($mResult);

		$zResult = $this->TimesheetModel->getAllMissingZeitmodelle();
		$zeitmodelle = $zResult[1];

		$mitarbeiterWithoutZeitmodell = array();
		$uids = array();

		if (!is_null($zResult))
		{
			foreach ($zeitmodelle as $zm)
			{
				array_push($uids, strtolower($zm[0]));
			}
		}
		if (hasData($mResult))
		{
			foreach ($mitarbeiter as $ma)
			{
				$uid = $ma->uid;
				if(!in_array($uid, $uids))
				{
					$mitarbeiterWithoutZeitmodell[$uid] = $uid;
				}
			}
		}
		elseif (isError($mResult))
		{
			show_error(getError($mResult));
		}

		return $mitarbeiterWithoutZeitmodell;
	}

	private function _getProjektleiter()
	{
		$mResult = $this->MitarbeiterModel->getEmployeesZeitaufzeichnungspflichtig();
		$mitarbeiter = getData($mResult);

		$pResult = $this->ProjektRessourceModel->getProjektleiterActiveProjects();
		$projektleiter = getData($pResult);

		$projektleitendeMitarbeiter = array();
		if(hasData($pResult) && hasData($mResult))
		{
			foreach ($projektleiter as $pl)
			{
				foreach ($mitarbeiter as $ma)
				{
					if($pl->mitarbeiter_uid == $ma->mitarbeiter_uid)
					{
						$projektleitendeMitarbeiter[$pl->mitarbeiter_uid][]= $pl->titel;
					}
				}
			}
		}
		elseif (isError($mResult))
		{
			show_error(getError($mResult));
		}
		elseif (isError($pResult))
		{
			show_error(getError($pResult));
		}
		return $projektleitendeMitarbeiter;
	}
}
