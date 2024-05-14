<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Variable extends API_Controller
{
	/**
	 * Variable API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Variable' => 'basis/variable:rw'));
		// Load model VariableModel
		$this->load->model('system/variable_model', 'VariableModel');


	}

	/**
	 * @return void
	 */
	public function getVariable()
	{
		$uid = $this->get('uid');
		$name = $this->get('name');

		if (isset($uid) && isset($name))
		{
			$result = $this->VariableModel->load(array($uid, $name));

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postVariable()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['uid']) && isset($this->post()['name']))
			{
				$result = $this->VariableModel->update(array($this->post()['uid'], $this->post()['name']), $this->post());
			}
			else
			{
				$result = $this->VariableModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($variable = NULL)
	{
		return true;
	}
}
