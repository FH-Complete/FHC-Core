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
			'postStudentProjektarbeitEndupload' => self::PERM_LOGGED
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

		if (!isset($projektarbeit_id) || isEmptyString($projektarbeit_id))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$ret = $this->ProjektarbeitModel->getProjektarbeitAbgabetermine($projektarbeit_id);
		
		$this->terminateWithSuccess($ret);
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
}

