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
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->_ci->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		
		$this->_ci->load->library('SignatureLib');
		
		$this->_ci->load->config('abgabe');
		$this->loadPhrases([
			'abgabetool'
		]);
		
		
	}
	
	// basically the notifyBetreuerMail function but email goes to assistenz
	// and new abgaben are further evaluated for missing signature status
	public function notifyAssistenzAboutMissingSignatureUploads() {
		$this->_ci->logInfo('Start job FHC-Core->notifyAssistenzAboutMissingSignatureUploads');
		
		$interval = $this->_ci->config->item('PAABGABE_EMAIL_JOB_INTERVAL');
		$relevantTypes = $this->_ci->config->item('RELEVANT_PAABGABETYPEN_SAMMELMAIL_ASSISTENZ');

		$result = $this->_ci->PaabgabeModel->findAbgabenNewOrUpdatedSinceByAbgabedatum($interval, $relevantTypes);
		$retval = getData($result);

		// retval are paabgaben joined with projektarbeit and betreuer
		if(count($retval) == 0) {
			$this->logInfo("Keine Emails über neue Paabgaben an Assistenzen versandt");
			return;
		}

		// group changed/new abgaben for projektarbeiten
		$projektarbeiten = [];
		foreach($retval as $abgabeWithNewUpload) {
			// Check if the current item has a 'projektarbeit_id' field.
			// Replace 'projektarbeit_id' with the actual key name if it's different.
			if (isset($abgabeWithNewUpload->projektarbeit_id)) {
				$projektarbeitId = $abgabeWithNewUpload->projektarbeit_id;

				// If the 'projektarbeit_id' is not yet a key in $projektarbeiten, 
				// initialize it as an empty array.
				if (!isset($projektarbeiten[$projektarbeitId])) {
					$projektarbeiten[$projektarbeitId] = [];
				}
				
				// check signature for that abgabe, main point of this job
				$this->checkAbgabeSignatur($abgabeWithNewUpload, $abgabeWithNewUpload->student_uid);
				
				// Add the current row to the array associated with its 'projektarbeit_id'.
				$projektarbeiten[$projektarbeitId][] = $abgabeWithNewUpload;
			}
		}

		// for each projektarbeit fetch their assistenz and same them in their own dictionary to avoid too many mails
		$assistenzMap = [];
		// for each projektarbeit fetch their betreuer and save them in their own dictionary to avoid too many mails
		$projektarbeitBetreuerMap = [];
		forEach($projektarbeiten as $projektarbeit_id => $abgaben) {

			$assistenzResult = $this->_ci->OrganisationseinheitModel->getAssistenzForOE($abgaben[0]->stg_oe_kurzbz);

			forEach($assistenzResult->retval as $assistenzRow) {
				if (!isset($assistenzMap[$assistenzRow->person_id])) {
					$assistenzMap[$assistenzRow->person_id] = [];
				}

				// Add the current $assistenzRow to the $assistenzMap as an array associated with its projektarbeit_id.
				$assistenzMap[$assistenzRow->person_id][] = [$projektarbeit_id, $assistenzRow];
			}

			$betreuerResult = $this->_ci->ProjektbetreuerModel->getAllBetreuerOfProjektarbeit($projektarbeit_id);

			forEach($betreuerResult->retval as $betreuerRow) {
				if (!isset($projektarbeitBetreuerMap[$projektarbeit_id])) {
					$projektarbeitBetreuerMap[$projektarbeit_id] = [];
				}

				// Add the current betreuerRow to the betreuerMap as an array associated with its projektarbeit_id.
				$projektarbeitBetreuerMap[$projektarbeit_id][] = $betreuerRow;
			}
			
		}

		$count = 0;
		foreach($assistenzMap as $assistenz_person_id => $tupelArr) {

			$abgabenString = '<div style="font-family: Arial, sans-serif; color: #333;">';
			$hasIssues = false; // Track if this assistant actually needs an email

			foreach($tupelArr as $tupel) {
				$projektarbeit_id = $tupel[0];
				$assistenzRow = $tupel[1];

				$betreuerArray = $projektarbeitBetreuerMap[$projektarbeit_id] ?? [];
				$allAbgaben = $projektarbeiten[$projektarbeit_id];

				// only keep abgaben that are not correctly signed
				$issueAbgaben = array_filter($allAbgaben, function($abgabe) {
					// We only care about cases where it's explicitly NOT true (false, error, or null)
					return $abgabe->signatur !== true;
				});

				// if this specific project has no signature issues, skip to the next project
				if(empty($issueAbgaben)) {
					continue;
				}

				// If we reached here, we have at least one issue to report
				$hasIssues = true;

				// Format the Student Name (using the first available abgabe object)
				$s = reset($issueAbgaben);
				$nameParts = array_filter([$s->titelpre, $s->vorname, $s->nachname, $s->titelpost]);
				$studentFullName = implode(' ', $nameParts);

				// Format the Supervisors string
				$betreuerStrings = [];
				foreach($betreuerArray as $b) {
					$bNameParts = array_filter([$b->titelpre, $b->vorname, $b->nachname, $b->titelpost]);
					$bFullName = implode(' ', $bNameParts);
					$betreuerStrings[] = "{$bFullName} ({$b->betreuerart_kurzbz})";
				}
				$allBetreuerFormatted = implode(', ', $betreuerStrings);

				$projektarbeit_titel = $s->titel ?? 'Kein Titel vergeben';

				// Project Header Section
				$abgabenString .= "
             <div style='margin-top: 25px; padding: 12px; background-color: #fff5f5; border-left: 4px solid #dc3545; border-bottom: 1px solid #fee;'>
                <strong style='font-size: 16px; color: #b02a37;'>Projekt: {$projektarbeit_titel}</strong><br/>
                <div style='margin-top: 5px; font-size: 14px;'>
                   <strong>Studierende/r:</strong> {$studentFullName}
                </div>
                <div style='margin-top: 3px; font-size: 14px;'>
                   <strong>Betreuer:</strong> {$allBetreuerFormatted}
                </div>
                <span style='color: #666; font-size: 12px;'>
                   ID: {$projektarbeit_id} | Stg: {$s->stgtyp}{$s->stgkz} ({$s->studiensemester_kurzbz})
                </span>
             </div>";

				// Start Table
				$abgabenString .= '
             <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                <thead>
                   <tr style="background-color: #f8f9fa; text-align: left;">
                      <th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 20%;">Datum</th>
                      <th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 45%;">Abgabe/Bezeichnung</th>
                      <th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 35%;">Status</th>
                   </tr>
                </thead>
                <tbody>';

				$printed = []; // lazy hack to avoid duplicate rows
				foreach ($issueAbgaben as $abgabe) {
					// if we had this paabgabe already (erstbetreuer/zweitbetreuer fetch achieves duplicates
					if(in_array($abgabe->paabgabe_id, $printed)) {
						continue; // skip this forEach iteration
					}
					
					$printed[] = $abgabe->paabgabe_id;
					
					$abgabedatumFormatted = (new DateTime($abgabe->abgabedatum))->format('d.m.Y');

					// label and color
					if ($abgabe->signatur === false) {
						$sigLabel = "FEHLENDE SIGNATUR";
						$sigBg = "#dc3545";
					} elseif ($abgabe->signatur === 'error') {
						$sigLabel = "PRÜFUNG FEHLGESCHLAGEN";
						$sigBg = "#fd7e14";
					} else {
						$sigLabel = "DATEI NICHT GEFUNDEN";
						$sigBg = "#6c757d";
					}

					$abgabenString .= "
                <tr>
                   <td style='padding: 10px; border: 1px solid #ddd; font-size: 13px; vertical-align: top;'>{$abgabedatumFormatted}</td>
                   <td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>
                      <strong>{$abgabe->bezeichnung}</strong>
                   </td>
                   <td style='padding: 10px; border: 1px solid #ddd; font-size: 13px; text-align: center;'>
                      <span style='color: #fff; background-color: {$sigBg}; padding: 3px 8px; border-radius: 3px; font-weight: bold; font-size: 11px;'>
                        {$sigLabel}
                      </span>
                   </td>
                </tr>";
				}

				$abgabenString .= '</tbody></table>';
			}

			$abgabenString .= '</div>';

			// only send the email if at least one project had an issue
			if ($hasIssues) {
				$assistenzRow = $tupelArr[0][1];
				$anrede = $assistenzRow->anrede;
				$anredeFillString = $assistenzRow->anrede == "Herr" ? "r" : "";
				$fullFormattedNameString = $assistenzRow->first;

				$path = $this->_ci->config->item('URL_ASSISTENZ');
				$url = CIS_ROOT . $path;

				$body_fields = array(
					'anrede' => $anrede,
					'anredeFillString' => $anredeFillString,
					'fullFormattedNameString' => $fullFormattedNameString,
					'abgabenString' => $abgabenString,
					'linkAbgabetool' => $url
				);

				$email = $assistenzRow->uid . "@" . DOMAIN;

				sendSanchoMail(
					'PAANoSigAssSM',
					$body_fields,
					$email,
					$this->p->t('abgabetool', 'c4missingSignatureNotification')
				);

				$count++;
			}
		}

		$this->_ci->logInfo($count . " Emails bezüglich fehlender Signaturen erfolgreich versandt");
		$this->_ci->logInfo('End job FHC-Core->notifyAssistenzAboutMissingSignatureUploads');
	}

	/**
	 * helper function to check the signature status of uploaded files for zwischenabgabe & endupload
	 */
	private function checkAbgabeSignatur($abgabe, $student_uid) {
		$paabgabetypenToCheck = $this->config->item('SIGNATUR_CHECK_PAABGABETYPEN');

		if(!in_array($abgabe->paabgabetyp_kurzbz, $paabgabetypenToCheck)) {
			return;
		}

		if (!defined('SIGNATUR_URL')) {
			$abgabe->signatur = 'error';
			return;
		}

		$path = PAABGABE_PATH.$abgabe->paabgabe_id.'_'.$student_uid.'.pdf';

		$signaturVorhanden = null; // if frontend receives null -> indicates no file found at path
		if(file_exists($path)) {

			// Check if the document is signed
			$signList = SignatureLib::list($path);
			if (is_array($signList) && count($signList) > 0)
			{
				// The document is signed
				$signaturVorhanden = true;
			}
			elseif ($signList === null)
			{
				// frontend knows to handle it this way for signatures
				$signaturVorhanden = 'error';
			}
			else
			{
				$signaturVorhanden = false;
			}

			$abgabe->signatur = $signaturVorhanden;
		}
	}

	public function notifyAssistenzAboutChangedAbgaben() {

		$this->_ci->logInfo('Start job FHC-Core->notifyAssistenzAboutChangedAbgaben');

		$interval = $this->_ci->config->item('PAABGABE_EMAIL_JOB_INTERVAL');
		$relevantTypes = $this->_ci->config->item('RELEVANT_PAABGABETYPEN_SAMMELMAIL_ASSISTENZ');
		// get all new or changed termine in interval
		$result = $this->_ci->PaabgabeModel->findAbgabenNewOrUpdatedSince($interval, $relevantTypes);
		
		$retval = getData($result);

		if(count($retval) == 0) {
			$this->_ci->logInfo("Keine Emails an Assistenzen über neue oder veränderte Termine versandt");
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

		// for each projektarbeit fetch their assistenz and same them in their own dictionary to avoid too many mails
		$assistenzMap = [];
		// for each projektarbeit fetch their betreuer and save them in their own dictionary to avoid too many mails
		$projektarbeitBetreuerMap = [];
		forEach($projektarbeiten as $projektarbeit_id => $abgaben) {
			
			$assistenzResult = $this->_ci->OrganisationseinheitModel->getAssistenzForOE($abgaben[0]->stg_oe_kurzbz);

			forEach($assistenzResult->retval as $assistenzRow) {
				if (!isset($assistenzMap[$assistenzRow->person_id])) {
					$assistenzMap[$assistenzRow->person_id] = [];
				}

				// Add the current $assistenzRow to the $assistenzMap as an array associated with its projektarbeit_id.
				$assistenzMap[$assistenzRow->person_id][] = [$projektarbeit_id, $assistenzRow];
			}

			$betreuerResult = $this->_ci->ProjektbetreuerModel->getAllBetreuerOfProjektarbeit($projektarbeit_id);

			forEach($betreuerResult->retval as $betreuerRow) {
				if (!isset($projektarbeitBetreuerMap[$projektarbeit_id])) {
					$projektarbeitBetreuerMap[$projektarbeit_id] = [];
				}

				// Add the current betreuerRow to the betreuerMap as an array associated with its projektarbeit_id.
				$projektarbeitBetreuerMap[$projektarbeit_id][] = $betreuerRow;
			}
		}

		$count = 0;
		foreach($assistenzMap as $assistenz_person_id => $tupelArr) {

			$abgabenString = '<div style="font-family: Arial, sans-serif; color: #333;">';

			foreach($tupelArr as $tupel) {
				$projektarbeit_id = $tupel[0];
				$assistenzRow = $tupel[1];

				$betreuerArray = $projektarbeitBetreuerMap[$projektarbeit_id] ?? [];
				$changedAbgaben = $projektarbeiten[$projektarbeit_id];

				$relevantAbgaben = array_values(array_filter($changedAbgaben, function($abgabetermin) use ($assistenzRow) {
					if($abgabetermin->updatevon == null && $abgabetermin->insertvon != $assistenzRow->uid) {
						return $abgabetermin;
					} else if($abgabetermin->updatevon != null && $abgabetermin->updatevon != $assistenzRow->uid) {
						return $abgabetermin;
					}
				}));

				if(count($relevantAbgaben) == 0) {
					continue;
				}

				// Format the Student Name
				$s = $relevantAbgaben[0];
				$nameParts = [];
				if (!empty($s->titelpre)) $nameParts[] = $s->titelpre;
				$nameParts[] = $s->vorname;
				$nameParts[] = $s->nachname;
				if (!empty($s->titelpost)) $nameParts[] = $s->titelpost;
				$studentFullName = implode(' ', $nameParts);

				// Format the Supervisors string
				$betreuerStrings = [];
				foreach($betreuerArray as $b) {
					$bNameParts = [];
					if (!empty($b->titelpre)) $bNameParts[] = $b->titelpre;
					$bNameParts[] = $b->vorname;
					$bNameParts[] = $b->nachname;
					if (!empty($b->titelpost)) $bNameParts[] = $b->titelpost;

					$bFullName = implode(' ', $bNameParts);
					$betreuerStrings[] = "{$bFullName} ({$b->betreuerart_kurzbz})";
				}
				$allBetreuerFormatted = implode(', ', $betreuerStrings);

				$projektarbeit_titel = $s->titel ?? 'Kein Titel vergeben';

				// Project Header Section
				$abgabenString .= "
				<div style='margin-top: 25px; padding: 12px; background-color: #f8f9fa; border-left: 4px solid #007bff; border-bottom: 1px solid #eee;'>
					<strong style='font-size: 16px; color: #0056b3;'>Projekt: {$projektarbeit_titel}</strong><br/>
					<div style='margin-top: 5px; font-size: 14px;'>
						<strong>Studierende/r:</strong> {$studentFullName}
					</div>
					<div style='margin-top: 3px; font-size: 14px;'>
						<strong>Betreuer:</strong> {$allBetreuerFormatted}
					</div>
					<span style='color: #666; font-size: 12px;'>
						ID: {$projektarbeit_id} | Stg: {$s->stgtyp}{$s->stgkz} ({$s->studiensemester_kurzbz})
					</span>
				</div>";

				// Start Table
				$abgabenString .= '
				<table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
					<thead>
						<tr style="background-color: #eee; text-align: left;">
							<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 20%;">Zieldatum</th>
							<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px;">Bezeichnung</th>
						</tr>
					</thead>
					<tbody>';

				foreach ($relevantAbgaben as $abgabe) {
					$dateEmailFormatted = (new DateTime($abgabe->datum))->format('d.m.Y');
					$abgabedatumFormatted = (new DateTime($abgabe->abgabedatum))->format('d.m.Y');
					$kurzbzLine = !empty($abgabe->kurzbz) ? "<br/><small style='color: #777; font-style: italic;'>{$abgabe->kurzbz}</small>" : "";

					$abgabenString .= "
					<tr>
						<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px; vertical-align: top;'>{$dateEmailFormatted}</td>
						<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>
							<strong>{$abgabe->bezeichnung}</strong>{$kurzbzLine}
						</td>
					</tr>";
				}

				$abgabenString .= '</tbody></table>';
			}

			$abgabenString .= '</div>';

			// done with building the change list, now send it
			$assistenzRow = $tupelArr[0][1];
			$anrede = $assistenzRow->anrede;
			$anredeFillString = $assistenzRow->anrede == "Herr" ? "r" : "";
			$fullFormattedNameString = $assistenzRow->first;
			
			

			$path = $this->_ci->config->item('URL_ASSISTENZ');
			$url = CIS_ROOT.$path;

			$body_fields = array(
				'anrede' => $anrede,
				'anredeFillString' => $anredeFillString,
				'fullFormattedNameString' => $fullFormattedNameString,
				'abgabenString' => $abgabenString,
				'linkAbgabetool' => $url
			);

			$email = $assistenzRow->uid."@".DOMAIN;
			
			// send email with bundled info
			sendSanchoMail(
				'PAAChangesAssSM',
				$body_fields,
				$email,
				$this->p->t('abgabetool', 'changedAbgabeterminev2')
			);

			$count++;
		}

		$this->_ci->logInfo($count . " Emails erfolgreich versandt");
		$this->_ci->logInfo('End job FHC-Core->notifyAssistenzAboutChangedAbgaben');
	}

	public function notifyBetreuerAboutChangedAbgaben() {
		
		$this->_ci->logInfo('Start job FHC-Core->notifyBetreuerAboutChangedAbgaben');

		$interval = $this->_ci->config->item('PAABGABE_EMAIL_JOB_INTERVAL');

		$relevantTypes = $this->_ci->config->item('RELEVANT_PAABGABETYPEN_SAMMELMAIL_BETREUER');

		// get all new or changed termine in interval
		$result = $this->_ci->PaabgabeModel->findAbgabenNewOrUpdatedSince($interval, $relevantTypes);
		$retval = getData($result);
		if(!$retval) {
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

				// check if the updatevon field is NOT the same as the student the projektarbeit is assigned to
				// since uploading a file to a paabgabe is also putting updateamum & updatevon
				// we have our own "student has uploaded a file" emailjob anyways
				if($newOrChangedAbgabe->student_uid === $newOrChangedAbgabe->updatevon) {
					continue;
				}
				
				// If the 'projektarbeit_id' is not yet a key in $projektarbeiten, 
				// initialize it as an empty array.
				if (!isset($projektarbeiten[$projektarbeitId])) {
					$projektarbeiten[$projektarbeitId] = [];
				}
				
				// Add the current row to the array associated with its 'projektarbeit_id'.
				$projektarbeiten[$projektarbeitId][] = $newOrChangedAbgabe;
			}
		}

		if(count($projektarbeiten) == 0) {
			$this->_ci->logInfo("Keine Emails an Betreuer über neue oder veränderte Termine versandt");
			return;
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

			$relevantCounter = 0; // workaround to check if a betreuer needs to have any notification about relevant
			// abgaben at all to avoid sending empty emails since we filter on certain conditions
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
					continue;
				}

				$relevantCounter++;

				// format the Student Name
				$s = $relevantAbgaben[0];
				$nameParts = [];
				if (!empty($s->titelpre)) $nameParts[] = $s->titelpre;
				$nameParts[] = $s->vorname;
				$nameParts[] = $s->nachname;
				if (!empty($s->titelpost)) $nameParts[] = $s->titelpost;
				$studentFullName = implode(' ', $nameParts);

				$projektarbeit_titel = $s->titel ?? 'Kein Titel vergeben';

				// project header section
				$abgabenString .= "
					<div style='margin-top: 25px; padding: 12px; background-color: #f8f9fa; border-left: 4px solid #007bff; border-bottom: 1px solid #eee;'>
						<strong style='font-size: 16px; color: #0056b3;'>Projekt: {$projektarbeit_titel}</strong><br/>
						<div style='margin-top: 5px; font-size: 14px;'>
							<strong>Studierende/r:</strong> {$studentFullName}
						</div>
						<span style='color: #666; font-size: 12px;'>
							ID: {$projektarbeit_id} | Rolle: {$betreuerRow->betreuerart_kurzbz} | 
							Stg: {$s->stgtyp}{$s->stgkz} ({$s->studiensemester_kurzbz})
						</span>
					</div>";

				// start table
				$abgabenString .= '
					<table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
						<thead>
							<tr style="background-color: #eee; text-align: left;">
								<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 20%;">Zieldatum</th>
								<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px;">Bezeichnung</th>
							</tr>
						</thead>
						<tbody>';

				foreach ($relevantAbgaben as $abgabe) {
					$dateEmailFormatted = (new DateTime($abgabe->datum))->format('d.m.Y');
					$abgabedatumFormatted = (new DateTime($abgabe->abgabedatum))->format('d.m.Y');
					$kurzbzLine = !empty($abgabe->kurzbz) ? "<br/><small style='color: #777; font-style: italic;'>{$abgabe->kurzbz}</small>" : "";

					$abgabenString .= "
						<tr>
							<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px; vertical-align: top;'>{$dateEmailFormatted}</td>
							<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>
								<strong>{$abgabe->bezeichnung}</strong>{$kurzbzLine}
							</td>
						</tr>";
				}

				$abgabenString .= '</tbody></table>';
			}
			
			// close container
			$abgabenString .= '</div>'; 

			// done with building the change list, now send it
			$betreuerRow = $tupelArr[0][1];
			
			if($relevantCounter == 0) {
				$this->_ci->logInfo('No Relevant Abgaben to notify Betreuer PersonID: "'.$betreuerRow->person_id.'".');
				continue;
			}
			
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

			if(!$email) {
				$this->_ci->logInfo('Could not send Email for Betreuer PersonID: "'.$data->person_id.'".');
				continue;
			}
			
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

		// dont filter for relevant types since this mail should gather all UPLOAD info
		
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
						<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 15%;">Zieldatum</th>
						<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 25%;">Studierende/r</th>
						<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px;">Bezeichnung</th>
						<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 15%;">Abgabedatum</th>
					</tr>
				</thead>
				<tbody>';
			
			foreach ($abgaben as $abgabe) {
				// format the student name
				$nameParts = [];
				if (!empty($abgabe->titelpre)) $nameParts[] = $abgabe->titelpre;
				$nameParts[] = $abgabe->vorname;
				$nameParts[] = $abgabe->nachname;
				if (!empty($abgabe->titelpost)) $nameParts[] = $abgabe->titelpost;
				$studentFullName = implode(' ', $nameParts);

				// format dates inline
				$dateEmailFormatted = (new DateTime($abgabe->datum))->format('d.m.Y');
				$abgabedatumFormatted = (new DateTime($abgabe->abgabedatum))->format('d.m.Y');

				// handle the optional Kurzbezeichnung
				$kurzbzLine = !empty($abgabe->kurzbz) ? "<br/><small style='color: #666; font-style: italic;'>{$abgabe->kurzbz}</small>" : "";

				$abgabenString .= "
				<tr>
					<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px; vertical-align: top;'>{$dateEmailFormatted}</td>
					<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px; vertical-align: top;'>{$studentFullName}</td>
					<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px;'>
						<strong>{$abgabe->bezeichnung}</strong>{$kurzbzLine}
					</td>
					<td style='padding: 10px; border: 1px solid #ddd; font-size: 13px; vertical-align: top;'>{$abgabedatumFormatted}</td>
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

			$result = $this->_ci->ProjektbetreuerModel->getBetreuerOfProjektarbeit($abgaben[0]->projektarbeit_id, $abgaben[0]->betreuerart_kurzbz);
			$data = getData($result)[0];

			$email = $data->uid ? $data->uid."@".DOMAIN : $data->private_email;

			// in rare cases there are betreuer (often zweitbetreuer) without uid and without private email
			if(!$email) {
				$this->_ci->logInfo('Could not send Email for Betreuer PersonID: "'.$data->person_id.'".');
				continue;
			}
			
			// send email with bundled info
			sendSanchoMail(
				'PaabgabeUpdatesBetSM',
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

		$interval = $this->_ci->config->item('PAABGABE_EMAIL_JOB_INTERVAL');

		$relevantTypes = $this->_ci->config->item('RELEVANT_PAABGABETYPEN_SAMMELMAIL_STUDENT');

		$result = $this->_ci->PaabgabeModel->findAbgabenNewOrUpdatedSince($interval, $relevantTypes);
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
						<th style="padding: 10px; border: 1px solid #ddd; font-size: 13px; width: 25%;">Zieldatum</th>
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