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
	}
}