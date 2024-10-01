<?php

/**
 * FH-Complete
 *
 * @package             FHC-Helper
 * @author              FHC-Team
 * @copyright           Copyright (c) 2023 fhcomplete.net
 * @license             GPLv3
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;
use \DOMDocument as DOMDocument;
use \XSLTProcessor as XSLTProcessor;
use \Studierendenantragstatus_model as Studierendenantragstatus_model;
use \stdClass as stdClass;

class AntragLib
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		// Configs
		$this->_ci->load->config('studierendenantrag');

		// Models
		$this->_ci->load->model('education/Studierendenantrag_model', 'StudierendenantragModel');
		$this->_ci->load->model('education/Studierendenantragstatus_model', 'StudierendenantragstatusModel');
		$this->_ci->load->model('education/Studierendenantraglehrveranstaltung_model', 'StudierendenantraglehrveranstaltungModel');
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->_ci->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->_ci->load->model('person/Person_model', 'PersonModel');
		$this->_ci->load->model('education/Pruefung_model', 'PruefungModel');

		// Helper
		$this->_ci->load->helper('hlp_sancho_helper');

		// Libraries
		$this->_ci->load->library('PermissionLib');
		$this->_ci->load->library('PrestudentLib');
	}

	/**
	 * @param integer		$antrag_id
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function cancelAntrag($antrag_id, $insertvon)
	{
		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_CANCELLED,
			'insertvon' => $insertvon
		]);

		// NOTE(chris): remove "preabbrecher" statusgrund and paused stati for sibling Anträge for Stgl-Abmeldungen if set
		$res = $this->_ci->StudierendenantragModel->load($antrag_id);
		if (hasData($res) && current(getData($res))->typ == Studierendenantrag_model::TYP_ABMELDUNG_STGL) {
			$this->unpauseAntrag($antrag_id, Studierendenantragstatus_model::INSERTVON_ABMELDUNGSTGL);

			$this->_ci->PrestudentstatusModel->addSelect('tbl_status_grund.statusgrund_kurzbz');
			$res = $this->_ci->PrestudentstatusModel->getLastStatusWithStgEmail(current(getData($res))->prestudent_id, '', 'Student');
			if (hasData($res) && current(getData($res))->statusgrund_kurzbz == 'preabbrecher') {
				$prestudentstatus = current(getData($res));
				$this->_ci->PrestudentstatusModel->update([
					'prestudent_id' => $prestudentstatus->prestudent_id,
					'status_kurzbz'=>$prestudentstatus->status_kurzbz,
					'studiensemester_kurzbz'=>$prestudentstatus->studiensemester_kurzbz,
					'ausbildungssemester'=>$prestudentstatus->ausbildungssemester
				], [
					'statusgrund_id' => null
				]);
			}
		}

		return $result;
	}

	/**
	 * @param integer		$antrag_id
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function pauseAntrag($antrag_id, $insertvon)
	{
		switch ($insertvon) {
			case Studierendenantragstatus_model::INSERTVON_ABMELDUNGSTGL:
				$result = $this->_ci->StudierendenantragstatusModel->stopAntraegeForAbmeldungStgl($antrag_id);
				break;
			case Studierendenantragstatus_model::INSERTVON_DEREGISTERED:
				$result = $this->_ci->StudierendenantragstatusModel->stopAntraegeForAbbruchBy($antrag_id);
				break;
			default:
				$result = $this->_ci->StudierendenantragstatusModel->insert([
					'studierendenantrag_id' => $antrag_id,
					'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_PAUSE,
					'insertvon' => $insertvon
				]);
				break;
		}

		return $result;
	}

	/**
	 * @param integer		$antrag_id
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function unpauseAntrag($antrag_id, $insertvon)
	{
		if ($insertvon == Studierendenantragstatus_model::INSERTVON_DEREGISTERED)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_right'));
		if ($insertvon == Studierendenantragstatus_model::INSERTVON_ABMELDUNGSTGL) {
			return $this->_ci->StudierendenantragstatusModel->resumeAntraegeForAbmeldungStgl($antrag_id);
		}
		// NOTE(chris): get last status that is not pause
		$this->_ci->StudierendenantragstatusModel->addOrder('insertamum');
		$this->_ci->StudierendenantragstatusModel->addLimit(1);
		$result = $this->_ci->StudierendenantragstatusModel->loadWhere([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz !=' => Studierendenantragstatus_model::STATUS_PAUSE
		]);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', 'error_no_antragstatus', ['id' => $antrag_id]));
		$status = current(getData($result));

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => $status->studierendenantrag_statustyp_kurzbz,
			'insertvon' => $insertvon
		]);
		return $result;
	}

	/**
	 * NOTE(chris): permissions & verification must be handled outside
	 *
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz
	 * @param string		$insertvon
	 * @param string		$grund
	 *
	 * @return stdClass
	 */
	public function createAbmeldung($prestudent_id, $studiensemester_kurzbz, $insertvon, $grund)
	{
		$result = $this->_ci->PrestudentModel->load($prestudent_id);
		if (isError($result))
			return $result;
		if(!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', "error_no_prestudent", ['prestudent_id' => $prestudent_id]));

		$prestudent = getData($result)[0];
		if($prestudent->person_id == getAuthPersonId())
			$typ = Studierendenantrag_model::TYP_ABMELDUNG;
		else
			$typ = Studierendenantrag_model::TYP_ABMELDUNG_STGL;

		$result = $this->_ci->StudierendenantragModel->insert([
			'prestudent_id' => $prestudent_id,
			'studiensemester_kurzbz'=> $studiensemester_kurzbz,
			'datum' => date('c'),
			'typ' => $typ,
			'insertvon' => $insertvon,
			'grund' => $grund
		]);

		if (isError($result))
			return $result;

		$antrag_id = getData($result);

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_CREATED,
			'insertvon' => $insertvon
		]);

		if (isError($result))
			return $result;

		return success($antrag_id);
	}

	/**
	 * @param array			$studierendenantrag_ids
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function approveAbmeldung($studierendenantrag_ids, $insertvon)
	{
		$this->_ci->load->model('crm/Student_model', 'StudentModel');

		$errors = [];
		foreach ($studierendenantrag_ids as $studierendenantrag_id) {
			$result = $this->_ci->StudierendenantragModel->load($studierendenantrag_id);
			if (isError($result))
			{
				$errors[] = getError($result);
				continue;
			}
			if(!hasData($result))
			{
				$errors[] = $this->_ci->p->t('studierendenantrag', 'error_no_antrag_found', ['id' => $studierendenantrag_id]);
				continue;
			}
			$antrag = getData($result)[0];

			$insertam = date('c');

			$result = $this->_ci->StudierendenantragstatusModel->insert([
				'studierendenantrag_id' => $studierendenantrag_id,
				'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED,
				'insertvon' => $insertvon,
				'insertamum' => $insertam
			]);
			if (isError($result))
				$errors[] = getError($result);
			else {
				$this->_ci->StudiengangModel->addJoin('public.tbl_prestudent ps', 'studiengang_kz');
				$result = $this->_ci->StudiengangModel->loadWhere(['prestudent_id' => $antrag->prestudent_id]);
				$stg = '';
				$orgform = '';
				if (hasData($result)) {
					$studiengang = current(getData($result));
					$stg = $studiengang->bezeichnung;
				}
				if ($antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG)
				{
					$resultPrestudentStatus = $this->_ci->PrestudentstatusModel->getLastStatusWithStgEmail($antrag->prestudent_id);
					if (isError($resultPrestudentStatus))
						$errors[] = getError($resultPrestudentStatus);

					else {
						$prestudent_status = getData($resultPrestudentStatus)[0];
						$orgform = $prestudent_status->orgform_kurzbz;

						$vorlage ='Sancho_Mail_Antrag_A_Approve';
						$subject = $this->_ci->p->t('studierendenantrag', 'mail_subject_A_Approve');

						$result = $this->pauseAntrag($studierendenantrag_id, Studierendenantragstatus_model::INSERTVON_DEREGISTERED);
						if (isError($result))
							$errors[] = getError($result);

						$this->_ci->load->model('crm/Statusgrund_model', 'StatusgrundModel');
						$result = $this->_ci->StatusgrundModel->loadWhere(['statusgrund_kurzbz' => 'abbrecherStud']);
						if (isError($result)) {
							$errors[] = getError($result);
							continue;
						} elseif (!hasData($result)) {
							$errors[] = $this->_ci->p->t('lehre', 'error_noStatusgrund', ['statusgrund_kurzbz' => 'abbrecherStud']);
							continue;
						}
						$statusgrund = current(getData($result));

						$result = $this->_ci->prestudentlib->setAbbrecher(
                            $antrag->prestudent_id,
                            $antrag->studiensemester_kurzbz,
                            $insertvon,
                            $statusgrund->statusgrund_id,
                            $antrag->datum,
                            $insertam
                        );
						if (isError($result)) {
							$errors[] = getError($result);
							continue;
						}

						$result = $this->_ci->PersonModel->loadPrestudent($antrag->prestudent_id);
						$data = [
							'student' => $this->_ci->p->t('person', 'studentIn'),
							'sem' => $antrag->studiensemester_kurzbz,
							'linkPdf' => base_url('content/pdfExport.php?xml=Antrag' .
								$antrag->typ .
								'.xml.php&xsl=Antrag' .
								$antrag->typ .
								'&id=' .
								$antrag->studierendenantrag_id .
								'&output=pdf')
						];
						if (hasData($result)) {
							$person = current(getData($result));
							$data['student'] = trim($person->vorname . ' ' . $person->nachname);
							$data['vorname'] = $person->vorname;
							$data['nachname'] = $person->nachname;
						}
						$result = $this->_ci->StudentModel->loadWhere(['prestudent_id'=> $antrag->prestudent_id]);
						if (hasData($result)) {
							$student = current(getData($result));
							$data['UID'] = $student->student_uid;
						}

						$data['Orgform'] = $prestudent_status->orgform;
						$data['stg'] = $stg;

						// NOTE(chris): Sancho mail
						sendSanchoMail($vorlage, $data, $prestudent_status->email, $subject);
					}
				} else { // ($antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG_STGL)
					$result = $this->pauseAntrag($studierendenantrag_id, Studierendenantragstatus_model::INSERTVON_ABMELDUNGSTGL);
					if (isError($result))
						$errors[] = getError($result);
					
					$result = $this->_ci->PrestudentstatusModel->getLastStatusWithStgEmail($antrag->prestudent_id, '', 'Student');
					if (isError($result))
					{
						$errors[] = getError($result);
						continue;
					}
					if(!hasData($result))
					{
						$errors[] = $this->_ci->p->t('studierendenantrag', 'error_no_prestudentstatus', ['prestudent_id' => $antrag->prestudent_id]);
						continue;
					}
					$prestudentstatus = getData($result)[0];
					$orgform = $prestudentstatus->orgform_kurzbz;

					$result = $this->_ci->PrestudentstatusModel->withGrund('preabbrecher')->update([
						'prestudent_id' => $prestudentstatus->prestudent_id,
						'status_kurzbz'=>$prestudentstatus->status_kurzbz,
						'studiensemester_kurzbz'=>$prestudentstatus->studiensemester_kurzbz,
						'ausbildungssemester'=>$prestudentstatus->ausbildungssemester
					], []);
					if (isError($result))
					{
						$errors[] = getError($result);
						continue;
					}
				}

				$res = $this->_ci->PrestudentModel->load($antrag->prestudent_id);

				if (hasData($res)) {
					$prestudent = current(getData($res));
					$res = $this->_ci->PersonModel->load($prestudent->person_id);
					if (hasData($res)) {
						$person = current(getData($res));
						$name = trim($person->vorname . ' ' . $person->nachname);
						$vorname = $person->vorname;
						$nachname = $person->nachname;
					} else {
						$name = $this->_ci->p->t('person', 'studentIn');
						$vorname = '';
						$nachname = $name;
					}
					$res = $this->_ci->StudentModel->loadWhere(['prestudent_id' => $antrag->prestudent_id]);
					if (hasData($res)) {
						$email = $this->_ci->StudentModel->getEmailFH(current(getData($res))->student_uid);
						$vorlage = $antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG ? 'Student' : 'Stgl';

						// NOTE(chris): Sancho mail
						sendSanchoMail(
							'Sancho_Mail_Antrag_A_' . $vorlage,
							[
								'name' => $name,
								'grund' => $antrag->grund,
								'vorname' => $vorname,
								'nachname' => $nachname,
								'Orgform' => $orgform,
								'stg' => $stg
							],
							$email,
							$this->_ci->p->t('studierendenantrag', 'mail_subject_A_' . $vorlage)
						);
					}
				}
			}
		}

		if (count($errors))
			return error(implode(',', $errors));

		return success();
	}

	/**
	 * @param integer		$studierendenantrag_id
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function denyObjectionAbmeldung($studierendenantrag_id, $insertvon, $grund = null)
	{
		$result = $this->_ci->StudierendenantragModel->load($studierendenantrag_id);
		if (isError($result))
		{
			return $result;
		}
		if(!hasData($result))
		{
			return error($this->_ci->p->t('studierendenantrag', 'error_no_antrag_found', ['id' => $studierendenantrag_id]));
		}
		$antrag = getData($result)[0];

		$result = $this->_ci->StudierendenantragstatusModel->loadWhere([
			'studierendenantrag_id' => $studierendenantrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED
		]);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', 'error_not_approved'));

		$status = current(getData($result));

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $studierendenantrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_OBJECTION_DENIED,
			'grund' => $grund,
			'insertvon' => $insertvon
		]);
		if (isError($result))
			return $result;
		else {
			$result = $this->pauseAntrag($studierendenantrag_id, Studierendenantragstatus_model::INSERTVON_DEREGISTERED);
			// NOTE(chris): here we should have error handling but at the
			// moment there is no way to notify the user for "soft" errors

			$this->_ci->load->model('crm/Statusgrund_model', 'StatusgrundModel');
			$result = $this->_ci->StatusgrundModel->loadWhere(['statusgrund_kurzbz' => 'abbrecherStgl']);
			if (isError($result))
				return $result;
			if (!hasData($result))
				return error($this->_ci->p->t('lehre', 'error_noStatusgrund', ['statusgrund_kurzbz' => 'abbrecherStgl']));
			
			$statusgrund = current(getData($result));

			$result = $this->_ci->prestudentlib->setAbbrecher(
                $antrag->prestudent_id,
                $antrag->studiensemester_kurzbz,
                $insertvon,
                $statusgrund->statusgrund_id,
                $status->insertamum
            );

			if (isError($result))
				return $result;

			$res = $this->_ci->PrestudentModel->load($antrag->prestudent_id);

			if (hasData($res)) {
				$this->_ci->load->model('crm/Student_model', 'StudentModel');

				$prestudent = current(getData($res));
				$res = $this->_ci->PersonModel->load($prestudent->person_id);
				if (hasData($res)) {
					$person = current(getData($res));
					$name = trim($person->vorname . ' ' . $person->nachname);
					$vorname = $person->vorname;
					$nachname = $person->nachname;
				} else {
					$name = $this->_ci->p->t('person', 'studentIn');
				}

				$res = $this->_ci->StudentModel->loadWhere(['prestudent_id' => $antrag->prestudent_id]);
				if (hasData($res)) {
					$email = $this->_ci->StudentModel->getEmailFH(current(getData($res))->student_uid);

					$res = $this->_ci->StudierendenantragModel->getStgAndSem($antrag->studierendenantrag_id);
					$stg = '';
					$orgform = '';
					if (hasData($res)) {
						$studiengang = current(getData($res));
						$stg = $studiengang->bezeichnung;
						$orgform = $studiengang->orgform_kurzbz;
					}

					sendSanchoMail(
						'Sancho_Mail_Antrag_A_ObjDenied',
						[
							'name' => $name,
							'vorname' => $vorname,
							'nachname' => $nachname,
							'grund' => $grund,
							'Orgform' => $orgform,
							'stg' => $stg
						],
						$email,
						$this->_ci->p->t('studierendenantrag', 'mail_subject_A_ObjectionDenied')
					);
				}
			}
		}

		return success();
	}

	/**
	 * NOTE(chris): permissions & verification must be handled outside
	 *
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz
	 * @param string		$insertvon
	 * @param string		$grund
	 * @param string		$datum_wiedereinstieg
	 *
	 * @return stdClass
	 */
	public function createUnterbrechung($prestudent_id, $studiensemester_kurzbz, $insertvon, $grund, $datum_wiedereinstieg, $dms_id)
	{
		$datum_wiedereinstieg = new DateTime($datum_wiedereinstieg);
		$datum_wiedereinstieg = $datum_wiedereinstieg->format("Y-m-d");
		$result = $this->_ci->StudierendenantragModel->insert([
			'prestudent_id' => $prestudent_id,
			'studiensemester_kurzbz'=> $studiensemester_kurzbz,
			'datum' => date('c'),
			'typ' => Studierendenantrag_model::TYP_UNTERBRECHUNG,
			'insertvon' => $insertvon,
			'grund' => $grund,
			'datum_wiedereinstieg' => $datum_wiedereinstieg,
			'dms_id' => $dms_id
		]);

		if (isError($result))
			return $result;

		$antrag_id = getData($result);

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_CREATED,
			'insertvon' => $insertvon
		]);

		if (isError($result))
			return $result;

		return success($antrag_id);
	}


	/**
	 * @param array			$studierendenantrag_ids
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function approveUnterbrechung($studierendenantrag_ids, $insertvon)
	{
		$this->_ci->load->model('person/Kontakt_model', 'KontaktModel');
		$this->_ci->load->model('crm/Student_model', 'StudentModel');

		$errors = [];

		foreach ($studierendenantrag_ids as $studierendenantrag_id)
		{
			$data = $this->getDataForUnterbrechung($studierendenantrag_id);

			if (isError($data)) {
				$error_msg = getError($data);
				if (is_array($error_msg) && isset($error_msg['message']))
					$error_msg = $error_msg['message'];

				$errors['failed_' . $studierendenantrag_id] = 'Could not approve Unterbrechung for studierendenantrag_id: ' .
				$studierendenantrag_id .
				'<br>Details:<br>' .
				$error_msg;
			} else {
				$data = getData($data);

				$result = $this->_ci->StudierendenantragstatusModel->insert([
					'studierendenantrag_id' => $studierendenantrag_id,
					'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED,
					'insertvon' => $insertvon
				]);
				if (isError($result))
				{
					$errors['failed_' . $studierendenantrag_id] = $this->_ci->p->t('studierendenantrag', 'error_U_Approve', [
						'studierendenantrag_id' => $studierendenantrag_id,
						'message' => getError($result)['message']
					]);
				}
				else
				{
					$studierendenantrag_status_id = getData($result);
					$resultAntrag = $this->_ci->StudierendenantragModel->load($studierendenantrag_id);
					if (isError($resultAntrag))
						return $resultAntrag;
					$resultAntrag = getData($resultAntrag);
					if (!$resultAntrag)
						return error($this->_ci->p->t('studierendenantrag', 'error_no_antrag_found', ['id' => $studierendenantrag_id]));
					$resultAntrag = current($resultAntrag);

						// Prestudentstatus und Unterbrechungsfolgeaktionen setzen
					$result = $this->_ci->prestudentlib->setUnterbrecher(
						$resultAntrag->prestudent_id,
						$resultAntrag->studiensemester_kurzbz,
						$studierendenantrag_id
					);

					if (isError($result)) {
						$this->_ci->StudierendenantragstatusModel->delete($studierendenantrag_status_id);
						return $result;
					}


					//Mail
					$subject = $this->_ci->p->t('studierendenantrag', 'mail_subject_U_Approve');
					$mail = [];

					if (isset($data['errors']['person_id']))
					{
								//send assistenz mit id
						$errors[] = $this->_ci->p->t('studierendenantrag', 'error_mail_and_name', ['message' => $data['errors']['person_id']]);
						$mail['ass'] = $this->_ci->p->t('studierendenantrag', 'StudentIn', ['prestudent_id' => $data['antrag']->prestudent_id]);
					}
					elseif (isset($data['errors']['email']))
					{
						if (isset($data['errors']['person']))
						{
							//send assistenz mit id
							$errors[] = $this->_ci->p->t('studierendenantrag', 'error_mail_and_name', [
								'message' => $data['errors']['email'] . '<br>' . $data['errors']['person']
							]);
							$mail['ass'] = $this->_ci->p->t('studierendenantrag', 'StudentIn', ['prestudent_id' => $data['antrag']->prestudent_id]);
						}
						else
						{
							//send assistenz mit name
							$errors[] = $this->_ci->p->t('studierendenantrag', 'error_mail', ['message' => $data['errors']['email']]);
							$mail['ass'] = trim($data['person']->vorname . ' ' . $data['person']->nachname);
						}
					}
					else
					{
						if (isset($data['errors']['person']))
						{
							//send assistenz mit id & student mit "Student/in"
							$errors[] = $this->_ci->p->t('studierendenantrag', 'error_name', ['message' => $data['errors']['person']]);
							$mail['ass'] = $this->_ci->p->t('studierendenantrag', 'StudentIn', ['prestudent_id' => $data['antrag']->prestudent_id]);
							$mail['stu'] = $this->_ci->p->t('person', 'StudentIn');
						}
						else
						{
							//send normal
							$mail['ass'] = $mail['stu'] = trim($data['person']->vorname . ' ' . $data['person']->nachname);
						}
					}

					if (isset($mail['ass'])) {
						// NOTE(chris): Sancho mail
						$mailVorlage = 'Sancho_Mail_Antrag_U_Approve';

						$result = $this->_ci->StudentModel->loadWhere(['prestudent_id'=> $data['antrag']->prestudent_id]);
						if (hasData($result)) {
							$student = current(getData($result));
							$data['UID'] = $student->student_uid;
						}

						$result = $this->_ci->PersonModel->getFullName($insertvon);
						if (isError($result))
							return $result;
						$approvedBy = $insertvon;
						if (hasData($result))
						{
							$approvedBy = getData($result);
						}

						if (!sendSanchoMail(
							$mailVorlage,
							[
								'name' => $mail['ass'],
								'stg' => $data['studiengang']->bezeichnung,
								'Orgform' => $data['prestudent_status']->orgform_kurzbz,
								'vorname' => $data['person']->vorname,
								'nachname' => $data['person']->nachname,
								'UID' => $data['UID'],
								'sem' => $resultAntrag->studiensemester_kurzbz,
								'linkPdf' => base_url(
									'content/pdfExport.php?xml=AntragUnterbrechung.xml.php&xsl=AntragUnterbrechung&id=' .
									$studierendenantrag_id .
									'&output=pdf'
								),
								'insertvon' => $approvedBy
							],
							$data['prestudent_status']->email,
							$subject
						)) {
							$errors[] = $this->_ci->p->t('studierendenantrag', 'error_mail_to', $data['prestudent_status']);
						}
					}
					if (isset($mail['stu'])) {
						// NOTE(chris): Sancho mail
						$mailVorlage = 'Sancho_Mail_Antrag_U_Student';
						if ($data['studienbeitrag'])
							$mailVorlage .= '_SB';
						if (!sendSanchoMail(
							$mailVorlage,
							[
								'name' => $mail['stu'],
								'stg' => $data['studiengang']->bezeichnung,
								'Orgform' => $data['prestudent_status']->orgform_kurzbz,
								'vorname' => $data['person']->vorname,
								'nachname' => $data['person']->nachname
							],
							$data['email'],
							$subject
						)) {
							$errors[] = $this->_ci->p->t('studierendenantrag', 'error_mail_to', $data);
						}
					}
				}
			}
		}

		if (count($errors))
			return error($errors);

		return success();
	}

	/**
	 * @param array			$studierendenantrag_ids
	 * @param string		$insertvon
	 * @param string		$grund
	 *
	 * @return stdClass
	 */
	public function rejectUnterbrechung($studierendenantrag_ids, $insertvon, $grund)
	{
		$this->_ci->load->model('person/Kontakt_model', 'KontaktModel');
		$this->_ci->load->model('crm/Student_model', 'StudentModel');

		$errors = [];

		foreach ($studierendenantrag_ids as $studierendenantrag_id) {
			$data = $this->getDataForUnterbrechung($studierendenantrag_id);

			if (isError($data)) {
				$error_msg = getError($data);
				if (is_array($error_msg) && isset($error_msg['message']))
					$error_msg = $error_msg['message'];

				$errors['failed_' . $studierendenantrag_id] = $this->_ci->p->t('studierendenantrag', 'error_U_Reject', [
					'studierendenantrag_id' => $studierendenantrag_id,
					'message' => $error_msg
				]);
			} else {
				$data = getData($data);

				$result = $this->_ci->StudierendenantragstatusModel->insert([
					'studierendenantrag_id' => $studierendenantrag_id,
					'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_REJECTED,
					'insertvon' => $insertvon,
					'grund' => $grund
				]);
				if (isError($result)) {
					$errors['failed_' . $studierendenantrag_id] = $this->_ci->p->t('studierendenantrag', 'error_U_Reject', [
                        'studierendenantrag_id' => $studierendenantrag_id,
                        'message' => getError($result)['message']
                    ]);
				} else {
					$name = '';

					if (isset($data['errors']['person_id']) || isset($data['errors']['email'])) {
						$error_msg = [];
						if (isset($data['errors']['person_id']))
							$error_msg[] = $data['errors']['person_id'];
						if (isset($data['errors']['email']))
							$error_msg[] = $data['errors']['email'];
						$error_msg = $this->_ci->p->t('studierendenantrag', 'error_mail', ['message' => implode('<br>', $error_msg)]);
						$errors[] = $error_msg;
					} else {
						if (isset($data['errors']['person'])) {
							//send student mit "Student/in"
							$errors[] = $this->_ci->p->t('studierendenantrag', 'error_name', ['message' => $data['errors']['person']]);
							$name = $this->_ci->p->t('person', 'studentIn');
							$vorname = "";
							$nachname = $name;
						} else {
							//send normal
							$name = trim($data['person']->vorname . ' ' . $data['person']->nachname);
							$vorname = $data['person']->vorname;
							$nachname = $data['person']->nachname;
						}
					}
					if ($name)
						// NOTE(chris): Sancho mail
						if (!sendSanchoMail(
							'Sancho_Mail_Antrag_U_Reject',
							[
								'name' => $name,
								'vorname' => $vorname,
								'nachname' => $nachname,
								'grund' => $grund,
								'stg' => $data['studiengang']->bezeichnung,
								'Orgform' => $data['prestudent_status']->orgform_kurzbz,
								'prestudent_id' => $data['prestudent_status']->prestudent_id,
								'abmeldungLink' => site_url('lehre/Studierendenantrag/abmeldung/' . $data['prestudent_status']->prestudent_id),
								'abmeldungLinkCIS' => CIS_ROOT .
									'index.ci.php/lehre/Studierendenantrag/abmeldung/' .
									$data['prestudent_status']->prestudent_id
							],
							$data['email'],
							$this->_ci->p->t('studierendenantrag', 'mail_subject_U_Reject')
						))
							$errors[] = $this->_ci->p->t('studierendenantrag', 'error_mail_to', $data);
				}
			}
		}

		if (count($errors))
			return error($errors);

		return success();
	}

	/**
	 * @param integer		$studierendenantrag_id
	 *
	 * @return array
	 */
	private function getDataForUnterbrechung($studierendenantrag_id)
	{
		$result = [];
		$errors = [];

		$res = $this->_ci->StudierendenantragModel->load($studierendenantrag_id);
		if (isError($res))
			return $res;

		$res = getData($res);
		if (!$res)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_antrag_found', ['id' => $studierendenantrag_id]));

		$result['antrag'] = $antrag = current($res);
		$this->_ci->StudiengangModel->addJoin('public.tbl_prestudent ps', 'studiengang_kz');
		$res = $this->_ci->StudiengangModel->loadWhere(['prestudent_id' => $antrag->prestudent_id]);
		if (hasData($res)) {
			$result['studiengang'] = current(getData($res));
		}
		else{
			$result['studiengang'] = new stdClass();
			$result['studiengang']->bezeichnung = "";
		}

		$res = $this->_ci->PrestudentstatusModel->getLastStatusWithStgEmail($antrag->prestudent_id);
		if (isError($res))
			return $res;

		$res = getData($res);
		if (!$res)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_prestudentstatus', $antrag));

		$result['prestudent_status'] = current($res);


		$res = $this->_ci->PrestudentModel->load($antrag->prestudent_id);

		if (isError($res)) {
			$errors['person_id'] = getError($res);
		} else {
			$res = getData($res);
			if (!$res) {
				$errors['person_id'] = $this->_ci->p->t('studierendenantrag', 'error_no_prestudent', $antrag);
			} else {
				$person_id = current($res)->person_id;

				$res = $this->_ci->PersonModel->load($person_id);
				if (isError($res)) {
					$errors['person'] = getError($res);
				} else {
					$res = getData($res);
					if (!$res) {
						$errors['person'] = $this->_ci->p->t('studierendenantrag', 'error_no_person', ['person_id' => $person_id]);
					} else {
						$result['person'] = current($res);
					}
				}

				$res = $this->_ci->StudentModel->loadWhere(['prestudent_id' => $antrag->prestudent_id]);
				if (isError($res)) {
					$errors['email'] = getError($res);
				} else {
					$res = getData($res);

					if (!$res) {
						$errors['email'] = $this->_ci->p->t('studierendenantrag', 'error_no_email', ['person_id' => $person_id]);
					} else {
						$result['email'] = $this->_ci->StudentModel->getEmailFH(current($res)->student_uid);
					}
				}
			}
		}

		$result['studienbeitrag'] = false;
		if (!isset($errors['person_id'])) {
			$date_target = new DateTime(
				$this->_ci->config->item('frist_rueckzahlung_studiengebuer_' . substr($result['antrag']->studiensemester_kurzbz, 0, 2)) .
				substr($result['antrag']->studiensemester_kurzbz, 2)
			);
			$date_created = new DateTime($result['antrag']->datum);
			if ($date_created < $date_target) {
				$this->_ci->load->model('crm/Konto_model', 'KontoModel');
				$result['studienbeitrag'] = $this->_ci->KontoModel->checkStudienbeitragFromPerson(
                    $person_id,
                    $result['antrag']->studiensemester_kurzbz
                );
			}
		}

		$result['errors'] = $errors;

		return success($result);
	}

	/**
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz
	 * @param string		$insertvon
	 * @param boolean		$repeat
	 *
	 * @return stdClass
	 */
	public function createWiederholung($prestudent_id, $studiensemester_kurzbz, $insertvon, $repeat)
	{
		$result = $this->_ci->StudierendenantragModel->loadIdAndStatusWhere([
			'tbl_studierendenantrag.prestudent_id' => $prestudent_id,
			'studiensemester_kurzbz'=> $studiensemester_kurzbz,
			'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG
		]);

		$antrag_id = null;
		if (hasData($result)) {
			$antrag = current(getData($result));
			if ($antrag->status == Studierendenantragstatus_model::STATUS_REOPENED ||
				$antrag->status == Studierendenantragstatus_model::STATUS_REQUESTSENT_1 ||
				$antrag->status == Studierendenantragstatus_model::STATUS_REQUESTSENT_2)
			{
				$antrag_id = $antrag->studierendenantrag_id;
			}
			else
			{
				return error($this->_ci->p->t('global', 'antragBereitsGestellt'));
			}
		}

		if ($antrag_id === null) {
			$result = $this->_ci->StudierendenantragModel->insert([
				'prestudent_id' => $prestudent_id,
				'studiensemester_kurzbz'=> $studiensemester_kurzbz,
				'datum' => date('c'),
				'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG,
				'insertvon' => $insertvon
			]);

			if (isError($result))
				return $result;

			$antrag_id = getData($result);
		}

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => $repeat
				? Studierendenantragstatus_model::STATUS_CREATED
				: Studierendenantragstatus_model::STATUS_PASS,
			'insertvon' => $insertvon
		]);

		if ($repeat) {
			$res = $this->_ci->PrestudentstatusModel->getLastStatusWithStgEmail($prestudent_id);
			if (isError($res))
				return $res;
			$res = getData($res);
			if (!$res)
				return error($this->_ci->p->t('studierendenantrag', 'error_no_prestudentstatus', ['prestudent_id' => $prestudent_id]));

			$prestudent_status = current($res);
			$email = $prestudent_status->email;
			// NOTE(chris): Sancho mail
			$lvzuweisungLink = site_url('lehre/Antrag/Wiederholung/assistenz/' . $antrag_id);
			if (defined('VILESCI_ROOT')) {
				$lvzuweisungLink = VILESCI_ROOT . 'index.ci.php/lehre/Antrag/Wiederholung/assistenz/' . $antrag_id;
			}
			sendSanchoMail(
				'Sancho_Mail_Antrag_W_New',
				[
					'antrag_id' => $antrag_id,
					'stg' => $prestudent_status->stg_bezeichnung,
					'Orgform' => $prestudent_status->orgform,
					'lvzuweisungLink' => $lvzuweisungLink
				],
				$email,
				$this->_ci->p->t('studierendenantrag', 'mail_subject_W_New')
			);
		}

		if (isError($result))
			return $result;

		return success($antrag_id);
	}

	/**
	 * @param integer		$studierendenantrag_id
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function reopenWiederholung($studierendenantrag_id, $insertvon)
	{
		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $studierendenantrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_REOPENED,
			'insertvon' => $insertvon
		]);
		return $result;
	}

	/**
	 * @param integer		$studierendenantrag_id
	 * @param string		$objectedvon
	 *
	 * @return stdClass
	 */
	public function objectAbmeldung($studierendenantrag_id, $objectedvon)
	{
		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $studierendenantrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_OBJECTED,
			'insertvon' => $objectedvon
		]);
		return $result;
	}

	public function getWiederholungsAntraege($status)
	{
		$studiengaenge = $this->_ci->permissionlib->getSTG_isEntitledFor('student/studierendenantrag');
		$result = $this->_ci->StudierendenantragModel->loadForStudiengaenge(
			$studiengaenge,
			Studierendenantrag_model::TYP_WIEDERHOLUNG,
			$status
		);
		if (!getData($result))
			return $result;
		$result = getData($result);
		$grouped = [];

		foreach ($result as $item) {
			if (!isset($grouped[$item->studiengang_kz])) {
				$grouped[$item->studiengang_kz] = [
					'bezeichnung' => $item->bezeichnung,
					'bezeichnung_mehrsprachig' => $item->bezeichnung_mehrsprachig,
					'antraege' => []
				];
			}
			$grouped[$item->studiengang_kz]['antraege'][] = $item;
		}

		return success($grouped);
	}

	public function getLvsForAntrag($antrag_id)
	{
		$this->_ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->_ci->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$result = $this->_ci->StudierendenantragModel->load($antrag_id);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_antrag_found', ['id' => $antrag_id]));
		$antrag = current($result);


		$result = $this->_ci->StudierendenantragModel->getStgAndSem($antrag_id);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_stg_and_sem', ['id' => $antrag_id]));
		$result = current($result);
		$studiengang_kz = $result->studiengang_kz;
		$orgform_kurzbz = $result->orgform_kurzbz;
		$ausbildungssemester = $result->ausbildungssemester;
		$sprache = $result->sprache;

		// NOTE(chris): check permission
		$allowedStgs = $this->_ci->permissionlib->getSTG_isEntitledFor('student/studierendenantrag') ?: [];
		if (!in_array($studiengang_kz, $allowedStgs)) {
			$allowedStgs = $this->_ci->permissionlib->getSTG_isEntitledFor('student/antragfreigabe') ?: [];
			if (!in_array($studiengang_kz, $allowedStgs)) {
				if(!$this->isOwnAntrag($antrag_id))
					return error('Forbidden');
			}
		}


		$result = $this->_ci->StudiensemesterModel->getNextFrom($antrag->studiensemester_kurzbz);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_sem_after', ['semester' => $antrag->studiensemester_kurzbz]));
		$semA = current($result)->studiensemester_kurzbz;

		$result = $this->_ci->StudiensemesterModel->getNextFrom($semA);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_sem_after', ['semester' => $semA]));
		$semB = current($result)->studiensemester_kurzbz;

		$result = $this->_ci->StudierendenantraglehrveranstaltungModel->loadWhere(['studierendenantrag_id' => $antrag_id]);
		if (isError($result))
			return $result;
		$result = getData($result) ?: [];

		$lvszugewiesen = array();
		foreach ($result as $lv)
		{
			$lvszugewiesen[$lv->lehrveranstaltung_id] = $lv;
		}

		$result = $this->getLvsByStgStsemAndSem(
			$studiengang_kz,
			$orgform_kurzbz,
			$semA,
			$ausbildungssemester + 1,
			$antrag->prestudent_id,
			$sprache
		);
		if (isError($result))
			return $result;
		$lvsA = $result->retval; // NOTE(chris): don't use getData() because we want to differenciate [] and null
		$repeat_last = false;
		if ($lvsA) {
			foreach($lvsA as $lv)
			{
				if (isset($lvszugewiesen[$lv->lehrveranstaltung_id]) &&
					($lvszugewiesen[$lv->lehrveranstaltung_id]->note == $this->_ci->config->item('wiederholung_note_nicht_zugelassen')))
				{
					$lv->antrag_zugelassen = true;
					$lv->antrag_anmerkung = $lvszugewiesen[$lv->lehrveranstaltung_id]->anmerkung;
				}
			}
		} elseif ($lvsA === null) {
			// NOTE(chris): We are repeating the last semester
			$repeat_last = true;

			$result = $this->_ci->PrestudentstatusModel->getStatusByFilter($antrag->prestudent_id, 'Student', $ausbildungssemester - 1);
			if (isError($result))
				return $result;

			$stdsems = getData($result) ?: [];
			$stdsem = null;

			$result = $this->_ci->StudiensemesterModel->load($antrag->studiensemester_kurzbz);
			if (isError($result))
				return $result;
			if (!hasData($result))
				return error($this->_ci->p->t(
					'studierendenantrag',
					'error_no_stdsem',
					['studiensemester_kurzbz' => $antrag->studiensemester_kurzbz]
				));
			$asem = current(getData($result));

			foreach ($stdsems as $sem) {
				$result = $this->_ci->StudiensemesterModel->load($sem->studiensemester_kurzbz);
				if (isError($result))
					return $result;
				if (hasData($result)) {
					if (current(getData($result))->start < $asem->start) {
						$stdsem = $sem->studiensemester_kurzbz;
						break;
					}
				}
			}

			// NOTE(chris): if we don't find a status in the previous semester there is something wrong
			if (!$stdsem)
				return error($this->_ci->p->t('studierendenantrag', 'error_no_status_in_prev_sem'));

			$result = $this->getLvsByStgStsemAndSem(
				$studiengang_kz,
				$orgform_kurzbz,
				$semA,
				$ausbildungssemester - 1,
				$antrag->prestudent_id,
				$sprache
			);
			if (isError($result))
				return $result;
			
			$lvsA = getData($result) ?: [];
			
			$result = $this->getLvsByStgStsemAndSem(
				$studiengang_kz,
				$orgform_kurzbz,
				$stdsem,
				$ausbildungssemester - 1,
				$antrag->prestudent_id,
				$sprache
			);
			if (isError($result))
				return $result;

			$lvsAtest = getData($result) ?: [];

			if (count(array_intersect(array_map(function ($a) {
				return $a->lehrveranstaltung_id;
			}, $lvsA), array_map(function ($a) {
				return $a->lehrveranstaltung_id;
			}, $lvsAtest)))) {
				foreach ($lvsA as $lv) {
					if (isset($lvszugewiesen[$lv->lehrveranstaltung_id]) && ($lvszugewiesen[$lv->lehrveranstaltung_id]->note == 0)) {
						$lv->antrag_anmerkung = $lvszugewiesen[$lv->lehrveranstaltung_id]->anmerkung;
						$lv->antrag_zugelassen = true;
					}
				}
			} else {
				$lvsA = null;
			}
		}

		$result = $this->getLvsByStgStsemAndSem(
			$studiengang_kz,
			$orgform_kurzbz,
			$semB,
			$ausbildungssemester,
			$antrag->prestudent_id,
			$sprache
		);
		if (isError($result))
			return $result;
		$lvsB = getData($result) ?: [];
		foreach($lvsB as $lv)
		{
			if(isset($lvszugewiesen[$lv->lehrveranstaltung_id]) && ($lvszugewiesen[$lv->lehrveranstaltung_id]->note == 0))
			{
				$lv->antrag_anmerkung = $lvszugewiesen[$lv->lehrveranstaltung_id]->anmerkung;
				$lv->antrag_zugelassen = true;
			}
			// TODO(manu): eventuelle Änderungen taggen
		}

		$result = [
			'1' . $semA => $lvsA,
			'2' . $semB => $lvsB ?: []
		];
		if ($repeat_last)
			$result['repeat_last'] = true;

		return success($result);
	}

	public function getLvsByStgStsemAndSem(
		$studiengang_kz,
		$orgform_kurzbz,
		$studiensemester_kurzbz,
		$ausbildungssemester,
		$prestudent_id,
		$sprache
	) {
		$this->_ci->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$result = $this->_ci->StudienplanModel->getStudienplaeneBySemester(
			$studiengang_kz,
			$studiensemester_kurzbz,
			$ausbildungssemester,
			$orgform_kurzbz
		);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result) {
			$result = $this->_ci->StudiengangModel->load($studiengang_kz);
			if (isError($result))
				return $result;
			if (!hasData($result))
				return error($this->_ci->p->t('studierendenantrag', 'error_no_stg', ['studiengang_kz' => $studiengang_kz]));
			$stg = current(getData($result));

			if ($ausbildungssemester > $stg->max_semester)
				return success();
			return error($this->_ci->p->t('studierendenantrag', 'error_no_studienplan', [
				'studiengang_kz' => $studiengang_kz,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'semester' => $ausbildungssemester
			]));
		}
		if (count($result) > 1) {
			$langmap = array_unique(array_map(function ($a) {
				return $a->sprache;
			}, $result));
			if ($sprache
				&& count($langmap) == count($result)
				&& in_array($sprache, $langmap)
			) {
				$result = array_filter($result, function ($a) use ($sprache) {
					return $a->sprache == $sprache;
				});
			} else {
				return error($this->_ci->p->t('studierendenantrag', 'error_multiple_studienplan', [
					'studiengang_kz' => $studiengang_kz,
					'studiensemester_kurzbz' => $studiensemester_kurzbz,
					'semester' => $ausbildungssemester
				]));
			}
		}
		$studienplan = current($result);

		return $this->_ci->StudienplanModel->getStudienplanLehrveranstaltungForPrestudent(
			$studienplan->studienplan_id,
			$ausbildungssemester,
			$prestudent_id
		);
	}

	/**
	 * Checks if a prestudent can submit an Antrag for Abmeldung
	 *
	 * @param integer		$prestudent_id
	 *
	 * @return \stdClass	on success retval 0 means not a student;
	 *                      retval 1 means Berechtigt;
	 *                      retval -1 means has already an Antrag pending;
	 *                      retval -2 means other Antrag pending;
	 *                      retval -3 means in blacklist stg
	 */
	public function getPrestudentAbmeldeBerechtigt($prestudent_id)
	{
		$result = $this->_ci->PrestudentModel->load($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);
		$result = current(getData($result));
		$stg_kz = $result->studiengang_kz;
		if (in_array($stg_kz, $this->_ci->config->item('stgkz_blacklist_abmeldung')))
			return success(-3);

		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);
		$result = current(getData($result));
		$datumStatus = $result->datum;

		if (!in_array($result->status_kurzbz, $this->_ci->config->item('antrag_prestudentstatus_whitelist_abmeldung'))) {
			$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere([
				'tbl_studierendenantrag.prestudent_id' => $prestudent_id,
				's.studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED
			], [
				Studierendenantrag_model::TYP_ABMELDUNG,
				Studierendenantrag_model::TYP_ABMELDUNG_STGL
			]);
			if (isError($result))
				return $result;
			if (hasData($result))
				return success(-1);

			$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere([
				'tbl_studierendenantrag.prestudent_id' => $prestudent_id,
				's.studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_PAUSE
			], [
				Studierendenantrag_model::TYP_ABMELDUNG,
				Studierendenantrag_model::TYP_ABMELDUNG_STGL
			]);
			if (isError($result))
				return $result;
			if (hasData($result))
				return success(-1);

			return success(0);
		}

		$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere(['tbl_studierendenantrag.prestudent_id' => $prestudent_id]);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(1);
		$result= getData($result);
		foreach ($result as $antrag)
		{
			if ($antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG || $antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG_STGL)
			{
				if ($antrag->status == Studierendenantragstatus_model::STATUS_CREATED)
					return success(-1);
				elseif ($antrag->status == Studierendenantragstatus_model::STATUS_APPROVED && $antrag->datum > $datumStatus)
					return success(-1);
			}
			if ($antrag->typ == Studierendenantrag_model::TYP_WIEDERHOLUNG)
			{
				if($antrag->status == Studierendenantragstatus_model::STATUS_PASS)
					return success(-2);
			}
		}

		return success(1);
	}

	/**
	 * Checks if a prestudent can submit an Antrag for Unterbrechung
	 *
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz		(optional)
	 *
	 * @return \stdClass	on success retval 0 means not a student;
	 *                      retval 1 means Berechtigt;
	 * 						retval -1 means has already an Antrag pending;
	 * 						retval -2 means other Antrag pending;
	 * 						retval -3 means in blacklist stg
	 */
	public function getPrestudentUnterbrechungsBerechtigt($prestudent_id, $studiensemester_kurzbz = null, $datum_wiedereinstieg = null)
	{
		$result = $this->_ci->PrestudentModel->load($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);
		$result = current(getData($result));
		$stg_kz = $result->studiengang_kz;
		if (in_array($stg_kz, $this->_ci->config->item('stgkz_blacklist_unterbrechung')))
			return success(-3);

		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);
		$result = current(getData($result));
		$prestudent_stdsem = $result->studiensemester_kurzbz;
		$datumStatus = $result->datum;
		if (!in_array($result->status_kurzbz, $this->_ci->config->item('antrag_prestudentstatus_whitelist'))
			&& $result->status_kurzbz != 'Unterbrecher') {
			return success(0);
		}
		$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere(['tbl_studierendenantrag.prestudent_id' => $prestudent_id]);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(1);

		$result = getData($result);
		foreach ($result as $antrag)
		{
			if ($antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG || $antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG_STGL)
			{
				if($antrag->status == Studierendenantragstatus_model::STATUS_CREATED)
					return success(-2);
				elseif($antrag->status == Studierendenantragstatus_model::STATUS_APPROVED && $antrag->datum > $datumStatus)
					return success(-2);
			}
			if ($antrag->typ == Studierendenantrag_model::TYP_UNTERBRECHUNG)
			{
				// NOTE(chris): Ignore canceled ones
				if ($antrag->status == Studierendenantragstatus_model::STATUS_CANCELLED)
					continue;
			}
			if ($antrag->typ == Studierendenantrag_model::TYP_WIEDERHOLUNG)
			{
				if($antrag->status == Studierendenantragstatus_model::STATUS_PASS)
					return success(-2);
			}
		}

		if (!$studiensemester_kurzbz) {
			$sems = $this->getSemesterForUnterbrechung($prestudent_id, $prestudent_stdsem);
			if (!count(array_filter($sems, function ($item) {
				return !$item['disabled'];
			})))
				return success(-1);
		} else {
			if ($this->_ci->StudierendenantragModel->hasRunningUnterbrechungBetween($prestudent_id, $studiensemester_kurzbz, $datum_wiedereinstieg))
				return success(-1);
		}
		
		return success(1);
	}

	/**
	 * Checks if a prestudent can submit an Antrag for Wiederholung
	 *
	 * @param integer		$prestudent_id
	 *
	 * @return \stdClass	on success retval 0 means not a student;
	 * 						retval 1 means Berechtigt;
	 * 						retval -1 means has already an Antrag pending;
	 * 						retval -2 means other Antrag pending;
	 * 						retval -3 means in blacklist stg
	 */
	public function getPrestudentWiederholungsBerechtigt($prestudent_id)
	{
		$result = $this->_ci->PrestudentModel->load($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);
		$result = current(getData($result));
		$stg_kz = $result->studiengang_kz;
		if (in_array($stg_kz, $this->_ci->config->item('stgkz_blacklist_wiederholung')))
			return success(-3);

		$result = $this->getFailedExamForPrestudent($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);

		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);

		$result = current(getData($result));
		$datumStatus = $result->datum;
		if (!in_array($result->status_kurzbz, $this->_ci->config->item('antrag_prestudentstatus_whitelist'))) {
			$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere([
				'tbl_studierendenantrag.prestudent_id' => $prestudent_id,
				'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG,
				's.studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED
			]);
			if (isError($result))
				return $result;
			if (hasData($result))
				return success(-1);

			$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere([
				'tbl_studierendenantrag.prestudent_id' => $prestudent_id,
				'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG,
				's.studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_DEREGISTERED
			]);
			if (isError($result))
				return $result;
			if (hasData($result))
				return success(-1);

			$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere([
				'tbl_studierendenantrag.prestudent_id' => $prestudent_id,
				'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG,
				's.studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_PAUSE
			]);
			if (isError($result))
				return $result;
			if (hasData($result))
				return success(-1);

			return success(0);
		}
		$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere(['tbl_studierendenantrag.prestudent_id' => $prestudent_id]);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(1);
		$result= getData($result);
		foreach ($result as $antrag)
		{
			if ($antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG || $antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG_STGL)
			{
				if($antrag->status == Studierendenantragstatus_model::STATUS_CREATED)
					return success(-2);
				elseif($antrag->status == Studierendenantragstatus_model::STATUS_APPROVED && $antrag->datum > $datumStatus)
					return success(-2);
			}
			if ($antrag->typ == Studierendenantrag_model::TYP_WIEDERHOLUNG)
			{
				return success(-1);
			}
		}

		return success(1);
	}

	/**
	 * Gets details for a new Antrag
	 *
	 * @param integer		$prestudent_id
	 *
	 * @return \stdClass
	 */
	public function getDetailsForNewAntrag($prestudent_id)
	{
		$result = $this->_ci->PrestudentstatusModel->loadLastWithStgDetails($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', 'error_no_prestudentstatus', ['prestudent_id' => $prestudent_id]));
		$result = current(getData($result));
		return success($result);
	}

	/**
	 * Gets details for the latest Antrag of one or more types
	 *
	 * @param integer		$prestudent_id
	 * @param array|string	$typ
	 *
	 * @return \stdClass
	 */
	public function getDetailsForLastAntrag($prestudent_id, $typ = null)
	{
		$where = [
			'tbl_studierendenantrag.prestudent_id' => $prestudent_id
		];
		$types = null;
		if ($typ) {
			if (is_array($typ))
				$types = $typ;
			else
				$where['typ'] = $typ;
		}
		$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere($where, $types);
		if (isError($result))
			return $result;

		$antraege = getData($result) ?: [];
		$resultAntrag = null;
		foreach ($antraege as $antrag) {
			if ($antrag->status != Studierendenantragstatus_model::STATUS_CANCELLED) {
				$resultAntrag = $antrag;
				break;
			}
		}
		if (!$resultAntrag)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_antrag_found_prestudent', [
				'typ' => $typ ?: '',
				'prestudent_id' => $prestudent_id
			]));

		return $this->addDetailsToAntrag($resultAntrag);
	}

	/**
	 * Gets details for a specific Antrag
	 *
	 * @param integer		$studierendenantrag_id
	 *
	 * @return \stdClass
	 */
	public function getDetailsForAntrag($studierendenantrag_id)
	{
		$where = [
			's.studierendenantrag_id' => $studierendenantrag_id
		];

		$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere($where);
		if (isError($result))
		return $result;

		if (!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', "error_no_antrag_found", ['id' => $studierendenantrag_id]));
		$resultAntrag = current(getData($result));

		return $this->addDetailsToAntrag($resultAntrag);
	}

	/**
	 * Helper function for getDetailsForAntrag and getDetailsForLastAntrag
	 *
	 * @param \stdClass		$antrag
	 *
	 * @return \stdClass
	 */
	protected function addDetailsToAntrag($antrag)
	{
		$result = $this->_ci->PrestudentstatusModel->loadLastWithStgDetails(
			$antrag->prestudent_id,
			$antrag->studiensemester_kurzbz,
			$antrag->insertamum
		);
		if (isError($result))
			return $result;
		if (!hasData($result)) {
			$result = $this->_ci->PrestudentstatusModel->loadLastWithStgDetails(
				$antrag->prestudent_id,
				null,
				$antrag->insertamum
			);
			if (isError($result))
				return $result;
			if (!hasData($result))
				return error($this->_ci->p->t('studierendenantrag', 'error_no_prestudent_in_sem', $antrag));
			$tmp = current(getData($result));
			$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
			$res = $this->_ci->StudiensemesterModel->load($antrag->studiensemester_kurzbz);
			if (hasData($res))
				$tmp->studienjahr_kurzbz = current(getData($res))->studienjahr_kurzbz;
			else
				$tmp->studienjahr_kurzbz = '';
			// NOTE(chris): the semester might not be correct on this fallback so we disable it
			$tmp->semester = '';
		}

		$result = current(getData($result));

		$result->status = $antrag->status;
		$result->statustyp = $antrag->statustyp;
		$result->status_insertvon = $antrag->status_insertvon;
		$result->grund = $antrag->grund;
		$result->studierendenantrag_id = $antrag->studierendenantrag_id;
		$result->typ = $antrag->typ;
		$result->datum = $antrag->datum;
		$result->dms_id = $antrag->dms_id;
		$result->datum_wiedereinstieg = $antrag->datum_wiedereinstieg;

		return success($result);
	}

	/**
	 * Rearrange the free semester slots for a new Unterbrechung
	 *
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz
	 *
	 * @return array
	 */
	public function getSemesterForUnterbrechung($prestudent_id, $studiensemester_kurzbz)
	{
		$result = $this->_ci->StudierendenantragModel->getFreeSlotsForUnterbrechung($prestudent_id, $studiensemester_kurzbz);
		if (isError($result))
			return [];
		$result = getData($result);
		if (!$result)
			return [];
		return array_reduce($result, function ($carry, $item) {
			if (!isset($carry[$item->von]))
				$carry[$item->von] = [
					'studienjahr_kurzbz' => $item->studienjahr_kurzbz,
					'studiensemester_kurzbz' => $item->von,
					'wiedereinstieg' => [],
					'disabled' => true
				];
			
			$carry[$item->von]['wiedereinstieg'][] = [
				'studiensemester_kurzbz' => $item->bis,
				'start' => $item->ende,
				'disabled' => (boolean)$item->studierendenantrag_id
			];
			
			if ($carry[$item->von]['disabled'] && !$item->studierendenantrag_id) {
				$carry[$item->von]['disabled'] = false;
			}
			
			return $carry;
		}, []);
		return $result;
	}

	public function getAktivePrestudentenInStgs($studiengaenge, $query)
	{
		$blacklist = $this->_ci->config->item('stgkz_blacklist_abmeldung');
		$studiengaenge = array_diff($studiengaenge, $blacklist);
		return $this->_ci->StudiengangModel->getAktivePrestudenten(
			$studiengaenge,
			[ Studierendenantrag_model::TYP_ABMELDUNG ],
			$query
		);
	}

	public function getFailedExamForPrestudent($prestudent_id, $max_date = null, $studiensemester_kurzbz = null)
	{
		return $this->_ci->PruefungModel->loadWhereCommitteeExamFailedForPrestudent($prestudent_id, $max_date, $studiensemester_kurzbz);
	}

	public function saveLvs($lvArray)
	{
		$result = $this->_ci->StudierendenantraglehrveranstaltungModel->deleteWhere([
			'studierendenantrag_id' => $lvArray[0]['studierendenantrag_id']
		]);
		if (isError($result))
			return $result;

		$result = $this->_ci->StudierendenantraglehrveranstaltungModel->insertBatch($lvArray);
		if (isError($result))
			return $result;

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $lvArray[0]['studierendenantrag_id'],
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_LVSASSIGNED,
			'insertvon' => $lvArray[0]['insertvon']
		]);
		if (isError($result))
			return $result;

		$antrag_status_id = getData($result);
		$result = $this->_ci->StudierendenantragstatusModel->loadWithTyp($antrag_status_id);

		return $result;
	}

	public function approveWiederholung($antrag_id, $insertvon)
	{
		$this->_ci->load->model('crm/Student_model', 'StudentModel');

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED,
			'insertvon' => $insertvon
		]);

		if (isError($result)) {
			return $result;
		}

		$result = $this->_ci->StudierendenantragModel->getStgEmail($antrag_id);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_stg_email', ['id' => $antrag_id]));

		$email = current($result)->email;

		$result = $this->_ci->StudierendenantragModel->getStgAndSem($antrag_id);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error($this->_ci->p->t('studierendenantrag', 'error_no_stg_antrag', ['id' => $antrag_id]));

		$stg = current($result);
		$semester = $stg->ausbildungssemester;

		$result = $this->_ci->StudierendenantragModel->load($antrag_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', 'error_no_antrag_found', ['id' => $antrag_id]));
		$result = current(getData($result));
		$prestudent_id = $result->prestudent_id;

		$result = $this->_ci->PersonModel->loadPrestudent($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->p->t('studierendenantrag', 'error_no_person_prestudent', ['prestudent_id' => $prestudent_id]));
		$person = current(getData($result));
		$student = trim($person->vorname . ' ' . $person->nachname);

		$result = $this->_ci->PersonModel->getFullName($insertvon);
		if (isError($result))
			return $result;
		$mitarbeiter = $insertvon;
		if (hasData($result)) {
			$mitarbeiter = getData($result);
		}

		$result = $this->_ci->StudentModel->loadWhere(['prestudent_id'=> $prestudent_id]);
		if (hasData($result)) {
			$studentObj = current(getData($result));
			$student_uid = $studentObj->student_uid;
		}
		else
			$student_uid = '';

		// NOTE(chris): Sancho mail
		if (!sendSanchoMail(
			'Sancho_Mail_Antrag_W_Approve',
			[
				'antrag_id' => $antrag_id,
				'stg' => $stg->bezeichnung,
				'sem' => $semester,
				'student' => $student,
				'mitarbeiter' => $mitarbeiter,
				'Orgform' => $stg->orgform_kurzbz,
				'UID' => $student_uid
			],
			$email,
			$this->_ci->p->t('studierendenantrag', 'mail_subject_W_Approve')
		))
			return error($this->_ci->p->t('studierendenantrag', 'error_mail_to', ['email' => $email]));

		if ($student_uid) {
			$email = $this->_ci->StudentModel->getEmailFH($student_uid);
			$vorlage = 'Sancho_Mail_Antrag_W_Student';

			$sem_not_allowed = $sem_to_repeat = '';
			$list_not_allowed = $list_to_repeat = $this->_ci->p->t('studierendenantrag', 'mail_part_error_no_lvs');

			$result = $this->getLvsForAntrag($antrag_id);
			if (hasData($result)) {
				$lvs = getData($result);
				if (isset($lvs['repeat_last'])) {
					unset($lvs['repeat_last']);
					$vorlage .= '_Lst';
				}
				foreach ($lvs as $sem => $lv_list) {
					$lvs_filtered = array_filter($lv_list, function ($el) {
						return property_exists($el, 'antrag_zugelassen') && $el->antrag_zugelassen;
					});
					if (substr($sem, 0, 1) == '1') {
						$sem_not_allowed = substr($sem, 1);
						$list_not_allowed = array_map(function ($el) {
							return $el->bezeichnung . '(' . $el->lehrform_kurzbz . ')';
						}, $lvs_filtered);
						$list_not_allowed = '<ul><li>' . implode('</li><li>', $list_not_allowed) . '</li></ul>';
					} else {
						$sem_to_repeat = substr($sem, 1);
						$list_to_repeat = array_map(function ($el) {
							return $el->bezeichnung . '(' . $el->lehrform_kurzbz . ')';
						}, $lvs_filtered);
						$list_to_repeat = '<ul><li>' . implode('</li><li>', $list_to_repeat) . '</li></ul>';
					}
				}
			}
			
			// NOTE(chris): Sancho mail
			sendSanchoMail(
				$vorlage,
				[
					'antrag_id' => $antrag_id,
					'stg' => $stg->bezeichnung,
					'sem' => $semester,
					'mitarbeiter' => $mitarbeiter,
					'student' => $student,
					'sem_not_allowed' => $sem_not_allowed,
					'list_not_allowed' => $list_not_allowed,
					'sem_to_repeat' => $sem_to_repeat,
					'list_to_repeat' => $list_to_repeat,
					'Orgform' => $stg->orgform_kurzbz
				],
				$email,
				$this->_ci->p->t('studierendenantrag', 'mail_subject_W_Student')
			);
		}


		return success();
	}

	public function getAntragHistory($antrag_id)
	{
		$result = $this->_ci->StudierendenantragstatusModel->loadWithTypWhere([
			'studierendenantrag_id' => $antrag_id
		]);
		return $result;
	}


	/**
	 * @param integer		$studierendenantrag_id
	 *
	 * @return boolean
	 */
	protected function isOwnAntrag($studierendenantrag_id)
	{
		if ($studierendenantrag_id == null)
			return false;
		$result = $this->_ci->StudierendenantragModel->loadForPerson(getAuthPersonId());
		if (!hasData($result))
			return false;
		$antraege = array_map(function ($antrag) {
			return $antrag->studierendenantrag_id;
		}, getData($result));

		return in_array($studierendenantrag_id, $antraege);
	}

	/**
	 * @param integer		$studierendenantrag_id
	 * @param string		$permission either 'student/antragfreigabe' or 'student/studierendenantrag'
	 *
	 * @return boolean
	 */
	protected function hasAccessToAntrag($studierendenantrag_id, $permission)
	{
		$studiengaenge = $this->_ci->permissionlib->getSTG_isEntitledFor($permission);
		if (!$studiengaenge)
			return false;
		$result = $this->_ci->StudierendenantragModel->isInStudiengang($studierendenantrag_id, $studiengaenge);
		return (boolean)getData($result);
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToShowAntrag($antrag_id)
	{
		return
		(
			$this->isOwnAntrag($antrag_id) ||
			$this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe') ||
			$this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag')
		);
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToSeeHistoryForAntrag($antrag_id)
	{
		return
		(
			$this->isOwnAntrag($antrag_id) ||
			$this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe') ||
			$this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag')
		);
	}

	/**
	 * @param integer		$prestudent_id
	 * @param boolean		$checkAssistencePermission
	 *
	 * @return boolean
	 */
	public function isEntitledToCreateAntragFor($prestudent_id, $checkAssistencePermission = false)
	{
		$result = $this->_ci->PrestudentModel->load($prestudent_id);
		if (!hasData($result))
			return false;

		$result = getData($result)[0];
		$person_id = $result->person_id;

		if (getAuthPersonId() == $person_id)
			return true;

		if ($checkAssistencePermission)
		{
			$studiengaenge = $this->_ci->permissionlib->getSTG_isEntitledFor('student/studierendenantrag');
			if (in_array($result->studiengang_kz, $studiengaenge ?: []))
				return true;
		}

		return false;
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToCancelAntrag($antrag_id)
	{
		$result = $this->_ci->StudierendenantragModel->load($antrag_id);
		if (!hasData($result))
			return false;
		$antrag = current(getData($result));

		if ($antrag->typ != Studierendenantrag_model::TYP_ABMELDUNG_STGL)
			return $this->isOwnAntrag($antrag_id);
		
		return $this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag');
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToPauseAntrag($antrag_id)
	{
		return ($this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe') || $this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag'));
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToUnpauseAntrag($antrag_id)
	{
		return ($this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe') || $this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag'));
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToReopenAntrag($antrag_id)
	{
		return $this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag');
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToObjectAntrag($antrag_id)
	{
		return ($this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe') || $this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag'));
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToApproveAntrag($antrag_id)
	{
		return $this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe');
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToRejectAntrag($antrag_id)
	{
		return $this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe');
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function antragCanBeManualPaused($antrag_id)
	{
		$this->_ci->StudierendenantragModel->db->where_not_in('campus.get_status_studierendenantrag(studierendenantrag_id)', [
			Studierendenantragstatus_model::STATUS_DEREGISTERED,
			Studierendenantragstatus_model::STATUS_APPROVED,
			Studierendenantragstatus_model::STATUS_PAUSE
		]);
		$result = $this->_ci->StudierendenantragModel->loadWhere([
			'studierendenantrag_id' => $antrag_id,
			'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG
		]);

		return hasData($result);
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function antragCanBeManualUnpaused($antrag_id)
	{
		return $this->_ci->StudierendenantragModel->isManuallyPaused($antrag_id);
	}

	/**
	 * @param integer		$antrag_id
	 * @param string|array	$status
	 *
	 * @return boolean
	 */
	public function hasStatus($antrag_id, $status)
	{
		$result = $this->_ci->StudierendenantragModel->getWithLastStatusWhere(['s.studierendenantrag_id' => $antrag_id]);
		if (!hasData($result))
			return false;
		$lastStatus = getData($result)[0];

		if (!is_array($status))
			$status = [$status];

		return in_array($lastStatus->studierendenantrag_statustyp_kurzbz, $status);
	}

	/**
	 * @param integer		$antrag_id
	 * @param string|array	$type
	 *
	 * @return boolean
	 */
	public function hasType($antrag_id, $type)
	{
		$result = $this->_ci->StudierendenantragModel->load($antrag_id);
		if (!hasData($result))
			return false;
		$antrag = getData($result)[0];

		if (!is_array($type))
			$type = [$type];

		return in_array($antrag->typ, $type);
	}


	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function getLvsForPrestudent($prestudent_id, $studiensemester_kurzbz)
	{
		$result = $this->_ci->StudierendenantraglehrveranstaltungModel->getLvsForPrestudent($prestudent_id, $studiensemester_kurzbz);
		return $result;
	}
}