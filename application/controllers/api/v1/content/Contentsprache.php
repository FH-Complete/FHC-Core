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

class Contentsprache extends API_Controller
{
	/**
	 * Contentsprache API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Contentsprache' => 'basis/contentsprache:rw'));
		// Load model ContentspracheModel
		$this->load->model('content/contentsprache_model', 'ContentspracheModel');
	}

	/**
	 * @return void
	 */
	public function getContentsprache()
	{
		$contentspracheID = $this->get('contentsprache_id');

		if (isset($contentspracheID))
		{
			$result = $this->ContentspracheModel->load($contentspracheID);

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
	public function postContentsprache()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['contentsprache_id']))
			{
				$result = $this->ContentspracheModel->update($this->post()['contentsprache_id'], $this->post());
			}
			else
			{
				$result = $this->ContentspracheModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($contentsprache = NULL)
	{
		return true;
	}
}
