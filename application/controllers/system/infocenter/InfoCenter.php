<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class InfoCenter extends VileSci_Controller
{
	public function __construct()
    {
        parent::__construct();

        $this->load->library('WidgetLib');
    }

	/**
	 *
	 */
	public function index()
	{
		$listFiltersSent = array(
			'Sent 1' => 100,
			'Sent 2' => 200,
			'Sent 3' => 300
		);

		$listFiltersNotSent = array(
			'Not Sent 1' => 400,
			'Not Sent 2' => 500,
			'Not Sent 3' => 600
		);

		$this->load->view(
			'system/infocenter/infocenter.php',
			array(
				'listFiltersSent' => $listFiltersSent,
				'listFiltersNotSent' => $listFiltersNotSent
			)
		);
	}
}
