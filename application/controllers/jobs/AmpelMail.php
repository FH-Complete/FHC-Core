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
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

class AmpelMail extends CLI_Controller
{
	const CIS_AMPELVERWALTUNG_URL = CIS_ROOT. "cis/index.php?menu=".
		CIS_ROOT. "cis/menu.php?content_id=&content=".
		CIS_ROOT. "cis/private/tools/ampelverwaltung.php";

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Load helpers
		$this->load->helper('hlp_sancho');

		// Load models
		$this->load->model('content/Ampel_model', 'AmpelModel');
		$this->load->model('person/Person_model', 'PersonModel');
	}

	/**
	 * Generates mail content for new and overdue Ampeln, which
	 * 1. are not confirmed by the user yet and
	 * 2. are marked to be sent by email
	 * Ampel is new when inserted within the last 7 days before the cronjob runs (as cronjob runs weekly)
	 * Ampel is overdue when the cronjob runs after the deadline date.
	 * @return void
	 */
	public function generateAmpelMail()
	{
		// Get all notifications, that are not expired, not before vorlaufzeit AND email is true
		$result_active_ampeln = $this->AmpelModel->active(true);

		// Stores users, description, deadline and system-link of notifications
		$new_ampel_data_arr = array(); // data of new notifications that are not confirmed
		$overdue_ampel_data_arr = array(); // data of overdue notifications that are not confirmed

		if (hasData($result_active_ampeln))
		{
			$ampel_arr = $result_active_ampeln->retval;

			// Loop through ampeln
			foreach ($ampel_arr as $ampel)
			{
				echo "Checking Ampel: ".$ampel->kurzbz;

				$ampel_id = $ampel->ampel_id;
				$now = date_create(date('Y-m-d'));
				$deadline = date_create($ampel->deadline);
				$insert_date = date_create($ampel->insertamum);
				$qry_all_ampel_user = $ampel->benutzer_select; // sql select to get all user who get this ampel
				$new = false;
				$overdue = false;

				// get all user, who get this ampel
				$result_ampel_user = $this->AmpelModel->execBenutzerSelect($qry_all_ampel_user);

				if (hasData($result_ampel_user))
				{
					$ampel_user_arr = $result_ampel_user->retval;

					// loop through all user, who get this ampel
					foreach ($ampel_user_arr as $ampel_user)
					{
						$uid = $ampel_user->uid;

						// break if ampel was almost confirmed by the user
						if($this->AmpelModel->isConfirmed($ampel_id, $uid))
							continue;

						// check if ampel is new (inserted within last week, as cronjob will run every week)
						if ($now->diff($insert_date)->days <= 7) $new = true;

						//check if ampel is overdue
						if ($now > $deadline) $overdue = true;

						// if ampel is new
						if ($new)
						{
							$html_text = '<p><strong>'. strtoupper($ampel->kurzbz). '</strong></p><br>';

							// create template-specific data array of new notifications
							// if uid already exists in the array, only the html text will be added to the existing html text
							$new_ampel_data_arr =
								$this->_getAmpelContentData($uid, $html_text, $new_ampel_data_arr);
						}

						// if ampel is overdue
						if ($overdue)
						{
							$html_text = '
								<p><strong>'. strtoupper($ampel->kurzbz). '</strong><br>
								<small>
									<i style="color: #65696E;">Die Deadline für die Bestätigung war am
										<span style="color: #FF0000;">'. date_format($deadline, 'Y-m-d'). '</span>
									</i>
								</small></p><br>';

							// create template-specific data array of overdue notifications
							// if uid already exists in the array, only the html text will be added to the existing html text
							$overdue_ampel_data_arr =
								$this->_getAmpelContentData($uid, $html_text, $overdue_ampel_data_arr);
						}
					}
				}
				elseif (isError($result_ampel_user))
				{
					show_error(getError($result_ampel_user));
				}
			}
		}
		elseif (isError($result_active_ampeln))
		{
			show_error(getError($result_active_ampeln));
		}

		// Send mails for new ampeln merged by user
		foreach ($new_ampel_data_arr as $new_ampel_data)
		{
			echo "\nSend New Ampel Mail to ".$new_ampel_data['uid'];
			sendSanchoMail(
				'Sancho_Content_AmpelNeu',
				$new_ampel_data,
				$new_ampel_data['uid']. '@'. DOMAIN,
				'Du hast eine neue Ampel!',
				'sancho_header_neue_nachrichten_in_ampelsystem.jpg'
			);
		};
		// Send mails for overdue ampeln merged by user
		foreach ($overdue_ampel_data_arr as $overdue_ampel_data)
		{
			echo "\nSend Ampel Ueberfaellig Mail to ".$overdue_ampel_data['uid'];
			sendSanchoMail(
				'Sancho_Content_AmpelUeberfaellig',
				$overdue_ampel_data,
				$overdue_ampel_data['uid']. '@'. DOMAIN,
				'Bestätige bitte Deine Ampel!',
				'sancho_header_ampel_overdue.jpg'
			);
		};
	}

	// ------------------------------------------------------------------------
	// Private methods
	/**
	 * Returns associative array with data as needed in the ampel content templates.
	 * @param string $uid UID, needed to merge user.
	 * @param string $html_text	Specific HTML Content to be set initially for a user or to add if user exists.
	 * @param array $ampel_data_arr Array with ampeln.
	 * @return array
	 */
	private function _getAmpelContentData($uid, $html_text, $ampel_data_arr)
	{
		$firstName = $this->PersonModel->getByUid($uid)->retval[0]->vorname;

		// check if user already exists in the array
		$key_position = array_search($uid, array_column($ampel_data_arr, 'uid'));

		// If user already exists in the array, add only new ampel data to users ampel list
		if ($key_position !== false)
		{
			$ampel_data_arr[$key_position]['ampel_list'] .= $html_text;
		}
		// If user does not exist, create new user and push all data needed
		else
		{
			$ampel_data_arr[] = array(
				'uid' => $uid,
				'firstName' => $firstName,
				'ampel_list' => $html_text,
				'link' => self::CIS_AMPELVERWALTUNG_URL
			);
		}
		return $ampel_data_arr;
	}
}
