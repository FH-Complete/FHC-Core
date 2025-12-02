<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class AbgabetoolJob extends JOB_Controller
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_ci =& get_instance();

		$this->_ci->load->helper('hlp_sancho_helper');

		$this->_ci->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$this->_ci->load->model('education/Paabgabe_model', 'PaabgabeModel');
		$this->_ci->load->model('crm/Student_model', 'StudentModel');

		$this->_ci->load->config('abgabe');
		$this->loadPhrases([
			'abgabetool'
		]);
	}
	
	public function notifyBetreuerAboutChangedAbgaben() {
		
	}

	public function notifyBetreuerMail() {
		// send all new projektarbeit abgabe UPLOADS since the last job run to the related betreuer
		// this job gathers all new or changed file uploads via field 'abgabedatum', enduploads still
		// send an email directly after happening since they are kind of important

		$this->_ci->logInfo('Start job FHC-Core->notifyBetreuerMail');

		$interval = $this->_ci->config->item('PAABGABE_EMAIL_JOB_INTERVAL');

		$result = $this->_ci->PaabgabeModel->findAbgabenNewOrUpdatedSinceByAbgabedatum($interval);
		$retval = getData($result);

		// retval are paabgaben joined with projektarbeit and betreuer
		if(count($retval) == 0) {
			$this->logInfo("Keine Emails an Betreuer versandt");
			return;
		}

		// group contents per betreuer person_id
		$betreuer_uids = [];
		forEach($retval as $paabgabe) {
			if(!isset($betreuer_uids[$paabgabe->person_id])) {
				$betreuer_uids[$paabgabe->person_id] = [];
			}

			$betreuer_uids[$paabgabe->person_id][] = $paabgabe;
		}

		$count = 0;
		forEach ($betreuer_uids as $person_id => $abgaben) {
			// $person_id is from betreuer

			$result = $this->_ci->ProjektarbeitModel->getProjektbetreuerAnrede($person_id);
			$data = getData($result)[0];

			// $abgabe is the array of paabgabe objects
			$anrede = $data->anrede;
			$anredeFillString = $data->anrede == "Herr" ? "r" : "";
			$fullFormattedNameString = $data->first;

			$result = $this->_ci->ProjektarbeitModel->getProjektbetreuerEmail($paabgabe->projektarbeit_id);
			$data = getData($result)[0];

			// https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.spaceship-op
			// php has spaceships 🚀🚀🚀🚀🚀
			
			// sorting $abgaben array by datum
			usort($abgaben, function ($a, $b) {
				return strtotime($a->datum) <=> strtotime($b->datum);
			});

			$projektarbeit_titel = $abgaben[0]->titel;
			$abgabenString = '<br /><br />';
			foreach ($abgaben as $abgabe) {
				$datetime = new DateTime($abgabe->datum);
				$dateEmailFormatted = $datetime->format('d.m.Y');

				$datetimeAbgabe = new DateTime($abgabe->abgabedatum);
				$abgabedatumFormatted = $datetimeAbgabe->format('d.m.Y');

				$abgabenString .= 'Abgabedatum: '.$abgabedatumFormatted.' Zieldatum: '.$dateEmailFormatted . ' ' . $abgabe->bezeichnung . ' <br /> ' . $abgabe->kurzbz . '<br />';
			}
			
			$path = $this->_ci->config->item('URL_MITARBEITER');
			$url = APP_ROOT.$path;
			
			$body_fields = array(
				'anrede' => $anrede,
				'anredeFillString' => $anredeFillString,
				'fullFormattedNameString' => $fullFormattedNameString,
				'paTitel' => $projektarbeit_titel,
				'abgabenString' => $abgabenString,
				'linkAbgabetool' => $url
			);

			// send email with bundled info
			sendSanchoMail(
				'PaabgabeUpdatesBetSM',
				$body_fields,
				$data->private_email,
				$this->p->t('abgabetool', 'changedAbgabeterminev2')
			);
			
			$count++;
		}
		
		$this->_ci->logInfo($count . " Emails erfolgreich versandt");
		$this->_ci->logInfo('End job FHC-Core->notifyBetreuerMail');
	}

	public function notifyStudentMail()
	{
		// send all new projektarbeit abgabe since the last job run to the related student

		$this->_ci->logInfo('Start job FHC-Core->notifyStudentMail');

		$interval = $this->_ci->config->item('PAABGABE_EMAIL_JOB_INTERVAL');

		$result = $this->_ci->PaabgabeModel->findAbgabenNewOrUpdatedSince($interval);
		$retval = getData($result);

		if(count($retval) == 0) {
			$this->_ci->logInfo("Keine Emails an Studenten versandt");
			return;
		}
		
		// group results per projektarbeit/student_uid
		$student_uids = [];
		forEach($retval as $paabgabe) {
			if(!isset($student_uids[$paabgabe->student_uid])) {
				$student_uids[$paabgabe->student_uid] = [];
			}

			$student_uids[$paabgabe->student_uid][] = $paabgabe;
		}

		$count = 0;
		foreach ($student_uids as $uid => $abgaben) {
			// $uid is the student's UID
			$result = $this->_ci->StudentModel->getEmailAnredeForStudentUID($uid);
			$data = getData($result)[0];

			// $abgabe is the array of paabgabe objects
			$anredeFillString = $data->anrede=="Herr"?"r":"";
			$fullFormattedNameString = trim($data->titelpre." ".$data->vorname." ".$data->vornamen." ".$data->nachname." ".$data->titelpost);

			// https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.spaceship-op
			// php has spaceships 🚀🚀🚀🚀🚀
			usort($abgaben, function($a, $b) {
				return strtotime($a->datum) <=> strtotime($b->datum);
			});

			$projektarbeit_titel = $abgaben[0]->titel;
			$abgabenString = '<br /><br />';
			forEach($abgaben as $abgabe) {
				$datetime = new DateTime($abgabe->datum);
				$dateEmailFormatted = $datetime->format('d.m.Y');

				$abgabenString .= $dateEmailFormatted.' '.$abgabe->bezeichnung.' '.$abgabe->kurzbz.'<br />';
			}
			
			$route =  $this->_ci->config->item('URL_STUDENTS');
			$url = APP_ROOT.$route;

			$body_fields = array(
				'anrede' => $data->anrede,
				'anredeFillString' => $anredeFillString,
				'fullFormattedNameString' => $fullFormattedNameString,
				'paTitel' => $projektarbeit_titel,
				'abgabenString' => $abgabenString,
				'linkAbgabetool' => $url
			);

			// send email with bundled info
			sendSanchoMail(
				'PaabgabeUpdatesSammelmail',
				$body_fields,
				$uid.'@'.DOMAIN,
				$this->p->t('abgabetool', 'changedAbgabeterminev2')
			);

			$count++;
			
		}

		$this->_ci->logInfo($count . " Emails erfolgreich versandt");
		$this->_ci->logInfo('End job FHC-Core->notifyStudentMail');
	}
}