<?php

if(!defined('BASEPATH')) exit('No direct script access allowed');

class File extends APIv1_Controller
{
	/**
	 * Person API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model FileModel
		$this->load->model('file_model', 'FileModel');
		// Load set the uid of the model to let to check the permissions
		$this->FileModel->setUID($this->_getUID());
	}
	
	/**
	 * @return void
	 */
	public function postFile()
	{
		$result = $this->FileModel->saveFile($this->post());
		
		if($result === TRUE)
		{
			$httpstatus = REST_Controller::HTTP_OK;
			$payload = [
				'success' => true,
				'message' => 'File saved.'
			];
			$payload['data'] = $result;
		}
		else
		{
			$payload = [
				'success' => false,
				'message' => 'Could not save file.'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		$this->response($payload, $httpstatus);
	}
}