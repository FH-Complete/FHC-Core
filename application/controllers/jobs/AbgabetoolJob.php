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

		// TODO: student name in abgabenstring hinzufügen
		
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

			// start the container
			$abgabenString = '<div style="font-family: Arial, sans-serif; color: #333;">';

			$result = $this->_ci->ProjektarbeitModel->getProjektbetreuerAnrede($betreuer_person_id);
			$data = getData($result)[0];

			$anrede = $data->anrede;
			$anredeFillString = $data->anrede == "Herr" ? "r" : "";
			$fullFormattedNameString = $data->first;

			forEach($tupelArr as $tupel) {
				$projektarbeit_id = $tupel[0];
				$betreuerRow = $tupel[1];

				$changedAbgaben = $projektarbeiten[$projektarbeit_id];

				$relevantAbgaben = array_values(array_filter($changedAbgaben, function($abgabetermin) use ($betreuerRow) {
					if($abgabetermin->updatevon == null && $abgabetermin->insertvon != $betreuerRow->uid) {
						return $abgabetermin;
					} else if($abgabetermin->updatevon != null && $abgabetermin->updatevon != $betreuerRow->uid) {
						return $abgabetermin;
					}
				}));

				if(count($relevantAbgaben) == 0) {
					continue; // Changed break to continue to allow checking other projects in the tuple
				}

				$projektarbeit_titel = $relevantAbgaben[0]->titel ?? 'Kein Titel vergeben';

				// --- Start Project Header Section ---
				$abgabenString .= "
				<div style='margin-top: 25px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #007bff;'>
					<strong style='font-size: 16px;'>Projekt: {$projektarbeit_titel}</strong><br/>
					<span style='color: #666; font-size: 13px;'>
						ID: {$projektarbeit_id} | Rolle: {$betreuerRow->betreuerart_kurzbz} | 
						Studiengang: {$relevantAbgaben[0]->stgtyp}{$relevantAbgaben[0]->stgkz} ({$relevantAbgaben[0]->studiensemester_kurzbz})
					</span>
				</div>";
			
					// --- Start Table ---
					$abgabenString .= '
				<table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px;">
					<thead>
						<tr style="background-color: #eee; text-align: left;">
							<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px;">Zieldatum</th>
							<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px;">Bezeichnung</th>
							<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px;">Abgabe bis</th>
						</tr>
					</thead>
					<tbody>';

				foreach ($relevantAbgaben as $abgabe) {
					$dateEmailFormatted = (new DateTime($abgabe->datum))->format('d.m.Y');
					$abgabedatumFormatted = (new DateTime($abgabe->abgabedatum))->format('d.m.Y');

					$kurzbzLine = !empty($abgabe->kurzbz) ? "<br/><small style='color: #777;'>{$abgabe->kurzbz}</small>" : "";

					$abgabenString .= "
					<tr>
						<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>{$dateEmailFormatted}</td>
						<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>
							<strong>{$abgabe->bezeichnung}</strong>{$kurzbzLine}
						</td>
					   <td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>{$abgabedatumFormatted}</td>
					</tr>";
				}

				$abgabenString .= '</tbody></table>';
			}

			$abgabenString .= '</div>'; // Close container

			// done with building the change list, now send it
			$betreuerRow = $tupelArr[0][1];
			
			$path = $this->_ci->config->item('URL_MITARBEITER');
			$url = CIS_ROOT.$path;

			$body_fields = array(
				'anrede' => $anrede,
				'anredeFillString' => $anredeFillString,
				'fullFormattedNameString' => $fullFormattedNameString,
				'abgabenString' => $abgabenString,
				'linkAbgabetool' => $url
			);
			
			$email = $betreuerRow->uid ? $betreuerRow->uid."@".DOMAIN : $betreuerRow->private_email;
			
			// send email with bundled info
			sendSanchoMail(
				'PAAChangesBetSM',
				$body_fields,
				$email,
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

			// https://www.php.net/manual/en/migration70.new-features.php#migration70.new-features.spaceship-op
			// php has spaceships 🚀🚀🚀🚀🚀
			
			// sorting $abgaben array by datum
			usort($abgaben, function ($a, $b) {
				return strtotime($a->datum) <=> strtotime($b->datum);
			});

			$projektarbeit_titel = $abgaben[0]->titel;

			// initialize the table and headers
			$abgabenString = '
			<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; color: #333; margin-top: 15px; margin-bottom: 15px;">
				<thead>
					<tr style="background-color: #f2f2f2; text-align: left;">
						<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 20%;">Zieldatum</th>
						<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px;">Bezeichnung</th>
						<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 20%;">Abgabe bis</th>
					</tr>
				</thead>
				<tbody>';

			foreach ($abgaben as $abgabe) {
				// format dates inline for cleaner code
				$dateEmailFormatted = (new DateTime($abgabe->datum))->format('d.m.Y');
				$abgabedatumFormatted = (new DateTime($abgabe->abgabedatum))->format('d.m.Y');

				// handle the optional Kurzbezeichnung
				$kurzbzLine = !empty($abgabe->kurzbz) ? "<br/><small style='color: #666; font-style: italic;'>{$abgabe->kurzbz}</small>" : "";

				$abgabenString .= "
				<tr>
					<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>{$dateEmailFormatted}</td>
					<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>
						<strong>{$abgabe->bezeichnung}</strong>{$kurzbzLine}
					</td>
					<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>{$abgabedatumFormatted}</td>
				</tr>";
			}

			$abgabenString .= '</tbody></table>';
			
			$path = $this->_ci->config->item('URL_MITARBEITER');
			$url = CIS_ROOT.$path;
			
			$body_fields = array(
				'anrede' => $anrede,
				'anredeFillString' => $anredeFillString,
				'fullFormattedNameString' => $fullFormattedNameString,
				'paTitel' => $projektarbeit_titel,
				'abgabenString' => $abgabenString,
				'linkAbgabetool' => $url
			);

			$result = $this->_ci->ProjektbetreuerModel->getBetreuerOfProjektarbeit($paabgabe->projektarbeit_id, $paabgabe->betreuerart_kurzbz);
			$data = getData($result)[0];
			
			$email = $data->uid ? $data->uid."@".DOMAIN : $data->private_email;

			// send email with bundled info
			sendSanchoMail(
				'paabgabeUpdatesBetSM',
				$body_fields,
				$email,
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

		$interval = '10 days';//$this->_ci->config->item('PAABGABE_EMAIL_JOB_INTERVAL');

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
			
			// initialize the table and headers
			$abgabenString = '
			<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; color: #333; margin-top: 15px; margin-bottom: 15px;">
				<thead>
					<tr style="background-color: #f2f2f2; text-align: left;">
						<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 25%;">Datum</th>
						<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px;">Bezeichnung / Hinweis</th>
					</tr>
				</thead>
				<tbody>';

			foreach ($abgaben as $abgabe) {
				$dateEmailFormatted = (new DateTime($abgabe->datum))->format('d.m.Y');

				// handle the optional Kurzbezeichnung
				$kurzbzLine = !empty($abgabe->kurzbz) ? "<br/><small style='color: #666; font-style: italic;'>{$abgabe->kurzbz}</small>" : "";

				$abgabenString .= "
				<tr>
					<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px; vertical-align: top;'>
						{$dateEmailFormatted}
					</td>
					<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>
						<strong>{$abgabe->bezeichnung}</strong>{$kurzbzLine}
					</td>
				</tr>";
			}

			$abgabenString .= '</tbody></table>';
			
			$route =  $this->_ci->config->item('URL_STUDENTS');
			$url = CIS_ROOT.$route;

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

			$count++;
			
		}

		$this->_ci->logInfo($count . " Emails erfolgreich versandt");
		$this->_ci->logInfo('End job FHC-Core->notifyStudentMail');
	}
}