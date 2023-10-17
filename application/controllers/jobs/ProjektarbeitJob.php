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
		$arrayStg = array();
		$countMissedAbgaben = 0;
		$countTotal = 0;
		$countMails = 0;

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
				'punkte' => null,
				'beginn' => null,
				'ende' => null,
				'faktor' => $projektarbeit->faktor,
				'freigegeben' => $projektarbeit->freigegeben,
				'gesperrtbis' => $projektarbeit->gesperrtbis,
				'stundensatz' => $projektarbeit->stundensatz,
				'themenbereich' => $projektarbeit->themenbereich,
				'anmerkung' => $projektarbeit->anmerkung,
				'updateamum' => null,
				'updatevon' => null,
				'insertamum' => $now->format('c'),
				'insertvon' => 'Projektjob',
				'ext_id' => $projektarbeit->ext_id,
				'gesamtstunden' => null,
				'titel_english' => $projektarbeit->titel_english,
				'sprache' => $projektarbeit->sprache,
				'abgabedatum' => null,
				'kontrollschlagwoerter' => $projektarbeit->kontrollschlagwoerter,
				'schlagwoerter' => $projektarbeit->schlagwoerter,
				'schlagwoerter_en' => $projektarbeit->schlagwoerter_en,
				'abstract' => $projektarbeit->abstract,
				'abstract_en' => $projektarbeit->abstract_en,
				'final' => $projektarbeit->final,
			]);

			$this->db->order_by("projektarbeit_id", "desc");
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

				//Array Studiengänge
				if (!isset($arrayStg[$projekt->studiengang_kz]))
				{
					$result = $this->StudiengangModel->loadWhere([
						'studiengang_kz' => $projekt->studiengang_kz
					]);
					if (isError($result))
						$this->logError(getError($result));
					elseif (!hasData($result))
					{
						$this->logInfo('Kein Studiengang für' . $projekt->studiengang_kz . 'gefunden');
					}
					else
					{
						$studiengang = current(getData($result));
						$email = $studiengang->email;

						$arrayStg[$projekt->studiengang_kz] = array(
							'countMissedAbgaben' => $countMissedAbgaben,
							'email' => $email
						);
					}
				}
				$arrayStg[$projekt->studiengang_kz]['countMissedAbgaben'] = ($arrayStg[$projekt->studiengang_kz]['countMissedAbgaben'] + 1);

				//Mailarray
				$mailArray[] = 	array(
					'studiengang_kz' => $projekt->studiengang_kz,
					'projekt_id_alt' => $projekt->projektarbeit_id,
					'projekt_id_neu' => $projekt_id_copy,
					'student_uid' => $projekt->student_uid,
					'vorname' => $projekt->vorname,
					'nachname' => $projekt->nachname,
					'titel' => $projekt->titel);
			}

			// (3)Betreuungen kopieren
			$result = $this->ProjektbetreuerModel->loadWhere([
				'projektarbeit_id' => $projekt->projektarbeit_id
			]);
			if (isError($result)) {
				$this->logError(getError($result));
				continue;
			}
			elseif (!hasData($result))
			{
				$this->logInfo('Keine Betreuung für' . $projekt->projektarbeit_id . 'gefunden');
			}
			else
			{
				$betreuung = getData($result);

				//error handler, catching also warnings
				set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line, array $err_context) {
					throw new ErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
				}, E_WARNING);

				foreach ($betreuung as $bet) {
					$now = new Datetime();
					try {
						$this->ProjektbetreuerModel->insert([
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
							'zugangstoken' => null,
							'zugangstoken_gueltigbis' => null
						]);
					} catch (ErrorException $e) {
						$this->logError("Error Insert Betreuungen, check projekt_id: " . $projekt_id_copy);
						continue;
					}
				}
				restore_error_handler();
			}
		}
		array_multisort($mailArray);

		//(4) Sancho Mail
		foreach ($arrayStg as $stg_kz => $item)
		{
			$maildata = " <table class='table table-striped'> <thead>
							<tr>
							<th align=left>UID </th>
							<th align=left>Name</th>
							<th align=left>Projektarbeit</th>
							</tr>
							</thead>
							<tbody>";
			$email = $item['email'];
			$anzahlMissedAbgaben = $item['countMissedAbgaben'];

			foreach ($mailArray as $m)
			{
				if ($stg_kz == $m['studiengang_kz'])
				{
					$maildata .= "<tr>" .
						"<td>". $m['student_uid'] . "</td>".
						"<td>". trim($m['vorname'] . " " . $m['nachname']) ."</td>".
						"<td>".	$m['titel'] . "</td></tr>";
				}
			}
			$maildata .= "</tbody></table>";

			$betreff = 'Versäumte Abgabe(n) Projektarbeiten / Project Work(s) not uploaded in time';
			$data = [
				'anzahlMissedAbgaben' => $anzahlMissedAbgaben,
				'link' =>  APP_ROOT. '/vilesci/lehre/abgabe_assistenz_frameset.php?stg_kz=' . $stg_kz,
				'table' => $maildata
			];

			$countTotal = $countTotal + $anzahlMissedAbgaben;

			//send mail
			if (sendSanchoMail('Sancho_Mail_Stgl_MissedAbgaben', $data, $email, $betreff))
			{
				$countMails++;
			}
		}

		$this->logInfo($countTotal . ' projektarbeiten not uploaded in time, ' . $countMails . ' sent mails.');
		$this->logInfo('End Job Projektarbeit');
	}
}
