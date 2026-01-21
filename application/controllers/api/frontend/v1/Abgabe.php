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

use CI3_Events as Events;

class Abgabe extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getConfig' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_lektor:rw'),
			'getConfigStudent' => array('basis/abgabe_assistenz:rw', 'basis/abgabe_student:rw', 'basis/abgabe_lektor:rw'),
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
		$old_abgabe_beurteilung_link =$this->config->item('old_abgabe_beurteilung_link');
		$turnitin_link = $this->config->item('turnitin_link');
		$abgabetypenBetreuer = $this->config->item('ALLOWED_ABGABETYPEN_BETREUER');
		
		$ret = array(
			'old_abgabe_beurteilung_link' => $old_abgabe_beurteilung_link,
			'turnitin_link' => $turnitin_link,
			'abgabetypenBetreuer' => $abgabetypenBetreuer
		);
		
		$this->terminateWithSuccess($ret);
	}

	/**
	 * loads config related to abgabetool for students to avoid handing out links reserved for employees
	 */
	public function getConfigStudent() {
		$moodle_link =$this->config->item('STG_MOODLE_LINK');

		$ret = array(
			'moodle_link' => $moodle_link,
		);

		$this->terminateWithSuccess($ret);
	}
	
	/**
	 * fetches all projektabgabetermine for a given projektarbeit_id used in cis4 student abgabetool & lektor abgabetool
	 */
	public function getStudentProjektabgaben() {
		$projektarbeit_id = $this->input->get("projektarbeit_id",TRUE);
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		if ($projektarbeit_id === NULL || trim((string)$projektarbeit_id) === '') {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}

		$result = $this->ProjektarbeitModel->load($projektarbeit_id);
		$projektarbeitArr = $this->getDataOrTerminateWithError($result);

		if(count($projektarbeitArr) > 0) {
			$projektarbeit = $projektarbeitArr[0];
		} else {
			$this->terminateWithError($this->p->t('abgabetool','c4projektarbeitNichtGefunden'), 'general');
		}
		
		$res = $this->ProjektarbeitModel->getStudentInfoForProjektarbeitId($projektarbeit_id);
		if(isError($res)) {
			$this->terminateWithError($this->p->t('abgabetool', 'c4errorLoadingStudentForProjektarbeitID'));
		}

		if(!hasData($res)) {
			$this->terminateWithError($this->p->t('abgabetool', 'c4noAssignedStudentForProjektarbeitID'));
		}
		$data = getData($res)[0];
		$student_uid = $data->uid;

		$zugeordnet = $this->checkZuordnung($projektarbeit_id, getAuthUID());
		if(getAuthUID() == $student_uid || $zugeordnet) {
			$projektarbeitIsCurrent = false;
			$returnFunc = function ($result) use (&$projektarbeitIsCurrent) {
				$projektarbeitIsCurrent = $result;
			};
			Events::trigger('projektarbeit_is_current', $projektarbeit_id, $returnFunc);

			$ret = $this->ProjektarbeitModel->getProjektarbeitAbgabetermine($projektarbeit_id);

			foreach ($ret->retval as $termin) {
				$this->checkAbgabeSignatur($termin, $projektarbeit);
			}

			$this->terminateWithSuccess(array($ret, $projektarbeitIsCurrent));
		}
	}

	/**
	 * fetches all projektarbeiten and betreuer for a given student_uid used in cis4 student abgabetool
	 */
	public function getStudentProjektarbeiten()
	{
		$uid = $this->input->get("uid",TRUE);
		
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		// if uid is missing or empty, fall back to getAuthUID()
		if ($uid === NULL || trim((string)$uid) === '') {
			$uid = getAuthUID();
		}

		$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter(getAuthUID());
		if ($isMitarbeiter) {
			$result = $this->ProjektarbeitModel->getStudentProjektarbeitenWithBetreuer($uid);
		} else {
			$result = $this->ProjektarbeitModel->getStudentProjektarbeitenWithBetreuer(getAuthUID());
		}

		$projektarbeiten = getData($result);
		
		if(count($projektarbeiten)) {
			foreach($projektarbeiten as $pa) {
				
				$downloadPaFunc = function ($babgeschickt, $zweitbetreuer_abgeschickt) use ($pa) {
					$pa->babgeschickt = $babgeschickt;
					$pa->zweitbetreuer_abgeschickt = $zweitbetreuer_abgeschickt;
				};
				
				Events::trigger('projektbeurteilung_check_available', $pa->projektarbeit_id, $pa->bperson_id, $downloadPaFunc);
					
				if(isset($pa->babgeschickt) && $pa->babgeschickt) {
					$downloadLink1 = '';
					$downloadLinkFunc1 = function ($link) use (&$downloadLink1) {
						$downloadLink1 = $link;
					};
					
					Events::trigger('projektbeurteilung_download_link', $pa->projektarbeit_id, $pa->betreuerart_kurzbz, $pa->bperson_id, $downloadLinkFunc1);
					
					// use config fallback in case the event fails
					if($downloadLink1 == '') {
						$fallback = $this->config->item('beurteilung_link_fallback');

						$search = array(
							'betreuerart_kurzbz=?',
							'projektarbeit_id=?',
							'person_id=?'
						);

						$replace = array(
							'betreuerart_kurzbz=' . $pa->betreuerart_kurzbz,
							'projektarbeit_id=' . $pa->projektarbeit_id,
							'person_id=' . $pa->bperson_id
						);

						$fallback = str_replace($search, $replace, $fallback);
						$downloadLink1 = APP_ROOT.$fallback;
						
					}
					$pa->downloadLink1 = $downloadLink1;
				}
				
				$pa->email = $pa->mitarbeiter_uid.'@'.DOMAIN;
				
				if($pa->zweitbetreuer_person_id !== null) {

					if($pa->zweitbetreuer_abgeschickt) {
						$downloadLink2 = '';
						$downloadLinkFunc2 = function ($link) use (&$downloadLink2) {
							$downloadLink2 = $link;
						};

						Events::trigger('projektbeurteilung_download_link', $pa->projektarbeit_id, $pa->zweitbetreuer_betreuerart_kurzbz, $pa->zweitbetreuer_person_id, $downloadLinkFunc2);

						// use config fallback in case the event fails
						if($downloadLink2 == '') {
							$fallback = $this->config->item('beurteilung_link_fallback');

							$search = array(
								'betreuerart_kurzbz=?',
								'projektarbeit_id=?',
								'person_id=?'
							);

							$replace = array(
								'betreuerart_kurzbz=' . $pa->zweitbetreuer_betreuerart_kurzbz,
								'projektarbeit_id=' . $pa->projektarbeit_id,
								'person_id=' . $pa->zweitbetreuer_person_id
							);

							$fallback = str_replace($search, $replace, $fallback);
							$downloadLink2 = APP_ROOT.$fallback;

						}
						
						$pa->downloadLink2 = $downloadLink2;
					}

					$result = $this->ProjektarbeitModel->getProjektbetreuerAnrede($pa->zweitbetreuer_person_id);

					$data = getData($result);
					if(count($data) > 0) {
						$pa->zweitbetreuer = $data[0];
					}
				}
			}
		}
		
		$this->terminateWithSuccess(array($projektarbeiten));
	}



	/**
	 * projektarbeit - upload for zwischenabgaben in cis4 student abgabetool
	 */
	public function postStudentProjektarbeitZwischenabgabe()
	{
		$this->checkUploadSize();

		$projektarbeit_id = $this->input->post('projektarbeit_id');
		$paabgabe_id = $this->input->post('paabgabe_id');
		$student_uid = $this->input->post('student_uid');
		$bperson_id = $this->input->post('bperson_id');
		$paabgabetyp_kurzbz = $this->input->post('paabgabetyp_kurzbz');

		if ($projektarbeit_id === NULL || trim((string)$projektarbeit_id) === ''
			|| $paabgabe_id === NULL || trim((string)$paabgabe_id) === ''
			|| $student_uid === NULL || trim((string)$student_uid) === ''
			|| $paabgabetyp_kurzbz === NULL || trim((string)$paabgabetyp_kurzbz) === '') {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}

		$zugeordnet = $this->checkZuordnung($projektarbeit_id, getAuthUID());
		if(getAuthUID() == $student_uid || $zugeordnet) {
			
		
			$path = PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf';
			
			if ((isset($_FILES) and isset($_FILES['file']) and ! $_FILES['file']['error'])) {
				move_uploaded_file($_FILES['file']['tmp_name'], $path);
	
				if(file_exists($path)) {
	
					chmod($path, 0640);
					
					$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
					$res = $this->PaabgabeModel->update($paabgabe_id, array(
						'abgabedatum' => date('Y-m-d'),
						'updatevon' => getAuthUID(),
						'updateamum' => date('Y-m-d H:i:s')
					));
	
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
		} else {
			$this->terminateWithError($this->p->t('abgabetool', 'c4noZuordnungBetreuerStudent'));
		}

}

	/**
	 * upload für finale abgaben aka Endupload in cis4 student abgabetool
	 */
	public function postStudentProjektarbeitEndupload()
	{
		$this->checkUploadSize();

		$projektarbeit_id = $this->input->post('projektarbeit_id');
		$paabgabe_id = $this->input->post('paabgabe_id');
		$student_uid = $this->input->post('student_uid');
		$sprache = $this->input->post('sprache');
		$abstract = $this->input->post('abstract');
		$abstract_en = $this->input->post('abstract_en');
		$schlagwoerter = $this->input->post('schlagwoerter');
		$schlagwoerter_en = $this->input->post('schlagwoerter_en');
		$seitenanzahl = $this->input->post('seitenanzahl');
		$bperson_id = $this->input->post('bperson_id');
		$paabgabetyp_kurzbz = $this->input->post('paabgabetyp_kurzbz');

		if ($projektarbeit_id === NULL || trim((string)$projektarbeit_id) === ''
			|| $paabgabe_id === NULL || trim((string)$paabgabe_id) === ''
			|| $student_uid === NULL || trim((string)$student_uid) === ''
			|| $paabgabetyp_kurzbz === NULL || trim((string)$paabgabetyp_kurzbz) === ''
			|| $abstract === NULL || $abstract_en === NULL
			|| $schlagwoerter === NULL || $schlagwoerter_en === NULL
			|| $seitenanzahl === NULL || $sprache === NULL) {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}

		$zugeordnet = $this->checkZuordnung($projektarbeit_id, getAuthUID());
		if(getAuthUID() == $student_uid || $zugeordnet) {
			if ((isset($_FILES) and isset($_FILES['file']) and !$_FILES['file']['error'])) {
				move_uploaded_file($_FILES['file']['tmp_name'], PAABGABE_PATH . $paabgabe_id . '_' . $student_uid . '.pdf');

				if (file_exists(PAABGABE_PATH . $paabgabe_id . '_' . $student_uid . '.pdf')) {

					$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

					$result = $this->ProjektarbeitModel->load($projektarbeit_id);
					$projektarbeitArr = $this->getDataOrTerminateWithError($result);

					if (count($projektarbeitArr) > 0) {
						$projektarbeit = $projektarbeitArr[0];
					} else {
						$this->terminateWithError($this->p->t('abgabetool', 'c4projektarbeitNichtGefunden'), 'general');
					}

					$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
					$result = $this->PaabgabeModel->load($paabgabe_id);
					$paabgabeArr = $this->getDataOrTerminateWithError($result);

					if (count($paabgabeArr) > 0) {
						$paabgabe = $paabgabeArr[0];
					} else {
						$this->terminateWithError($this->p->t('abgabetool', 'c4projektabgabeNichtGefunden'), 'general');
					}

					$this->checkAbgabeSignatur($paabgabe, $projektarbeit);
					$signaturstatus = $paabgabe->signatur;

					// update projektarbeit cols
					$this->ProjektarbeitModel->updateProjektarbeit($projektarbeit_id, $sprache, $abstract, $abstract_en
						, $schlagwoerter, $schlagwoerter_en, $seitenanzahl);


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

					$this->logLib->logInfoDB(array('endupload', $res, array(
						'abgabedatum' => date('Y-m-d'),
						'updatevon' => getAuthUID(),
						'updateamum' => date('Y-m-d H:i:s')
					), getAuthUID(), getAuthPersonId(), array($projektarbeit_id, $sprache, $abstract, $abstract_en
					, $schlagwoerter, $schlagwoerter_en, $seitenanzahl)));

					$this->terminateWithSuccess($abgabe);
				} else {
					$this->terminateWithError('Error moving File');
				}

			} else {
				$this->terminateWithError('File missing');
			}
		} else {
			$this->terminateWithError($this->p->t('abgabetool', 'c4noZuordnungBetreuerStudent'));
		}

	}

	/**
	 * tabulator tabledata fetch for abgabetool/mitarbeiter
	 * initially fetches all currently active projektarbeiten with assigned mentorship
	 * showAll functionality also retrieves older finished projektarbeiten
	 */
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

		$showAllBool = false; // fallback if input strings are anything else for whatever reason
		if (in_array($boolParamStrLower, $trueStrings, true)) {
			$showAllBool = true;
		} elseif (in_array($boolParamStrLower, $falseStrings, true)) {
			$showAllBool = false;
		}

		$projektarbeiten = $this->ProjektarbeitModel->getMitarbeiterProjektarbeiten(getAuthUID(), $showAllBool);


		forEach($projektarbeiten->retval as $pa) {

			$oldLink = ''; // show this when paIsCurrent == false -> moodle course template
			$newLink = ''; // get curated path for betreuer type
			$returnFunc = function ( $resultOld, $resultNew) use (&$oldLink, &$newLink) {
				$newLink = $resultNew;
				$oldLink = $resultOld;
			};
			
			Events::trigger('projektbeurteilung_formular_link', $pa->betreuerart_kurzbz, APP_ROOT, $pa->projektarbeit_id, $pa->student_uid, $returnFunc);
			$pa->beurteilungLinkNew = $newLink;
			$pa->beurteilungLinkOld = $oldLink;
		}
		
		
		$this->terminateWithSuccess(array($projektarbeiten, DOMAIN));
	}
	
	/**
	 * called by abgabetool/mitarbeiter in mitarbeiterdetail.js when adding a single new abgabetermin
	 * initially fetches all
	 */
	public function postProjektarbeitAbgabe() {
		$projektarbeit_id = $this->input->post('projektarbeit_id');
		$paabgabe_id = $this->input->post('paabgabe_id');
		$paabgabetyp_kurzbz = $this->input->post('paabgabetyp_kurzbz');
		$datum = $this->input->post('datum');
		$fixtermin = $this->input->post('fixtermin');
		$kurzbz = $this->input->post('kurzbz');
		$note = $this->input->post('note');
		$beurteilungsnotiz = $this->input->post('beurteilungsnotiz');
		$upload_allowed = $this->input->post('upload_allowed');
		$betreuer_person_id = $this->input->post('betreuer_person_id');

		if ($projektarbeit_id === NULL || trim((string)$projektarbeit_id) === ''
			|| $paabgabe_id === NULL || trim((string)$paabgabe_id) === ''
			|| $datum === NULL || trim((string)$datum) === ''
			|| $kurzbz === NULL
			|| $paabgabetyp_kurzbz === NULL || trim((string)$paabgabetyp_kurzbz) === '') {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}

		$zugeordnet = $this->checkZuordnung($projektarbeit_id, getAuthUID());
		if(!$zugeordnet) {
			$this->terminateWithError($this->p->t('abgabetool', 'c4noZuordnungBetreuerStudent'));
		}
		
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
					'fixtermin' => $fixtermin,
					'beurteilungsnotiz' => $beurteilungsnotiz,
					'upload_allowed' => $upload_allowed,
					'updatevon' => getAuthUID(),
					'updateamum' => date('Y-m-d H:i:s')
				)
			);
			
			$this->logLib->logInfoDB(array('paabgabe updated',$result, array(
				'paabgabetyp_kurzbz' => $paabgabetyp_kurzbz,
				'datum' => $datum,
				'kurzbz' => $kurzbz,
				'note' => $note,
				'fixtermin' => $fixtermin,
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

		// check if abgabe even has note
		if($paabgabe->note) {
			$this->load->model('education/Note_model', 'NoteModel');
			$result = $this->NoteModel->load($paabgabe->note);
			$noteArr = $this->getDataOrTerminateWithError($result);
			$note = $noteArr[0];
			if($note->positiv === false) {
				
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

	/**
	 * called by abgabetool/mitarbeiter in mitarbeiterdetail.js when deleting an abgabetermin
	 * deletion is only possible if user is assistenz OR betreuer deletes their own custom termin
	 * none of these roles are allowed to delete if students uploaded something for that termin
	 */
	public function deleteProjektarbeitAbgabe() {
		$paabgabe_id = $this->input->post('paabgabe_id');

		if ($paabgabe_id === NULL || trim((string)$paabgabe_id) === '') {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}

		$zugeordnet = $this->checkZuordnungByPaabgabe($paabgabe_id, getAuthUID());
		
		if(!$zugeordnet) {
			$this->terminateWithError($this->p->t('abgabetool', 'c4noZuordnungBetreuerStudent'));
		}

		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
		
		$paabgabeResult = $this->PaabgabeModel->load($paabgabe_id);
		$paabgabeArr = $this->getDataOrTerminateWithError($paabgabeResult);

		if(count($paabgabeArr) == 0) {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}
		
		$result = $this->PaabgabeModel->delete($paabgabe_id);
		$result = $this->getDataOrTerminateWithError($result);

		// TODO: consider this in nightly email job
		$this->logLib->logInfoDB(array($paabgabeArr[0], getAuthUID(), getAuthPersonId()));
		$this->terminateWithSuccess($result);
	}

	/**
	 * endpoint for adding the same paabgabe for multiple projektarbeiten
	 * can be slow for large n since it queries twice per projektarbeit_id
	 */
	public function postSerientermin() {
		$projektarbeit_ids = $this->input->post('projektarbeit_ids');
		$datum = $this->input->post('datum');
		$paabgabetyp_kurzbz = $this->input->post('paabgabetyp_kurzbz');
		$bezeichnung = $this->input->post('bezeichnung');
		$kurzbz = $this->input->post('kurzbz');
		$fixtermin = $this->input->post('fixtermin');
		$upload_allowed = $this->input->post('upload_allowed');

		if ($projektarbeit_ids === NULL || !is_array($projektarbeit_ids) || empty($projektarbeit_ids)
			|| $datum === NULL || trim((string)$datum) === ''
			|| $kurzbz === NULL
			|| $bezeichnung === NULL || trim((string)$bezeichnung) === ''
			|| $paabgabetyp_kurzbz === NULL || trim((string)$paabgabetyp_kurzbz) === '') {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}

		// old script checks if there already are tbl_paabgabe entries with exact date, type & kurzbz
		// for each termin - good to check that in principle but should not matter in this place. if necessary
		// duplicate abgabetermine can be easily deleted manually, also via cronjob@night.

		// since this entry includes the kurzbz string match, it should have only ever mattered when there were
		// multiple users entering the exact same set of (date, type, kurzbz) - which is a much more narrow case than the
		// general "saveMultiple" function should handle

		// old script afterwards again queries if user is not the zweitbetreuer of any id - this is blocked in the ui
		// and should never unintentionally happen
		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		$res = [];
		$abgaben = [];
		foreach ($projektarbeit_ids as $projektarbeit_id) {
			
			$zugeordnet = $this->checkZuordnung($projektarbeit_id, getAuthUID());
			if(!$zugeordnet) {
				$this->terminateWithError($this->p->t('abgabetool', 'c4noZuordnungBetreuerStudent'));
			}
			
			$result = $this->PaabgabeModel->insert(
				array(
					'projektarbeit_id' => $projektarbeit_id,
					'paabgabetyp_kurzbz' => $paabgabetyp_kurzbz,
					'fixtermin' => $fixtermin,
					'datum' => $datum,
					'kurzbz' => $kurzbz,
					'upload_allowed' => $upload_allowed,
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

	/**
	 * called by Abgabetool/Deadlines
	 * fetches the next upcoming abgabtermine for a given betreuer person_id
	 * resembles the legacy abgabetool functionality of "show deadlines"
	 */
	public function fetchDeadlines() {
		$person_id = $this->input->post('person_id');

		if ($person_id === NULL || trim((string)$person_id) === '') {
			$person_id = getAuthPersonId();
		}
		
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

	/**
	 * called by Abgabetool/Mitarbeiter & Abgabetool/Assistenz
	 * fetches all available paabgabetypen to enable a logical selection of them
	 * based on active status and role assistenz/betreuer
	 */
	public function getPaAbgabetypen() {
		$this->load->model('education/Paabgabetyp_model', 'PaabgabetypModel');

		$result = $this->PaabgabetypModel->getAll();
		$paabgabetypen = $this->getDataOrTerminateWithError($result);
		
		
		$this->terminateWithSuccess($paabgabetypen);
	}

	/**
	 * helper function to fetch the correct email for a projektarbeits erstbetreuer
	 */
	private function getProjektbetreuerEmail($projektarbeit_id) {
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$result = $this->ProjektarbeitModel->getProjektbetreuerEmail($projektarbeit_id);
		$email = $this->getDataOrTerminateWithError($result);
		
		return $email[0]->uid ? $email[0]->uid.'@'.DOMAIN : $email[0]->private_email;

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

		$allowed_noten_abgabetool = $this->config->item('ALLOWED_NOTEN_ABGABETOOL');

		$this->terminateWithSuccess(array($noten, $allowed_noten_abgabetool));
	}

	/**
	 * helper function to send a sancho mail to students if a betreuer or assistenz grades a quality gate
	 * termin as negative (nicht bestanden)
	 */
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
			$this->terminateWithError($this->p->t('abgabetool','c4userNichtGefunden'), 'general');
		}
		
		$subject = $this->p->t('abgabetool', 'c4qualgateNegativEmailSubjectv2');
		$tomail = $student_uid.'@'.DOMAIN;
		
		$datetime = new DateTime($paabgabe->datum);
		$dateEmailFormatted = $datetime->format('d.m.Y');
		
		$data = array(
			'betreuerfullname' => $anrede->first,
			'qualgatebezeichnung' => $paabgabetyp_kurzbz->bezeichnung,
			'datum' => $dateEmailFormatted,
			'projektarbeitname' => $projektarbeit->titel
		);
		
		// students still get theirs on event, since it is very unlikely that this
		// leads to spam on their end
		
		$mailres = sendSanchoMail(
			'QualGateNegativ',
			$data,
			$tomail,
			$subject
		);
		
	}

	/**
	 * tabulator tabledata fetch for abgabetool/assistenz
	 * initially fetches all ungraded projektarbeiten with all their abgabetermine
	 */
	public function getProjektarbeitenForStudiengang() {
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		
		$studiengang_kz = $this->input->get("studiengang_kz", TRUE);
		$benotet = $this->input->get("benotet", TRUE);

		if ($studiengang_kz === NULL || trim((string)$studiengang_kz) === '') {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}
		
		// TODO: recheck getSTGEntitlement here!
		$stg_allowed = $this->permissionlib->getSTG_isEntitledFor('basis/abgabe_assistenz:rw');
		if($stg_allowed == false) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'), 'general');
		}
		
		// check if provided studiengang_kz is included in stg_allowed to proceed
		if(!in_array($studiengang_kz, $stg_allowed)) {
			$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'), 'general');
		}
		
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
	
	// TODO: this could be in a generic info controller and reused
	/**
	 * GET METHOD
	 * returns List of all studiengang_kz a user has the assigned permission 'basis/abgabe_assistenz:rw' for
	 * used in Abgabetool/Assistenz to populate Studiengang Dropdown
	 */
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

	/**
	 * GET METHOD
	 * endpoint to download the abgabe of a paabgabe termin zwischenabgabe or endupload
	 */
	public function getStudentProjektarbeitAbgabeFile()
	{
		$this->load->helper('download');

		$projektarbeit_id = $this->input->get('projektarbeit_id');
		$paabgabe_id = $this->input->get('paabgabe_id');
		$student_uid = $this->input->get('student_uid');

		if ($paabgabe_id === NULL || trim((string)$paabgabe_id) === ''
			|| $projektarbeit_id === NULL || trim((string)$projektarbeit_id) === ''
			|| $student_uid === NULL || trim((string)$student_uid) === '') {
			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}

		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		// zuordnung function is supposed for mitarbeiter_uids, students should be allowed to download their own files
		// without adapting zuordnung logic
		$zugeordnet = $this->checkZuordnung($projektarbeit_id, getAuthUID());
		if(getAuthUID() == $student_uid || $zugeordnet) {
			$file_path = PAABGABE_PATH.$paabgabe_id.'_'.$student_uid.'.pdf';
			
			
			if(file_exists($file_path)) {
				$this->terminateWithFileOutput('application/octet-stream', file_get_contents($file_path), basename($file_path));
			} else {
				$this->terminateWithError('File not found');
			}
		} else {
			$this->terminateWithError($this->p->t('abgabetool', 'c4noZuordnungBetreuerStudent'));
		}
	}

	/**
	 * POST METHOD
	 * endpoint to enable Assistenz/Betreuer to edit the zusatzdate of a projektarbeit, in case the student somehow
	 * can't do it themself
	 */
	public function postStudentProjektarbeitZusatzdaten(){
		$projektarbeit_id = $this->input->post('projektarbeit_id');
		$sprache = $this->input->post('sprache');
		$abstract = $this->input->post('abstract');
		$abstract_en = $this->input->post('abstract_en');
		$schlagwoerter = $this->input->post('schlagwoerter');
		$schlagwoerter_en = $this->input->post('schlagwoerter_en');
		$seitenanzahl = $this->input->post('seitenanzahl');

		if ($projektarbeit_id === NULL || trim((string)$projektarbeit_id) === ''
			|| $sprache === NULL || trim((string)$sprache) === ''
			|| $seitenanzahl === NULL || trim((string)$seitenanzahl) === ''
			|| $abstract === NULL || trim((string)$abstract) === ''
			|| $abstract_en === NULL || trim((string)$abstract_en) === ''
			|| $schlagwoerter === NULL || trim((string)$schlagwoerter) === ''
			|| $schlagwoerter_en === NULL || trim((string)$schlagwoerter_en) === '') {

			$this->terminateWithError($this->p->t('global', 'wrongParameters'), 'general');
		}
			
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		$result = $this->ProjektarbeitModel->load($projektarbeit_id);
		$projektarbeitArr = $this->getDataOrTerminateWithError($result);

		if(count($projektarbeitArr) > 0) {
			$projektarbeit = $projektarbeitArr[0];
		} else {
			$this->terminateWithError($this->p->t('abgabetool','c4projektarbeitNichtGefunden'), 'general');
		}

		$zugeordnet = $this->checkZuordnung($projektarbeit_id, getAuthUID());
		if(!$zugeordnet) {
			$this->terminateWithError($this->p->t('abgabetool', 'c4noZuordnungBetreuerStudent'));
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

	/**
	 * helper function to check the signature status of uploaded files for zwischenabgabe & endupload
	 */
	private function checkAbgabeSignatur($abgabe, $projektarbeit) {
		$paabgabetypenToCheck = $this->config->item('SIGNATUR_CHECK_PAABGABETYPEN');

		if(!in_array($abgabe->paabgabetyp_kurzbz, $paabgabetypenToCheck)) {
			return;
		}

		if (!defined('SIGNATUR_URL')) {
			$abgabe->signatur = 'error';
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

	private function sendUploadEmail($bperson_id, $projektarbeit_id, $paabgabetyp_kurzbz, $student_uid) {
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');

		$resBetr = $this->ProjektarbeitModel->getProjektbetreuerAnrede($bperson_id);


		$result = $this->ProjektarbeitModel->load($projektarbeit_id);
		$projektarbeitArr = $this->getDataOrTerminateWithError($result);

		if(count($projektarbeitArr) > 0) {
			$projektarbeit = $projektarbeitArr[0];
		} else {
			$this->terminateWithError($this->p->t('abgabetool','c4projektarbeitNichtGefunden'), 'general');
		}
		
		$projektarbeitIsCurrent = false;
		$returnFunc = function ($result) use (&$projektarbeitIsCurrent) {
			$projektarbeitIsCurrent = $result;
		};
		Events::trigger('projektarbeit_is_current', $projektarbeit_id, $returnFunc);
		if(!$projektarbeitIsCurrent) {
			$this->terminateWithError($this->p->t('abgabetool','c4fehlerAktualitaetProjektarbeit'), 'general');
		}

		// Link to Abgabetool
		if (defined('CIS4') && CIS4) {
			$ci3BootstrapFilePath = "cis.php";
		} else {
			$ci3BootstrapFilePath = "index.ci.php";
		}

		$path = $this->config->item('URL_MITARBEITER');
		$url = APP_ROOT.$path;
		
		// getProjektbetreuerAnrede fetches distinct on person_id, so there should be one row. zweitbetreuer is handled seperately afterwards 
		foreach($resBetr->retval as $betreuerRow) {

			// query student benutzer view for every betreuer row
			$studentUser = $this->ProjektarbeitModel->getProjektarbeitBenutzer($student_uid)->retval[0];

			// 1. Begutachter mail ohne Token
			$mail_baselink = APP_ROOT.$this->config->item('PROJEKTARBEITSBEURTEILUNG_MAIL_BASELINK_ERSTBEGUTACHTER');
//			$mail_baselink = APP_ROOT."index.ci.php/extensions/FHC-Core-Projektarbeitsbeurteilung/ProjektarbeitsbeurteilungErstbegutachter";
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

			if(!$email) $this->terminateWithError($this->p->t('abgabetool', 'c4fehlerMailBegutachter'), 'general');
			
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
				$this->terminateWithError($this->p->t('abgabetool', 'c4fehlerMailBegutachter'), 'general');
			}

			// 2. Begutachter mail, wenn Endabgabe, mit Token wenn extern
			if ($paabgabetyp_kurzbz == 'end')
			{
				// Zweitbegutachter holen
				$this->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');
				$zweitbegutachterRetval = getData($this->ProjektbetreuerModel->getZweitbegutachterWithToken($bperson_id, $projektarbeit_id, $studentUser->uid));
				
				if ($zweitbegutachterRetval && count($zweitbegutachterRetval) > 0)
				{

					foreach ($zweitbegutachterRetval as $begutachter)
					{
						// token generieren, wenn noch nicht vorhanden und notwendig (wird in methode überprüft)
						$tokenGenRes = $this->ProjektbetreuerModel->generateZweitbegutachterToken($begutachter->person_id, $projektarbeit_id);

						if (!$tokenGenRes)
						{
							$this->terminateWithError($this->p->t('abgabetool', 'c4fehlerMailZweitBegutachter'), 'general');
						}

						$begutachterMitTokenRetval = getData($this->ProjektbetreuerModel->getZweitbegutachterWithToken($bperson_id, $projektarbeit_id, $studentUser->uid, $begutachter->person_id));
						
						if (!$begutachterMitTokenRetval && count($begutachterMitTokenRetval) <= 0)
						{
							$this->terminateWithError($this->p->t('abgabetool', 'c4fehlerMailZweitBegutachter'), 'general');
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
							$this->terminateWithError($this->p->t('abgabetool', 'c4fehlerMailBegutachter'), 'general');
						}

					}
				}
			}
		}
	}
	
	private function checkZuordnung($projektarbeit_id, $betreuer_uid) {
		// check if authenticated user is zugewiesen as betreuer to projektarbeit or has admin/assistenz berechtigung
		// over the studiengang of the student working on that projektarbeit_id

		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		
		$res = $this->ProjektarbeitModel->getStudentInfoForProjektarbeitId($projektarbeit_id);
		if(isError($res)) {
			$this->terminateWithError($this->p->t('abgabetool', 'c4errorLoadingStudentForProjektarbeitID'));
		}
		
		if(!hasData($res)) {
			$this->terminateWithError($this->p->t('abgabetool', 'c4noAssignedStudentForProjektarbeitID'));
		}
		$data = getData($res)[0];
		$student_uid = $data->uid;
		$studiengang_kz = $data->studiengang_kz;
		
		$res = $this->ProjektarbeitModel->checkZuordnung($student_uid, $betreuer_uid);
		if(isError($res)) {
			$this->terminateWithError($this->p->t('abgabetool', 'c4errorLoadingBetreuerStudentZuordnung'));
		}

		// if this is true betreuer has zuordnung to the given $projektarbeit_id and conversely the $student_uid
		// assigned to that project
		if(hasData($res)) {
			return true;
		} else {
			$berechtigt = $this->ProjektarbeitModel->hasBerechtigungForProjektarbeit($projektarbeit_id);
			if($berechtigt) {
				return true;
			}
			
			// otherwhise if there is no zuordnung via global admin or assistenz berechtigung,
			// check if the given uid has permissions over the studiengang of the student
			// via the abgabetool specific berechtigungen
			// 'basis/abgabe_assistenz:rw' OR 'basis/abgabe_lektor:rw'

			if ($this->permissionlib->isBerechtigt('basis/abgabe_assistenz', 'suid', $studiengang_kz)) {
				return true;
			}
				
			if ($this->permissionlib->isBerechtigt('basis/abgabe_lektor', 'suid', $studiengang_kz)){
				return true;
			}
		}
		
		return false;
	}
	
	private function checkZuordnungByPaabgabe($paabgabe_id, $betreuer_uid) {
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$res = $this->ProjektarbeitModel->getProjektarbeitByPaabgabeID($paabgabe_id);
		if(isError($res)) {
			$this->terminateWithError($this->p->t('abgabetool', 'c4errorLoadingProjektarbeitForPaabgabeID'));
		}

		if(!hasData($res)) {
			$this->terminateWithError($this->p->t('abgabetool', 'c4noAssignedProjektarbeitForPaabgabeID'));
		}
		$data = getData($res)[0];
		$projektarbeit_id = $data->projektarbeit_id;
	
		return $this->checkZuordnung($projektarbeit_id, $betreuer_uid);
	}

}