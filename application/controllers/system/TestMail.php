<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * NOTE: extends the FHC_Controller instead of the Auth_Controller because it is NOT neeaded to checks permissions to logout
 */
class TestMail extends Auth_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(array(
			'index' => 'admin:rw'
		));

		$this->load->helper('hlp_sancho_helper');

		$this->load->model('person/Person_model', 'PersonModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Logout the current logged user
	 */
	public function index()
	{
		$dbModel = new DB_Model();

		$dataResult = $dbModel->execReadOnlyQuery('
			SELECT b.uid
			  FROM public.tbl_benutzer b
			 WHERE b.aktiv = TRUE
		      ORDER BY RANDOM()
			 LIMIT 10
		');

		echo date('H:i:s');
		echo "\n";

		foreach (getData($dataResult) as $data)
		{
			echo $data->uid;
			echo "\n";

			$mailSent = sendSanchoMail(
				'AnrechnungAntragStellen',
				array(),
				$data->uid . '@' . DOMAIN,
				'This is a mail test. Sorry for bothering you!'
			);
		}

		echo date('H:i:s');
		echo "\n";
	}
}

