<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the FilterCmptLib (back-end)
 * Provides data to the ajax get calls about the filter component
 * Listens to ajax post calls to change the filter data
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 * NOTE: extends the FHC_Controller instead of the Auth_Controller because the FilterCmpt has its
 * 	own permissions check
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
		$result = $this->ContentModel->getMenu(6739, get_uid());
		$menu = getData($result) ?? (object)['childs' => []];

		$this->outputJsonSuccess($menu);
	}

}
