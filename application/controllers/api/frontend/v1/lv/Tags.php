<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Tags extends Tag_Controller
{
	const BERECHTIGUNG_KURZBZ = ['admin:rw', 'assistenz:rw'];

	public function __construct()
	{
		parent::__construct([
			'getTag' => self::BERECHTIGUNG_KURZBZ,
			'getTags' => self::BERECHTIGUNG_KURZBZ,
			'addTag' => self::BERECHTIGUNG_KURZBZ,
			'updateTag' => self::BERECHTIGUNG_KURZBZ,
			'doneTag' => self::BERECHTIGUNG_KURZBZ,
			'deleteTag' => self::BERECHTIGUNG_KURZBZ,
			'updateLehre' => self::BERECHTIGUNG_KURZBZ,
			'doneLehre' => self::BERECHTIGUNG_KURZBZ,
			'deleteLehre' => self::BERECHTIGUNG_KURZBZ,
		]);
	}
}