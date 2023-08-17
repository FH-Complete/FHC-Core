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

		// Load Model
		$this->load->model('education/Studierendenantrag_model', 'StudierendenantragModel');
		$this->load->model('education/Studierendenantragstatus_model', 'StudierendenantragstatusModel');
		$this->load->model('education/Pruefung_model', 'PruefungModel');
		$this->load->model('person/Kontakt_model', 'KontaktModel');
		$this->load->model('crm/Student_model', 'StudentModel');
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

				$result = $this->StudiengangModel->load($antrag->studiengang_kz);
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
				'name' => trim($leitung['Details']->vorname . ' ' . $leitung['Details']->nachname)
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

			// NOTE(chris): Sancho mail
			if (sendSanchoMail("Sancho_Mail_Antrag_Stgl", $data, $leitung['Details']->uid . '@' . DOMAIN, 'Anträge - Aktion(en) erforderlich'))
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
			$datum = new DateTime($antrag->datum_wiedereinstieg);
			$data = array(
				'prestudent' => $antrag->prestudent_id,
				'name' => trim($antrag->vorname . ' '. $antrag->nachname),
				'datum_wiedereinstieg' => $datum->format('d.m.Y')
			);

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
				null,
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
				$result = $this->prestudentlib->setAbbrecher($prestudent->prestudent_id, $prestudent->studiensemester_kurzbz, $insertvon);
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

		$this->StudierendenantragModel->addSelect('prestudent_id');
		$this->StudierendenantragModel->addSelect('studiensemester_kurzbz');
		$this->StudierendenantragModel->addSelect('s.insertamum');

		$this->StudierendenantragModel->db->where_in('public.get_rolle_prestudent(prestudent_id, studiensemester_kurzbz)', $this->config->item('antrag_prestudentstatus_whitelist'));

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
				$result = $this->prestudentlib->setAbbrecher(
                    $antrag->prestudent_id,
                    $antrag->studiensemester_kurzbz,
                    $insertvon,
                    'abbrecherStgl',
                    $antrag->insertamum
                );
				if (isError($result))
					$this->logError(getError($result));
				else
				{
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
						'prestudent' => $antrag->prestudent_id,
						'studiensemester' => $antrag->studiensemester_kurzbz,
						'name' => trim($person->vorname . ' '. $person->nachname),
					);

					if(!sendSanchoMail('Sancho_Mail_Antrag_A_Assist', $dataMail, $email, 'Einspruchsfrist abgelaufen'))
					{
						$this->logWarning("Failed to send Notification to " . $email);
					}
				}
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
				$urlCIS = CIS_ROOT . 'index.ci.php/lehre/Studierendenantrag/wiederholung/' . $prestudent->prestudent_id;
				$email = $this->StudentModel->getEmailFH($this->StudentModel->getUID($prestudent->prestudent_id));

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
					'prestudent_id' => $prestudent->prestudent_id,
					'url' => $url,
					'urlCIS' => $urlCIS,
					'fristablauf' => $fristende->format('d.m.Y')
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
