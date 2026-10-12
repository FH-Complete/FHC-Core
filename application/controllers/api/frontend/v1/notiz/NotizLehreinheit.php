<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class NotizLehreinheit extends Notiz_Controller
{
	const PERMISSION = 'basis/lvverwaltung';
	
	public function __construct()
	{
		parent::__construct([
			'getUid' => [self::PERMISSION . ':r'],
			'getNotizen' => [self::PERMISSION . ':r'],
			'loadNotiz' => [self::PERMISSION . ':r'],
			'addNewNotiz' => [self::PERMISSION . ':rw'],
			'updateNotiz' => [self::PERMISSION . ':rw'],
			'deleteNotiz' => [self::PERMISSION . ':rw'],
			'loadDokumente' => [self::PERMISSION . ':r'],
			'getMitarbeiter' => [self::PERMISSION . ':r'],
			'isBerechtigt' => [self::PERMISSION . ':r'],
		]);

		//Load Models
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');
		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('LVVerwaltungPermissionLib');

		if ($this->router->method == 'addNewNotiz')
		{
			$json = $this->input->post('data');
			$post_data = json_decode($json, true);
			$lehreinheit_id = $post_data['id'];

			if(!$lehreinheit_id)
			{
				return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Lehreinheit ID']), self::ERROR_TYPE_GENERAL);
			}
			$this->_checkAllowedOesFromLehreinheit($lehreinheit_id);
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
			$this->_checkAllowedOesFromLehreinheit($lehreinheit_id);
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
			$this->_checkAllowedOesFromLehreinheit($lehreinheit_id);
		}

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	private function _checkAllowedOesFromLehreinheit($lehreinheit_id, $allowedOes)
	{
		if (!$this->lvverwaltungpermissionlib->isEntitledForLehreinheit($lehreinheit_id, 'suid'))
			return $this->terminateWithError($this->p->t('ui', 'notAuthorized'), self::ERROR_TYPE_GENERAL);
	}

	public function isBerechtigt($id, $typeId)
	{
		if($typeId != "lehreinheit_id")
		{
			$this->terminateWithError($this->p->t('ui','error_typeNotizIdIncorrect'), self::ERROR_TYPE_GENERAL);
		}

		if(!$this->lvverwaltungpermissionlib->isEntitledForLehreinheit($id, 'suid'))
		{
			$result =  $this->p->t('lehre','error_keineSchreibrechte');

			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess("berechtigt in überschreibender Funktion");
	}


}
