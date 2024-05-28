<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class NotizPerson extends Notiz_Controller
{
	public function __construct()
	{
		parent::__construct([
			'isBerechtigt' => ['admin:r', 'assistenz:r'],
		]);
	}

	public function isBerechtigt($id, $typeId)
	{
		if($typeId != "person_id")
		{
			return $this->terminateWithError($this->p->t('ui', 'error_typeNotizIdIncorrect'), self::ERROR_TYPE_GENERAL);
		}

		//TODO define permission
		if (!$this->permissionlib->isBerechtigt('admin', 'suid') && !$this->permissionlib->isBerechtigt('assistenz', 'suid'))
		{
			$result =  $this->p->t('lehre', 'error_keineSchreibrechte');

			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		return $this->outputJsonSuccess(true);
	}

	public function loadDokumente()
	{
		$notiz_id = $this->input->post('notiz_id');

		$this->NotizModel->addSelect($this->NotizModel->escape(base_url('content/notizdokdownload.php?id=' . $notiz_id)) . ' AS preview');
		
		return parent::loadDokumente();
	}
}