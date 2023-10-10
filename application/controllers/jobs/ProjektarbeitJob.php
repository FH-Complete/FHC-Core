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
		//$this->load->model('education/Betreuerart_model', 'BetreuerartModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
		$this->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		//Load Library
		$this->load->library('VorlageLib');
	}

	public function handleMissedAbgaben()
	{
		$mailArray = array();
		$countMissedAbgaben = 0;
		$countTotal = 0;
		$countMails = 0;
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
		if (isError($result))
			echo $nl . $result;

		if (!hasData($result)) {
			echo $nl . 'End Job Projektarbeit Update: 0 Mails sent';
			return;
		}

		$projektarbeiten = getData($result);
		//var_dump($projektarbeiten);

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
				echo $nl . "------" . $nl . " erfolgreiches update von projektarbeit_id " . $projekt->projektarbeit_id . " :" . $projekt->titel;

			//copy Projektarbeit
			$result = $this->ProjektarbeitModel->load($projekt->projektarbeit_id);
			if (isError($result)) {
				echo "error: " . getError($result);
				continue;
			}
			if (!hasData($result)) {
				echo $nl . 'Keine Projektarbeit für projektarbeit_id ' . $projekt->projektarbeit_id . 'gefunden';
				continue;
			}
			$projektarbeit = getData($result)[0];
			//var_dump($projektarbeit);

			$result = $this->ProjektarbeitModel->insert([
				'projekttyp_kurzbz' => $projektarbeit->projekttyp_kurzbz,
				'titel' => $projektarbeit->titel,
				'insertvon' => 'Projektjob',
				'note' => NULL,
				'lehreinheit_id' => $projektarbeit->lehreinheit_id,
				'student_uid' => $projektarbeit->student_uid,
				'firma_id' => $projektarbeit->firma_id,
				'punkte' => $projektarbeit->punkte, //TODO(manu) ebenfalls null?
				'beginn' => NULL,
				'ende' => NULL,
				'faktor' => $projektarbeit->faktor,
				'freigegeben' => $projektarbeit->freigegeben,
				'gesperrtbis' => $projektarbeit->gesperrtbis,
				'stundensatz' => $projektarbeit->stundensatz,
				'themenbereich' => $projektarbeit->themenbereich,
				'anmerkung' => $projektarbeit->anmerkung,
				'updateamum' => NULL,
				'updatevon' => NULL,
				'insertamum' => $projektarbeit->insertamum, //TODO(manu) besser aktueller timestamp?
				'ext_id' => $projektarbeit->ext_id,
				'gesamtstunden' => NULL, //TODO(manu) oder übernehmen?
				'titel_english' => $projektarbeit->titel_english,
				'sprache' => $projektarbeit->seitenanzahl,
				'abgabedatum' => $projektarbeit->abgabedatum,
				'kontrollschlagwoerter' => $projektarbeit->kontrollschlagwoerter,
				'schlagwoerter' => $projektarbeit->schlagwoerter,
				'schlagwoerter_en' => $projektarbeit->schlagwoerter_en,
				'abstract' => $projektarbeit->abstract,
				'abstract_en' => $projektarbeit->abstract_en,
				'final' => $projektarbeit->final,
			]);
			if (isError($result))
				echo "error: " . getError($result);

			$result = $this->ProjektarbeitModel->loadWhere([
				'student_uid' => $projektarbeit->student_uid,
				'insertvon' => 'Projektjob',
				'note' => NULL
			]);
			if (isError($result))
				//$this->logError(getError($result));
				echo "error: " . getError($result);
			elseif (!hasData($result)) {
				echo $nl . 'Keine neu angelegte projektarbeit_id für StudentId' . $projektarbeit->student_uid . 'gefunden';
			}
			else
			{
				$projektarbeit_copy = getData($result)[0];
				//var_dump($projektarbeit_copy);
				$projekt_id_copy = $projektarbeit_copy->projektarbeit_id;
				echo $nl . "Projektarbeit alt " . $projekt->projektarbeit_id . " Projektarbeit neu: " . $projekt_id_copy;
				echo $nl . "Studiengang_kz" . $projekt->studiengang_kz;

				//Mail array
				if (!isset ($mailArray[$projekt->studiengang_kz])) {
					$mailArray[$projekt->studiengang_kz] = $countMissedAbgaben;
				}
				$mailArray[$projekt->studiengang_kz] = $mailArray[$projekt->studiengang_kz] + 1;


			}
			//Betreuungen kopieren

			//get bestehende Betreuungen
			$result = $this->ProjektbetreuerModel->loadWhere([
				'projektarbeit_id' => $projekt->projektarbeit_id,
				//'betreuerart_kurzbz' => $projekt->betreuerart_kurzbz
			]);
			if (isError($result))
				//$this->logError(getError($result));
				echo "error: " . getError($result);
			elseif (!hasData($result)) {
				echo $nl . 'Keine Betreuung für' . $projekt->projektarbeit_id . 'gefunden';
			} else {
				$betreuung = getData($result);
				//var_dump($betreuung);

				foreach ($betreuung as $bet) {
					echo $nl . $bet->person_id . " P_ID ALT: " . $nl . $bet->projektarbeit_id . "P_ID NEU" . $projekt_id_copy . $nl . " Art: " . $bet->betreuerart_kurzbz;
					$result = $this->ProjektbetreuerModel->insert([
						'person_id' => $bet->person_id,
						'projektarbeit_id' => $projekt_id_copy,
						'note' => NULL,
						'faktor' => $bet->faktor,
						'name' => $bet->name,
						'punkte' => $bet->punkte,
						'stundensatz' => $bet->stundensatz,
						'updateamum' => $bet->updateamum,
						//TODO insertamum, updateam, updatevon ? Projektjob und aktueller Timestamp
						'updatevon' => $bet->updatevon,
						'insertamum' => $bet->insertamum,
						'insertvon' => 'Projektjob',
						'ext_id' => $bet->ext_id,
						'betreuerart_kurzbz' => $bet->betreuerart_kurzbz,
						'stunden' => NULL,
						'vertrag_id' => NULL,  //TODO oder besser vertrag_id
						'zugangstoken' => $bet->zugangstoken,
						'zugangstoken_gueltigbis' => $bet->zugangstoken_gueltigbis
					]);
					if (isError($result))
						echo "error: " . getError($result);
					else
						echo $nl . "neue Betreuung für person_id " . $bet->person_id . ' und projektarbeit_id ' . $projekt_id_copy . ' angelegt';

				}

				//var_dump($projektarbeit_copy);
				//$projekt_id_copy = $projektarbeit_copy->projektarbeit_id;
				//echo "Projektarbeit alt " . $projekt->projektarbeit_id . " Projektarbeit neu: " . $projekt_id_copy;
			}

		}

		//Sancho Mail
		var_dump($mailArray);


		echo $nl . "Arraytest: " . $nl;
		foreach ($mailArray as $key => $item){

			$result = $this->StudiengangModel->loadWhere([
				'studiengang_kz' => $key
			]);
			if (isError($result))
				//$this->logError(getError($result));
				echo "error: " . getError($result);
			elseif (!hasData($result)) {
				echo $nl . 'Kein Studiengang für' . $key . 'gefunden';
			}
			else
			{
				$studiengang = current(getData($result));
				//var_dump($studiengang);
				$email = $studiengang->email;
				$countTotal = $countTotal + $item;

				echo $nl . "Mail an Studiengang " . $key . " , Anzahl missed PAs: " . $item . " email: " . $studiengang->email. $nl;

				$countMails++;
			}
		}


		echo $nl . 'End Job Projektarbeit: ' . $countTotal . ' Missed Abgaben Total, ' . $countMails . ' verschickte Mails: ' . $nl;
	}



}
