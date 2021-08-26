<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Variables
 * Provides interface for managing user variables.
 */
class Variables extends Auth_Controller
{
	private $_uid;

	/**
	 * Variables constructor.
	 * Sets logged in user, loads models and libraries.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'setVar' => 'basis/variable:rw',
				'getVar' => 'basis/variable:rw',
				'changeStudiensemesterVar' => 'basis/variable:rw',
				'changeStudengangsTypVar' => 'basis/variable:rw'
			)
		);

		$this->load->model('system/variable_model', 'VariableModel');

		$this->_setAuthUID();

		$this->load->library('VariableLib', array('uid' => $this->_uid));
	}

	/**
	 * Sets a user variable based on received post parameters, outputs JSON response.
	 */
	public function setVar()
	{
		$name = $this->input->post('name');
		$wert = $this->input->post('wert');

		$result = $this->VariableModel->setVariable($this->_uid, $name, $wert);

		$this->outputJson($result);
	}

	/**
	 * gets a user variable based on received post parameter, outputs JSON response.
	 */
	public function getVar()
	{
		$name = $this->input->get('name');
		$typ = $this->input->get('typ');

		$this->outputJson($this->VariableModel->getVariables($this->_uid, array($name, $typ)));
	}

	/**
	 * Changes a user variable containing a Studiensemester based on received post parameters, outputs JSON response.
	 */
	public function changeStudiensemesterVar()
	{
		$name = $this->input->post('name');
		$change = $this->input->post('change');

		$result = $this->variablelib->changeStudiensemesterVar($this->_uid, $name, $change);

		$this->outputJson($result);
	}

	public function changeStudengangsTypVar()
	{
		$name = $this->input->post('name');
        $change = $this->input->post('change');

        $result = $this->variablelib->changeStudengangsTypVar($this->_uid, $name, $change);
		$this->outputJson($result);
	}

	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid) show_error('User authentification failed');
	}
}
