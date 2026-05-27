<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 */
class ClassSchedule extends Auth_Controller
{
	private $_uid;  // uid of the logged user

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => array('lehre/unterrichtszeiten_gk:r'),
			)
		);

		$this->load->library('PermissionLib');
		$this->load->library('AuthLib');

		$this->loadPhrases(
			array(
				'ui',
				'global',
				'person',
				'abschlusspruefung',
				'password',
				'lehre'
			)
		);

		$this->_setAuthUID();
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods
	public function index()
	{
		return $this->load->view('lehre/class_schedule/index.php', [
			'permissions' => [
				'lehre/unterrichtszeiten_typ_r' => $this->permissionlib->isBerechtigt('lehre/unterrichtszeiten_typ', 's'),
				'lehre/unterrichtszeiten_typ_w' => $this->permissionlib->isBerechtigt('lehre/unterrichtszeiten_typ', 'suid'),
			],
		]);
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid) show_error('User authentification failed');
	}
}
