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

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		//Permission checks for allowed Oes
/*		$allowedOes = $this->permissionlib->getOE_isEntitledFor('assistenz') ?: [];

		$this->terminateWithSuccess($allowedOes);*/

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function isBerechtigt($id, $typeId)
	{
		if($typeId != "lehreinheit_id")
		{
			$this->terminateWithError($this->p->t('ui','error_typeNotizIdIncorrect'), self::ERROR_TYPE_GENERAL);
		}

		//TODO define permission
		if(!$this->permissionlib->isBerechtigt('admin', 'suid') && !$this->permissionlib->isBerechtigt('assistenz', 'suid'))
		{
			$result =  $this->p->t('lehre','error_keineSchreibrechte');

			$this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess("berechtigt in überschreibender Funktion");
	}
}
