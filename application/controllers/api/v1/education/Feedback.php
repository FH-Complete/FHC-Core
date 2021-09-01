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

class Feedback extends API_Controller
{
	/**
	 * Feedback API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Feedback' => 'basis/feedback:rw'));
		// Load model FeedbackModel
		$this->load->model('education/Feedback_model', 'FeedbackModel');
	}

	/**
	 * @return void
	 */
	public function getFeedback()
	{
		$feedback_id = $this->get('feedback_id');

		if (isset($feedback_id))
		{
			$result = $this->FeedbackModel->load($feedback_id);

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
	public function postFeedback()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['feedback_id']))
			{
				$result = $this->FeedbackModel->update($this->post()['feedback_id'], $this->post());
			}
			else
			{
				$result = $this->FeedbackModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($feedback = NULL)
	{
		return true;
	}
}
