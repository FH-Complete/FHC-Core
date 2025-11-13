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

		// signatur fehlt mail
		$this->_ci->load->model('crm/Student_model', 'StudentModel');
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		
		
		// 2. begutachter mail
		$this->_ci->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');

		$this->loadPhrases([
			'abgabetool'
		]);
		
//		$this->_ci->load->model('extensions/FHC-Core-Anwesenheiten/QR_model', 'QRModel');
//		$this->_ci->load->config('extensions/FHC-Core-Anwesenheiten/qrsettings');
	}



	public function notifyBetreuerMail()
	{
		// send all new projektarbeit abgabe since the lat job run to the related betreuer
		
//		$this->logInfo('Start job queue scheduler FHC-Core->notifyBetreuerMail');

		$milliseconds = $this->_ci->config->item('PAABGABE_UPDATEAMUM_EMAIL_THRESHOLD_MILLISECONDS');

		$result = $this->PaabgabeModel->findAbgabenNewOrUpdatedSince($milliseconds);
		$retval = getData($result);

		// group results per projektarbeit/student_uid
		$student_uids = [];
		forEach($retval as $paabgabe) {
			if(!in_array($paabgabe->student_uid, $student_uids, true)) {
				$student_uids[$paabgabe->student_uid] = [$paabgabe];
			} else {
				$student_uids[$paabgabe->student_uid][] = $paabgabe;
			}
		}
		
		
		
		// send emails with bundled info
		
//		$rows_affected = $this->QRModel->db->affected_rows();
//
//		if (isError($result))
//		{
//			$this->logError(getError($result), $milliseconds);
//		} else {
//			$this->logInfo($rows_affected." QR Codes deleted.");
//		}
//
//		$this->logInfo('End job queue scheduler FHC-Core->notifyBetreuerMail');
	}

	public function notifyStudentMail()
	{
		// send all new projektarbeit abgabe since the last job run to the related student

		$this->logInfo('Start job queue scheduler FHC-Core->notifyStudentMail');

		$milliseconds = $this->_ci->config->item('PAABGABE_EMAIL_THRESHOLD_MS');

		$result = $this->_ci->PaabgabeModel->findAbgabenNewOrUpdatedSince($milliseconds);
		$retval = getData($result);

		// group results per projektarbeit/student_uid
		$student_uids = [];
		forEach($retval as $paabgabe) {
			if(!isset($student_uids[$paabgabe->student_uid])) {
				$student_uids[$paabgabe->student_uid] = [];
			}

			$student_uids[$paabgabe->student_uid][] = $paabgabe;
		}

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

			// TODO: check this config flag in job context
			// Link to Entschuldigungsmanagement
			if(defined('CIS4') && CIS4) {
				$ci3BootstrapFilePath = "cis.php";
			} else {
				$ci3BootstrapFilePath = "index.ci.php";
			}
			$url = APP_ROOT.$ci3BootstrapFilePath.'/Cis/Abgabetool/Student';
			
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
				'paabgabeUpdatesSammelmail',
				$body_fields,
				$uid.'@'.DOMAIN,
				$this->p->t('abgabetool', 'changedAbgabeterminev2')
			);
		}

		$this->logInfo('End job queue scheduler FHC-Core->notifyStudentMail');
	}
}