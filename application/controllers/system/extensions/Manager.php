<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Manager extends Auth_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'system/extensions:r',
				'toggleExtension' => 'system/extensions:rw',
				'delExtension' => 'system/extensions:rw',
				'uploadExtension' => 'system/extensions:rw'
			)
		);

		// Loads the form helper
		$this->load->helper('form');

		// Loads WidgetLib
		$this->load->library('WidgetLib');

		// Loads the extensions library
		$this->load->library('ExtensionsLib');

		$this->loadPhrases(
			array(
				'extensions',
				'table',
				'ui'
			)
		);
	}

	/**
	 *
	 */
	public function index()
	{
		$viewData = array(
			'extensions' => $this->extensionslib->getInstalledExtensions()
		);

		$this->load->view('system/extensions/manager.php', $viewData);
	}

	/**
	 *
	 */
	public function toggleExtension()
	{
		$extension_id = $this->input->post('extension_id');
		$enabled = $this->input->post('enabled');

		// Clean the parameter
		$enabled = $enabled == 'true' ? true : false;

		// Output the enable/disable of the extension
		$this->outputJsonSuccess(
			$this->extensionslib->toggleExtension($extension_id, $enabled)
		);
	}

	/**
	 *
	 */
	public function delExtension()
	{
		$extension_id = $this->input->post('extension_id');

		$delExtension = $this->extensionslib->delExtension($extension_id);

		$this->outputJsonSuccess($delExtension);
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
	public function uploadExtension()
	{
		$notPerformSql = $this->input->post('notPerformSql');

		// It converts the notPerformSql parameter from the checkbox value to a boolean one
		if ($notPerformSql == 'on') $notPerformSql = true;
		if ($notPerformSql !== true) $notPerformSql = false;

		$this->extensionslib->installExtension(null, null, !$notPerformSql);
	}
}
