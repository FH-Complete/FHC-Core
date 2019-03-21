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

		// Load helpers to upload files
		$this->load->helper('form');

		// Loads the extensions library
		$this->load->library('ExtensionsLib');
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
		$toggleExtension = false;

		$extension_id = $this->input->post('extension_id');
		$enabled = $this->input->post('enabled');

		if ($enabled === 'true')
		{
			$toggleExtension = $this->extensionslib->enableExtension($extension_id);
		}
		else
		{
			$toggleExtension = $this->extensionslib->disableExtension($extension_id);
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($toggleExtension));
	}

	/**
	 *
	 */
	public function delExtension()
	{
		$delExtension = false;

		$extension_id = $this->input->post('extension_id');

		$delExtension = $this->extensionslib->delExtension($extension_id);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($delExtension));
	}

	/**
	 * Installiert eine Extension.
	 * Es wird davon ausgegangen, dass die Extension ueber einen Fileupload hochgeladen wird.
	 * alternativ kann hier auch der Name und Filename uebergeben werden um die installation ueber
	 * die Commandline ohne Upload durchzufuehren.
	 * @param $extensioName string Name der Extension
	 * @param $filename Url Encoded Pfad zum tgz File der Extension
	 */
	public function uploadExtension($extensionName = null, $filename = null)
	{
		$this->extensionslib->installExtension($extensionName, urldecode($filename));
	}
}
