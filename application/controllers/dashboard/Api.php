<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Api extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct(
			array(
				'index'		=> 'dashboard/admin:rw',
				'getNews'	=> 'dashboard/benutzer:r',
                'getAmpeln'	=> 'dashboard/benutzer:r',
			)
		);

		$this->load->library('AuthLib', null, 'AuthLib');

		$this->_setAuthUID();
	}

	public function index()
	{
		echo 'Dashboard API Controller';
	}

	/**
	 * Get News.
	 */
	public function getNews()
	{
		$limit =  $this->input->get('limit');

		$this->load->model('content/News_model', 'NewsModel');

		$result = $this->NewsModel->getAll($limit);

		if (hasData($result))
		{
			$this->outputJson(getData($result), REST_Controller::HTTP_OK);
		}
		else
		{
			$this->terminateWithJsonError('fehler entdeckt');
		}
	}


	/**
	 * Get Ampeln.
	 */
	public function getAmpeln()
	{

		$this->load->model('content/Ampel_model', 'AmpelModel');
		$result = $this->AmpelModel->getByUser($this->_uid);

		if (hasData($result))
		{
			$this->outputJson(getData($result), REST_Controller::HTTP_OK);
		}
		else
		{
			$this->terminateWithJsonError('fehler entdeckt');
		}
	}

	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid) show_error('User authentification failed');
	}
}
