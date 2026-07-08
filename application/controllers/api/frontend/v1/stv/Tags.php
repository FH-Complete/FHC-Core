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
			'deleteTag' => self::BERECHTIGUNG_KURZBZ
		]);

	$this->config->load('stv');
	}

	public function getTag($readonly_tags = null)
	{
		parent::getTag($this->config->item('stv_prestudent_tags'));
	}
	public function getTags($tags = null)
	{
		parent::getTags($this->config->item('stv_prestudent_tags'));
	}
	public function addTag($withZuordnung = true, $updatable_tags = null)
	{
		parent::addTag(true, $this->config->item('stv_prestudent_tags'));
	}
	public function updateTag($updatable_tags = null)
	{
		parent::updateTag($this->config->item('stv_prestudent_tags'));
	}
	public function deleteTag($withZuordnung = true, $updatable_tags = null)
	{
		parent::deleteTag(true, $this->config->item('stv_prestudent_tags'));
	}
	public function doneTag($updatable_tags = null)
	{
		parent::doneTag($this->config->item('stv_prestudent_tags'));
	}
}
