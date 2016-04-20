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

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Course extends REST_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('studies/course_model', 'CourseModel');
		// Load set the addonID of the model to let to check the permissions
		$this->CourseModel->setAddonID($this->_getAddonID());
	}
	
	public function getEnabledCourses()
	{
		$result = $this->CourseModel->getEnabledCourses();
		
		if(!is_null($result) && $result->num_rows() > 0)
		{
			$payload = [
				'success'	=>	TRUE,
				'message'	=>	'Courses found',
				'data'		=>	$result->result()[0]
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		else
		{
			$payload = [
				'success'	=>	FALSE,
				'message'	=>	'Person not found'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		
		$this->response($payload, $httpstatus);
	}
}