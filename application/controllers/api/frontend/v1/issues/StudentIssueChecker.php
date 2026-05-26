<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH.'/controllers/api/frontend/v1/issues/IssueChecker.php';

class StudentIssueChecker extends IssueChecker
{
	protected $_apps = array(
		'core',
		'dvuh',
		'bis'
	);

	//protected $_fehlercodes = array(
		//~ 'CORE_AA_0001'
	//);
}
