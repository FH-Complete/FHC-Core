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

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about documents
 * Listens to ajax post calls to change the documents data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 *
 * This controller handles output and access to documents.
 * It checks permissions to render documents in an alternative format
 * or it creates a XML file, transforms it with the XSL-FO Vorlage from the
 * database and generates a PDF file with unoconv or docsbox.
 * This file is then archivated in the database.
 *
 * The last part is the CodeIgniter version of content/pdfExport.php when not
 * using the get paremeter: "archivdokument" but using the get parameter:
 * "archive".
 * Use archiveSigned() instead of providing the "sign" get parameter and
 * archive() otherwise.
 */
class Documents extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'permissionAlternativeFormat' => self::PERM_LOGGED,
			'archive' => ['admin:rw', 'assistenz:rw'],
			'archiveSigned' => ['admin:rw', 'assistenz:rw']
		]);
	}

	/**
	 * Checks if the current user has permission to render documents in an
	 * alternative format.
	 *
	 * @param string				$oe_kurzbz Or studiengang_kz
	 *
	 * @return void
	 */
	public function permissionAlternativeFormat($oe_kurzbz)
	{
		$this->terminateWithSuccess($this->permissionlib->isBerechtigt('system/change_outputformat', null, $oe_kurzbz));
	}

	/**
	 * Download a not signed document.
	 *
	 * @param string				$xml (optional)
	 * @param string				$xsl (optional)
	 *
	 * @return void
	 */
	public function archive($xml = null, $xsl = null)
	{
		return $this->_archive($xml, $xsl);
	}

	/**
	 * Download a signed document.
	 *
	 * @param string				$xml (optional)
	 * @param string				$xsl (optional)
	 *
	 * @return void
	 */
	public function archiveSigned($xml = null, $xsl = null)
	{
		return $this->_archive($xml, $xsl, getAuthUID());
	}

	/**
	 * Helper function for archive() and archiveSigned()
	 *
	 * @param string				$xml
	 * @param string				$xsl
	 * @param string				$sign_user (optional)
	 *
	 * @return void
	 */
	public function _archive($xml, $xsl, $sign_user = null)
	{
		if (!$xml || !$xsl) {
			$this->load->library('form_validation');
			if (!$xml) {
				$xml = $this->input->post_get('xml');
				$this->form_validation->set_rules('xml', 'xml', 'required');
			}
			if (!$xsl) {
				$xsl = $this->input->post_get('xsl');
				$this->form_validation->set_rules('xsl', 'xsl', 'required');
			}

			if (!$this->form_validation->run())
				$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$xsl_oe_kurzbz = null;
		$version = $this->input->post_get('version') ?: null;

		// Get the OE or STG of the document
		$xsl_oe_kurzbz = $this->input->post_get('xsl_oe_kurzbz')
			?: $this->input->post_get('xsl_stg_kz')
			?: $this->input->post_get('stg_kz');
		if (is_null($xsl_oe_kurzbz)) {
			$uid = $this->input->post_get('uid');
			if ($uid) {
				$uid = current(explode(';', $uid));
				$this->load->model('crm/Student_model', 'StudentModel');
				$result = $this->StudentModel->load([$uid]);
				if (!isError($result) && hasData($result))
					$xsl_oe_kurzbz = current(getData($result))->studiengang_kz;
			}
		}
		if (is_null($xsl_oe_kurzbz)) {
			$prestudent_id = $this->input->post_get('prestudent_id');
			if ($prestudent_id) {
				$prestudent_id = current(explode(';', $prestudent_id));
				$this->load->model('crm/Prestudent_model', 'PrestudentModel');
				$result = $this->PrestudentModel->load($prestudent_id);
				if (!isError($result) && hasData($result))
					$xsl_oe_kurzbz = current(getData($result))->studiengang_kz;
			}
		}
		if (is_null($xsl_oe_kurzbz))
			$xsl_oe_kurzbz = 0;

		// Vorlage
		$this->load->model('system/Vorlage_model', 'VorlageModel');

		$result = $this->VorlageModel->load($xsl);
		$vorlage = current($this->getDataOrTerminateWithError($result));
		if (!$vorlage)
			show_404();

		// Akte Data
		$akteData = [
			'dokument_kurzbz' => $vorlage->dokument_kurzbz ?: 'Zeugnis',
			'mimetype' => 'application/pdf',
			'erstelltam' => date('Y-m-d'),
			'gedruckt' => true,
			'insertamum' => date('c'),
			'insertvon' => getAuthUID(),
			'uid' => $this->input->post_get('uid') ?: '',
			'archiv' => true,
			'signiert' => !!$sign_user,
			'stud_selfservice' => $vorlage->stud_selfservice
		];
		$studiengang_kz = null;
		if ($akteData['uid']) {
			$this->load->model('crm/Student_model', 'StudentModel');
			$this->StudentModel->addJoin('public.tbl_studiengang', 'studiengang_kz', 'LEFT');
			$result = $this->StudentModel->load([$akteData['uid']]);
			$student = current($this->getDataOrTerminateWithError($result));

			$ss = $this->input->post_get('ss');
			
			if ($ss !== null) {
				$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
				$result = $this->PrestudentstatusModel->getLastStatus($student->prestudent_id, $ss);
				$status = current($this->getDataOrTerminateWithError($result));
				if (!$status)
					$this->terminateWithError('StudentIn hat keinen Status in diesem Semester'); // TODO(chris): phrase
				$semester = $status->ausbildungssemester;

				$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
				$this->StudentlehrverbandModel->addJoin('public.tbl_benutzer', 'uid = student_uid');
				$this->StudentlehrverbandModel->addJoin('public.tbl_studiengang', 'studiengang_kz');
				$result = $this->StudentlehrverbandModel->load([
					'studiensemester_kurzbz' => $ss,
					'student_uid' => $akteData['uid']
				]);
				$res = current($this->getDataOrTerminateWithError($result));

				$studiengang_kz = $res->studiengang_kz;
				$akteData['person_id'] = $res->person_id;
				switch ($xsl) {
					case 'Ausbildungsver':
					case 'AusbVerEng':
						$akteData['titel'] = mb_substr($xsl .
							"_" .
							strtoupper($res->typ) .
							strtoupper($res->kurzbz) .
							"_" .
							$semester .
							"_" .
							$ss, 0, 64);
						$akteData['bezeichnung'] = mb_substr($vorlage->bezeichnung . " " . $student->kuerzel, 0, 64);
						break;
					case 'LVZeugnisEng':
					case 'LVZeugnis':
					case 'Zertifikat':
						$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
						$result = $this->LehrveranstaltungModel->load($this->input->post_get('lvid'));
						$lv = current($this->getDataOrTerminateWithError($result));
						$akteData['dokument_kurzbz'] = $xsl;
						$akteData['titel'] = mb_substr($xsl .
							"_" .
							strtoupper($res->typ) .
							strtoupper($res->kurzbz) .
							"_" .
							$semester .
							'_' .
							$ss .
							'_' .
							str_replace(' ', '_', $lv->bezeichnung), 0, 60);
						$akteData['bezeichnung'] = mb_substr($xsl .
							" " .
							strtoupper($res->typ) .
							strtoupper($res->kurzbz) .
							" " .
							$semester .
							". Semester" .
							' ' .
							$ss .
							' ' .
							$lv->bezeichnung, 0, 64);
						break;
					case 'SZeugnis':
						$akteData['titel'] = mb_substr($vorlage->bezeichnung . " " . $student->kuerzel, 0, 64);
						$akteData['bezeichnung'] = mb_substr($vorlage->bezeichnung . " " . $student->kuerzel, 0, 64);
						break;
					default:
						$akteData['titel'] = mb_substr($xsl .
							"_" .
							strtoupper($res->typ) .
							strtoupper($res->kurzbz) .
							"_" .
							$semester .
							"_" .
							$ss, 0, 64);
						$akteData['bezeichnung'] = mb_substr($xsl .
							" " .
							strtoupper($res->typ) .
							strtoupper($res->kurzbz) .
							" " .
							$semester .
							". Semester" .
							' ' .
							$ss, 0, 64);
						break;
				}
			} else {
				$studiengang_kz = $student->studiengang_kz;
				$akteData['person_id'] = $student->person_id;
				$akteData['titel'] = $vorlage->bezeichnung . '_' . $student->kuerzel;
				$akteData['bezeichnung'] = mb_substr($vorlage->bezeichnung . " " . $student->kuerzel, 0, 64);
			}
		} else {
			$prestudent_id = $this->input->post_get('prestudent_id');
			if ($prestudent_id) {
				$this->load->model('crm/prestudent_model', 'PrestudentModel');
				$this->PrestudentModel->addJoin('public.tbl_studiengang', 'studiengang_kz', 'LEFT');
				$result = $this->PrestudentModel->load($prestudent_id);
				$prestudent = current($this->getDataOrTerminateWithError($result));
				
				$studiengang_kz = $prestudent->studiengang_kz;
				$akteData['person_id'] = $prestudent->person_id;
				$akteData['titel'] = mb_substr($xsl . "_" . $prestudent->kuerzel, 0, 64);
				$akteData['bezeichnung'] = mb_substr($vorlage->bezeichnung . " " . $prestudent->kuerzel, 0, 64);
			}
		}

		// Access rights
		if (!$this->permissionlib->isBerechtigt('admin', 'suid', $studiengang_kz)
			&& !$this->permissionlib->isBerechtigt('assistenz', 'suid', $studiengang_kz))
			return $this->_outputAuthError([$this->router->method => ['admin:rw', 'assistenz:rw']]);
		if ($xsl == 'AccountInfo') {
			$this->load->model('resource/Mitarbeiter_model', 'MitarbeiterModel');
			$this->load->model('crm/Student_model', 'StudentModel');
			$uids = $this->input->post_get('uid');
			if ($uids) {
				$uids = explode(';', $uids);
				foreach ($uids as $uid) {
					$result = $this->MitarbeiterModel->load($uid);
					if (!isError($result) && hasData($result)) {
						if (!$this->permissionlib->isBerechtigt('admin', 'suid', 0)
							&& !$this->permissionlib->isBerechtigt('mitarbeiter', 'suid', 0))
							return $this->_outputAuthError([$this->router->method => ['admin:rw', 'mitarbeiter:rw']]);
					} else {
						$result = $this->StudentModel->load([$uid]);
						if (!isError($result) && hasData($result)) {
							$student = current(getData($result));
							if (!$this->permissionlib->isBerechtigt('admin', 'suid', $student->studiengang_kz)
								&& !$this->permissionlib->isBerechtigt('admin', 'suid', 0)
								&& !$this->permissionlib->isBerechtigt('assistenz', 'suid', $student->studiengang_kz)
								&& !$this->permissionlib->isBerechtigt('assistenz', 'suid', 0)
								&& !$this->permissionlib->isBerechtigt('support', 'suid', 0))
								return $this->_outputAuthError([$this->router->method => ['admin:rw', 'assistenz:rw', 'support:rw']]);
						}
					}
				}
			}
		} else {
			$this->load->model('system/Vorlagestudiengang_model', 'VorlagestudiengangModel');

			$result = $this->VorlagestudiengangModel->getCurrent($xsl, $xsl_oe_kurzbz, $version);
			$access_rights = current($this->getDataOrTerminateWithError($result));
			if (!$access_rights || !$access_rights->berechtigung)
				return show_404();
			
			$allowed = false;
			foreach ($access_rights->berechtigung as $access_right) {
				if ($this->permissionlib->isBerechtigt($access_right)) {
					$allowed = true;
					break;
				}
			}
			if (!$allowed)
				return $this->_outputAuthError([$this->router->method => $access_rights]);
		}

		// Output format
		$outputformat = $this->input->post_get('output') ?: 'pdf';
		if ($outputformat != 'pdf'
			// An der FHTW darf das Studienblatt und das Prüfungsprotokoll auch in anderen Formaten exportiert werden
			&& !(CAMPUS_NAME == 'FH Technikum Wien'
				&& ($xsl == 'Studienblatt'
					|| $xsl == 'StudienblattEng'
					|| $xsl == 'PrProtBA'
					|| $xsl == 'PrProtBAEng'
					|| $xsl == 'PrProtMA'
					|| $xsl == 'PrProtMAEng'
				)
			)
			&& !$this->permissionlib->isBerechtigt('system/change_outputformat', null, $xsl_oe_kurzbz)
		) {
			$outputformat = 'pdf';
		}

		// XML Params
		$params = 'xmlformat=xml';
		foreach ([
			'uid',
			'stg_kz',
			'person_id',
			'id',
			'prestudent_id',
			'buchungsnummern',
			'ss',
			'abschlusspruefung_id',
			'typ',
			'all',
			'preoutgoing_id',
			'lvid',
			'projekt_kurzbz',
			'von',
			'bis',
			'stundevon',
			'stundebis',
			'sem',
			'lehreinheit',
			'mitarbeiter_uid',
			'studienordnung_id',
			'fixangestellt',
			'standort',
			'abrechnungsmonat',
			'form',
			'projektarbeit_id',
			'betreuerart_kurzbz',
			'studiensemester_kurzbz'
		] as $key) {
			$value = $this->input->post_get($key);
			if ($value !== null)
				$params .= '&' . $key . '=' . urlencode($value);
		}
		$value = $this->input->post_get('vertrag_id');
		if ($value !== null) {
			foreach ($value as $id)
				$params .= '&vertrag_id[]=' . urlencode($id);
		}

		if (!$vorlage->archivierbar)
			$this->terminateWithError('Dieses Dokument ist nicht archivierbar'); // TODO(chris): phrase
		
		if ($sign_user && !$vorlage->signierbar)
			$this->terminateWithError('Diese Vorlage darf nicht signiert werden'); // TODO(chris): phrase

		
		$this->load->library('DocumentExportLib');

		// XML Data
		$result = $this->documentexportlib->getDataURL($xml, $params);
		$data = $this->getDataOrTerminateWithError($result);
		$this->documentexportlib->addArchiveToData($data);

		// Output
		$result = $this->documentexportlib->getContent($vorlage, $data, $xsl_oe_kurzbz, $version, $outputformat, $sign_user);

		$content = $this->getDataOrTerminateWithError($result);
		$akteData['titel'] .= '.pdf';
		$akteData['inhalt'] = base64_encode($content);

		$this->load->model('crm/Akte_model', 'AkteModel');
		$result = $this->AkteModel->insert($akteData);
		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(true);
	}
}
