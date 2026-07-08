<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class NotizPrestudent extends Notiz_Controller
{
	public function __construct()
	{
		parent::__construct([
			'isBerechtigt' => ['admin:r', 'assistenz:r'],
		]);

		//Load Models
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		$this->load->model('crm/Student_model', 'StudentModel');

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);

		//Permission checks for Studiengangsarray
		$allowedStgs = $this->permissionlib->getSTG_isEntitledFor('assistenz') ?: [];

		if ($this->router->method == 'addNewNotiz')
		{
			$json = $this->input->post('data');
			$post_data = json_decode($json, true);
			$prestudent_id = $post_data['id'];

			if(!$prestudent_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Lehreinheit ID']), self::ERROR_TYPE_GENERAL);
			}
			$this->_checkAllowedOesFromPrestudent($prestudent_id, $allowedStgs);
		}

		if ($this->router->method == 'updateNotiz')
		{
			$json = $this->input->post('data');
			$post_data = json_decode($json, true);
			$notiz_id = $post_data['notiz_id'];

			if(!$notiz_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Notiz ID']), self::ERROR_TYPE_GENERAL);
			}

			//get prestudent_id
			$result = $this->NotizzuordnungModel->loadWhere(['notiz_id' => $notiz_id]);

			$data = $this->getDataOrTerminateWithError($result);
			$prestudent_id = current($data)->prestudent_id;

			if(!$prestudent_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Prestudent ID']), self::ERROR_TYPE_GENERAL);
			}
			$this->_checkAllowedOesFromPrestudent($prestudent_id, $allowedStgs);
		}

		if ($this->router->method == 'deleteNotiz')
		{
			$notiz_id = $this->input->post('notiz_id');
			$prestudent_id = $this->input->post('id');

			if(!$notiz_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Notiz ID']), self::ERROR_TYPE_GENERAL);
			}

			if(!$prestudent_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Prestudent ID']), self::ERROR_TYPE_GENERAL);
			}
			$this->_checkAllowedOesFromPrestudent($prestudent_id, $allowedStgs);
		}
	}

	public function isBerechtigt($id, $typeId)
	{
		if($typeId != "prestudent_id")
		{
			$this->terminateWithError($this->p->t('ui','error_typeNotizIdIncorrect'), self::ERROR_TYPE_GENERAL);
		}

		if(!$this->permissionlib->isBerechtigt('admin', 'suid') && !$this->permissionlib->isBerechtigt('assistenz', 'suid'))
		{
			$result =  $this->p->t('lehre','error_keineSchreibrechte');

			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess("berechtigt in überschreibender Funktion");
	}

	private function _checkAllowedOesFromPrestudent($prestudent_id, $allowedStgs)
	{
		$student_uid = $this->StudentModel->getUID($prestudent_id);

		$result = $this->StudentModel->loadWhere(['student_uid' => $student_uid]);

		$data = $this->getDataOrTerminateWithError($result);
		$studiengang_kz = current($data)->studiengang_kz;

		if (!in_array($studiengang_kz, $allowedStgs))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_keineBerechtigungStg'), self::ERROR_TYPE_GENERAL);
		}
	}

}