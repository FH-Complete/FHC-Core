<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 *
 * Cronjobs to be run for sending emails informing about status of Lehrauftraege.
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class LehrauftragJob extends JOB_Controller
{
	const BERECHTIGUNG_LEHRAUFTRAG_ERTEILEN = 'lehre/lehrauftrag_erteilen';
	const BERECHTIGUNG_LEHRAUFTRAG_AKZEPTIEREN = 'lehre/lehrauftrag_akzeptieren';

	const LEHRAUFTRAG_ERTEILEN_URI = 'lehre/lehrauftrag/LehrauftragErteilen';
	const LEHRAUFTRAG_AKZEPTIEREN_URI = '/lehre/lehrauftrag/LehrauftragAkzeptieren';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Load models
		$this->load->model('accounting/Vertrag_model', 'VertragModel');
		$this->load->model('accounting/Vertragvertragsstatus_model', 'VertragvertragsstatusModel');
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$this->load->model('system/Benutzerrolle_model', 'BenutzerrolleModel');

		// Load libraries
		$this->load->library('PermissionLib');

		// Load helpers
		$this->load->helper('hlp_sancho_helper');
	}

	/**
	 * This daily job sends information about all lehr-/projektauftraege ordered (and not approved) the day bofore.
	 * Receivers: Department-/Kompetenzfeldleiter
	 **/
	public function mailLehrauftraegeToApprove()
	{
		// Get vertrag_ids of lehrauftraege that had been ordered and had NOT been approved or cancelled YESTERDAY
		$this->VertragvertragsstatusModel->addSelect('vertrag_id');
		$result = $this->VertragvertragsstatusModel->getOrdered_fromDate('YESTERDAY');

		// Get lehrveranstaltung_ids and studiensemester of the lehr-/or projektauftrag contracts
		$lehreinheit_data_arr = array();
		if ($vertrag_arr = getData($result))
		{
			foreach ($vertrag_arr as $vertrag)
			{
				$result = $this->VertragModel->getLehreinheitData($vertrag->vertrag_id, 'lehrveranstaltung_id, studiensemester_kurzbz');

				if (hasData($result))
				{
					$obj = new StdClass();
					$obj->lehrveranstaltung_id = $result->retval[0]->lehrveranstaltung_id;
					$obj->studiensemester_kurzbz = $result->retval[0]->studiensemester_kurzbz;
					$lehreinheit_data_arr []= $obj;
				}
			}
		}

		/**
		 * Build the data array to be used in the email. Data array is clustered as follows:
		 * Array
		 * 	[studiensemester_kurzbz]		// studiensemester of lehreinheit
		 * 	Array
		 *		[oe_kurzbz]					// oe of lehreinheits lehrveranstaltung
		 *		[oe_bezeichnung]
		 *		Array
		 *			[stg_kz]				// stg of lehreinheits lehrveranstaltung
		 * 			[stg_kurzbz]
		 *			[stg_bezeichnung]
		 *			[amount] 				// amount of new ordered lehrauftraege of that stg
		 */
		$data_arr = array();
		foreach ($lehreinheit_data_arr as $lehreinheit_data)
		{
			$result = $this->_getLVData($lehreinheit_data->lehrveranstaltung_id);

			if (hasData($result))
			{
				// Search if studiensemester exists in data_arr
				$ss_index = array_search($lehreinheit_data->studiensemester_kurzbz, array_column($data_arr, 'studiensemester_kurzbz'));

				// If studiensemester is new, add studienesemester, oe and stg
				if ($ss_index === false)
				{
					$data = array(
						'studiensemester_kurzbz' => $lehreinheit_data->studiensemester_kurzbz
					);

					$data []= array(
						'oe_kurzbz' => $result->retval[0]->oe_kurzbz,
						'oe_bezeichnung' => $result->retval[0]->lv_oe_bezeichnung
					);

					// Add stg data to oe, start amount with 1
					$data[0][] = array(
						'stg_kz' => $result->retval[0]->studiengang_kz,
						'stg_kurzbz' => strtoupper($result->retval[0]->stg_typ. $result->retval[0]->stg_kurzbz),
						'stg_bezeichnung' => $result->retval[0]->lv_stg_bezeichnung,
						'amount' => 1
					);

					// Push to final data_arr
					$data_arr []= $data;
				}
				// Else if studiensemester exists
				else
				{
					// Search if oe exists inside existing studiensemester of data_arr
					$oe_index = array_search($result->retval[0]->oe_kurzbz, array_column($data_arr[$ss_index], 'oe_kurzbz'));

					// If oe is new, add oe and stg to studiensemester
					if ($oe_index === false)
					{
						// Add oe data
						$data_arr[$ss_index][] = array(
							'oe_kurzbz' => $result->retval[0]->oe_kurzbz,
							'oe_bezeichnung' => $result->retval[0]->lv_oe_bezeichnung,

							// Add stg data to oe, start amount with 1
							array(
								'stg_kz' => $result->retval[0]->studiengang_kz,
								'stg_kurzbz' => strtoupper($result->retval[0]->stg_typ. $result->retval[0]->stg_kurzbz),
								'stg_bezeichnung' => $result->retval[0]->lv_stg_bezeichnung,
								'amount' => 1
							)
						);
					}
					// Else if oe exists
					else
					{
						// Search if stg exists inside existing oe of data_arr
						$stg_index = array_search($result->retval[0]->studiengang_kz, array_column($data_arr[$ss_index][$oe_index], 'stg_kz'));

						// If stg is new, add stg to oe, start amount with 1
						if ($stg_index === false)
						{
							$data_arr[$ss_index][$oe_index][] = array(
								'stg_kz' => $result->retval[0]->studiengang_kz,
								'stg_kurzbz' => strtoupper($result->retval[0]->stg_typ. $result->retval[0]->stg_kurzbz),
								'stg_bezeichnung' => $result->retval[0]->lv_stg_bezeichnung,
								'amount' => 1
							);
						}
						// Else if stg exists
						else
						{
							// Increase amount +1
							$data_arr[$ss_index][$oe_index][$stg_index]['amount']++;
						}
					}
				}
			}
		}

		/**
		 * Cluster data by uid of entitled mail receivers.
		 * Returning array is clustered as follows:
		 * Array
		 * 	[uid]
		 * Array
		 * 		[studiensemester_kurzbz]		// studiensemester of lehreinheit
		 * 		Array
		 *			[oe_kurzbz]					// oe of lehreinheits lehrveranstaltung
		 *			[oe_bezeichnung]
		 *			Array
		 *				[stg_kz]				// stg of lehreinheits lehrveranstaltung
		 * 				[stg_kurzbz]
		 *				[stg_bezeichnung]
		 *				[amount] 				// amount of new ordered lehrauftraege of that stg
		 */
		$data_arr = $this->_clusterData_byReceiver($data_arr);

		// Send email
		if(!$this->_sendMail_toApprove($data_arr))
		{
			$this->logInfo('SUCCEDED: Sending emails about yesterdays ordered lehrauftraege succeded.');
		}
		else
		{
			$this->logError('Error when sending emails in job MailLehrauftragToApprove');
		}
	}

	/**
	 * This daily job sends information about all lehr-/projektauftraege approved the day bofore.
	 * Receivers: lectors
	 **/
	public function mailLehrauftraegeToAccept()
	{
		// Get vertrag_id and uid of lehrauftraege that had been approved and had NOT been accepted or cancelled YESTERDAY
		$this->VertragvertragsstatusModel->addSelect('vertrag_id, uid');
		$this->VertragvertragsstatusModel->addOrder('uid');
		$result = $this->VertragvertragsstatusModel->getApproved_fromDate('YESTERDAY');

		/**
		 * Build the data array to be used in the email. Data array is clustered as follows:
		 * Array
		 * 	[uid]				// lectors uid (mail receiver)
		 * 	[studiensemester]	// studiensemester of the lehrauftraege (can be more, e.g. 'WS2019 and SS2020')
		 * 	[amount]			// amount of new approved lehrauftraege
		 **/
		$data_arr = array();
		if ($vertrag_arr = getData($result))
		{
			foreach ($vertrag_arr as $vertrag)
			{
				// Get studiensemester of the lehrauftrag
				$this->VertragModel->addSelect('vertragsstunden_studiensemester_kurzbz');
				$result = $this->VertragModel->load($vertrag->vertrag_id);
				if ($studiensemester = getData($result))
				{
					$studiensemester = $studiensemester[0]->vertragsstunden_studiensemester_kurzbz;
				}

				// Search if uid exists in data_arr
				$uid_index = array_search($vertrag->uid, array_column($data_arr, 'uid'));

				// If uid is new, add uid, studiensemester and start amount with 1
				if ($uid_index === false)
				{
					$data = array();
					$data['uid'] = $vertrag->uid;
					$data['studiensemester'] = $studiensemester;
					$data['amount']= 1;
					$data_arr []= $data;
				}
				// Else if uid exists
				else
				{
					// If studiensemester is new, add to studiensemester-string
					if (strpos($data_arr[$uid_index]['studiensemester'], $studiensemester) === false)
					{
						$data_arr[$uid_index]['studiensemester'] .= ' und '. $studiensemester;
					}

					// Increase amount +1
					$data_arr[$uid_index]['amount']++;
				}
			}
		}

		// Send email
		if ($this->_sendMail_toAccept($data_arr))
		{
			$this->logInfo('SUCCEDED: Sending emails about yesterdays approved lehrauftraege succeded.');
		}
		else
		{
			$this->logError('Error when sending emails in job MailLehrauftragToAccept');
		}
	}

	//******************************************************************************************************************
	//	PRIVATE FUNCTIONS
	//******************************************************************************************************************

	/**
	 * Get data of given lehrveranstaltung.
	 * @param $lehrveranstaltung_id
	 * @return mixed
	 */
	private function _getLVData($lehrveranstaltung_id)
	{
		$this->LehrveranstaltungModel->addSelect('
				tbl_lehrveranstaltung.oe_kurzbz,
				oe.bezeichnung AS "lv_oe_bezeichnung",
				tbl_lehrveranstaltung.studiengang_kz,
				stg.bezeichnung AS "lv_stg_bezeichnung",
				stg.typ AS "stg_typ",
				stg.kurzbz AS "stg_kurzbz"
			');

		$this->LehrveranstaltungModel->addJoin('lehre.tbl_studienplan_lehrveranstaltung stpllv', 'lehrveranstaltung_id');
		$this->LehrveranstaltungModel->addJoin('lehre.tbl_studienplan stpl', 'studienplan_id');
		$this->LehrveranstaltungModel->addJoin('lehre.tbl_studienordnung sto', 'studienordnung_id');
		$this->LehrveranstaltungModel->addJoin('public.tbl_studiengang stg', 'ON stg.studiengang_kz = tbl_lehrveranstaltung.studiengang_kz');
		$this->LehrveranstaltungModel->addJoin('public.tbl_organisationseinheit oe', 'ON oe.oe_kurzbz = tbl_lehrveranstaltung.oe_kurzbz');
		$this->LehrveranstaltungModel->addOrder('stpllv.insertamum', 'DESC');
		$this->LehrveranstaltungModel->addLimit(1);

		return $this->LehrveranstaltungModel->load($lehrveranstaltung_id);
	}

	/**
	 * Send Sancho eMail about ordered Lehrauftraege.
	 * @param $data_arr
	 */
	private function _sendMail_toApprove($data_arr)
	{
		// Loop through 'container' of mail recipients
		foreach($data_arr as $data)
		{
			// Set mail recipients (department assistance/leader)
			$to = $data['uid']. '@'. DOMAIN;
			$html_table = $this->_renderData_LehrauftraegeToApprove($data);

			// Prepare mail content
			$content_data_arr = array(
				'table' 	=> $html_table
			);

			sendSanchoMail(
				'LehrauftragNeueBestellungen',
				$content_data_arr,
				$to,
				'Bestellung neuer Lehraufträge',
				'sancho_header_min_bw.jpg',
				'sancho_footer_min_bw.jpg'
			);
		}
	}

	/**
	 * Cluster the data array by entitled mail receiver.
	 * Returning array is clustered as follows:
	 * Array
	 * 	[uid]
	 * Array
	 * 		[studiensemester_kurzbz]		// studiensemester of lehreinheit
	 * 		Array
	 *			[oe_kurzbz]					// oe of lehreinheits lehrveranstaltung
	 *			[oe_bezeichnung]
	 *			Array
	 *				[stg_kz]				// stg of lehreinheits lehrveranstaltung
	 * 				[stg_kurzbz]
	 *				[stg_bezeichnung]
	 *				[amount] 				// amount of new ordered lehrauftraege of that stg
	 * @param $data_arr
	 * @return array
	 *
	 */
	private function _clusterData_byReceiver($data_arr)
	{
		$mail_data_arr = array();	// final array with all data clustered by mail receiver

		// Loop through 'container' of studiensemester
		foreach ($data_arr as $data)
		{
			$data_len = count($data) - 1;

			// Loop through 'container' of organisational units
			for ($i = 0; $i < $data_len; $i++)
			{
				// Get all users entitled by organisational unit
				$result = $this->BenutzerrolleModel->getBenutzerByBerechtigung(self::BERECHTIGUNG_LEHRAUFTRAG_ERTEILEN, $data[$i]['oe_kurzbz'], 'suid');

				if ($berechtigung_arr = getData($result))
				{
					// Loop through entitled users
					foreach ($berechtigung_arr as $berechtigung)
					{
						// Search if UID exists inside mail_data_arr
						$uid_index = array_search($berechtigung->uid, array_column($mail_data_arr, 'uid'));

						// If UID is new, add UID to final array
						if ($uid_index === false)
						{
							// add UID with corresponding data
							$mail_data_arr [] = array(
								'uid' => $berechtigung->uid,
								array(
									'studiensemester_kurzbz' => $data['studiensemester_kurzbz'],
									$data[$i]
								)
							);
						} // Else if UID exists
						else
						{
							// Search if studiensemester exists inside the existing UID array
							$ss_index = array_search($data['studiensemester_kurzbz'], array_column($mail_data_arr[$uid_index], 'studiensemester_kurzbz'));

							// If studiensemester is new, add studiensemester to existing UID
							if ($ss_index === false)
							{
								$mail_data_arr[$uid_index] []= array(
									'studiensemester_kurzbz' => $data['studiensemester_kurzbz'],
									$data[$i]
								);
							}
						}
					}
				}
			}
		}

		return $mail_data_arr;
	}

	/**
	 * Render the data array for the mail template returing a HTML table.
	 * @param $data_arr	Data to be used in HTML table
	 * @return string	HTML table to be embedded in eMail
	 */
	private function _renderData_LehrauftraegeToApprove($data_arr)
	{
		$html = '';
		foreach ($data_arr as $studiensemester_container)
		{
			if (is_array($studiensemester_container))	// is_array 'trims' the outer associative key [uid]
			{
				if (isset($studiensemester_container['studiensemester_kurzbz']))
				{
					$studiensemester = $studiensemester_container['studiensemester_kurzbz'];

					// Link to LehrauftragErteilen
					$url =  site_url(self::LEHRAUFTRAG_ERTEILEN_URI).'?studiensemester='. $studiensemester;
				}

				// HTML table header
				$html .= '
					<br>
					<span style="font-size: small;"><b>Studiensemester: '. $studiensemester. '</b></span>
					<br><br>
					<table style="width: 100%; border-collapse: collapse;" border="1" cellpadding="5">
					<thead>
					<tr>
					<th style="width: 30%; font-size: 12px;"><span>LV-Organisationseinheit</span></th>
					<th style="width: 40%; font-size: 12px;"><span>Studiengang</span></th>
					<th style="width: 15%; font-size: 12px;"><span>STG-Kurzbezeichnung</span></th>
					<th style="width: 15%; font-size: 12px;"><span><strong>Anzahl neu bestellter Lehrauftr&auml;ge</strong></span></th>
					</tr>
					</thead>
					<tbody>'
				;

				// HTML table body
				foreach ($studiensemester_container as $oe_container)
				{
					if (is_array($oe_container))	// is_array 'trims' the outer associative key [studiensemester_kurzbz]
					{
						if (isset($oe_container['oe_bezeichnung']))
						{
							$oe_bezeichnung = $oe_container['oe_bezeichnung'];
						}

						foreach ($oe_container as $stg_data)
						{
							if (is_array($stg_data))	// is_array 'trims' the outer associative keys [oe_kurzbz] and [oe_bezeichnung]
							{
								$html .= '
									<tr>
									<td><span style="font-size: small;">'. $oe_bezeichnung. '</span></td>
									<td><span style="font-size: small;">'. $stg_data['stg_bezeichnung']. '</span></td>
									<td align="center"><span style="font-size: small;">'. $stg_data['stg_kurzbz']. '</span></td>
									<td align="center"><span style="font-size: small;"><strong>'. $stg_data['amount']. '</strong></span></td>
									</tr>
								';
							}
						}
					}
				}

				// HTML table body end and link
				$html .= '
					</tbody>
					</table>
					<br>
					<span style="font-size: small;">'. anchor($url, 'Lehraufträge Übersicht '. $studiensemester). '</span>
					<br><br>
				';
			}
		}

		return $html;
	}

	/**
	 * Send Sancho eMail about ordered Lehrauftraege.
	 * @param $data_arr
	 */
	private function _sendMail_toAccept($data_arr)
	{
		// Loop through 'container' of mail recipients
		foreach($data_arr as $data)
		{
			// Set mail recipient (lector)
			$to = $data['uid']. '@'. DOMAIN;

			// Link to LehrauftragAkzeptieren
			$url =  CIS_ROOT. 'cis/index.php?menu='.
				CIS_ROOT. 'cis/menu.php?content_id=&content='.
				CIS_ROOT. index_page(). self::LEHRAUFTRAG_AKZEPTIEREN_URI;

			// Get first name
			$first_name = '';
			$this->load->model('person/Benutzer_model', 'BenutzerModel');
			$this->BenutzerModel->addSelect('vorname');
			$this->BenutzerModel->addJoin('public.tbl_person', 'person_id');
			$result = $this->BenutzerModel->loadWhere(array('uid' => $data['uid']));

			if (hasData($result))
			{
				$first_name = $result->retval[0]->vorname;
			}

			// Prepare mail content
			$content_data_arr = array(
				'vorname' => $first_name,
				'studiensemester' => $data['studiensemester'],
				'anzahl' 	=> $data['amount'],
				'link'		=> anchor($url, 'Lehraufträge Übersicht')
			);

			sendSanchoMail(
				'LehrauftragNeueErteilte',
				$content_data_arr,
				$to,
				'Neu erteilte Lehraufträge zum Annehmen bereit'
			);
		}
		return true;
	}
}
