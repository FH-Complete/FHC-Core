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

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function isBerechtigt($id, $typeId)
	{
		if($typeId != "prestudent_id")
		{
			return $this->terminateWithError($this->p->t('ui','error_typeNotizIdIncorrect'), self::ERROR_TYPE_GENERAL);
		}

		//TODO define permission
		if(!$this->permissionlib->isBerechtigt('admin', 'suid') && !$this->permissionlib->isBerechtigt('assistenz', 'suid'))
		{
			$result =  $this->p->t('lehre','error_keineSchreibrechte');

			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess("berechtigt in überschreibender Funktion");
	}
}