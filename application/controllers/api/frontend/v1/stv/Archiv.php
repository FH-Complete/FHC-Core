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

if (!defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about archive documents
 * Listens to ajax post calls to change the archive documents
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Archiv extends FHCAPI_Controller
{
	/**
	 * Calls the parent's constructor and prepares libraries and phrases
	 */
	public function __construct()
	{
		parent::__construct([
			'getArchiv' => ['admin:r', 'assistenz:r'],
			'getArchivVorlagen' => ['admin:r', 'assistenz:r'],
			'archive' => ['admin:w', 'assistenz:w'],
			'download' => ['admin:w', 'assistenz:w'],
			'update' => ['admin:w'],
			'delete' => ['admin:w', 'assistenz:w'],
		]);

		// Load models
		$this->load->model('crm/Akte_model', 'AkteModel');
		$this->load->model('system/Vorlage_model', 'VorlageModel');

		// Load language phrases
		$this->loadPhrases([
			'archiv'
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Get archive documents for a person
	 
	 * @return void
	 */
	public function getArchiv()
	{
		$person_id = $this->input->get('person_id');

		$this->load->library('form_validation');

		if (!$person_id || !is_array($person_id))
		{
			$this->form_validation->set_rules('person_id', 'Person ID', 'required');

			if (!$this->form_validation->run())
				$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->AkteModel->getArchiv($person_id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * Get Vorlagen for archiving documents
	 * @return void
	 */
	public function getArchivVorlagen()
	{
		$result = $this->VorlageModel->getArchivVorlagen();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * 
	 * @param
	 * @return object success or error
	 */
	public function download()
	{
		$akte_id = $this->input->get('akte_id');

		if (!is_numeric($akte_id)) $this->terminateWithError('akte Id missing');

		$result = $this->AkteModel->load($akte_id);

		if (!hasData($result)) $this->terminateWithError('Akte not found');

		$data = getData($result)[0];

		$fileObj = new stdClass();
		if (isset($data->inhalt) && $data->inhalt != '')
		{
			// Define handle to output stream
			$tmpFilePointer   = fopen("php://output", 'w');
			$meta_data = stream_get_meta_data($tmpFilePointer);
			$filename = $meta_data["uri"];
			fwrite($tmpFilePointer, $data->inhalt);

			header('Content-Description: File Transfer');
			header('Content-Type: '. $data->mimetype);
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			//header('Content-Length: ' . filesize($fileObj->file));
			//header("Content-type: $data->mimetype");
			header('Content-Disposition: attachment; filename="'.$data->titel.'"');
			readfile($filename);
			die();
		}
		else
		{
			$this->load->library('AkteLib');

			$result = $this->aktelib->get($akte_id);
		}
	}

	/**
	 * Updating an Akte
	 * @return void
	 */
	public function update()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('akte_id', 'Akte Id', 'required');
		$this->form_validation->set_rules('signiert', 'Signiert', 'is_bool');
		$this->form_validation->set_rules('stud_selfservice', 'Self-Service', 'is_bool');

		//Events::trigger('konto_update_validation', $this->form_validation);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$id = $this->input->post('akte_id');

		// get the akte
		$result = $this->AkteModel->load($id);

		if (!hasData($result)) $this->terminateWithError("Akte not found!");

		$akte = getData($result)[0];

		$allowed = [
			'signiert',
			'stud_selfservice'
		];

		$data = [
			'updateamum' => date('c'),
			'updatevon' => getAuthUID()
		];

		// if Akte has Inhalt directly in Akte table
		if (isset($_FILES['datei']['tmp_name']))
		{
		$this->addMeta('read', "read");
			// update inhalt directly

			// get tmp file
			$filename = $_FILES['datei']['tmp_name'];
			// open it
			$fp = fopen($filename,'r');
			// read it
			$content = fread($fp, filesize($filename));
			fclose($fp);
			// encode it
			$data['inhalt'] = base64_encode($content);
			$this->addMeta('content', base64_encode($content));
		}

		
		foreach ($allowed as $field)
			if ($this->input->post($field) !== null)
				$data[$field] = $this->input->post($field);

		$this->addMeta("data", $data);

		$result = $this->AkteModel->update($id, $data);

		$this->getDataOrTerminateWithError($result);

		$result = null;

		$this->terminateWithSuccess($result);
	}


	/**
	 * Delete archived Akte
	 *
	 * @return void
	 */
	public function delete()
	{
		$this->load->library('form_validation');

		$this->form_validation->set_rules('akte_id', 'Akte ID', 'required');
		$this->form_validation->set_rules('studiengang_kz', 'Studiengang', 'has_permissions_for_stg[admin:rw,assistenz:rw]');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$akte_id = $this->input->post('akte_id');

		$result = $this->AkteModel->load($akte_id);

		if (!hasData($result))
		{
			$this->terminateWithError($this->p->t('archiv', 'error_missing', [
				'akte_id' => $akte_id
			]));
		}

		$result = getData($result)[0];

		if ($result->dokument_kurzbz == 'Ausbvert'
				&& isset($result->akzeptiertamum)
				&& !isEmptyString($result->akzeptiertamum)
				&& !has_permissions_for_stg($this->input->post('studiengang_kz'), 'admin:rw')
		)
		{
			$this->terminateWithError($this->p->t('archiv', 'nur_admins_loschen_ausbildungsvertraege', [
				'akte_id' => $akte_id
			]));
		}
		
		$result = $this->AkteModel->delete($akte_id);
		if (isError($result)) $this->terminateWithError(getError($result));

		$this->terminateWithSuccess();
	}
}
