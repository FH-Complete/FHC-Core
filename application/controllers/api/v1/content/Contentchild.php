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

class Contentchild extends API_Controller
{
	/**
	 * Contentchild API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Contentchild' => 'basis/contentchild:rw'));
		// Load model ContentchildModel
		$this->load->model('content/contentchild_model', 'ContentchildModel');
	}

	/**
	 * @return void
	 */
	public function getContentchild()
	{
		$contentchildID = $this->get('contentchild_id');

		if (isset($contentchildID))
		{
			$result = $this->ContentchildModel->load($contentchildID);

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
	public function postContentchild()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['contentchild_id']))
			{
				$result = $this->ContentchildModel->update($this->post()['contentchild_id'], $this->post());
			}
			else
			{
				$result = $this->ContentchildModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($contentchild = NULL)
	{
		return true;
	}
}
