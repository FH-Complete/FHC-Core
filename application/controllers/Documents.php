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
 * This controller handles output and access to documents.
 * It creates a XML file, transforms it with the XSL-FO Vorlage from the
 * database and generates a PDF file with unoconv or docsbox.
 * This file is then outputted as download.
 *
 * It is the CodeIgniter version of content/pdfExport.php when not using the
 * get paremeters: "archivdokument" and "archive".
 * Use exportSigned() instead of providing the "sign" get parameter and
 * export() otherwise.
 */
class Documents extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct([
			'export' => self::PERM_LOGGED,
			'exportSigned' => self::PERM_LOGGED
		]);

		// Load Phrases
		$this->loadPhrases([
			'stv'
		]);
	}

	/**
	 * Download a not signed document.
	 *
	 * @param string				$xml
	 * @param string				$xsl
	 *
	 * @return void
	 */
	public function export($xml, $xsl)
	{
		return $this->_export($xml, $xsl);
	}

	/**
	 * Download a signed document.
	 *
	 * @param string				$xml
	 * @param string				$xsl
	 *
	 * @return void
	 */
	public function exportSigned($xml, $xsl)
	{
		return $this->_export($xml, $xsl, getAuthUID());
	}

	/**
	 * Helper function for export() and exportSigned()
	 *
	 * @param string				$xml
	 * @param string				$xsl
	 * @param string				$sign_user (optional)
	 *
	 * @return void
	 */
	protected function _export($xml, $xsl, $sign_user = null)
	{
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

		// Access rights
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
			if (isError($result))
				show_error(getError($result));
			if (!hasData($result))
				return show_404();
			
			$access_rights = current(getData($result))->berechtigung;
			if (!$access_rights)
				return show_404();
			$allowed = false;
			foreach ($access_rights as $access_right) {
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

		$this->load->library('DocumentExportLib');
		$this->load->model('system/Vorlage_model', 'VorlageModel');

		$result = $this->VorlageModel->load($xsl);
		if (isError($result))
			show_error(getError($result));
		if (!hasData($result))
			show_404();
		
		$vorlage = current(getData($result));
		if ($sign_user && !$vorlage->signierbar)
			show_error($this->p->t("stv", "grades_error_sign"));

		
		// Filename
		$filename = ($vorlage->bezeichnung ?: $vorlage->vorlage_kurzbz);
		switch ($xsl) {
			case 'LV_Informationen':
				$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
				$result = $this->StudiengangModel->load($this->input->post_get('stg_kz'));
				if (!isError($result) && hasData($result))
		/output			$filename .= '_' . sanitizeProblemChars(current(getData($result))->kurzbzlang);

				$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
				$result = $this->StudiensemesterModel->load($this->input->post_get('ss'));
				if (!isError($result) && hasData($result))
					$filename .= '_' . sanitizeProblemChars(current(getData($result))->studiensemester_kurzbz);
				break;
			case 'Honorarvertrag':
				$uid = str_replace(';', '', $this->input->post_get('uid') ?: '');
				$this->load->model('person/Benutzer_model', 'BenutzerModel');
				$this->BenutzerModel->addJoin('public.tbl_person', 'person_id', 'LEFT');
				$result = $this->BenutzerModel->load([$uid]);
				if (!isError($result) && hasData($result)) {
					$user = current(getData($result));
					$filename .= '_' . sanitizeProblemChars($user->nachname) . '_' . sanitizeProblemChars($user->vorname);
				}
				break;
			case 'Studienordnung':
				$filename = 'Studienordnung-Studienplan-';
				
				$this->load->model('organisation/Studienordnung_model', 'StudienordnungModel');
				$result = $this->StudienordnungModel->load($this->input->post_get('studienordnung_id'));
				if (!isError($result) && hasData($result)) {
					$so = current(getData($result));
					$filename .= sprintf("%'.04d", $so->studiengang_kz) . '-' . $so->studiengangkurzbzlang;
				}
				break;
			default:
				$uid = str_replace(';', '', $this->input->post_get('uid') ?: '');
				$this->load->model('person/Benutzer_model', 'BenutzerModel');
				$this->BenutzerModel->addJoin('public.tbl_person', 'person_id', 'LEFT');
				$result = $this->BenutzerModel->load([$uid]);
				if (!isError($result) && hasData($result)) {
					$user = current(getData($result));
					$filename .= '_' . sanitizeProblemChars($user->nachname);
				}
				break;
		}

		// XML Data
		$result = $this->documentexportlib->getDataURL($xml, $params);
		if (isError($result))
			show_error(getError($result));

		$data = getData($result);

		// Get the content
		$contentResult = $this->documentexportlib->getContent($filename, $vorlage, $data, $xsl_oe_kurzbz, $version, $outputformat, $sign_user);

		// If an error occurred
		if (isError($contentResult)) show_error(getError($contentResult));

		$fileObj = new stdClass();
		$fileObj->file_content = getData($contentResult);
		$fileObj->name = $filename,;
		$fileObj->mimetype = isEmptyString($vorlage->mimetype) ? 'application/pdf' : $vorlage->mimetype;
		$fileObj->disposition = 'attachment';

		// Download
		$this->outputFile($fileObj);
	}
}
