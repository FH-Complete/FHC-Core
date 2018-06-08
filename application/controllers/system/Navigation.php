<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Navigation extends Auth_Controller
{
	const SESSION_NAME = 'NAVIGATION_MENU';

	/**
	 * Constructor
	 */
	public function __construct()
    {
        parent::__construct(
			array(
				'menu' => 'basis/vilesci:r',
				'header' => 'basis/vilesci:r'
			)
		);

		$this->config->load('navigation');

		// Load session library
		$this->load->library('session');
		$this->load->library('ExtensionsLib');
	}

	/**
	 * This function creates the left Menu for each Page
	 * @param navigation_widget_called GET Parameter witch holds the currently called Page
	 * @return JSON object with the Menu Entries
	 */
	public function menu()
	{
		$navigation_widget_called = $this->input->get('navigation_widget_called');
		$json = array();

		if (isset($navigation_widget_called))
		{
			// Get Menu Entries of the Core
			$navigationMenuArray = $this->config->item('navigation_menu');
			$json = $this->wildcardsearch($navigationMenuArray, $navigation_widget_called);

			// Load Menu Entries of Extensions
			$extensions = $this->extensionslib->getInstalledExtensions();
			if(hasData($extensions))
			{
				$json_extension = array();
				foreach($extensions->retval as $ext)
				{
					$filename = APPPATH.'config/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$ext->name.'/navigation.php';
					if (file_exists($filename))
					{
						unset($config);
						include($filename);
						if(isset($config['navigation_menu']) && is_array($config['navigation_menu']))
						{
							$json_extension = array_merge_recursive($json_extension, $this->wildcardsearch($config['navigation_menu'], $navigation_widget_called));
						}
					}
				}
				// Merge Extension Menuentries with the Core Entries
				$json = array_merge_recursive($json, $json_extension);
			}

			// Load dynamic Menu Entries from Session
			if (isset($_SESSION['navigation_menu']))
			{
				$navigationMenuSessionArray = $_SESSION['navigation_menu'];

				if (isset($navigationMenuSessionArray) && is_array($navigationMenuSessionArray))
				{
					if (isset($navigationMenuSessionArray[$navigation_widget_called]))
					{
						$json = array_merge_recursive($json, $navigationMenuSessionArray[$navigation_widget_called]);
					}
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 * This function creates the Top Menu for each Page
	 * @param navigation_widget_called GET Parameter witch holds the currently called Page
	 * @return JSON object with the Menu Entries
	 */
	public function header()
	{
		$navigation_widget_called = $this->input->get('navigation_widget_called');
		$json = array();

		if (isset($navigation_widget_called))
		{
			// Load Header Entries of Core
			$navigationHeaderArray = $this->config->item('navigation_header');
			$json = $this->wildcardsearch($navigationHeaderArray, $navigation_widget_called);

			// Load Header Entries of Extensions
			$extensions = $this->extensionslib->getInstalledExtensions();
			if(hasData($extensions))
			{
				$json_extension = array();
				foreach($extensions->retval as $ext)
				{
					$filename = APPPATH.'config/'.ExtensionsLib::EXTENSIONS_DIR_NAME.'/'.$ext->name.'/navigation.php';
					if (file_exists($filename))
					{
						unset($config);
						include($filename);
						if(isset($config['navigation_header']) && is_array($config['navigation_header']))
						{
							$json_extension = array_merge_recursive($json_extension, $this->wildcardsearch($config['navigation_header'], $navigation_widget_called));
						}
					}
				}
				$json = array_merge_recursive($json, $json_extension);
			}

			// Load dynamic Header Entries from Session
			if (isset($_SESSION['navigation_header']))
			{
				$navigationHeaderSessionArray = $_SESSION['navigation_header'];

				if (isset($navigationHeaderSessionArray) && is_array($navigationHeaderSessionArray))
				{
					if (isset($navigationHeaderSessionArray[$navigation_widget_called]))
					{
						$jsontmp = $this->wildcardsearch($navigationHeaderSessionArray, $navigation_widget_called);
						$json = array_merge_recursive($json, $jsontmp);
					}
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	/**
	 * Searches a Menuentry. If there is no exact entry it searches for Wildcard Entries with a Star
	 * Example:
	 * Searching for /system/foo/index will Match the following Menuentries:
	 * 		/system/foo/index
	 *		/system/foo/*
	 *		/system/*
	 *		*
	 *
	 * @param $navigationArray Array to Search in.
	 * @param $navigation_widget_called Navigation to search for.
	 * @return Navigation Array if found, empty array otherwise
	 */
	private function wildcardsearch($navigationArray, $navigation_widget_called)
	{
		// Sort Navigation to have them in correct order
		krsort($navigationArray);

		// 100% match found
		if(isset($navigationArray[$navigation_widget_called]))
		{
			return $navigationArray[$navigation_widget_called];
		}
		else
		{
			foreach($navigationArray as $key=>$row)
			{
				// Search for * Entries
				if(mb_strpos($key, '*') === 0 || mb_strpos($key, '*') === mb_strlen($key) - 1)
				{
					// Take * Entry if Matches
					$search = mb_substr($key, 0, -1);
					if($search == '' || mb_strpos($navigation_widget_called, $search) === 0)
					{
						return $row;
					}
				}
			}
		}

		return array();
	}
}
