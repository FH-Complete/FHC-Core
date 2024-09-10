<?php

if (!defined("BASEPATH")) exit("No direct script access allowed");

use \DateTime as DateTime;

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

		$this->load->library('AntragLib');

		// Load Model
		$this->load->model('education/Studierendenantrag_model', 'StudierendenantragModel');
		$this->load->model('education/Studierendenantragstatus_model', 'StudierendenantragstatusModel');
		$this->load->model('education/Pruefung_model', 'PruefungModel');
		$this->load->model('person/Kontakt_model', 'KontaktModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->loadPhrases([
			'lehre'
		]);
	}

	/**
	 * Send infomail to Stgl
	 */
	public function sendStglSammelmail()
	{
		$this->load->model('person/Person_model', 'PersonModel');

		$this->logInfo('Start Job sendStglSammelmail');

		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->StudierendenantragModel->addJoin('public.tbl_prestudent', 'prestudent_id');
		$this->db->group_start();
		$this->db->where('typ', Studierendenantrag_model::TYP_ABMELDUNG);
		$this->db->where('campus.get_status_studierendenantrag(studierendenantrag_id)', Studierendenantragstatus_model::STATUS_CREATED);
		$this->db->group_end();

		$this->db->or_group_start();
		$this->db->where('typ', Studierendenantrag_model::TYP_ABMELDUNG_STGL);
		$this->db->where('campus.get_status_studierendenantrag(studierendenantrag_id)', Studierendenantragstatus_model::STATUS_CREATED);
		$this->db->group_end();

		$this->db->or_group_start();
		$this->db->where('typ', Studierendenantrag_model::TYP_UNTERBRECHUNG);
		$this->db->where('campus.get_status_studierendenantrag(studierendenantrag_id)', Studierendenantragstatus_model::STATUS_CREATED);
		$this->db->group_end();

		$this->db->or_group_start();
		$this->db->where('typ', Studierendenantrag_model::TYP_WIEDERHOLUNG);
		$this->db->where('campus.get_status_studierendenantrag(studierendenantrag_id)', Studierendenantragstatus_model::STATUS_LVSASSIGNED);
		$this->db->group_end();

		$result =  $this->StudierendenantragModel->load();
		if(isError($result))
			return $this->logError(getError($result));

		if(!hasData($result))
			return $this->logInfo('End Job sendStglSammelmail: 0 Mails sent');

		$antraege = getData($result);

		$stgs = array();
		$stgLeitungen = array();

		foreach ($antraege as $antrag)
		{
			if (!isset($stgs[$antrag->studiengang_kz]))
			{
				$result = $this->StudiengangModel->getLeitung($antrag->studiengang_kz);
				if (isError($result))
				{
					$this->logError(getError($result));
					continue;
				}
				if (!hasData($result))
				{
					$this->logError('Keine Leitung für Studiengang ' . $antrag->studiengang_kz . ' gefunden!');
					continue;
				}

				$leitung = current(getData($result));
				if (!isset($stgLeitungen[$leitung->uid]))
				{
					$stgLeitungen[$leitung->uid] = [ 'Details' => $leitung, 'stgs' => [] ];
				}
				$stgLeitungen[$leitung->uid]['stgs'][] = $antrag->studiengang_kz;

				$result = $this->StudierendenantragModel->getStgAndSem($antrag->studierendenantrag_id);
				if (isError($result))
				{
					$this->logError(getError($result));
					continue;
				}
				if (!hasData($result))
				{
					$this->logError('Keine Details für Studiengang ' . $antrag->studiengang_kz . ' gefunden!');
					continue;
				}
				$details = current(getData($result));

				$stgs[$antrag->studiengang_kz] = [
					'Abmeldung' => [],
					'Unterbrechung' => [],
					'Wiederholung' => [],
					'Details' => $details
				];
			}
			$stgs[$antrag->studiengang_kz][str_replace('Stgl', '', $antrag->typ)] = $antrag;
		}

		$this->load->model('system/Sprache_model', 'SpracheModel');
		$result = $this->SpracheModel->loadWhere(['content' => true]);
		if (isError($result)) {
			$this->logError(getError($result));
			$languages = [DEFAULT_LANGUAGE];
		} elseif (!hasData($result)) {
			$languages = [DEFAULT_LANGUAGE];
		} else {
			$languages = array_map(function ($row) {
				return $row->sprache;
			}, getData($result));
		}

		$count = 0;
		foreach ($stgLeitungen as $leitung)
		{
			$data = [
				'name' => trim($leitung['Details']->vorname . ' ' . $leitung['Details']->nachname),
				'vorname' => $leitung['Details']->vorname,
				'nachname' => $leitung['Details']->nachname
			];

			foreach ($languages as $lang) {
				unset($this->p);
				$this->loadPhrases(['studierendenantrag'], $lang);

				$table = '';
				foreach ($leitung['stgs'] as $studiengang_kz) {
					$rows = '';
					$stg = $stgs[$studiengang_kz];
					foreach (['Abmeldung', 'Unterbrechung', 'Wiederholung'] as $typ) {
						$c = count($stg[$typ]);
						if ($c) {
							$rows .= $this->p->t('studierendenantrag', 'mail_part_x_new_' . $typ, ['count' => $c]);
						}
					}
					$table .= $this->p->t('studierendenantrag', 'mail_part_table', [
                        'stg_bezeichnung' => $stg['Details']->bezeichnung,
                        'stg_orgform_kurzbz' => $stg['Details']->orgform_kurzbz,
                        'rows' => $rows
                    ]);
				}
				$data['table_' . $lang] = $table;
			}

			$data['table'] = $data['table_' . DEFAULT_LANGUAGE];
			$data['leitungLink'] = APP_ROOT. 'index.ci.php/lehre/Studierendenantrag/leitung';

			//Mail an Stgl und Assistenz
			$to = $leitung['Details']->uid . '@' . DOMAIN;
			$cc = $leitung['Details']->email;

			// NOTE(chris): Sancho mail
			if (sendSanchoMail(
				"Sancho_Mail_Antrag_Stgl",
				$data,
				$to,
				'Anträge - Aktion(en) erforderlich',
				DEFAULT_SANCHO_HEADER_IMG,
				DEFAULT_SANCHO_FOOTER_IMG,
				'',
				$cc
			))
				$count++;
		}

		$this->logInfo($count . " Emails erfolgreich versandt");

		$this->logInfo('End Job sendStglSammelmail');
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
			$res = $this->StudierendenantragModel->getStgAndSem($antrag->studierendenantrag_id);
			$stg = '';
			$orgform = '';
			if (hasData($res)) {
				$studiengang = current(getData($res));
				$stg = $studiengang->bezeichnung;
				$orgform = $studiengang->orgform_kurzbz;
			}

			$datum = new DateTime($antrag->datum_wiedereinstieg);
			$data = array(
				'prestudent' => $antrag->prestudent_id,
				'name' => trim($antrag->vorname . ' '. $antrag->nachname),
				'datum_wiedereinstieg' => $datum->format('d.m.Y'),
				'vorname' => $antrag->vorname,
				'nachname' => $antrag->nachname,
				'Orgform' => $orgform,
				'stg' => $stg
			);
			$result = $this->StudentModel->loadWhere(['prestudent_id'=> $antrag->prestudent_id]);
			if (hasData($result)) {
				$student = current(getData($result));
				$data['UID'] = $student->student_uid;
			}

			// NOTE(chris): Sancho mail
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

		$digi_start= $this->config->item('digitalization_start');
		if($digi_start)
			$digi_start = new DateTime($digi_start);

		$dateDeadline = new DateTime();
		$dateDeadline->sub(DateInterval::createFromDateString($modifier_deadline));

		$result = $this->PruefungModel->getAllPrestudentsWhereCommitteeExamFailed(
			[
				Studierendenantragstatus_model::STATUS_REQUESTSENT_1,
				Studierendenantragstatus_model::STATUS_REQUESTSENT_2
			],
			$dateDeadline,
			$digi_start
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
				$result = $this->StudierendenantragstatusModel->insert([
					'studierendenantrag_id' => $prestudent->studierendenantrag_id,
					'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_DEREGISTERED,
					'insertvon' => 'AntragJob'
				]);
				if (isError($result)) {
					$this->logError(getError($result));
				} else {
					$deregisterStatus = getData($result);

					$result = $this->antraglib->pauseAntrag(
						$prestudent->studierendenantrag_id,
						Studierendenantragstatus_model::INSERTVON_DEREGISTERED
					);
					if (isError($result))
						$this->logError(getError($result));

					$result = $this->prestudentlib->setAbbrecher($prestudent->prestudent_id, '', $insertvon);
					if (isError($result)) {
						$this->StudierendenantragstatusModel->delete($deregisterStatus);
						$this->logError(getError($result));
					} else {
						$count++;
						
						$datum_kp = new DateTime($prestudent->datum);
						$dataMail = array(
							'name'=> trim($prestudent->vorname . ' '. $prestudent->nachname),
							'vorname' => $prestudent->vorname,
							'nachname' => $prestudent->nachname,
							'pers_kz'=> $prestudent->matrikelnr,
							'stg' => $prestudent->bezeichnung,
							'lvbezeichnung' => $prestudent->lvbezeichnung,
							'datum_kp' => $datum_kp->format('d.m.Y'),
							'studiensemester'=> $prestudent->studiensemester_kurzbz,
							'Orgform'=> $prestudent->orgform,
							'prestudent_id' => $prestudent->prestudent_id,
							'fristablauf' => $dateDeadline->format('d.m.Y')
						);

						$email = $this->StudentModel->getEmailFH($this->StudentModel->getUID($prestudent->prestudent_id));
						// Mail to Student
						if (!sendSanchoMail('Sancho_Mail_Antrag_W_DL_Stud', $dataMail, $email, 'Wiederholung: Frist abgelaufen')) {
							$this->logWarning("Failed to send Notification to " . $email);
						}

						$result = $this->StudiengangModel->load($prestudent->studiengang_kz);
						if (!hasData($result)) {
							$this->logWarning('No Studiengang found');
							continue;
						}
						$studiengang = current(getData($result));
						$email = $studiengang->email;
						// Mail to Assistenz
						if (!sendSanchoMail('Sancho_Mail_Antrag_W_DL_Assist', $dataMail, $email, 'Wiederholung: Frist abgelaufen')) {
							$this->logWarning("Failed to send Notification to " . $email);
						}
					}
				}
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

		$this->StudierendenantragModel->addSelect('tbl_studierendenantrag.studierendenantrag_id');
		$this->StudierendenantragModel->addSelect('prestudent_id');
		$this->StudierendenantragModel->addSelect('studiensemester_kurzbz');
		$this->StudierendenantragModel->addSelect('s.insertamum');
		$this->StudierendenantragModel->addSelect('s.insertvon');
		$this->StudierendenantragModel->addJoin('public.tbl_student pts', 'prestudent_id');
		$this->StudierendenantragModel->addSelect('pts.student_uid');

		$this->StudierendenantragModel->db->where_in(
			'public.get_rolle_prestudent(prestudent_id, studiensemester_kurzbz)',
			$this->config->item('antrag_prestudentstatus_whitelist_abmeldung')
		);

		$result = $this->StudierendenantragModel->getWithLastStatusWhere([
            'typ' => Studierendenantrag_model::TYP_ABMELDUNG_STGL,
            'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED,
            's.insertamum <=' => $dateDeadline->format('c')
        ]);

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
				$result = $this->StudierendenantragstatusModel->insert([
					'studierendenantrag_id' => $antrag->studierendenantrag_id,
					'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_DEREGISTERED,
					'insertvon' => 'AntragJob'
				]);
				if (isError($result))
					$this->logError(getError($result));
				else {
					$deregisterStatus = getData($result);

					$result = $this->antraglib->pauseAntrag($antrag->studierendenantrag_id, Studierendenantragstatus_model::INSERTVON_DEREGISTERED);
					if (isError($result))
						$this->logError(getError($result));

					$this->load->model('crm/Statusgrund_model', 'StatusgrundModel');
					$result = $this->StatusgrundModel->loadWhere(['statusgrund_kurzbz' => 'abbrecherStgl']);
					if (isError($result)) {
						$this->logError(getError($result));
						continue;
					} elseif (!hasData($result)) {
						$this->logError($this->p->t('lehre', 'error_noStatusgrund', ['statusgrund_kurzbz' => 'abbrecherStgl']));
						continue;
					}
					
					$statusgrund = current(getData($result));

					$result = $this->prestudentlib->setAbbrecher(
	                    $antrag->prestudent_id,
	                    $antrag->studiensemester_kurzbz,
	                    'AntragJob',
	                    $statusgrund->statusgrund_id,
	                    $antrag->insertamum,
	                    null,
	                    $antrag->insertvon ?: $insertvon
	                );
					if (isError($result)) {
						$this->StudierendenantragstatusModel->delete($deregisterStatus);
						$this->logError(getError($result));
					} else {
						$count++;
						$result = $this->PrestudentModel->load($antrag->prestudent_id);
						if(!hasData($result)) {
							$this->logWarning('No Prestudent found');
							continue;
						}
						$prestudent = current(getData($result));
						$result = $this->StudiengangModel->load($prestudent->studiengang_kz);
						if(!hasData($result)) {
							$this->logWarning('No Studiengang found');
							continue;
						}
						$studiengang = current(getData($result));
						$result = $this->PersonModel->loadPrestudent($antrag->prestudent_id);
						if(!hasData($result))
						{
							$this->logWarning('No Person found');
							continue;
						}
						$person = current(getData($result));
						$email = $studiengang->email;
						$dataMail = array(
							'prestudent' => 'UID: ' . $antrag->student_uid . ', PreStudentId: ' . $antrag->prestudent_id,
							'studiensemester' => $antrag->studiensemester_kurzbz,
							'name' => trim($person->vorname . ' '. $person->nachname),
						);

						if(!sendSanchoMail('Sancho_Mail_Antrag_A_Assist', $dataMail, $email, 'Einspruchsfrist abgelaufen'))
						{
							$this->logWarning("Failed to send Notification to " . $email);
						}
					}
				}
			}
			$this->logInfo($count . "/" . count($antraege) . " Students set to Abbrecher");
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

		$digi_start = $this->config->item('digitalization_start');
		if ($digi_start) {
			try {
				$digi_start = new DateTime($digi_start);
			} catch(Exception $e) {
			}
		}

		if ($modifier_deadline) {
			$dateDeadline = new DateTime();
			$dateDeadline->sub(DateInterval::createFromDateString($modifier_deadline));
			
			if ($digi_start)
				$dateDeadline = max($digi_start, $dateDeadline);
		} else {
			$dateDeadline = $digi_start ?: null;
		}

		//first request
		if ($modifier_request_1) {
			$dateStichtag = new DateTime();
			$dateStichtag->sub(DateInterval::createFromDateString($modifier_request_1));
			if (!$dateDeadline || $dateStichtag > $dateDeadline)
				$this->sendReminder(
					'Request1',
					null,
					Studierendenantragstatus_model::STATUS_REQUESTSENT_1,
					$dateDeadline,
					$dateStichtag,
					$modifier_deadline,
					'Aufforderung: Bekanntgabe Wiederholung'
				);
		} else
			$this->logError('Config "wiederholung_job_request_1_date_modifier" nicht gesetzt');

		//second request
		if ($modifier_request_2) {
			$dateStichtag = new DateTime();
			$dateStichtag->sub(DateInterval::createFromDateString($modifier_request_2));
			if (!$dateDeadline || $dateStichtag > $dateDeadline)
				$this->sendReminder(
					'Request2',
					Studierendenantragstatus_model::STATUS_REQUESTSENT_1,
					Studierendenantragstatus_model::STATUS_REQUESTSENT_2,
					$dateDeadline,
					$dateStichtag,
					$modifier_deadline,
					'Reminder Aufforderung: Bekanntgabe Wiederholung'
				);
		} else
			$this->logError('Config "wiederholung_job_request_2_date_modifier" nicht gesetzt');

		$this->logInfo('Ende Job sendAufforderungWiederholer');
	}

	protected function prestudentsGetUnique($prestudents)
    {
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

	protected function sendReminder($name, $status_from, $status_to, $deadline, $date_stichtag, $modifier_deadline, $subject)
	{
		$this->logInfo('Start Job sendAufforderungWiederholer ' . $name);

		$result = $this->PruefungModel->getAllPrestudentsWhereCommitteeExamFailed($status_from, $date_stichtag, $deadline);

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
				$urlCIS = CIS_ROOT . 'index.ci.php/lehre/Studierendenantrag/wiederholung/' . $prestudent->prestudent_id;
				$email = $this->StudentModel->getEmailFH($this->StudentModel->getUID($prestudent->prestudent_id));

				$fristende = new DateTime($prestudent->datum);
				$fristende->add(DateInterval::createFromDateString($modifier_deadline));

				$datum_kp = new DateTime($prestudent->datum);

				$result = $this->StudiensemesterModel->getNextFrom($prestudent->studiensemester_kurzbz);
				$next_sem = "";
				$sem_after_next_sem = "";
				if (hasData($result)) {
					$next_sem = current(getData($result))->studiensemester_kurzbz;
					$result = $this->StudiensemesterModel->getNextFrom($next_sem);
					if (hasData($result)) {
						$sem_after_next_sem = current(getData($result))->studiensemester_kurzbz;
					}
				}

				$dataMail = array(
					'name'=> trim($prestudent->vorname . ' '. $prestudent->nachname),
					'vorname' => $prestudent->vorname,
					'nachname' => $prestudent->nachname,
					'pers_kz'=> $prestudent->matrikelnr,
					'stg' => $prestudent->bezeichnung,
					'lvbezeichnung' => $prestudent->lvbezeichnung,
					'datum_kp' => $datum_kp->format('d.m.Y'),
					'studiensemester'=> $prestudent->studiensemester_kurzbz,
					'Orgform'=> $prestudent->orgform,
					'prestudent_id' => $prestudent->prestudent_id,
					'url' => $url,
					'urlCIS' => $urlCIS,
					'fristablauf' => $fristende->format('d.m.Y'),
					'pre_wiederholer_sem' => $next_sem,
					'wiederholer_sem' => $sem_after_next_sem,
					'sem' => $prestudent->ausbildungssemester
				);

				// NOTE(chris): Sancho mail
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
			$this->logInfo($count . " Mails '" . $subject . "' sent");
		}
		$this->logInfo('Ende Job sendAufforderungWiederholer ' . $name);
	}
}
