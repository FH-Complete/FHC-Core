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
 *  (1) missed endupload projektarbeit: grade projektarbeit with grade 5 (Nicht genügend)
 *  (2) copy projektarbeit if max anzahl projektarbeiten nicht erreicht
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
		$countNotCopied = 0;
		$countTotal = 0;
		$countMails = 0;
		$projektarbeiten = [];

		$this->logInfo('Start Job Projektarbeit');
		//print_r( PHP_EOL . "Start Job Projektarbeit" . PHP_EOL);

		//get all Projektarbeiten not Uploaded in Time
		$resultNotUploaded = $this->ProjektarbeitModel->getAllProjektarbeitenNotUploadedInTime($startDate->format('c'));

		if (isError($resultNotUploaded) && $resultNotUploaded != [])
		{
			return $this->logError(getError($resultNotUploaded));
		}

		$projektarbeitenNotUploaded = getData($resultNotUploaded);
		if($projektarbeitenNotUploaded){
			foreach ($projektarbeitenNotUploaded as $projectNotUploaded) {
				$projectNotUploaded->reason = 'missedUpload';
			}
		}

		//get all negative Projektarbeiten (5) of student_uid with no open (null) or positive marks
		$resultNegative = $this->ProjektarbeitModel->getAllProjektarbeitenNegative($startDate->format('c'));

		if (isError($resultNegative) && $resultNegative != [])
		{
			return $this->logError(getError($resultNegative));
		}

		$projektarbeitenNegative = getData($resultNegative) ?: [];
		foreach ($projektarbeitenNegative as $projectNegative)
			$projectNegative->reason = 'negative';

		if (!hasData($resultNotUploaded) && !hasData($resultNegative
			|| $resultNotUploaded) == [] && $resultNegative == [])
			return print_r('End Job Projektarbeit Update: 0 Mails sent');
		//	return $this->logInfo('End Job Projektarbeit Update: 0 Mails sent');

		if (is_array($projektarbeitenNotUploaded) && is_array($projektarbeitenNegative))
		{
			$projektarbeiten = array_merge($projektarbeitenNotUploaded, $projektarbeitenNegative);
		}

		// (1) set mark to 7
		foreach ($projektarbeiten as $projekt)
		{
			$this->db->where('projektarbeit_id', $projekt->projektarbeit_id);

			if (!$projekt->note)
			{
				$result = $this->ProjektarbeitModel->update([
					'projektarbeit_id' => $projekt->projektarbeit_id
				], [
					'note' => 7
				]);
				if (isError($result))
				{
					$this->logError(getError($result));
				}
			}

			// (2) copy Projektarbeit
			//no more copying if count bakk >= 6 or count_diplom >= 3

			$end_of_copy_bachelor = $this->config->item('projektarbeitjob_finishCopy_bachelor') ? $this->config->item('projektarbeitjob_finishCopy_bachelor') : 6;
			$end_of_copy_master = $this->config->item('projektarbeitjob_finishCopy_diplom') ? $this->config->item('projektarbeitjob_finishCopy_diplom') : 3;

			$maxCountReached = $this->ProjektarbeitModel->checkifCountMaxProjektarbeiten(
				$projekt->student_uid,
				$end_of_copy_bachelor,
				$end_of_copy_master
			);
			if ($maxCountReached)
			{
				$countNotCopied++;
				$result = $this->StudiengangModel->loadWhere([
					'studiengang_kz' => $projekt->studiengang_kz
				]);
				if (isError($result))
				{
					$this->logError(getError($result));
				}

				if (!hasData($result))
				{
					//print_r('No Studiengang for studiengang_kz ' . $projekt->studiengang_kz . ' found');

					$this->logInfo('No Studiengang for studiengang_kz ' . $projekt->studiengang_kz . ' found');
				}
				$studiengang = current(getData($result));
				$email = $studiengang->email;

				$arrayStg[$projekt->studiengang_kz] = array(
					'countNotCopied' => $countNotCopied,
					'email' => $email,
					'countMissedAbgaben' => $countMissedAbgaben,
				);
/*				print_r("Max count of enduploads reached: " . $projekt->student_uid . ",id:" . $projekt->projektarbeit_id . " " . $projekt->studiengang_kz . PHP_EOL);
				print_r("Count of not copied: " . $countNotCopied . PHP_EOL);*/
				continue;
			}

			$result2 = $this->ProjektarbeitModel->load($projekt->projektarbeit_id);
			if (isError($result2))
			{
				$this->logError(getError($result2));
				continue;
			}
			if (!hasData($result2))
			{
				//print_r('No Projektarbeit found for projektarbeit_id ' .  $projekt->projektarbeit_id);
				$this->logInfo('No Projektarbeit found for projektarbeit_id ' .  $projekt->projektarbeit_id);
				continue;
			}
			$projektarbeit = current(getData($result2));

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
				//print_r('No projektarbeit_id for studentId ' . $projektarbeit->student_uid . ' found');
				$this->logInfo('No projektarbeit_id for studentId ' . $projektarbeit->student_uid . ' found');
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
					{
						$this->logError(getError($result));
					}

					if (!hasData($result))
					{
						//print_r('No Studiengang for studiengang_kz ' . $projekt->studiengang_kz . ' found');

						$this->logInfo('No Studiengang for studiengang_kz ' . $projekt->studiengang_kz . ' found');
					}
					$studiengang = current(getData($result));
					$email = $studiengang->email;

					$arrayStg[$projekt->studiengang_kz] = array(
						'countMissedAbgaben' => $countMissedAbgaben++,
						'email' => $email,
						'countNotCopied' => $countNotCopied,
					);
				}
				else {
					$arrayStg[$projekt->studiengang_kz]['countMissedAbgaben'] = $countMissedAbgaben++;
				}

				//Mailarray
				$reason = $projekt->reason ? $projekt->reason : null;

				$mailArray[] = 	array(
					'studiengang_kz' => $projekt->studiengang_kz,
					'projekt_id_alt' => $projekt->projektarbeit_id,
					'projekt_id_neu' => $projekt_id_copy,
					'student_uid' => $projekt->student_uid,
					'vorname' => $projekt->vorname,
					'nachname' => $projekt->nachname,
					'titel' => $projekt->titel,
					'reason' => $reason
					);
			}

			// (3) copy Betreuungen
			$result = $this->ProjektbetreuerModel->loadWhere([
				'projektarbeit_id' => $projekt->projektarbeit_id
			]);
			if (isError($result)) {
				$this->logError(getError($result));
				continue;
			}
			elseif (!hasData($result))
			{
				//print_r('No Betreuung found for projektarbeit_id ' . $projekt->projektarbeit_id);

				$this->logInfo('No Betreuung found for projektarbeit_id ' . $projekt->projektarbeit_id);
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
					}
					catch (ErrorException $e)
					{
						$this->logError("Error Insert Betreuungen, check projekt_id: " . $projekt_id_copy);
						continue;
					}
				}
				restore_error_handler();
			}
		}
		array_multisort($mailArray);

		// (4) Sancho Mail
		foreach ($arrayStg as $stg_kz => $item)
		{
			$maildata = " <table class='table table-striped'> <thead>
							<tr>
							<th align=left>UID </th>
							<th align=left>Name</th>
							<th align=left>Projektarbeit / Title</th>
							<th align=left></th>
							</tr>
							</thead>
							<tbody>";
			$email = $item['email'];

			//TODO(Manu) Total counts
			//change to counts / STG or keep mailtext countfree
			$anzahlMissedAbgaben = $item['countMissedAbgaben'];
			$anzahlNichtKopierteProjektarbeiten = $item['countNotCopied'];

			foreach ($mailArray as $m)
			{
				if ($stg_kz == $m['studiengang_kz'])
				{
					$maildata .= "<tr>" .
						"<td>". $m['student_uid'] . "</td>".
						"<td>". trim($m['vorname'] . " " . $m['nachname']) ."</td>".
						"<td>". $m['titel'] . "</td>".
						"<td>[".	$m['reason'] . "]</td></tr>";
				}
			}
			$maildata .= "</tbody></table>";

			$betreff = 'Kopierte Projektarbeiten / Project Work(s) copied';
			$data = [
/*				'anzahlMissedAbgaben' => $anzahlMissedAbgaben,
				'anzahlNichtKopierteProjektarbeiten' => $anzahlNichtKopierteProjektarbeiten,*/
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

/*		print_r( PHP_EOL.  $countTotal . ' projektarbeiten copied, ' . $countMails . ' sent mails.' . PHP_EOL);
		print_r('End Job Projektarbeit' . PHP_EOL);*/

		$this->logInfo($countTotal . ' projektarbeiten copied, ' . $countMails . ' sent mails.');
		$this->logInfo('End Job Projektarbeit');
	}
}
