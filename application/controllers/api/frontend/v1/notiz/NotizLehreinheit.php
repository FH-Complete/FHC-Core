<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class NotizLehreinheit extends Notiz_Controller
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
		]);

		//Load Models
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		//Permission checks for allowed Oes
		$allowedOes = $this->permissionlib->getOE_isEntitledFor('assistenz') ?: [];

		if ($this->router->method == 'addNewNotiz')
		{
			$json = $this->input->post('data');
			$post_data = json_decode($json, true);
			$lehreinheit_id = $post_data['id'];

			if(!$lehreinheit_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Lehreinheit ID']), self::ERROR_TYPE_GENERAL);
			}
			$this->_checkAllowedOesFromLehreinheit($lehreinheit_id, $allowedOes);
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

			//get lehreinheit_id
			$result = $this->NotizzuordnungModel->loadWhere(['notiz_id' => $notiz_id]);

			$data = $this->getDataOrTerminateWithError($result);
			$lehreinheit_id = current($data)->lehreinheit_id;

			if(!$lehreinheit_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Lehreinheit ID']), self::ERROR_TYPE_GENERAL);
			}
			$this->_checkAllowedOesFromLehreinheit($lehreinheit_id, $allowedOes);
		}

		if ($this->router->method == 'deleteNotiz')
		{
			$notiz_id = $this->input->post('notiz_id');
			$lehreinheit_id = $this->input->post('id');

			if(!$notiz_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Notiz ID']), self::ERROR_TYPE_GENERAL);
			}

			if(!$lehreinheit_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Lehreinheit ID']), self::ERROR_TYPE_GENERAL);
			}
			$this->_checkAllowedOesFromLehreinheit($lehreinheit_id, $allowedOes);
		}

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	private function _checkAllowedOesFromLehreinheit($lehreinheit_id, $allowedOes)
	{
		//get oe from lehreinheit
		$result = $this->LehreinheitModel->getOes($lehreinheit_id);
		$data = $this->getDataOrTerminateWithError($result);
		$oes = current($data);

		if (!in_array($oes, $allowedOes))
		{
			return $this->terminateWithError($this->p->t('ui', 'error_keineBerechtigungStg') . " " . $oes, self::ERROR_TYPE_GENERAL);
		}
	}

	public function isBerechtigt($id, $typeId)
	{
			if($typeId != "lehreinheit_id")
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


}
