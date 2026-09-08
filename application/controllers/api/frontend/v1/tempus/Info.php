<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Info extends FHCAPI_Controller
{

	private $_ci;

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getStudiengaenge' => 'lehre/lvplan:rw',
		]);

		$this->_ci =& get_instance();

		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->loadPhrases([
			'ui'
		]);

	}

	public function getStudiengaenge()
	{
		$this->_ci->StudiengangModel->addDistinct();
		$this->_ci->StudiengangModel->addSelect('studiengang_kz');
		$this->_ci->StudiengangModel->addSelect("CONCAT(kurzbzlang, ' (', UPPER(CONCAT(typ, kurzbz)), ') - ', tbl_studiengang.bezeichnung) AS name");

		$result = $this->getDataOrTerminateWithError($this->_ci->StudiengangModel->load());
		$this->terminateWithSuccess($result);
	}
}
