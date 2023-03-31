<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class CisVue extends Auth_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'Menu' => 'user:r'
		]);

		// Loads authentication library and starts authentication
		$this->load->library('AuthLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 */
	public function Menu()
	{
		$this->load->model('content/Content_model', 'ContentModel');
		$result = $this->ContentModel->getMenu(defined('CIS4_MENU_ENTRY') ? CIS4_MENU_ENTRY : null, get_uid());
		$menu = getData($result) ?? (object)['childs' => []];

		$this->outputJsonSuccess($menu);
	}

}
