<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class CLI_Manager extends CLI_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		// Load helpers to upload files
		$this->load->helper('form');

		// Loads the extensions library
		$this->load->library('ExtensionsLib');
	}

	/**
	 * Installiert eine Extension.
	 * Es wird davon ausgegangen, dass die Extension ueber einen Fileupload hochgeladen wird.
	 * alternativ kann hier auch der Name und Filename uebergeben werden um die installation ueber
	 * die Commandline ohne Upload durchzufuehren.
	 * @param $extensioName string Name der Extension
	 * @param $filename Url Encoded Pfad zum tgz File der Extension
	 * @param $perform_sql boolean ob die SQL Befehle ausgeführt werden
	 */
	public function installExtension($extensionName, $filename)
	{
		$this->extensionslib->installExtension($extensionName, urldecode($filename), true);
	}

	/**
	 * Install an extension, same as installExtension but without running the SQL statements
	 */
	public function installExtensionNoSQL($extensionName, $filename)
	{
		$this->extensionslib->installExtension($extensionName, urldecode($filename), false);
	}
}

