<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Notiz extends Notiz_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getUid' => ['admin:r', 'assistenz:r'],
			'getNotizen' => ['admin:r', 'assistenz:r'],
			'loadNotiz' => ['admin:r', 'assistenz:r'], // TODO(manu): self::PERM_LOGGED
			'addNewNotiz' => ['admin:rw', 'assistenz:rw'], // TODO(manu): self::PERM_LOGGED
			'updateNotiz' => ['admin:rw', 'assistenz:rw'], // TODO(manu): self::PERM_LOGGED
			'deleteNotiz' => ['admin:r', 'assistenz:r'],
			'loadDokumente' => ['admin:r', 'assistenz:r'],
			'getMitarbeiter' => ['admin:r', 'assistenz:r'],
			'getCountNotes' => ['admin:r', 'assistenz:r'],
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


	public function getNotizen($id, $type)
	{

		//check if valid type
		$result = $this->NotizzuordnungModel->isValidType($type);
		if(isError($result))
			$this->terminateWithError($result->retval, self::ERROR_TYPE_GENERAL);

		//$this->terminateWithError(" after check type not valid", self::ERROR_TYPE_GENERAL);
		$result = $this->NotizModel->getNotizWithDocEntries($id, $type);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);

		//	return $this->terminateWithError("type not valid", self::ERROR_TYPE_GENERAL);

	}

	public function isBerechtigt($id, $typeId)
	{
		if(!$this->permissionlib->isBerechtigt('admin', 'suid') && !$this->permissionlib->isBerechtigt('assistenz', 'suid'))
		{
			$result =  $this->p->t('lehre','error_keineSchreibrechte');

			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}
		return success("berechtigt in überschreibender Funktion");
	}

}
