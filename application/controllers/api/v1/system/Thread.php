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

class Thread extends APIv1_Controller
{
	/**
	 * Thread API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model ThreadModel
		$this->load->model('system/thread_model', 'ThreadModel');
		
		
	}

	/**
	 * @return void
	 */
	public function getThread()
	{
		$threadID = $this->get('thread_id');
		
		if (isset($threadID))
		{
			$result = $this->ThreadModel->load($threadID);
			
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
	public function postThread()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['thread_id']))
			{
				$result = $this->ThreadModel->update($this->post()['thread_id'], $this->post());
			}
			else
			{
				$result = $this->ThreadModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($thread = NULL)
	{
		return true;
	}
}