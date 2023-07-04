<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

use \DateTime as DateTime;
// TODO(chris): Achtung not working

class AntragJob extends JOB_Controller
{
	/**
	 * API constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Configs
		$this->load->config('studierendenantrag');

		// Loads SanchoHelper
		$this->load->helper('hlp_sancho_helper');

		//Load Model
		$this->load->model('education/Studierendenantrag_model', 'StudierendenantragModel');
		$this->load->model('education/Studierendenantragstatus_model', 'StudierendenantragstatusModel');
		$this->load->model('education/Pruefung_model', 'PruefungModel');
		$this->load->model('person/Kontakt_model', 'KontaktModel');
	}

	/**
	 * Send reminder to Assistant for Wiedereinstieg Unterbrecher
	 *
	 */
	public function sendReminderWiedereinstieg()
	{
		$now = new DateTime();
		$modifier = $this->config->item('unterbrechung_job_remind_wiedereinstieg_date_modifier');
		if (!$modifier)
			return $this->logError('Konnte Job nicht starten: Config "unterbrechung_job_remind_wiedereinstieg_date_modifiers" nicht gesetzt');

		$end = new DateTime();
		$end->modify($modifier);

		$this->logInfo(sprintf(
			'Start Job sendReminderWiedereinstieg (Wiedereinstieg zwischen %s - %s)',
			$now->format('Y-m-d'),
			$end->format('Y-m-d')
		));

		$result = $this->StudierendenantragModel->getAntraegeWhereWiedereinstiegBetween($now, $end);

		if(isError($result))
		{
			$this->logError(getError($result));
			$this->logInfo('Ende Job sendReminderWiedereinstieg');
			return;
		}

		$antraege = getData($result) ?: [];
		$count = 0;
		foreach ($antraege as $antrag)
		{
			$datum = new DateTime($antrag->datum_wiedereinstieg);
			$data = array(
				'prestudent' => $antrag->prestudent_id,
				'name' => trim($antrag->vorname . ' '. $antrag->nachname),
				'datum_wiedereinstieg' => $datum->format('d.m.Y')
			);

			if(sendSanchoMail('Sancho_Mail_Antrag_U_Reminder', $data, $antrag->email, 'Reminder: Unterbrechung Wiedereinstieg'))
			{
				$count++;
				$this->StudierendenantragstatusModel->insert([
					'studierendenantrag_id' => $antrag->studierendenantrag_id,
					'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_REMINDERSENT,
					'insertvon' => 'AntragJob'
				]);
			}
		}
		$this->logInfo($count . ' Reminder gesendet - Ende Job sendReminderWiedereinstieg');
	}

	/**
	 * Set Wiederholer after deadline to Abbrecher
	 *
	 */
	public function handleWiederholerDeadline()
	{
		$this->logInfo('Start Job handleWiederholerDeadline');

		$this->load->library('PrestudentLib');

		$insertvon = $this->config->item('antrag_job_systemuser');
		if (!$insertvon) {
			$this->logError('Config "antrag_job_systemuser" nicht gesetzt');
			$this->logInfo('Ende Job handleWiederholerDeadline');
			return;
		}

		$modifier_deadline = $this->config->item('wiederholung_job_deadline_date_modifier');
		if (!$modifier_deadline) {
			$this->logError('Config "wiederholung_job_deadline_date_modifier" nicht gesetzt');
			$this->logInfo('Ende Job handleWiederholerDeadline');
			return;
		}

		$dateDeadline = new DateTime();
		$dateDeadline->sub(DateInterval::createFromDateString($modifier_deadline));

		$result = $this->PruefungModel->getAllPrestudentsWhereCommitteeExamFailed(
				[
					null,
					Studierendenantragstatus_model::STATUS_REQUESTSENT_1,
					Studierendenantragstatus_model::STATUS_REQUESTSENT_2
				],
				$dateDeadline,
				null
			);

		if(isError($result))
		{
			$this->logError(getError($result));
		}
		else
		{
			$prestudents = getData($result) ?: [];
			$count = 0;

			$prestudents = $this->prestudentsGetUnique($prestudents);

			foreach ($prestudents as $prestudent)
			{
				// TODO(chris): DEBUG REMOVE!
				if ($prestudent->studiensemester_kurzbz == 'WS2021')
				var_dump([$prestudent->prestudent_id, $prestudent->studiensemester_kurzbz, $prestudent->lvbezeichnung]);
				$result = success();
				// TODO(chris): find out how to filter old/wrong datasets!!!
				#$result = $this->prestudentlib->setAbbrecher($prestudent->prestudent_id, $prestudent->studiensemester_kurzbz, $insertvon);
				if (isError($result))
					$this->logError(getError($result));
				else
					$count++;
			}
			$this->logInfo($count . " Students set to Abbrecher");
		}

		$this->logInfo('Ende Job handleWiederholerDeadline');

	}

	/**
	 * Set Abmeldungen after deadline to Abbrecher
	 *
	 */
	public function handleAbmeldungenStglDeadline()
	{
		$this->logInfo('Start Job handleAbmeldungenStglDeadline');

		$this->load->library('AntragLib');

		$insertvon = $this->config->item('antrag_job_systemuser');
		if (!$insertvon) {
			$this->logError('Config "antrag_job_systemuser" nicht gesetzt');
			$this->logInfo('Ende Job handleAbmeldungenStglDeadline');
			return;
		}

		$modifier_deadline = $this->config->item('abmeldung_job_deadline_date_modifier');
		if (!$modifier_deadline) {
			$this->logError('Config "abmeldung_job_deadline_date_modifier" nicht gesetzt');
			$this->logInfo('Ende Job handleAbmeldungenStglDeadline');
			return;
		}

		$dateDeadline = new DateTime();
		$dateDeadline->sub(DateInterval::createFromDateString($modifier_deadline));

		$result = $this->StudierendenantragModel->getWithLastStatusWhere(
				[
					'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED_STGL,
					's.insertamum <=' => $dateDeadline->format('c')
				]
			);

		if(isError($result))
		{
			$this->logError(getError($result));
		}
		else
		{
			$antraege = getData($result) ?: [];
			$count = 0;

			foreach ($antraege as $antrag)
			{
				$result = $this->antraglib->approveAbmeldung([$antrag->studierendenantrag_id], $insertvon);

				if (isError($result))
					$this->logError(getError($result));
				else
					$count++;
			}
			$this->logInfo($count . " Students set to Abbrecher");
		}
		$this->logInfo('Ende Job handleAbmeldungenStglDeadline');
	}

	/**
	 * Send Request to Student do Decide between Wiederholung and Verzicht
	 *
	 */
	public function sendAufforderungWiederholer()
	{
		$this->logInfo('Start Job sendAufforderungWiederholer');

		$modifier_request_1 = $this->config->item('wiederholung_job_request_1_date_modifier');
		$modifier_request_2 = $this->config->item('wiederholung_job_request_2_date_modifier');
		$modifier_deadline = $this->config->item('wiederholung_job_deadline_date_modifier');

		if ($modifier_deadline)
		{
			$dateDeadline = new DateTime();
			$dateDeadline->sub(DateInterval::createFromDateString($modifier_deadline));
		}
		else
			$dateDeadline = null;

		//first request
		if ($modifier_request_1)
			$this->sendReminder(
				'Request1',
				null,
				Studierendenantragstatus_model::STATUS_REQUESTSENT_1,
				$dateDeadline,
				$modifier_request_1,
				$modifier_deadline,
				'Aufforderung: Bekanntgabe Wiederholung'
			);
		else
			$this->logError('Config "wiederholung_job_request_1_date_modifier" nicht gesetzt');

		//second request
		if ($modifier_request_2)
			$this->sendReminder(
				'Request2',
				Studierendenantragstatus_model::STATUS_REQUESTSENT_1,
				Studierendenantragstatus_model::STATUS_REQUESTSENT_2,
				$dateDeadline,
				$modifier_request_2,
				$modifier_deadline,
				'Reminder Aufforderung: Bekanntgabe Wiederholung'
			);
		else
			$this->logError('Config "wiederholung_job_request_2_date_modifier" nicht gesetzt');

		$this->logInfo('Ende Job sendAufforderungWiederholer');
	}

	protected function prestudentsGetUnique($prestudents) {
		$result = [];
		foreach ($prestudents as $prestudent) {
			if (!isset($result[$prestudent->prestudent_id]))
				$result[$prestudent->prestudent_id] = $prestudent;
			else {
				if ($result[$prestudent->prestudent_id]->datum > $prestudent->datum)
					$result[$prestudent->prestudent_id] = $prestudent;
			}
		}
		return $result;
	}

	protected function sendReminder($name, $status_from, $status_to, $deadline, $date_modifier, $modifier_deadline, $subject)
	{
		$this->logInfo('Start Job sendAufforderungWiederholer ' . $name);

		$dateStichtag = new DateTime();
		$dateStichtag->sub(DateInterval::createFromDateString($date_modifier));

		$result = $this->PruefungModel->getAllPrestudentsWhereCommitteeExamFailed($status_from, $dateStichtag, $deadline);

		if(isError($result))
		{
			$this->logError(getError($result));
		}
		else
		{
			$prestudents = getData($result) ?: [];
			$count = 0;

			$prestudents = $this->prestudentsGetUnique($prestudents);

			foreach ($prestudents as $prestudent)
			{
				$stg_kz = $prestudent->studiengang_kz;
				if (in_array($stg_kz, $this->config->item('stgkz_blacklist_wiederholung')))
					continue;
				$url = site_url('lehre/Studierendenantrag/wiederholung/' . $prestudent->prestudent_id);
				$email = $this->KontaktModel->getZustellKontakt($prestudent->person_id, ['email']);
				if (isError($email)) {
					$this->logError(getError($email));
				} else {
					$email = getData($email);

					if (!$email) {
						$this->logError('No email contact found for person_id: ' . $prestudent->person_id);
					}
					else
					{
						$email = current($email)->kontakt;
						$fristende = new DateTime($prestudent->datum);
						$fristende->add(DateInterval::createFromDateString($modifier_deadline));

						$dataMail = array(
							'name'=> trim($prestudent->vorname . ' '. $prestudent->nachname),
							'pers_kz'=> $prestudent->matrikelnr,
							'studiengang' => $prestudent->bezeichnung,
							'lvbezeichnung' => $prestudent->lvbezeichnung,
							'datum_kp' => $prestudent->datum,
							'studiensemester'=> $prestudent->studiensemester_kurzbz,
							'orgform'=> $prestudent->orgform,
							'url' => $url,
							'fristablauf' => $fristende->format('d.m.Y')
						);
						if(sendSanchoMail('Sancho_Mail_Antrag_W_' . $name, $dataMail, $email, $subject))
						{
							$antrag_id = null;
							$result = $this->StudierendenantragModel->loadWhere([
								'prestudent_id' => $prestudent->prestudent_id,
								'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG
							]);
							if (isError($result))
								$this->logError(getError($result));
							elseif (hasData($result))
								$antrag_id = current(getData($result) ?: []) -> studierendenantrag_id;
							if ($antrag_id == null)
							{
								$result = $this->StudierendenantragModel->insert([
									'prestudent_id' => $prestudent->prestudent_id,
									'studiensemester_kurzbz'=> $prestudent->studiensemester_kurzbz,
									'datum' => date('c'),
									'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG,
									'insertvon' => 'AntragJob'
								]);
								if (isError($result))
									$this->logError(getError($result));
								else
									$antrag_id = getData($result);
							}
							if ($antrag_id)
							{
								$result = $this->StudierendenantragstatusModel->insert([
									'studierendenantrag_id' => $antrag_id,
									'studierendenantrag_statustyp_kurzbz' => $status_to,
									'insertvon' => 'AntragJob'
								]);
								if (isError($result))
									$this->logError(getError($result));
							}
							$count++;
						}
					}
				}
			}
			$this->logInfo($count . " Mails '" . $subject . "' sent");
		}
		$this->logInfo('Ende Job sendAufforderungWiederholer ' . $name);
	}



	// TODO(chris): REMOVE DEBUG!

	/**
	 * Writes a cronjob info log
	 */
	protected function logInfo($response, $parameters = null)
	{
		echo $response . "\n";
	}

	/**
	 * Writes a cronjob debug log
	 */
	protected function logDebug($response, $parameters = null)
	{
		echo $response . "\n";
	}

	/**
	 * Writes a cronjob warning log
	 */
	protected function logWarning($response, $parameters = null)
	{
		echo $response . "\n";
	}

	/**
	 * Writes a cronjob error log
	 */
	protected function logError($response, $parameters = null)
	{
		echo $response . "\n";
	}


}
