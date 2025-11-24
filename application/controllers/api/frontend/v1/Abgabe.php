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

class Abgabe extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getConfig' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_lektor:rw'),
			'getStudentProjektarbeiten' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_student:rw', 'basis/abgabe_lektor:rw'),
			'getStudentProjektabgaben' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_student:rw', 'basis/abgabe_lektor:rw'),
			'postStudentProjektarbeitZwischenabgabe' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_student:rw'),
			'postStudentProjektarbeitEndupload' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_student:rw'),
			'getMitarbeiterProjektarbeiten' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_lektor:rw'),
			'postProjektarbeitAbgabe' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_lektor:rw'),
			'deleteProjektarbeitAbgabe' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_lektor:rw'),
			'postSerientermin' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_lektor:rw'),
			'fetchDeadlines' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_lektor:rw'),
			'getPaAbgabetypen' => self::PERM_LOGGED,
			'getNoten' => self::PERM_LOGGED,
			'getProjektarbeitenForStudiengang' =>array('basis/abgabe_assistenz:rw'),
			'getStudiengaenge' => array('basis/abgabe_assistenz:rw'),
			'getStudentProjektarbeitAbgabeFile' => array('basis/abgabe_student:rw', 'basis/abgabe_lektor:rw', 'basis/abgabe_assistenz:rw'),
			'postStudentProjektarbeitZusatzdaten' => array('basis/abgabe_lektor:rw', 'basis/abgabe_assistenz:rw')
			]);

		$this->load->library('PhrasesLib');
		$this->load->library('SignatureLib');

		// Loads LogLib with different debug trace levels to get data of the job that extends this class
		// It also specify parameters to set database fields
		$this->load->library('LogLib', array(
			'classIndex' => 5,
			'functionIndex' => 5,
			'lineIndex' => 4,
			'dbLogType' => 'API', // required
			'dbExecuteUser' => 'RESTful API',
			'requestId' => 'API',
			'requestDataFormatter' => function ($data) {
				return json_encode($data);
			}
		), 'logLib');
		
		$this->loadPhrases(
			array(
				'global',
				'ui',
				'abgabetool'
			)
		);
		$this->load->config('abgabe');
		$this->load->helper('hlp_sancho_helper');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * loads config related to abgabetool, found in application/config/abgabe
	 */
	public function getConfig() {
		$this->load->config('abgabe');
		$old_abgabe_beurteilung_link =$this->config->item('old_abgabe_beurteilung_link');
		$turnitin_link =$this->config->item('turnitin_link');
		
		$ret = array(
			'old_abgabe_beurteilung_link' => $old_abgabe_beurteilung_link,
			'turnitin_link' => $turnitin_link
		);
		
		$this->terminateWithSuccess($ret);
	}
	
	/**
	 * fetches all projektabgabetermine for a given projektarbeit_id used in cis4 student abgabetool & lektor abgabetool
	 */
	public function getStudentProjektabgaben() {
		$projektarbeit_id = $this->input->get("projektarbeit_id",TRUE);
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		if (!isset($projektarbeit_id) || isEmptyString($projektarbeit_id))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		$result = $this->ProjektarbeitModel->load($projektarbeit_id);
		$projektarbeitArr = $this->getDataOrTerminateWithError($result);

		if(count($projektarbeitArr) > 0) {
			$projektarbeit = $projektarbeitArr[0];
		} else {
			$this->terminateWithError($this->p->t('global','projektarbeitNichtGefunden'), 'general');
		}
		
		$paIsCurrent = $this->ProjektarbeitModel->projektarbeitIsCurrent($projektarbeit_id);

		$ret = $this->ProjektarbeitModel->getProjektarbeitAbgabetermine($projektarbeit_id);

		foreach($ret->retval as $termin) {
			$this->checkAbgabeSignatur($termin, $projektarbeit);
		}
		
		$this->terminateWithSuccess(array($ret, $paIsCurrent));
	}

	/**
	 * fetches all projektarbeiten and betreuer for a given student_uid used in cis4 student abgabetool
	 */
	public function getStudentProjektarbeiten()
	{
		$uid = $this->input->get("uid",TRUE);
		
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		if (!isset($uid) || isEmptyString($uid)) {
			$uid = getAuthUID();
		}

		$isZugeteilterBetreuer = count($this->ProjektarbeitModel->checkZuordnung($uid, getAuthUID())->retval) > 0;
//		$this->addMeta('isZugeteilterBetreuer', $isZugeteilterBetreuer);
		$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter(getAuthUID());

		if ($isMitarbeiter) {
			$result = $this->ProjektarbeitModel->getStudentProjektarbeitenWithBetreuer($uid);
		} else {
			$result = $this->ProjektarbeitModel->getStudentProjektarbeitenWithBetreuer(getAuthUID());
		}

		$projektarbeiten = getData($result);
		
		if(count($projektarbeiten)) {
			foreach($projektarbeiten as $pa) {
				$result = $this->ProjektarbeitModel->getProjektbetreuerEmail($pa->projektarbeit_id);
				
				$data = getData($result);
				if(count($data) > 0) {
					$pa->email = $data[0]->private_email;
				}
				if($pa->zweitbetreuer_person_id !== null) {
					
					// TODO: see assistenz query in projektarbeit_model
					
					// zweitbetreuer info since the 'getStudentProjektarbeitenWithBetreuer' query got quiete large,
					// enjoy optimizing that one in 2038. we need this to render a string like
					// Zweitbegutachter: FH-Prof. PD DI Dr. techn. Vorname Nachname MBA

					$result = $this->ProjektarbeitModel->getProjektbetreuerAnrede($pa->zweitbetreuer_person_id);

					$data = getData($result);
					if(count($data) > 0) {
						$pa->zweitbetreuer = $data[0];
					}
				}
			}
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
				$res = $this->PaabgabeModel->update($paabgabe_id, array(
					'abgabedatum' => date('Y-m-d'),
					'updatevon' => getAuthUID(),
					'updateamum' => date('Y-m-d H:i:s')
				));

				// enable this when lektor cry that they dont receive enough emails
//				$this->sendUploadEmail($bperson_id, $projektarbeit_id, $paabgabetyp_kurzbz, $student_uid);

				$this->logLib->logInfoDB(array('zwischenupload',$res, array(
					'abgabedatum' => date('Y-m-d'),
					'updatevon' => getAuthUID(),
					'updateamum' => date('Y-m-d H:i:s')
				), getAuthUID(), getAuthPersonId(), $student_uid));
				
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
			|| !isset($paabgabetyp_kurzbz) || isEmptyString($paabgabetyp_kurzbz)
			|| !isset($abstract) || !isset($abstract_en) // endupload zusatzdaten can be empty but should never be null
			|| !isset($schlagwoerter) || !isset($schlagwoerter_en)
			|| !isset($seitenanzahl) || !isset($sprache))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		if ((isset($_FILES) and isset($_FILES['file']) and ! $_FILES['file']['error'])) {
			move_uploaded_file($_FILES['file']['tmp_name'], PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf');

			if(file_exists(PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf')) {

				$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

				$result = $this->ProjektarbeitModel->load($projektarbeit_id);
				$projektarbeitArr = $this->getDataOrTerminateWithError($result);

				if(count($projektarbeitArr) > 0) {
					$projektarbeit = $projektarbeitArr[0];
				} else {
					$this->terminateWithError($this->p->t('global','projektarbeitNichtGefunden'), 'general');
				}

				$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
				$result = $this->PaabgabeModel->load($paabgabe_id);
				$paabgabeArr = $this->getDataOrTerminateWithError($result);

				if(count($paabgabeArr) > 0) {
					$paabgabe = $paabgabeArr[0];
				} else {
					$this->terminateWithError($this->p->t('global','projektabgabeNichtGefunden'), 'general');
				}
				
				$this->checkAbgabeSignatur($paabgabe, $projektarbeit);
				$signaturstatus = $paabgabe->signatur;
				
				if ($paabgabe->signatur === false)
				{
					// TODO: decide if we need this email at all, if yes -> nightly job sends email
//					$this->signaturFehltEmail($student_uid);
				}
				
				// update projektarbeit cols
				$this->ProjektarbeitModel->updateProjektarbeit($projektarbeit_id,$sprache,$abstract,$abstract_en
					,$schlagwoerter, $schlagwoerter_en, $seitenanzahl);


				// update paabgabe datum
				$res = $this->PaabgabeModel->update($paabgabe_id, array(
					'abgabedatum' => date('Y-m-d'),
					'updatevon' => getAuthUID(),
					'updateamum' => date('Y-m-d H:i:s')
				));

				$res = $this->PaabgabeModel->load($res->retval);
				$abgabe = getData($res)[0];
				$abgabe->signatur = $signaturstatus;
					
				$this->sendUploadEmail($bperson_id, $projektarbeit_id, $paabgabetyp_kurzbz, $student_uid);

				$this->logLib->logInfoDB(array('endupload',$res, array(
					'abgabedatum' => date('Y-m-d'),
					'updatevon' => getAuthUID(),
					'updateamum' => date('Y-m-d H:i:s')
				), getAuthUID(), getAuthPersonId(), array($projektarbeit_id,$sprache,$abstract,$abstract_en
				,$schlagwoerter, $schlagwoerter_en, $seitenanzahl)));
				
				$this->terminateWithSuccess($abgabe);
			} else {
				$this->terminateWithError('Error moving File');
			}

		} else {
			$this->terminateWithError('File missing');
		}

	}

	private function signaturFehltEmail($student_uid) {
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->StudentModel->addJoin('public.tbl_benutzer', 'ON (public.tbl_benutzer.uid = public.tbl_student.student_uid)');
		$this->StudentModel->addJoin('public.tbl_person', 'person_id');

//		$this->StudentModel->load($student_uid); -> this loads all students for some reason
		$result = $this->StudentModel->loadWhere(array('student_uid' => $student_uid));
		$this->StudentModel->resetQuery();
		
		$studentArr = $this->getDataOrTerminateWithError($result);

		if(count($studentArr) > 0) {
			$student = $studentArr[0];
		} else {
			$this->terminateWithError($this->p->t('global','userNichtGefunden'), 'general');
		}
		
		$result = $this->StudiengangModel->load($student->studiengang_kz);
		$studiengangArr = $this->getDataOrTerminateWithError($result);

		if(count($studiengangArr) > 0) {
			$stg_obj = $studiengangArr[0];
		} else {
			$this->terminateWithError($this->p->t('global','fehlerBeimLesenAusDatenbank'), 'general');
		}

		$subject = 'Abgabe ohne Signatur';
		$tomail = $stg_obj->email;
		$data = array(
			'vorname' => $student->vorname,
			'nachname' => $student->nachname,
			'studiengang' => $stg_obj->bezeichnung
		);

		
//		$mailres = sendSanchoMail(
//			'ParbeitsbeurteilungSiganturFehlt',
//			$data,
//			$tomail,
//			$subject,
//			'sancho_header_min_bw.jpg',
//			'sancho_footer_min_bw.jpg'
//		);
	}

	private function sendUploadEmail($bperson_id, $projektarbeit_id, $paabgabetyp_kurzbz, $student_uid) {

		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		$resBetr = $this->ProjektarbeitModel->getProjektbetreuerAnrede($bperson_id);


		$result = $this->ProjektarbeitModel->load($projektarbeit_id);
		$projektarbeitArr = $this->getDataOrTerminateWithError($result);

		if(count($projektarbeitArr) > 0) {
			$projektarbeit = $projektarbeitArr[0];
		} else {
			$this->terminateWithError($this->p->t('global','projektarbeitNichtGefunden'), 'general');
		}

		$projektarbeitIsCurrent = $this->ProjektarbeitModel->projektarbeitIsCurrent($projektarbeit_id);
		if(!$projektarbeitIsCurrent) {
			$this->terminateWithError($this->p->t('abgabetool','c4fehlerAktualitaetProjektarbeit'), 'general');
		}

		// Link to Abgabetool
		if (defined('CIS4') && CIS4) {
			$ci3BootstrapFilePath = "cis.php";
		} else {
			$ci3BootstrapFilePath = "index.ci.php";
		}

		$path = $this->_ci->config->item('URL_MITARBEITER');
		$url = APP_ROOT.$path;

//		$this->addMeta('betreuerArray', $resBetr->retval);
		
		// getProjektbetreuerAnrede fetches distinct on person_id, so there should be one row. zweitbetreuer is handled seperately afterwards 
		foreach($resBetr->retval as $betreuerRow) {

			// query student benutzer view for every betreuer row
			$studentUser = $this->ProjektarbeitModel->getProjektarbeitBenutzer($student_uid)->retval[0];
			
			// 1. Begutachter mail ohne Token
			$mail_baselink = APP_ROOT."index.ci.php/extensions/FHC-Core-Projektarbeitsbeurteilung/ProjektarbeitsbeurteilungErstbegutachter";
			$mail_fulllink = "$mail_baselink?projektarbeit_id=".$projektarbeit_id."&uid=".$studentUser->uid;
			$projekttyp_kurzbz = $projektarbeit->projekttyp_kurzbz;
			$subject = $projektarbeit->projekttyp_kurzbz == 'Diplom' ? 'Masterarbeitsbetreuung' : 'Bachelorarbeitsbetreuung';
			$abgabetyp = $paabgabetyp_kurzbz == 'end' ? 'Endabgabe' : 'Zwischenabgabe';

			$maildata = array();
			$maildata['geehrt'] = "geehrte".($betreuerRow->anrede=="Herr"?"r":"");
			$maildata['anrede'] = $betreuerRow->anrede;
			$maildata['betreuer_voller_name'] = $betreuerRow->first;
			$maildata['student_anrede'] = $studentUser->anrede;
			$maildata['student_voller_name'] = trim($studentUser->titelpre." ".$studentUser->vorname." ".$studentUser->nachname." ".$studentUser->titelpost);
			$maildata['abgabetyp'] = $abgabetyp;
			$maildata['parbeituebersichtlink'] = "<p><a href='$url'>Zur Projektarbeitsübersicht</a></p>";
			$maildata['bewertunglink'] = $projektarbeitIsCurrent && $paabgabetyp_kurzbz == 'end' ? "<p><a href='$mail_fulllink'>Zur Beurteilung der Arbeit</a></p>" : "";
			$maildata['token'] = "";
			
			$email = $this->getProjektbetreuerEmail($projektarbeit_id);
			
			if(!$email) $this->terminateWithError($this->p->t('abgabetool', 'fehlerMailBegutachter'), 'general');

//			$this->addMeta('$maildata', $maildata);

			$mailres = sendSanchoMail(
				'ParbeitsbeurteilungEndupload',
				$maildata,
				$email,
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
				$this->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');
				$zweitbegutachterRetval = getData($this->ProjektbetreuerModel->getZweitbegutachterWithToken($bperson_id, $projektarbeit_id, $studentUser->uid));
				
//				$this->addMeta('$zweitbegutachterRes', $zweitbegutachterRetval);
				
				if ($zweitbegutachterRetval && count($zweitbegutachterRetval) > 0)
				{

					foreach ($zweitbegutachterRetval as $begutachter)
					{
						// token generieren, wenn noch nicht vorhanden und notwendig (wird in methode überprüft)
						$tokenGenRes = $this->ProjektbetreuerModel->generateZweitbegutachterToken($begutachter->person_id, $projektarbeit_id);

						if (!$tokenGenRes)
						{
							$this->terminateWithError($this->p->t('abgabetool', 'fehlerMailZweitBegutachter'), 'general');
						}
						
						$begutachterMitTokenRetval = getData($this->ProjektbetreuerModel->getZweitbegutachterWithToken($bperson_id, $projektarbeit_id, $studentUser->uid, $begutachter->person_id));
						
//						$this->addMeta('$begutachterMitTokenRetval', $begutachterMitTokenRetval);
						
						if (!$begutachterMitTokenRetval && count($begutachterMitTokenRetval) <= 0)
						{
							$this->terminateWithError($this->p->t('abgabetool', 'fehlerMailZweitBegutachter'), 'general');
						}
						
						$begutachterMitToken = $begutachterMitTokenRetval[0];

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
						$zweitbetmaildata['bewertunglink'] = $projektarbeitIsCurrent ? "<p><a href='$mail_link'>Zur Beurteilung der Arbeit</a></p>" : "";
						$zweitbetmaildata['token'] = $projektarbeitIsCurrent && isset($begutachterMitToken->zugangstoken) && !$intern ? "<p>Zugangstoken: " . $begutachterMitToken->zugangstoken . "</p>" : "";
						
//						$this->addMeta('$zweitbetmaildata', $zweitbetmaildata);
						
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
		}

		$projektarbeiten = $this->ProjektarbeitModel->getMitarbeiterProjektarbeiten(getAuthUID(), $showAllBool);

		$this->terminateWithSuccess(array($projektarbeiten, DOMAIN));
	}
	
	// called by abgabetool/mitarbeiter when adding a new termin
	public function postProjektarbeitAbgabe() {
		$projektarbeit_id = $_POST['projektarbeit_id'];
		$paabgabe_id = $_POST['paabgabe_id'];
		$paabgabetyp_kurzbz = $_POST['paabgabetyp_kurzbz'];
		$datum = $_POST['datum'];
		$fixtermin = $_POST['fixtermin'];
		$kurzbz = $_POST['kurzbz'];
		$note = $_POST['note'];
		$beurteilungsnotiz = $_POST['beurteilungsnotiz'];
		$upload_allowed = $_POST['upload_allowed'];
		$betreuer_person_id = $_POST['betreuer_person_id'];

		if (!isset($projektarbeit_id) || isEmptyString($projektarbeit_id)
			|| !isset($paabgabe_id) || isEmptyString($paabgabe_id)
			|| !isset($datum) || isEmptyString($datum)
			|| !isset($kurzbz)
			|| !isset($paabgabetyp_kurzbz) || isEmptyString($paabgabetyp_kurzbz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');

		$existingPaabgabe = null; 
		if($paabgabe_id == -1) {
			$result = $this->PaabgabeModel->insert(
				array(
					'projektarbeit_id' => $projektarbeit_id,
					'paabgabetyp_kurzbz' => $paabgabetyp_kurzbz,
					'fixtermin' => $fixtermin,
					'datum' => $datum,
					'kurzbz' => $kurzbz,
					'note' => $note,
					'beurteilungsnotiz' => $beurteilungsnotiz,
					'upload_allowed' => $upload_allowed,
					'insertvon' => getAuthUID(),
					'insertamum' => date('Y-m-d H:i:s')
				)
			);
			// TODO: consider this in nightly email job
			$this->logLib->logInfoDB(array('paabgabe created',$result, getAuthUID(), getAuthPersonId()));
		} else {
			// load existing entry of paabgabe and check if note has changed to negativ, to avoid sending when
			// only notiz has changed.
			
			// TODO: what if paabgabe is a qualgate1, is benotet negativ and then its type is changed to gate2?
			
			$existingResult = $this->PaabgabeModel->load($paabgabe_id);
			$existingPaabgabeArr = getData($existingResult);
			if(count($existingPaabgabeArr) > 0) $existingPaabgabe = $existingPaabgabeArr[0];
			
			$result = $this->PaabgabeModel->update(
				$paabgabe_id,
				array(
					'paabgabetyp_kurzbz' => $paabgabetyp_kurzbz,
					'datum' => $datum,
					'kurzbz' => $kurzbz,
					'note' => $note,
					'beurteilungsnotiz' => $beurteilungsnotiz,
					'upload_allowed' => $upload_allowed,
					'updatevon' => getAuthUID(),
					'updateamum' => date('Y-m-d H:i:s')
				)
			);

			// TODO: consider this in nightly email job
			
			$this->logLib->logInfoDB(array('paabgabe updated',$result, array(
				'paabgabetyp_kurzbz' => $paabgabetyp_kurzbz,
				'datum' => $datum,
				'kurzbz' => $kurzbz,
				'note' => $note,
				'beurteilungsnotiz' => $beurteilungsnotiz,
				'upload_allowed' => $upload_allowed,
				'updatevon' => getAuthUID(),
				'updateamum' => date('Y-m-d H:i:s')
			), getAuthUID(), getAuthPersonId()));
		}
		
		// check if $paaabgabe is a qual gate and its note is deemed negative
		// -> send email to student with that info
		$paabgabe_id = $this->getDataOrTerminateWithError($result);
		
		$result = $this->PaabgabeModel->load($paabgabe_id);
		$paabgabeArr = $this->getDataOrTerminateWithError($result);
		$paabgabe = $paabgabeArr[0];
//		$this->addMeta('paabgabe', $paabgabeArr);

		// check if abgabe even has note
		if($paabgabe->note) {
			$this->load->model('education/Note_model', 'NoteModel');
			$result = $this->NoteModel->load($paabgabe->note);
			$noteArr = $this->getDataOrTerminateWithError($result);
			$note = $noteArr[0];
			if($note->positiv === false) {
//				$this->addMeta('noteNegativ', true);
				
				if($existingPaabgabe && $existingPaabgabe->note) {
					$result = $this->NoteModel->load($paabgabe->note);
					$noteArr = $this->getDataOrTerminateWithError($result);
					$note = $noteArr[0];
					if($note->positiv === false) {
						// do nothing since this means $beurteilungsnotiz change or smth else
					} else { // benotung legitimately changed -> email
						$this->sendQualGateNegativEmail($projektarbeit_id, $betreuer_person_id, $paabgabe);
					}
				} else { // nothing existing previously -> send that mail
					$this->sendQualGateNegativEmail($projektarbeit_id, $betreuer_person_id, $paabgabe);
				}
				
			}
		}
		
		$this->terminateWithSuccess([$paabgabe, $existingPaabgabe]);
	}

	public function deleteProjektarbeitAbgabe() {
		$paabgabe_id = $_POST['paabgabe_id'];

		if (!isset($paabgabe_id) || isEmptyString($paabgabe_id))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');

		$paabgabeResult = $this->PaabgabeModel->load($paabgabe_id);
		$paabgabeArr = $this->getDataOrTerminateWithError($paabgabeResult);

		if(count($paabgabeArr) == 0)
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		
		if($paabgabeArr[0]->insertvon === getAuthUID()) {
			$result = $this->PaabgabeModel->delete($paabgabe_id);
			$result = $this->getDataOrTerminateWithError($result);

			// TODO: consider this in nightly email job
			
			$this->logLib->logInfoDB(array($paabgabeArr[0], getAuthUID(), getAuthPersonId()));
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
		$fixtermin = $_POST['fixtermin'];

		if (!isset($projektarbeit_ids) || !is_array($projektarbeit_ids) || empty($projektarbeit_ids)
			|| !isset($datum) || isEmptyString($datum)
			|| !isset($kurzbz)
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

		// TODO: maybe check for betreuer zuordnung?

		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		$res = [];
		$abgaben = [];
		foreach ($projektarbeit_ids as $projektarbeit_id) {

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

			$dataAbgabe  = $this->getDataOrTerminateWithError($result);
			
			$abgaben[]= getData($this->PaabgabeModel->load($dataAbgabe))[0];
		}

		$this->logLib->logInfoDB(array('serientermin angelegt',$res, getAuthUID(), getAuthPersonId()));

		$this->terminateWithSuccess($abgaben);

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

	public function getPaAbgabetypen() {
		$this->load->model('education/Paabgabetyp_model', 'PaabgabetypModel');

		$result = $this->PaabgabetypModel->getAll();
		$paabgabetypen = $this->getDataOrTerminateWithError($result);
		
		
		$this->terminateWithSuccess($paabgabetypen);
	}
	
	private function getProjektbetreuerEmail($projektarbeit_id) {
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$result = $this->ProjektarbeitModel->getProjektbetreuerEmail($projektarbeit_id);
		$email = $this->getDataOrTerminateWithError($result);
		return $email[0]->private_email;
	}

	//TODO: SWITCH TO NOTEN API ONCE NOTENTOOL IS IN MASTER TO AVOID DUPLICATE API

	/**
	 * GET METHOD
	 * returns List of all available & active NotenOptions
	 */
	public function getNoten() {
		$this->load->model('education/Note_model', 'NoteModel');

		$result = $this->NoteModel->getAllActive();
		$noten = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($noten);
	}
	
	private function sendQualGateNegativEmail($projektarbeit_id, $betreuer_person_id, $paabgabe) {
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		
		$result = $this->ProjektarbeitModel->load($projektarbeit_id);
		$projektarbeitArr = $this->getDataOrTerminateWithError($result);
		$projektarbeit = $projektarbeitArr[0];
		
		$result = $this->ProjektarbeitModel->getProjektbetreuerAnrede($betreuer_person_id);
		$anredeArr = $this->getDataOrTerminateWithError($result);
		$anrede = $anredeArr[0];
		
		$student_uid = $projektarbeit->student_uid;

		$this->load->model('education/Paabgabetyp_model', 'PaabgabetypModel');
		$result = $this->PaabgabetypModel->load($paabgabe->paabgabetyp_kurzbz);
		$paabgabetyp_kurzbzArr = $this->getDataOrTerminateWithError($result);
		$paabgabetyp_kurzbz = $paabgabetyp_kurzbzArr[0];
		
		// Mail an Student wenn Qualgate negativ beurteilt wurde
		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->load([$student_uid]);
		$studentArr = $this->getDataOrTerminateWithError($result);
		$student = $studentArr[0];

		if(!$student) {
			$this->terminateWithError($this->p->t('global','userNichtGefunden'), 'general');
		}
		
		$subject = $this->p->t('abgabetool', 'c4qualgateNegativEmailSubject');
		$tomail = $student_uid.'@'.DOMAIN;
		
		$datetime = new DateTime($paabgabe->datum);
		$dateEmailFormatted = $datetime->format('d.m.Y');
		
		$data = array(
			'betreuerfullname' => $anrede->first,
			'qualgatebezeichnung' => $paabgabetyp_kurzbz->bezeichnung,
			'datum' => $dateEmailFormatted,
			'projektarbeitname' => $projektarbeit->titel
		);

//		$this->addMeta('$emaildata', $data);
		
		// students still get theirs on event, since it is very unlikely that this
		// leads to spam on their end
		
		$mailres = sendSanchoMail(
			'QualGateNegativ',
			$data,
			$tomail,
			$subject
		);
	}
	
	public function getProjektarbeitenForStudiengang() {
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		
		$studiengang_kz = $this->input->get("studiengang_kz", TRUE);
		$benotet = $this->input->get("benotet", TRUE);
		
		if (!isset($studiengang_kz) || isEmptyString($studiengang_kz))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		
		
		$result = $this->ProjektarbeitModel->getProjektarbeitenForStudiengang($studiengang_kz, $benotet);
		$projektarbeiten = $this->getDataOrTerminateWithError($result);

		if(count($projektarbeiten) == 0) { // avoid further abgabetermin queries if the are no projektarbeiten
			$this->terminateWithSuccess(array($projektarbeiten, DOMAIN));
		}
		
		$mapFunc = function($projektarbeit) {
			return $projektarbeit->projektarbeit_id;
		};
		$projektarbeiten_ids = array_map($mapFunc, $projektarbeiten);
		
		$ret = $this->ProjektarbeitModel->getProjektarbeitenAbgabetermine($projektarbeiten_ids);
		$projektabgaben = $this->getDataOrTerminateWithError($ret);
		
		// map the abgaben into projektarbeiten
		foreach($projektarbeiten as $projektarbeit) {
			$filterFunc = function($projektabgabe) use ($projektarbeit) {
				return $projektabgabe->projektarbeit_id == $projektarbeit->projektarbeit_id;
			};
			
			$projektarbeit->abgabetermine = array_values(array_filter($projektabgaben, $filterFunc));

			// check the signature status for enduploads
			foreach($projektarbeit->abgabetermine as $abgabe) {
				$this->checkAbgabeSignatur($abgabe, $projektarbeit);
			}
		}
		
		$this->terminateWithSuccess(array($projektarbeiten, DOMAIN));
	}
	
	// TODO: this could be in a generic info controller and resused
	public function getStudiengaenge() {
		$this->load->library('PermissionLib');
		
		$stg_allowed = $this->permissionlib->getSTG_isEntitledFor('basis/abgabe_assistenz:rw');
		
		if($stg_allowed == false) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'), 'general');
		}

		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		
		$result = $this->StudiengangModel->getStudiengaengeFiltered($stg_allowed);
		$data = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($data);
	}

	public function getStudentProjektarbeitAbgabeFile()
	{
		$this->load->helper('download');

		$paabgabe_id = $this->input->get('paabgabe_id');
		$student_uid = $this->input->get('student_uid');

		if (!isset($paabgabe_id) || isEmptyString($paabgabe_id) || !isset($student_uid) || isEmptyString($student_uid))
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');

		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		$isZugeteilterBetreuer = count($this->ProjektarbeitModel->checkZuordnung($student_uid, getAuthUID())->retval) > 0;
		$isAssistenz = $this->permissionlib->isBerechtigt('extension/abgabe_assistenz');
		
		if(getAuthUID() == $student_uid || $isZugeteilterBetreuer || $isAssistenz) {
			$file_path = PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf';
			
			
			if(file_exists($file_path)) {
				$this->terminateWithFileOutput('application/octet-stream', filesize($file_path), basename($file_path));

			} else {
				$this->terminateWithError('File not found');
			}
		} else {
			$this->terminateWithError('Keine Zuordnung!');
		}
	}

	public function postStudentProjektarbeitZusatzdaten(){
		$projektarbeit_id = $_POST['projektarbeit_id'];
		
		$sprache = $_POST['sprache'];
		$abstract = $_POST['abstract'];
		$abstract_en = $_POST['abstract_en'];
		$schlagwoerter = $_POST['schlagwoerter'];
		$schlagwoerter_en = $_POST['schlagwoerter_en'];
		$seitenanzahl = $_POST['seitenanzahl'];


		if (!isset($projektarbeit_id) || isEmptyString($projektarbeit_id)
			|| !isset($abstract) || !isset($abstract_en) // endupload zusatzdaten can be empty but should never be null
			|| !isset($schlagwoerter) || !isset($schlagwoerter_en)
			|| !isset($seitenanzahl) || !isset($sprache)) {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}
			
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		$result = $this->ProjektarbeitModel->load($projektarbeit_id);
		$projektarbeitArr = $this->getDataOrTerminateWithError($result);

		if(count($projektarbeitArr) > 0) {
			$projektarbeit = $projektarbeitArr[0];
		} else {
			$this->terminateWithError($this->p->t('global','projektarbeitNichtGefunden'), 'general');
		}
		
		// update projektarbeit cols
		$this->ProjektarbeitModel->updateProjektarbeit($projektarbeit_id,$sprache,$abstract,$abstract_en
			,$schlagwoerter, $schlagwoerter_en, $seitenanzahl);

		$this->logLib->logInfoDB(array('zusatzdatenEditMitarbeiter', array(
			'updatevon' => getAuthUID(),
			'updateamum' => date('Y-m-d H:i:s')
		), getAuthUID(), getAuthPersonId(), array($projektarbeit_id,$sprache,$abstract,$abstract_en
		,$schlagwoerter, $schlagwoerter_en, $seitenanzahl)));
				
		$result = $this->ProjektarbeitModel->load($projektarbeit_id);
		
		$this->terminateWithSuccess($result);
	}
	
	private function checkAbgabeSignatur($abgabe, $projektarbeit) {
		if($abgabe->paabgabetyp_kurzbz != 'end') {
			return;
		}
		
		$path = PAABGABE_PATH.$abgabe->paabgabe_id.'_'.$projektarbeit->student_uid.'.pdf';
		
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
	
}