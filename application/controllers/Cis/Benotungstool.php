<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Benotungstool extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => self::PERM_LOGGED
		]);

		$this->_ci =& get_instance();
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{

		
		// TODO: check if related CIS config is also loaded when being routed in Cis4 by vuerouter
		// TODO: check if new benotungstool should be configurable the exact same way?
		// TODO: move this to some config api?
		
		$viewData = array(
			'uid'=>getAuthUID(),
			'CIS_GESAMTNOTE_UEBERSCHREIBEN' => CIS_GESAMTNOTE_UEBERSCHREIBEN,
			'CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF' => CIS_GESAMTNOTE_PRUEFUNG_KOMMPRUEF,
			'CIS_GESAMTNOTE_PRUEFUNG_TERMIN3' => CIS_GESAMTNOTE_PRUEFUNG_TERMIN3,
			'CIS_GESAMTNOTE_PRUEFUNG_TERMIN2' => CIS_GESAMTNOTE_PRUEFUNG_TERMIN2,
			'CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE' => CIS_GESAMTNOTE_PRUEFUNG_MOODLE_LE_NOTE,
			'CIS_GESAMTNOTE_PUNKTE' => CIS_GESAMTNOTE_PUNKTE,
			'CIS_GESAMTNOTE_GEWICHTUNG' => CIS_GESAMTNOTE_GEWICHTUNG,
			'CIS_ANWESENHEITSLISTE_NOTENLISTE_ANZEIGEN' => CIS_ANWESENHEITSLISTE_NOTENLISTE_ANZEIGEN
		);

		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Benotungstool']);
	}
	
}