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

class Content extends API_Controller
{
	/**
	 * Content API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Content' => 'basis/content:rw'));
		// Load model ContentModel
		$this->load->model('content/content_model', 'ContentModel');
	}

	/**
	 * @return void
	 */
	public function getContent()
	{
		$contentID = $this->get('content_id');

		if (isset($contentID))
		{
			$result = $this->ContentModel->load($contentID);

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
	public function postContent()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['content_id']))
			{
				$result = $this->ContentModel->update($this->post()['content_id'], $this->post());
			}
			else
			{
				$result = $this->ContentModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($content = NULL)
	{
		return true;
	}
}
