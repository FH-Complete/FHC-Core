<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Cms extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct(['index' => 'basis/cms:r']);
		$this->load->library('PermissionLib');
		$this->config->load('cms');
		$this->loadPhrases(['global', 'ui', 'cms']);
	}

	public function index()
	{
		// decide whether the click statistics tab is shown at all.
		$this->load->view('Cms.php', [
			'clickstats' => $this->config->item('clickstats_enabled')
				&& $this->permissionlib->isBerechtigt('admin')
		]);
	}
}
