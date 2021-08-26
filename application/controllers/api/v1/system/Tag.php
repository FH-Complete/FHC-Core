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

class Tag extends API_Controller
{
	/**
	 * Tag API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Tag' => 'basis/tag:rw'));
		// Load model TagModel
		$this->load->model('system/tag_model', 'TagModel');


	}

	/**
	 * @return void
	 */
	public function getTag()
	{
		$tag = $this->get('tag');

		if (isset($tag))
		{
			$result = $this->TagModel->load($tag);

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
	public function postTag()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['tag']))
			{
				$result = $this->TagModel->update($this->post()['tag'], $this->post());
			}
			else
			{
				$result = $this->TagModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($tag = NULL)
	{
		return true;
	}
}
