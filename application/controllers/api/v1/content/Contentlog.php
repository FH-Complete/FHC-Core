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

class Contentlog extends API_Controller
{
	/**
	 * Contentlog API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Contentlog' => 'basis/contentlog:rw'));
		// Load model ContentlogModel
		$this->load->model('content/contentlog_model', 'ContentlogModel');
	}

	/**
	 * @return void
	 */
	public function getContentlog()
	{
		$contentlogID = $this->get('contentlog_id');

		if (isset($contentlogID))
		{
			$result = $this->ContentlogModel->load($contentlogID);

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
	public function postContentlog()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['contentlog_id']))
			{
				$result = $this->ContentlogModel->update($this->post()['contentlog_id'], $this->post());
			}
			else
			{
				$result = $this->ContentlogModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($contentlog = NULL)
	{
		return true;
	}
}
