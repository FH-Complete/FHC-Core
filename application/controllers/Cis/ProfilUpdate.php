<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 *
 */
class ProfilUpdate extends Auth_Controller
{

	public function __construct()
	{
		parent::__construct([
			'index' => ['student/stammdaten:r', 'mitarbeiter/stammdaten:r'],
			'show' => ['student/stammdaten:r', 'mitarbeiter/stammdaten:r', 'basis/cis:r'],
			'id' => ['student/stammdaten:r', 'mitarbeiter/stammdaten:r']
		]);

		$this->load->model('person/Profil_update_model', 'ProfilUpdateModel');
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('crm/Student_model', 'StudentModel');

		// Load language phrases
		$this->loadPhrases(
			array(
				'profilUpdate'
			)
		);

		$this->load->library('DmsLib');
		$this->load->library('PermissionLib');

		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();
	}

	public function index()
	{
		$this->load->view('Cis/ProfilUpdate');
	}

	public function id($profil_update_id = null)
	{
		$this->load->view('Cis/ProfilUpdate', ['profil_update_id' => $profil_update_id]);
	}

	public function show($dms_id)
	{
		$profil_update = $this->ProfilUpdateModel->loadWhere(['attachment_id' => $dms_id]);
		$profil_update = hasData($profil_update) ? getData($profil_update)[0] : null;

		//? checks if an profil update exists with the dms_id requested from the user
		if ($profil_update)
		{
			$is_mitarbeiter_profil_update = getData($this->MitarbeiterModel->isMitarbeiter($profil_update->uid));
			$is_student_profil_update = getData($this->StudentModel->isStudent($profil_update->uid));

			if (
				$this->permissionlib->isBerechtigt('student/stammdaten:r') && $is_student_profil_update ||
				$this->permissionlib->isBerechtigt('mitarbeiter/stammdaten:r') && $is_mitarbeiter_profil_update ||
				$this->uid == $profil_update->uid
			)
			{
				// Get file to be downloaded from DMS
				$newFilename = $this->uid . "/document_" . $dms_id;
				$download = $this->dmslib->getOutputFileInfo($dms_id);
				if (isError($download))
					return $download;

				// Download file
				$this->outputFile(getData($download));
			}
			else
			{
				show_error($this->p->t('profilUpdate', 'profilUpdate_permission_error'));
				return;
			}
		}
		else
		{
			show_error($this->p->t('profilUpdate', 'profilUpdate_dms_error'));
			return;
		}
	}
}

