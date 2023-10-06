<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 3.2
 * @filesource
 *
 * Cronjob to be run for missed Abgabe Bachelor and Master Arbeiten
 *
 *  Actions:
 *  (1) missed endupload projektarbeit: grade projektarbeit with grade 7 (Nicht beurteilt)
 *  (2) copy projektarbeit
 *  (3) set Betreuungen to 0 hours
 *  (4) sancho Mail to Studiengang with Link
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

class ProjektarbeitJob extends JOB_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Load SanchoHelper
		$this->load->helper('hlp_sancho_helper');

		//Load Models
		$this->load->model('education/Betreuerart_model', 'BetreuerartModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
		$this->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');

		//Load Library
		$this->load->library('VorlageLib');
	}

	public function handleMissedAbgaben()
	{
		$nl = "\n";
		echo "----------------- Test Cronjob cis/cronjobs/handleMissedAbgabenBachelorMaster.php ------------------";
		echo $nl;

		//get all missed Projektarbeiten
		$result = $this->ProjektarbeitModel->getMissedProjektarbeiten();
/*		if(isError($result))
			return $this->logError(getError($result));

		if(!hasData($result))
			return $this->logInfo('End Job sendStglSammelmail: 0 Mails sent');
		*/

		$projektarbeiten = getData($result);
		var_dump($projektarbeiten);


	}



}
