<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

//require_once('../../../include/studiengang.class.php');
//require_once('../../../include/student.class.php');
//require_once('../../../include/datum.class.php');
//require_once('../../../include/mail.class.php');
//require_once('../../../include/benutzerberechtigung.class.php');
//require_once('../../../include/phrasen.class.php');
//require_once('../../../include/projektarbeit.class.php');
//require_once('../../../include/projektbetreuer.class.php');

class Lehre extends FHCAPI_Controller
{
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'lvStudentenMail' => self::PERM_LOGGED,
			'LV' => self::PERM_LOGGED,
			'Pruefungen' => self::PERM_LOGGED,
			'getStudentProjektarbeiten' => self::PERM_LOGGED, // TODO: abgabetool berechtigung?
			'getStudentProjektabgaben' => self::PERM_LOGGED,
			'postStudentProjektarbeitZwischenabgabe' => self::PERM_LOGGED,
			'postStudentProjektarbeitEndupload' => self::PERM_LOGGED,
			'getMitarbeiterProjektarbeiten' => self::PERM_LOGGED,
			'postProjektarbeitAbgabe' => self::PERM_LOGGED,
			'deleteProjektarbeitAbgabe' => self::PERM_LOGGED,
			'postSerientermin' => self::PERM_LOGGED,
			'fetchDeadlines' => self::PERM_LOGGED // TODO: mitarbeiter recht prüfen
		]);

		$this->load->library('PhrasesLib');

		$this->loadPhrases(
			array(
				'global',
				'ui',
				'abgabetool'
			)
		);

		$this->load->helper('hlp_sancho_helper');
		
		require_once(FHCPATH . 'include/studiengang.class.php');
		require_once(FHCPATH . 'include/student.class.php');
		require_once(FHCPATH . 'include/projektarbeit.class.php');
		require_once(FHCPATH . 'include/projektbetreuer.class.php');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

    /**
	 * constructs the emails of the groups from a lehrveranstaltung
	 */
    public function lvStudentenMail()
	{
        $lehreinheit_id = $this->input->get("lehreinheit_id",TRUE);
        
        // return early if the required parameter is missing
        if(!isset($lehreinheit_id))
        {
            $this->terminateWithError('Missing required parameter', self::ERROR_TYPE_GENERAL);
        }

        $this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
        
        $studentenMails = $this->LehreinheitModel->getStudentenMail($lehreinheit_id);

        $studentenMails = $this->getDataOrTerminateWithError($studentenMails);

		//convert array of objects into array of strings
		$studentenMails = array_map(function($element){
			return $element->mail;
		}, $studentenMails);

        $this->terminateWithSuccess($studentenMails);
	}

	public function LV($studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getLvsByStudentWithGrades(getAuthUID(), $studiensemester_kurzbz, getUserLanguage(), $lehrveranstaltung_id);

		$result = current($this->getDataOrTerminateWithError($result));
		
		$this->terminateWithSuccess($result);
	}

	/**
	 * fetches all Pruefungen of a student for a specific lehrveranstaltung
	 * if the student passed the Pruefung on the first attempt, no information about the Pruefungen is stored in the database 
	 * @param mixed $lehrveranstaltung_id
	 * @return void
	 */
	public function Pruefungen($lehrveranstaltung_id)
	{
		$this->load->model('education/Pruefung_model', 'PruefungModel');

		$result = $this->PruefungModel->getByStudentAndLv(getAuthUID(), $lehrveranstaltung_id, getUserLanguage());

		$result = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($result);
	}

	/**
	 * fetches all projektabgabetermine for a given projektarbeit_id used in cis4 student abgabetool
	 */
	public function getStudentProjektabgaben() {
		$projektarbeit_id = $this->input->get("projektarbeit_id",TRUE);

		// TODO: error messages
		
		if (!isset($projektarbeit_id) || isEmptyString($projektarbeit_id))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		$projektarbeit_obj = new projektarbeit();
		if($projektarbeit_id==-1)
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		if(!$projektarbeit_obj->load($projektarbeit_id))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		$paIsCurrent = $projektarbeit_obj->projektarbeitIsCurrent($projektarbeit_id);
		
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$ret = $this->ProjektarbeitModel->getProjektarbeitAbgabetermine($projektarbeit_id);
		
		// TODO: fetch zweitbetreuer
		
		$this->terminateWithSuccess(array($ret, $paIsCurrent));
	}

	/**
	 * fetches all projektarbeiten and betreuer for a given student_uid used in cis4 student abgabetool
	 */
	public function getStudentProjektarbeiten($uid)
	{
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		if (!isset($uid) || isEmptyString($uid))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		
		$isZugeteilterBetreuer = count($this->ProjektarbeitModel->checkZuordnung($uid, getAuthUID())->retval) > 0;
		$this->addMeta('isZugeteilterBetreuer', $isZugeteilterBetreuer);
		$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter(getAuthUID());
		
		if ($isMitarbeiter && $isZugeteilterBetreuer){
			$projektarbeiten = $this->ProjektarbeitModel->getStudentProjektarbeitenWithBetreuer($uid);
		} else {
			$projektarbeiten = $this->ProjektarbeitModel->getStudentProjektarbeitenWithBetreuer(getAuthUID());
		}

		$this->terminateWithSuccess(array($projektarbeiten, DOMAIN, $uid));	
	}
	
	

	/**
	 * projektarbeit - upload for zwischenabgaben in cis4 student abgabetool
	 */
	public function postStudentProjektarbeitZwischenabgabe() 
	{

		$projektarbeit_id = $_POST['projektarbeit_id'];
		$paabgabe_id = $_POST['paabgabe_id'];
		$student_uid = $_POST['student_uid'];
		$bperson_id = $_POST['bperson_id'];
		$paabgabetyp_kurzbz = $_POST['paabgabetyp_kurzbz'];

		if (!isset($projektarbeit_id) || isEmptyString($projektarbeit_id)
			|| !isset($paabgabe_id) || isEmptyString($paabgabe_id)
			|| !isset($student_uid) || isEmptyString($student_uid)
			|| !isset($paabgabetyp_kurzbz) || isEmptyString($paabgabetyp_kurzbz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		if ((isset($_FILES) and isset($_FILES['file']) and ! $_FILES['file']['error'])) {
			move_uploaded_file($_FILES['file']['tmp_name'], PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf');
			
			if(file_exists(PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf')) {

				exec('chmod 640 "'.PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf'.'"');

				$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
				$res = $this->PaabgabeModel->updatePaabgabe($paabgabe_id);
				
				$this->sendUploadEmail($bperson_id, $projektarbeit_id, $paabgabetyp_kurzbz, $student_uid);
				$this->terminateWithSuccess($res);
			} else {
				$this->terminateWithError('Error moving File');
			}
			
		} else {
			$this->terminateWithError('File missing');
		}
		
	}

	/**
	 * upload für finale abgaben aka Endupload in cis4 student abgabetool
	 */
	public function postStudentProjektarbeitEndupload() 
	{

		$projektarbeit_id = $_POST['projektarbeit_id'];
		$paabgabe_id = $_POST['paabgabe_id'];
		$student_uid = $_POST['student_uid'];
		$sprache = $_POST['sprache'];
		$abstract = $_POST['abstract'];
		$abstract_en = $_POST['abstract_en'];
		$schlagwoerter = $_POST['schlagwoerter'];
		$schlagwoerter_en = $_POST['schlagwoerter_en'];
		$seitenanzahl = $_POST['seitenanzahl'];
		$bperson_id = $_POST['bperson_id'];
		$paabgabetyp_kurzbz = $_POST['paabgabetyp_kurzbz'];
		
		if (!isset($projektarbeit_id) || isEmptyString($projektarbeit_id)
			|| !isset($paabgabe_id) || isEmptyString($paabgabe_id)
			|| !isset($student_uid) || isEmptyString($student_uid)
			|| !isset($paabgabetyp_kurzbz) || isEmptyString($paabgabetyp_kurzbz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		
		// TODO: maybe check for other params aswell?

		if ((isset($_FILES) and isset($_FILES['file']) and ! $_FILES['file']['error'])) {
			move_uploaded_file($_FILES['file']['tmp_name'], PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf');

			if(file_exists(PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf')) {

				// Loads Libraries
				$this->load->library('SignatureLib');
				
				// Check if the document is signed
				$signaturVorhanden = true;
				$signList = SignatureLib::list(PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf');
				if (is_array($signList) && count($signList) > 0)
				{
					// The document is signed
					$uploadedDocumentSigned = 'The document is signed';
				}
				elseif ($signList === null)
				{
					$uploadedDocumentSigned = 'WARNING: signature server error';
				}
				else
				{
					$signaturVorhanden = false;
					$uploadedDocumentSigned = 'No document signature found';
				}
				$this->addMeta('signaturInfo', $uploadedDocumentSigned);

				if ($signaturVorhanden === false)
				{
					$this->signaturFehltEmail($student_uid);
				}
				
				// TODO error handle get data has data the updates
				// update projektarbeit cols
				$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
				$this->ProjektarbeitModel->updateProjektarbeit($projektarbeit_id,$sprache,$abstract,$abstract_en
					,$schlagwoerter, $schlagwoerter_en, $seitenanzahl);

				
				// update paabgabe datum
				$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
				$res = $this->PaabgabeModel->updatePaabgabe($paabgabe_id);
				
				$this->sendUploadEmail($bperson_id, $projektarbeit_id, $paabgabetyp_kurzbz, $student_uid);

				$this->terminateWithSuccess($res);
			} else {
				$this->terminateWithError('Error moving File');
			}

		} else {
			$this->terminateWithError('File missing');
		}
		
	}

	private function signaturFehltEmail($student_uid) {
		
		
		// Mail an Studiengang wenn keine Signatur gefunden wurde
		$student = new student();
		if(!$student->load($student_uid))
			$this->terminateWithError($this->p->t('global','userNichtGefunden'), 'general');

		$stg_obj = new studiengang();
		if(!$stg_obj->load($student->studiengang_kz))
			$this->terminateWithError($this->p->t('global','fehlerBeimLesenAusDatenbank'), 'general');

		$subject = 'Abgabe ohne Signatur';
		$tomail = $stg_obj->email;
		$data = array(
			'vorname' => $student->vorname,
			'nachname' => $student->nachname,
			'studiengang' => $stg_obj->bezeichnung
		);

		$mailres = sendSanchoMail(
			'ParbeitsbeurteilungSiganturFehlt',
			$data,
			$tomail,
			$subject,
			'sancho_header_min_bw.jpg',
			'sancho_footer_min_bw.jpg'
		);
	}
	
	private function sendUploadEmail($bperson_id, $projektarbeit_id, $paabgabetyp_kurzbz, $student_uid) {

		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		
		$resBetr = $this->ProjektarbeitModel->getProjektbetreuerAnrede($bperson_id);

		$projektarbeit_obj = new projektarbeit();

		if(!$projektarbeit_obj->load($projektarbeit_id))
			$this->terminateWithError('Ungueltiger Eintrag');

		$num_rows_sem = $projektarbeit_obj->projektarbeitIsCurrent($projektarbeit_id);

		if(!is_numeric($num_rows_sem) || $num_rows_sem < 0)
		{
			$this->terminateWithError($this->p->t('abgabetool','fehlerAktualitaetProjektarbeit'), 'general');
		}
		
		foreach($resBetr->retval as $betreuerRow) {

			// query student benutzer view for every betreuer row
			$studentUser = $this->ProjektarbeitModel->getProjektarbeitBenutzer($student_uid)->retval[0];
			
			// TODO: hasdata, getData etc

			// 1. Begutachter mail ohne Token
			$mail_baselink = APP_ROOT."index.ci.php/extensions/FHC-Core-Projektarbeitsbeurteilung/ProjektarbeitsbeurteilungErstbegutachter";
			$mail_fulllink = "$mail_baselink?projektarbeit_id=".$projektarbeit_id."&uid=".$studentUser->uid;
			$projekttyp_kurzbz = $projektarbeit_obj->projekttyp_kurzbz;
			$subject = $projektarbeit_obj->projekttyp_kurzbz == 'Diplom' ? 'Masterarbeitsbetreuung' : 'Bachelorarbeitsbetreuung';
			$abgabetyp = $paabgabetyp_kurzbz == 'end' ? 'Endabgabe' : 'Zwischenabgabe';

			$maildata = array();
			$maildata['geehrt'] = "geehrte".($betreuerRow->anrede=="Herr"?"r":"");
			$maildata['anrede'] = $betreuerRow->anrede;
			$maildata['betreuer_voller_name'] = $betreuerRow->first;
			$maildata['student_anrede'] = $studentUser->anrede;
			$maildata['student_voller_name'] = trim($studentUser->titelpre." ".$studentUser->vorname." ".$studentUser->nachname." ".$studentUser->titelpost);
			$maildata['abgabetyp'] = $abgabetyp;
			$maildata['parbeituebersichtlink'] = "<p><a href='".APP_ROOT."cis/private/lehre/abgabe_lektor_frameset.html'>Zur Projektarbeitsübersicht</a></p>";
			$maildata['bewertunglink'] = $num_rows_sem >= 1 && $paabgabetyp_kurzbz == 'end' ? "<p><a href='$mail_fulllink'>Zur Beurteilung der Arbeit</a></p>" : "";
			$maildata['token'] = "";
			
			$mailres = sendSanchoMail(
				'ParbeitsbeurteilungEndupload',
				$maildata,
				$betreuerRow->mitarbeiter_uid."@".DOMAIN,
				$subject,
				'sancho_header_min_bw.jpg',
				'sancho_footer_min_bw.jpg',
				get_uid()."@".DOMAIN);

			if(!$mailres)
			{
				$this->terminateWithError($this->p->t('abgabetool', 'fehlerMailBegutachter'), 'general');
			}

			// 2. Begutachter mail, wenn Endabgabe, mit Token wenn extern
			if ($paabgabetyp_kurzbz == 'end')
			{
				// Zweitbegutachter holen
				$zweitbegutachter = new projektbetreuer();
				$zweitbegutachterRes = $zweitbegutachter->getZweitbegutachterWithToken($bperson_id, $projektarbeit_id, $studentUser->uid);

				if ($zweitbegutachterRes)
				{
					$zweitbegutachterResults = $zweitbegutachter->result;

					foreach ($zweitbegutachterResults as $begutachter)
					{
						// token generieren, wenn noch nicht vorhanden und notwendig (wird in methode überprüft)
						$tokenGenRes = $zweitbegutachter->generateZweitbegutachterToken($begutachter->person_id, $projektarbeit_id);

						if (!$tokenGenRes)
						{
							$this->terminateWithError($this->p->t('abgabetool', 'fehlerMailZweitBegutachter'), 'general');
						}
						
						// Zweitbegutachter (evtl. mit Token) holen
						$zweitbegutachterMitToken = new projektbetreuer();
						$begutachterMitTokenRes = $zweitbegutachterMitToken->getZweitbegutachterWithToken($bperson_id, $projektarbeit_id, $studentUser->uid, $begutachter->person_id);

						if (!$begutachterMitTokenRes)
						{
							$this->terminateWithError($this->p->t('abgabetool', 'fehlerMailZweitBegutachter'), 'general');
						}
						
						// Email an Zweitbegutachter senden
						if (isset($zweitbegutachterMitToken->result[0]))
						{
							$begutachterMitToken = $zweitbegutachterMitToken->result[0];

							$path = $begutachterMitToken->betreuerart_kurzbz == 'Zweitbegutachter' ? 'ProjektarbeitsbeurteilungZweitbegutachter' : 'ProjektarbeitsbeurteilungErstbegutachter';
							$mail_baselink = APP_ROOT."index.ci.php/extensions/FHC-Core-Projektarbeitsbeurteilung/$path";
							$mail_fulllink = "$mail_baselink?projektarbeit_id=".$projektarbeit_id."&uid=".$studentUser->uid;
							$intern = isset($begutachterMitToken->uid);
							$mail_link = $intern ? $mail_fulllink : $mail_baselink;

							$zweitbetmaildata = array();
							$zweitbetmaildata['geehrt'] = "geehrte" . ($begutachterMitToken->anrede == "Herr" ? "r" : "");
							$zweitbetmaildata['anrede'] = $begutachterMitToken->anrede;
							$zweitbetmaildata['betreuer_voller_name'] = $begutachterMitToken->voller_name;
							$zweitbetmaildata['student_anrede'] = $maildata['student_anrede'];
							$zweitbetmaildata['student_voller_name'] = $maildata['student_voller_name'];
							$zweitbetmaildata['abgabetyp'] = $abgabetyp;
							$zweitbetmaildata['parbeituebersichtlink'] = $intern ? $maildata['parbeituebersichtlink'] : "";
							$zweitbetmaildata['bewertunglink'] = $num_rows_sem >= 1 ? "<p><a href='$mail_link'>Zur Beurteilung der Arbeit</a></p>" : "";
							$zweitbetmaildata['token'] = $num_rows_sem >= 1 && isset($begutachterMitToken->zugangstoken) && !$intern ? "<p>Zugangstoken: " . $begutachterMitToken->zugangstoken . "</p>" : "";
							
							$mailres = sendSanchoMail(
								'ParbeitsbeurteilungEndupload',
								$zweitbetmaildata,
								$begutachterMitToken->email,
								$subject,
								'sancho_header_min_bw.jpg',
								'sancho_footer_min_bw.jpg',
								get_uid()."@".DOMAIN
							);

							if (!$mailres)
							{
								$this->terminateWithError($this->p->t('abgabetool', 'fehlerMailBegutachter'), 'general');
							}
						}
					}
				}
			}
		}
	}

	public function getMitarbeiterProjektarbeiten() {
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		$boolParamStr = $this->input->get('showall');
		$trueStrings = ['true', '1'];
		$falseStrings = ['false', '0'];

		// Handle missing or invalid parameter
		if ($boolParamStr === null) {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}
		$boolParamStrLower = strtolower($boolParamStr);

		if (in_array($boolParamStrLower, $trueStrings, true)) {
			$showAllBool = true;
		} elseif (in_array($boolParamStrLower, $falseStrings, true)) {
			$showAllBool = false;
		} else {
//			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}
		
		$projektarbeiten = $this->ProjektarbeitModel->getMitarbeiterProjektarbeiten(getAuthUID(), $showAllBool);
		
		$this->terminateWithSuccess(array($projektarbeiten, DOMAIN));
	}
	
	public function postProjektarbeitAbgabe() {
		$projektarbeit_id = $_POST['projektarbeit_id'];
		$paabgabe_id = $_POST['paabgabe_id'];
		$paabgabetyp_kurzbz = $_POST['paabgabetyp_kurzbz'];
		$datum = $_POST['datum'];
		$fixtermin = $_POST['fixtermin'];
		$kurzbz = $_POST['kurzbz'];

		if (!isset($projektarbeit_id) || isEmptyString($projektarbeit_id)
			|| !isset($paabgabe_id) || isEmptyString($paabgabe_id)
			|| !isset($datum) || isEmptyString($datum)
			|| !isset($datum) || isEmptyString($datum)
			|| !isset($paabgabetyp_kurzbz) || isEmptyString($paabgabetyp_kurzbz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
		
		if($paabgabe_id == -1) {
			$result = $this->PaabgabeModel->insert(
				array(
					'projektarbeit_id' => $projektarbeit_id,
					'paabgabetyp_kurzbz' => $paabgabetyp_kurzbz,
					'fixtermin' => $fixtermin,
					'datum' => $datum,
					'kurzbz' => $kurzbz,
					'insertvon' => getAuthUID(),
					'insertamum' => date('Y-m-d H:i:s')
				)
			);
			
			$this->terminateWithSuccess($result);
		} else {
			$result = $this->PaabgabeModel->update(
				$paabgabe_id,
				array(
					'paabgabetyp_kurzbz' => $paabgabetyp_kurzbz,
					'datum' => $datum,
					'kurzbz' => $kurzbz,
					'updatevon' => getAuthUID(),
					'updateamum' => date('Y-m-d H:i:s')
				)
			);

			$this->terminateWithSuccess($result);
		}
	}
	
	public function deleteProjektarbeitAbgabe() {
		$paabgabe_id = $_POST['paabgabe_id'];
		
		if (!isset($paabgabe_id) || isEmptyString($paabgabe_id))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');

		$result = $this->PaabgabeModel->load($paabgabe_id);
		$result = $this->getDataOrTerminateWithError($result);
		
		if(count($result) == 0) 
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		// TODO: berechtigung?
		if($result[0]->insertvon === getAuthUID()) {
			$result = $this->PaabgabeModel->delete($paabgabe_id);
			$result = $this->getDataOrTerminateWithError($result);
			$this->terminateWithSuccess($result);
		}

		$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
	}

	/**
	 * endpoint for adding the same paabgabe for multiple projektarbeiten
	 * can be slow for large n since it queries twice per projektarbeit_id
	 */
	public function postSerientermin() {
		$projektarbeit_ids = $_POST['projektarbeit_ids'];
		$datum = $_POST['datum'];
		$paabgabetyp_kurzbz = $_POST['paabgabetyp_kurzbz'];
		$bezeichnung = $_POST['bezeichnung'];
		$kurzbz = $_POST['kurzbz'];

		if (!isset($projektarbeit_ids) || !is_array($projektarbeit_ids) || empty($projektarbeit_ids)
			|| !isset($datum) || isEmptyString($datum)
			|| !isset($kurzbz) || isEmptyString($kurzbz)
			|| !isset($bezeichnung) || isEmptyString($bezeichnung)
			|| !isset($paabgabetyp_kurzbz) || isEmptyString($paabgabetyp_kurzbz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		
		// old script checks if there already are tbl_paabgabe entries with exact date, type & kurzbz
		// for each termin - good to check that in principle but should not matter in this place. if necessary
		// duplicate abgabetermine can be easily deleted manually, also via cronjob@night.
		
		// since this entry includes the kurzbz string match, it should have only ever mattered when there were
		// multiple users entering the exact same set of (date, type, kurzbz) - which is a much more narrow case than the
		// general "saveMultiple" function should handle
		
		// old script afterwards again queries if user is not the zweitbetreuer of any id - this is blocked in the ui
		// and should never unintentionally happen
		
		// TODO: check berechtigung &/|| zuordnung?
		
		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		
		$res = [];
		foreach ($projektarbeit_ids as $projektarbeit_id) {
			
			$result = $this->PaabgabeModel->insert(
				array(
					'projektarbeit_id' => $projektarbeit_id,
					'paabgabetyp_kurzbz' => $paabgabetyp_kurzbz,
					'fixtermin' => false,
					'datum' => $datum,
					'kurzbz' => $kurzbz,
					'insertvon' => getAuthUID(),
					'insertamum' => date('Y-m-d H:i:s')
				)
			);
			
			$data = $this->getDataOrTerminateWithError($result);
			
//			$res[] = $data;

			// send mail to student
			$result = $this->ProjektarbeitModel->getStudentInfoForProjektarbeitId($projektarbeit_id);
			$data = $this->getDataOrTerminateWithError($result);

//			$this->addMeta('emaildata'.$projektarbeit_id, $data);
			
			$datetime = new DateTime($datum);
			$dateEmailFormatted = $datetime->format('d.m.Y');
			
			$anredeFillString = $data[0]->anrede=="Herr"?"r":"";
			
			$fullFormattedNameString = trim($data[0]->titelpre." ".$data[0]->vorname." ".$data[0]->nachname." ".$data[0]->titelpost);
			$res[] = $fullFormattedNameString;
			
			// Prepare mail content
			$body_fields = array(
				'anrede' => $data[0]->anrede,
				'anredeFillString' => $anredeFillString,
				'datum' => $dateEmailFormatted,
				'bezeichnung' => $bezeichnung,
				'fullFormattedNameString' => $fullFormattedNameString,
				'kurzbz' => $kurzbz
			);

			$email = $data[0]->uid."@".DOMAIN;
			
			sendSanchoMail(
				'neuerAbgabetermin',
				$body_fields,
				$email,
				$this->p->t('abgabetool', 'neuerTerminBachelorMasterbetreuung')
			);
		}
		
		$this->terminateWithSuccess($res);
		
	}
	
	public function fetchDeadlines() {
		$person_id = $_POST['person_id'];
		
		if (!isset($person_id) || isEmptyString($person_id))
			$person_id = getAuthPersonId();
		
		
		if($person_id !== getAuthPersonId()) {
			$this->load->library('PermissionLib');
			$isAdmin = $this->permissionlib->isBerechtigt('admin');
			if(!$isAdmin) $this->terminateWithError($this->p->t('ui', 'keineBerechtigung'), 'general');
		}

		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
		$result = $this->PaabgabeModel->getDeadlines($person_id);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}
}

