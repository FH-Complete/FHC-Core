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

class Filter extends API_Controller
{
	/**
	 * Filter API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Filter' => 'basis/filter:rw'));
		// Load model FilterModel
		$this->load->model('system/filter_model', 'FilterModel');


	}

	/**
	 * @return void
	 */
	public function getFilter()
	{
		$filterID = $this->get('filter_id');

		if (isset($filterID))
		{
			$result = $this->FilterModel->load($filterID);

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
	public function postFilter()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['filter_id']))
			{
				$result = $this->FilterModel->update($this->post()['filter_id'], $this->post());
			}
			else
			{
				$result = $this->FilterModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($filter = NULL)
	{
		return true;
	}
}
