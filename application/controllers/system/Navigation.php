<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Navigation extends FHC_Controller
{
	const SESSION_NAME = 'NAVIGATION_MENU';

	/**
	 *
	 */
	public function __construct()
    {
        parent::__construct();

		$this->config->load('navigation');

        // Load session library
        $this->load->library('session');
    }

	/**
	 *
	 */
	public function menu()
	{
		$navigation_widget_called = $this->input->get('navigation_widget_called');
		$json = array();

		if (isset($navigation_widget_called))
		{
			$navigationMenuArray = $this->config->item('navigation_menu');

			if (isset($navigationMenuArray) && is_array($navigationMenuArray))
			{
				if (isset($navigationMenuArray[$navigation_widget_called]))
				{
					$json = $navigationMenuArray[$navigation_widget_called];
				}
			}

			if (isset($_SESSION['navigation_menu']))
			{
				$navigationMenuSessionArray = $_SESSION['navigation_menu'];

				if (isset($navigationMenuSessionArray) && is_array($navigationMenuSessionArray))
				{
					if (isset($navigationMenuSessionArray[$navigation_widget_called]))
					{
						$json = array_merge($json, $navigationMenuSessionArray[$navigation_widget_called]);
					}
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 *
	 */
	public function header()
	{
		$navigation_widget_called = $this->input->get('navigation_widget_called');
		$json = array();

		if (isset($navigation_widget_called))
		{
			$navigationHeaderArray = $this->config->item('navigation_header');

			if (isset($navigationHeaderArray) && is_array($navigationHeaderArray))
			{
				if (isset($navigationHeaderArray[$navigation_widget_called]))
				{
					$json = $navigationHeaderArray[$navigation_widget_called];
				}
			}

			if (isset($_SESSION['navigation_header']))
			{
				$navigationHeaderSessionArray = $_SESSION['navigation_header'];

				if (isset($navigationHeaderSessionArray) && is_array($navigationHeaderSessionArray))
				{
					if (isset($navigationHeaderSessionArray[$navigation_widget_called]))
					{
						$json = array_merge($json, $navigationHeaderSessionArray[$navigation_widget_called]);
					}
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
