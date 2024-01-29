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

class Scrumsprint extends API_Controller
{
	/**
	 * Scrumsprint API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Scrumsprint' => 'basis/scrumsprint:rw'));
		// Load model ScrumsprintModel
		$this->load->model('project/scrumsprint_model', 'ScrumsprintModel');


	}

	/**
	 * @return void
	 */
	public function getScrumsprint()
	{
		$scrumsprintID = $this->get('scrumsprint_id');

		if (isset($scrumsprintID))
		{
			$result = $this->ScrumsprintModel->load($scrumsprintID);

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
	public function postScrumsprint()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['scrumsprint_id']))
			{
				$result = $this->ScrumsprintModel->update($this->post()['scrumsprint_id'], $this->post());
			}
			else
			{
				$result = $this->ScrumsprintModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($scrumsprint = NULL)
	{
		return true;
	}
}
