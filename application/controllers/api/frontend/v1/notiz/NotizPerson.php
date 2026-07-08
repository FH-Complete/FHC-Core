<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class NotizPerson extends Notiz_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getUid' => ['admin:r', 'assistenz:r'],
			'getNotizen' => ['admin:r', 'assistenz:r'],
			'loadNotiz' => ['admin:r', 'assistenz:r'],
			'addNewNotiz' => ['admin:rw', 'assistenz:rw'],
			'updateNotiz' => ['admin:rw', 'assistenz:rw'],
			'deleteNotiz' => ['admin:rw', 'assistenz:rw'],
			'loadDokumente' => ['admin:r', 'assistenz:r'],
			'getMitarbeiter' => ['admin:r', 'assistenz:r'],
			'isBerechtigt' => ['admin:r', 'assistenz:r'],
			'getCountNotes' => ['admin:r', 'assistenz:r'],
		]);

		//Load Models
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		//Permission checks for allowed Oes
		if ($this->router->method == 'addNewNotiz')
		{
			$json = $this->input->post('data');
			$post_data = json_decode($json, true);
			$person_id = $post_data['id'];

			$allowedStgs = $this->permissionlib->getSTG_isEntitledFor('assistenz') ?: [];

			if(!$person_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Person ID']), self::ERROR_TYPE_GENERAL);
			}
			$this->_checkIfBerechtigungForOnePrestudentExists($person_id, $allowedStgs);
		}

		if ( $this->router->method == 'updateNotiz')
		{
			$json = $this->input->post('data');
			$post_data = json_decode($json, true);
			$notiz_id = $post_data['notiz_id'];

			if(!$notiz_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Notiz ID']), self::ERROR_TYPE_GENERAL);
			}

			//get person_id
			$result = $this->NotizzuordnungModel->loadWhere(['notiz_id' => $notiz_id]);

			$data = $this->getDataOrTerminateWithError($result);
			$person_id = current($data)->person_id;

			$allowedStgs = $this->permissionlib->getSTG_isEntitledFor('assistenz') ?: [];
			$this->_checkIfBerechtigungForOnePrestudentExists($person_id, $allowedStgs);
		}

		if ($this->router->method == 'deleteNotiz' )
		{
			$notiz_id = $this->input->post('notiz_id');
			$person_id = $this->input->post('id');

			if(!$notiz_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Notiz ID']), self::ERROR_TYPE_GENERAL);
			}

			if(!$person_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'person ID']), self::ERROR_TYPE_GENERAL);
			}

			$allowedStgs = $this->permissionlib->getSTG_isEntitledFor('assistenz') ?: [];
			$this->_checkIfBerechtigungForOnePrestudentExists($person_id, $allowedStgs);
		}
	}

	public function isBerechtigt($id, $typeId)
	{
		if($typeId != "person_id")
		{
			$this->terminateWithError($this->p->t('ui', 'error_typeNotizIdIncorrect'), self::ERROR_TYPE_GENERAL);
		}

		if (!$this->permissionlib->isBerechtigt('admin', 'suid') && !$this->permissionlib->isBerechtigt('assistenz', 'suid'))
		{
			$result =  $this->p->t('lehre', 'error_keineSchreibrechte');
			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$this->terminateWithSuccess("berechtigt in überschreibender Funktion");
	}

	//stv: if person has permission of one studiengang of person -> permission to add/update/delete Note
	private function _checkIfBerechtigungForOnePrestudentExists($person_id, $allowedStgs)
	{
		$result = $this->PrestudentModel->loadWhere(['person_id' => $person_id]);
		$data = $this->getDataOrTerminateWithError($result);

		$checkarray = [];
		foreach ($data as $item)
		{
			if(in_array($item->studiengang_kz, $allowedStgs))
			{
				return true;
			}
		}

		$this->terminateWithError($this->p->t('ui', 'error_keineBerechtigungStg'), self::ERROR_TYPE_GENERAL);
	}
}
