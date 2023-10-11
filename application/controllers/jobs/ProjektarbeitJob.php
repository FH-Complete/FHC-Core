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
 * Cronjob to be run for Bachelor and Master Arbeiten failed to be uploaded in time
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

		// Config
		$this->load->config('projektarbeit');

		// Load SanchoHelper
		$this->load->helper('hlp_sancho_helper');

		//Load Models
		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$this->load->model('education/Projektbetreuer_model', 'ProjektbetreuerModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
	}

	public function handleProjektarbeitenNotUploadedInTime()
	{
		$startDate = $this->config->item('projektarbeitjob_start');
		if ($startDate)
			$startDate = new DateTime($startDate);

		$mailArray = array();
		$countMissedAbgaben = 0;
		$countTotal = 0;
		$countMails = 0;
		$nl = "\n";

		$this->logInfo('Start Job Projektarbeit');

		//get all Projektarbeiten not Uploaded in Time
		$result = $this->ProjektarbeitModel->getAllProjektarbeitenNotUploadedInTime($startDate->format('c'));
		if (isError($result))
			return $this->logError(getError($result));
		if (!hasData($result))
			return $this->logInfo('End Job Projektarbeit Update: 0 Mails sent');

		$projektarbeiten = getData($result);

		// (1) note auf 7 setzen
		foreach ($projektarbeiten as $projekt)
		{
			$this->db->where('projektarbeit_id', $projekt->projektarbeit_id);

			$result = $this->ProjektarbeitModel->update([
				'projektarbeit_id' => $projekt->projektarbeit_id
			], [
				'note' => 7
			]);

			if (isError($result))
				$this->logError(getError($result));

			// (2) copy Projektarbeit
			$result = $this->ProjektarbeitModel->load($projekt->projektarbeit_id);
			if (isError($result)) {
				$this->logError(getError($result));
				continue;
			}
			if (!hasData($result)) {
				$this->logInfo('Keine Projektarbeit für projektarbeit_id ' .  $projekt->projektarbeit_id . 'gefunden');
				continue;
			}
			$projektarbeit = current(getData($result));

			$now = new Datetime();

			$this->ProjektarbeitModel->insert([
				'projekttyp_kurzbz' => $projektarbeit->projekttyp_kurzbz,
				'titel' => $projektarbeit->titel,
				'note' => null,
				'lehreinheit_id' => $projektarbeit->lehreinheit_id,
				'student_uid' => $projektarbeit->student_uid,
				'firma_id' => $projektarbeit->firma_id,
				'punkte' => null, //TODO(manu) oder $projektarbeit->punkte
				'beginn' => null,
				'ende' => null,
				'faktor' => $projektarbeit->faktor,
				'freigegeben' => $projektarbeit->freigegeben, //TODO(manu) besser FALSE?
				'gesperrtbis' => $projektarbeit->gesperrtbis,
				'stundensatz' => $projektarbeit->stundensatz,
				'themenbereich' => $projektarbeit->themenbereich,
				'anmerkung' => $projektarbeit->anmerkung,
				'updateamum' => null,
				'updatevon' => null,
				'insertamum' => $now->format('c'),
				'insertvon' => 'Projektjob',
				'ext_id' => $projektarbeit->ext_id,
				'gesamtstunden' => null, //TODO(manu) oder $projektarbeit->gesamtstunden?
				'titel_english' => $projektarbeit->titel_english,
				'sprache' => $projektarbeit->sprache,
				'abgabedatum' => null, //TODO(manu) oder $projektarbeit->abgabedatum
				'kontrollschlagwoerter' => $projektarbeit->kontrollschlagwoerter,
				'schlagwoerter' => $projektarbeit->schlagwoerter,
				'schlagwoerter_en' => $projektarbeit->schlagwoerter_en,
				'abstract' => $projektarbeit->abstract,
				'abstract_en' => $projektarbeit->abstract_en,
				'final' => $projektarbeit->final,
			]);

			$this->db->order_by("projektarbeit_id", "desc"); //TODO(manu) Cronjob soll weiterlaufen, continue?
			$result = $this->ProjektarbeitModel->loadWhere([
				'student_uid' => $projektarbeit->student_uid,
				'insertvon' => 'Projektjob',
				'note' => null
			]);
			if (isError($result))
			{
				$this->logError(getError($result));
				continue;
			}
			elseif (!hasData($result))
			{
				$this->logInfo('Keine neu angelegte projektarbeit_id für StudentId' . $projektarbeit->student_uid . 'gefunden');
				continue;
			}
			else
			{
				$projektarbeit_copy = current(getData($result));
				$projekt_id_copy = $projektarbeit_copy->projektarbeit_id;

				//Start Mailarray
				if (!isset($mailArray[$projekt->studiengang_kz]))
				{
					$mailArray[$projekt->studiengang_kz] = $countMissedAbgaben;
				}
				$mailArray[$projekt->studiengang_kz] = $mailArray[$projekt->studiengang_kz] + 1;
			}

			// (3)Betreuungen kopieren
			$result = $this->ProjektbetreuerModel->loadWhere([
				'projektarbeit_id' => $projekt->projektarbeit_id
			]);
			if (isError($result))
				$this->logError(getError($result));

			elseif (!hasData($result))
			{
				$this->logInfo('Keine Betreuung für' . $projekt->projektarbeit_id . 'gefunden');
			}
			else
			{
				$betreuung = getData($result);

				foreach ($betreuung as $bet) {
					$now = new Datetime();
					$result = $this->ProjektbetreuerModel->insert([
						'person_id' => $bet->person_id,
						'projektarbeit_id' => $projekt_id_copy,
						'note' => null,
						'faktor' => $bet->faktor,
						'name' => $bet->name,
						'punkte' => $bet->punkte,
						'stundensatz' => $bet->stundensatz,
						'updateamum' => null,
						'updatevon' => null,
						'insertamum' => $now->format('c'),
						'insertvon' => 'Projektjob',
						'ext_id' => $bet->ext_id,
						'betreuerart_kurzbz' => $bet->betreuerart_kurzbz,
						'stunden' => null,
						'vertrag_id' => null,
						'zugangstoken' => null, //TODO(manu) sonst insertfehler DB: 1 datensatz 34195
						'zugangstoken_gueltigbis' => null //TODO analog zu token
					]);
					if (isError($result))
					{
						$this->logError(getError($result));
					}
				//	else
				//		echo $nl . "neue Betreuung für person_id " . $bet->person_id . ' und projektarbeit_id ' . $projekt_id_copy . ' angelegt';
				}
			}
		}

		//(4)Sancho Mail
		foreach ($mailArray as $stg_kz => $anzahlMissedAbgaben)
		{
			$result = $this->StudiengangModel->loadWhere([
				'studiengang_kz' => $stg_kz
			]);
			if (isError($result))
				$this->logError(getError($result));
			elseif (!hasData($result))
			{
				$this->logInfo('Kein Studiengang für' . $stg_kz . 'gefunden');
			}
			else
			{
				$studiengang = current(getData($result));
				$email = $studiengang->email;
				$betreff = 'Versäumte Abgabe(n) Projektarbeiten / Project Work(s) not uploaded in time';

				//TODO(manu) link basisurl?
				$data = [
					'anzahlMissedAbgaben' => $anzahlMissedAbgaben,
					'link' => 'https://vilesci.technikum-wien.at/vilesci/lehre/abgabe_assistenz_frameset.php?stg_kz=' . $stg_kz
				];

				$countTotal = $countTotal + $anzahlMissedAbgaben;

				//send mail
				if (sendSanchoMail('Sancho_Mail_Stgl_MissedAbgaben', $data, $email, $betreff)) {
					//echo $nl . "Mail an Studiengang " . $stg_kz . " , Anzahl missed PAs: " . $anzahlMissedAbgaben . " email: " . $studiengang->email . $nl;
					$countMails++;
				}
			}
		}
		$this->logInfo($countTotal . ' projektarbeiten not uploaded in time, ' . $countMails . ' sent mails.');
		$this->logInfo('End Job Projektarbeit');
		echo $nl . 'End Job Projektarbeit: ' . $countTotal . ' projektarbeiten not uploaded in time, ' . $countMails . ' sent mails. ' . $nl;
	}
}
