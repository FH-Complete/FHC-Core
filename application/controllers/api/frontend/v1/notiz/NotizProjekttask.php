<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class NotizProjekttask extends Notiz_Controller
{
	public function __construct()
	{
		parent::__construct([
			'isBerechtigt' => ['admin:r', 'assistenz:r'],
		]);
	}

	public function isBerechtigt($id, $typeId)
	{
		if($typeId != "projekttask_id")
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