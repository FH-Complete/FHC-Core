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
					return $this->logInfo('End Job Projektarbeit Update: 0 Mails sent');
				*/
		if(isError($result))
			echo $nl . $result;

		if(!hasData($result))
			echo $nl . 'End Job Projektarbeit Update: 0 Mails sent';


		$projektarbeiten = getData($result);
		var_dump($projektarbeiten);

		//note auf 7 setzen
		foreach ($projektarbeiten as $projekt) {

			$this->db->where('projektarbeit_id', $projekt->projektarbeit_id);

			$result = $this->ProjektarbeitModel->update([
				'projektarbeit_id' => $projekt->projektarbeit_id
			], [
				'note' => 7
			]);

			if (isError($result))
				echo "error: " . getError($result);
			else
				echo $nl . " erfolgreiches update von projektarbeit_id " . $projekt->projektarbeit_id;

			//copy Projektarbeit
			$result = $this->ProjektarbeitModel->load($projekt->projektarbeit_id);
			if (isError($result))
			{
				echo "error: " . getError($result);
				continue;
			}
			if(!hasData($result))
			{
				echo $nl . 'Keine Projektarbeit für projektarbeit_id ' . $projekt->projektarbeit_id . 'gefunden';
				continue;
			}
			$projektarbeit = getData($result)[0];
			var_dump($projektarbeit);

			$result = $this->ProjektarbeitModel->insert([
				'projekttyp_kurzbz' => $projektarbeit->projekttyp_kurzbz,
				'titel' => $projektarbeit->titel,
				'insertvon' => 'Projektjob',
				'note' => NULL,
				'lehreinheit_id' => $projektarbeit->lehreinheit_id,
				'student_uid' => $projektarbeit->student_uid

			]);
			if (isError($result))
				echo "error: " . getError($result);

			//Betreuungen kopieren

			//get bestehende Betreuungen
			$result = $this->ProjektbetreuerModel->loadWhere([
				'projektarbeit_id' => $projekt->projektarbeit_id,
				//'betreuerart_kurzbz' => $projekt->betreuerart_kurzbz
			]);
			if (isError($result))
				//$this->logError(getError($result));
				echo "error: " . getError($result);
			elseif (!hasData($result))
			{
				echo $nl . 'Keine Projektarbeit für' . $projekt->projektarbeit_id . 'gefunden';
			}
			else
			{
				$betreuung = getData($result);
				var_dump($betreuung);

				foreach($betreuung as $bet)
				{
					echo $nl . $bet->person_id . " " . $bet->projektarbeit_id . " Art: " . $bet->betreuerart_kurzbz;
				}

				//var_dump($projektarbeit_copy);
				//$projekt_id_copy = $projektarbeit_copy->projektarbeit_id;
				//echo "Projektarbeit alt " . $projekt->projektarbeit_id . " Projektarbeit neu: " . $projekt_id_copy;
			}


			$result = $this->ProjektarbeitModel->loadWhere([
				'student_uid' => $projektarbeit->student_uid,
				'insertvon' => 'Projektjob',
				'note' => NULL
			]);
			if (isError($result))
				//$this->logError(getError($result));
				echo "error: " . getError($result);
			elseif (!hasData($result))
			{
				echo $nl . 'Keine neu angelegte projektarbeit_id für StudentId' . $projektarbeit->student_uid . 'gefunden';
			}
			else
			{
				$projektarbeit_copy = getData($result)[0];
				//var_dump($projektarbeit_copy);
				$projekt_id_copy = $projektarbeit_copy->projektarbeit_id;
				echo $nl . "Projektarbeit alt " . $projekt->projektarbeit_id . " Projektarbeit neu: " . $projekt_id_copy;
			}




		}


	}



}
