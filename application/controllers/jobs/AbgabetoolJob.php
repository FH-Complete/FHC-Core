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
		$this->_ci->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');
		$this->_ci->load->model('education/Paabgabe_model', 'PaabgabeModel');
		$this->_ci->load->model('crm/Student_model', 'StudentModel');

		$this->_ci->load->config('abgabe');
		$this->loadPhrases([
			'abgabetool'
		]);
	}

	public function notifyBetreuerAboutChangedAbgaben() {
		$this->_ci->logInfo('Start job FHC-Core->notifyBetreuerAboutChangedAbgaben');

		$interval = $this->_ci->config->item('PAABGABE_EMAIL_JOB_INTERVAL');
		// get all new or changed termine in interval
		$result = $this->_ci->PaabgabeModel->findAbgabenNewOrUpdatedSince($interval);
		$retval = getData($result);

		if(count($retval) == 0) {
			$this->_ci->logInfo("Keine Emails an Betreuer über neue oder veränderte Termine versandt");
			return;
		}

		// group changed/new abgaben for projektarbeiten
		$projektarbeiten = [];
		foreach($retval as $newOrChangedAbgabe) {
			// Check if the current item has a 'projektarbeit_id' field.
			// Replace 'projektarbeit_id' with the actual key name if it's different.
			if (isset($newOrChangedAbgabe->projektarbeit_id)) {
				$projektarbeitId = $newOrChangedAbgabe->projektarbeit_id;

				// If the 'projektarbeit_id' is not yet a key in $projektarbeiten, 
				// initialize it as an empty array.
				if (!isset($projektarbeiten[$projektarbeitId])) {
					$projektarbeiten[$projektarbeitId] = [];
				}

				// Add the current row to the array associated with its 'projektarbeit_id'.
				$projektarbeiten[$projektarbeitId][] = $newOrChangedAbgabe;
			}
		}

		// for each projektarbeit fetch their betreuer and save them in their own dictionary to avoid too many mails
		$betreuerMap = [];
		forEach($projektarbeiten as $projektarbeit_id => $abgaben) {
			$betreuerResult = $this->_ci->ProjektbetreuerModel->getAllBetreuerOfProjektarbeit($projektarbeit_id);
			
			forEach($betreuerResult->retval as $betreuerRow) {
				if (!isset($betreuerMap[$betreuerRow->person_id])) {
					$betreuerMap[$betreuerRow->person_id] = [];
				}
				
				// Add the current betreuerRow to the betreuerMap as an array associated with its projektarbeit_id.
				$betreuerMap[$betreuerRow->person_id][] = [$projektarbeit_id, $betreuerRow];
			}
		}

		$count = 0;
		// now iterate over the betreuerMap and build 1 email about all projektarbeiten and their new/changed termine
		// $tupel = [$projektarbeit_id, $betreuerRow], each betreuer has 0..n [projektarbeit_id, changedAbgaben] tupel
		forEach($betreuerMap as $betreuer_person_id => $tupelArr) {

			$abgabenString = '<br /><br />';
			
			$result = $this->_ci->ProjektarbeitModel->getProjektbetreuerAnrede($betreuer_person_id);
			$data = getData($result)[0];

			$anrede = $data->anrede;
			$anredeFillString = $data->anrede == "Herr" ? "r" : "";
			$fullFormattedNameString = $data->first;

			forEach($tupelArr as $tupel) {
				$projektarbeit_id = $tupel[0];
				$betreuerRow = $tupel[1];

				$changedAbgaben = $projektarbeiten[$projektarbeit_id];

				// filter for abgaben which where not inserted by the current betreuer iteration if there is no updateamum
				// or not changed by the betreuer if there is updateamum
				$relevantAbgaben = array_filter($changedAbgaben, function($abgabetermin) use ($betreuerRow) {
					// new termin not created by that betreuer
					if($abgabetermin->updatevon == null && $abgabetermin->insertvon != $betreuerRow->uid) {
						return $abgabetermin;
					} else if($abgabetermin->updatevon != null && $abgabetermin->updatevon != $betreuerRow->uid) {
						return $abgabetermin;
					}
				});

				if(count($relevantAbgaben) == 0) {
					break; // skip that projektarbeit if only changes originate from the betreuer in question
				}

				$projektarbeit_titel = $relevantAbgaben[0]->titel ?? 'Kein Titel vergeben';
				$abgabenString .= 'Projektarbeit: '.$projektarbeit_titel.' ('.$betreuerRow->betreuerart_kurzbz.') ID:'.$projektarbeit_id.'<br/>';
				$abgabenString .= 'Stg: '.$relevantAbgaben[0]->stgtyp.$relevantAbgaben[0]->stgkz.' Semester: '.$relevantAbgaben[0]->studiensemester_kurzbz.'<br/>';
				foreach ($relevantAbgaben as $abgabe) {
					$datetime = new DateTime($abgabe->datum);
					$dateEmailFormatted = $datetime->format('d.m.Y');

					$datetimeAbgabe = new DateTime($abgabe->abgabedatum);
					$abgabedatumFormatted = $datetimeAbgabe->format('d.m.Y');

					$abgabenString .= ' Zieldatum: '.$dateEmailFormatted . ' ' . $abgabe->bezeichnung . ' <br /> ';
					if($abgabe->kurzbz != '') {
						$abgabenString .= $abgabe->kurzbz . '<br />';
					}
				}

				$abgabenString .= '<br/><br/>';


			}

			// done with building the change list, now send it
			$betreuerRow = $tupelArr[0][1];
			
			$path = $this->_ci->config->item('URL_MITARBEITER');
			$url = APP_ROOT.$path;

			$body_fields = array(
				'anrede' => $anrede,
				'anredeFillString' => $anredeFillString,
				'fullFormattedNameString' => $fullFormattedNameString,
				'abgabenString' => $abgabenString,
				'linkAbgabetool' => $url
			);

			// send email with bundled info
			sendSanchoMail(
				'PAAChangesBetSM',
				$body_fields,
				$betreuerRow->private_email,
				$this->p->t('abgabetool', 'changedAbgabeterminev2')
			);

			$count++;
		}

		$this->_ci->logInfo($count . " Emails erfolgreich versandt");
		$this->_ci->logInfo('End job FHC-Core->notifyBetreuerAboutChangedAbgaben');
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
			$this->logInfo("Keine Emails über neue Paabgaben an Betreuer versandt");
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