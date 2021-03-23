<?php

class ZGVUeberpruefung extends Auth_Controller
{
	private $_uid;  // uid of the logged user

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index' => 'lehre/zgvpruefung:r',
			)
		);

		$this->load->library('WidgetLib');
		$this->setControllerId(); // sets the controller id
	}

	public function index()
	{

		try{
			$this->load->view('system/infocenter/infocenterZgvUeberpruefung.php');
		}catch(Exception $e)
		{
			var_dump($e);
		}
	}
}