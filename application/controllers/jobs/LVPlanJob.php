<?php
/* Copyright (C) 2019 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

class LVPlanJob extends JOB_Controller
{
	/**
	 * Initialize LVPlanJob Class
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Check all Courses with direkt Groups attached and adds the Groups to the Schedule if missing
	 */
	public function addDirectGroups()
	{
		$studiensemester_arr = array();

		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('ressource/Stundenplandev_model', 'StundenplandevModel');
		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
		$this->load->model('education/Lehreinheitgruppe_model', 'LehreinheitgruppeModel');

		// Get actual Studiensemester
		$resultsem = $this->StudiensemesterModel->getAktOrNextSemester();

		if(hasData($resultsem))
		{
			$studiensemester_arr[] = $resultsem->retval[0]->studiensemester_kurzbz;
		}
		else
		{
			echo 'kein Studiensemester gefunden';
			return false;
		}

		// Get nearest Studiensemester to actual
		$resultsem = $this->StudiensemesterModel->getNearestFrom($studiensemester_arr[0]);
		if(hasData($resultsem))
		{
			$studiensemester_arr[] = $resultsem->retval[0]->studiensemester_kurzbz;
		}

		foreach($studiensemester_arr as $studiensemester)
		{
			echo "LVPlanJob/addDirectGroups Studiensemester: ".$studiensemester."\n";
			$succ = 0;
			$fail = 0;

			// get all schedule entries where group is missing
			$result = $this->StundenplandevModel->getMissingDirectGroups($studiensemester);
			if(hasData($result))
			{
				foreach($result->retval as $row)
				{
					$this->LehreinheitModel->addJoin('lehre.tbl_lehrveranstaltung','lehrveranstaltung_id');
					$result_le = $this->LehreinheitModel->loadWhere(array('lehreinheit_id' => $row->lehreinheit_id));

					// load additional data of course
					$unr = null;
					$stg_kz = null;
					$semester = null;
					$gruppe_kurzbz = null;

					if (hasData($result_le))
					{
						$le = $result_le->retval[0];
						$unr = $le->unr;
						$stg_kz = $le->studiengang_kz;
						$semester = $le->semester;
					}
					else
					{
						echo 'Failed to load Lehreinheit '.$row->lehreinheit_id;
						$fail++;
						continue;
					}

					// get direct group if course
					$result_leg = $this->LehreinheitgruppeModel->getDirectGroup($row->lehreinheit_id);
					if (hasData($result_leg))
					{
						$gruppe_kurzbz = $result_leg->retval[0]->gruppe_kurzbz;
					}
					else
					{
						echo 'Failed to load direct group for le '.$row->lehreinheit_id;
						$fail++;
						continue;
					}

					// add group to schedule
					$result = $this->StundenplandevModel->insert(
						array(
							'lehreinheit_id' => $row->lehreinheit_id,
							'unr' => $unr,
							'studiengang_kz' => $stg_kz,
							'semester' => $semester,
							'verband' => '',
							'gruppe' => '',
							'gruppe_kurzbz' => $gruppe_kurzbz,
							'mitarbeiter_uid' => $row->mitarbeiter_uid,
							'ort_kurzbz' => $row->ort_kurzbz,
							'datum' => $row->datum,
							'stunde' => $row->stunde,
							'titel' => null,
							'anmerkung' => null,
							'fix' => false,
							'updateamum' => date('Y-m-d H:i:s'),
							'updatevon' => 'lvplanjob',
							'insertvon' => 'lvplanjob',
							'insertamum' => date('Y-m-d H:i:s')
						)
					);

					if (isSuccess($result))
					{
						$succ++;
					}
					else
					{
						$fail++;
					}
				}
			}
			echo "New Entries ".$succ."\n";
			echo "Failed ".$fail."\n";
		}
	}

    /**
     * Send Mail to STGL, Kompetenzfeld and LV Planung about todays updated Zeitwuensche.
     * STGL gets list only of lectors who updated future assigend courses concerning their STG.
     * Kompetenzleitung gets list only of lectors who updated future assigend courses concerning their KF.
     * LVPlanung gets list of lectors who updated future assigend courses.
     */
    public function mailUpdatedZeitwuensche()
    {
        // Load models
        $this->load->model('ressource/Stundenplandev_model', 'StundenplandevModel');
        $this->load->model('organisation/Studiensemester_model', 'StundiensemesterModel');
        $this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
        $this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
        $this->load->model('person/Person_model', 'PersonModel');

        // Load libs
        $this->load->library('MailLib');

        // Load helpers
        $this->load->helper('hlp_sancho_helper');

        // Start Log Message
        $this->logInfo('Mail updated Zeitwuensche started.');

        // Get all lectors, who updated their Zeitwunsch today
        $db = new DB_Model();
        $result = $db->execReadOnlyQuery('
            SELECT  mitarbeiter_uid, von, bis
            FROM    campus.tbl_zeitwunsch_gueltigkeit
            WHERE   updateamum::date = NOW()::date
            -- dont get updated, if they were also inserted today
            AND     insertamum::date != NOW()::date
        ');

        if (!hasData($result))
        {
            return; // No updated Zeitwuensche today
        }

        $uidByStg_arr = array(); // Mail data for Studiengang
        $uidByOe_arr = array();  // Mail data for Kompetenzfeld

        // Loop through lectors, who updated their Zeitwunsch today
        $zwg_arr = getData($result);
        foreach ($zwg_arr as $zwg)
        {
            $next = $this->StudiensemesterModel->getNext();

            // Get Studiensemester concerend by Zeitwunschgueltigkeit
            // If Zeitwunschgueltigkeit Ende is null, get only till next Studiensemester, as LVs will be only
            // assigned for actual or next Studiensemester
            $result = $this->StudiensemesterModel->getByDate(
                $zwg->von,
                is_null($zwg->bis) ? $next->retval[0]->ende : $zwg->bis
            );
            $studiensemester_arr = getData($result);

            // Get Stundenplan entries of lector of Studiensemester concerned by Zeitwunschgueltigkeit
            $result = $this->StundenplandevModel
                ->getStundenplanData(
                    null,
                    implode(array_column($studiensemester_arr, 'studiensemester_kurzbz'), ', '),
                    null,
                    $zwg->mitarbeiter_uid,
                    null,
                    true    //...but only from now on
                );

            if (!hasData($result))
            {
                continue;   // Continue if lector has no Stundenplan entries
            }

            // Loop through Stundenplan entries
            $stundenplanData_arr = getData($result);
            foreach ($stundenplanData_arr as $stundenplanData)
            {
                // Get LE of Stundenplan entry
                $result = $this->LehreinheitModel->load($stundenplanData->lehreinheit_id);

                if (!hasData($result))
                {
                    // Return log error msg if no LE found and continue
                    $this->logError('
                        LVPlanJob / mailUpdatedZeitwuensche did not find Lehreinheit for '. $zwg->mitarbeiter_uid
                    );
                    continue;
                }

                $le = getData($result)[0];

                // GET LV by LE of Stundenplan entry
                $result = $this->LehrveranstaltungModel->load($le->lehrveranstaltung_id);

                if (!hasData($result))
                {
                    // Return log error msg if no LV found and continue
                    $this->logError('
                        LVPlanJob / mailUpdatedZeitwuensche did not find Lehrveranstaltung for '. $zwg->mitarbeiter_uid
                    );
                    continue;
                }

                $lv = getData($result)[0];

                // Build unique Studiengang array
                if (!array_key_exists($lv->studiengang_kz, $uidByStg_arr))
                {
                    $uidByStg_arr[$lv->studiengang_kz] = array($zwg->mitarbeiter_uid);

                }
                else
                {
                    // Add unique lector array to Studiengang array
                    if (!in_array($zwg->mitarbeiter_uid, $uidByStg_arr[$lv->studiengang_kz]))
                    {
                        $uidByStg_arr[$lv->studiengang_kz][]= $zwg->mitarbeiter_uid;
                    }
                }

                // Build unique Kompetenzfeld array
                if (!array_key_exists($lv->oe_kurzbz, $uidByOe_arr))
                {
                    $uidByOe_arr[$lv->oe_kurzbz] = array($zwg->mitarbeiter_uid);

                }
                else
                {
                    // Add unique lector array to Kompetenzfeld array
                    if (!in_array($zwg->mitarbeiter_uid, $uidByOe_arr[$lv->oe_kurzbz]))
                    {
                        $uidByOe_arr[$lv->oe_kurzbz][]= $zwg->mitarbeiter_uid;
                    }
                }
            }
        }

        // Send mail to STG Assistenz
        $result = $this->_sendMailToStg($uidByStg_arr);
        if (isError($result))
        {
            $this->logError(getError($result));
        }

        // Send mail to Kompetenzfeld Leitung
        $result = $this->_sendMailToKF($uidByOe_arr);
        if (isError($result))
        {
            $this->logError(getError($result));
        }

        // Send mail to LV Planung
        $result = $this->_sendMailToLvPlanung($zwg_arr);
        if (isError($result))
        {
            $this->logError(getError($result));
        }

        // End Log Message
        $this->logInfo('Mail updated Zeitwuensche ended.');
    }

    /**
     * Send Mail to STGL Assistance about lectors, who teach LV assigend to the STG, and who updated Zeitwuensche.
     *
     * @param $data_arr
     * @param $stg_bezeichnung
     */
    private function _sendMailToStg($data_arr)
    {
        foreach ($data_arr as $stg_kurzbz => $uid_arr)
        {
            // Get STG eMail
            $this->load->model('organisation/Studiengang_model', 'StudiengangModel');
            $result = $this->StudiengangModel->load($stg_kurzbz);
            $stgMail = $result->retval[0]->email;

            $lektorenTabelle = '
            <table><thead>
                <tr>
                    <th style="text-align:left">Name</th> 
                    <th style="text-align:left">UID</th>
                </tr>
            </thead><tbody>
        ';

            foreach($uid_arr as $uid)
            {
                $person = $this->PersonModel->getByUid($uid);
                $lektorenTabelle.= '
                <tr>
                    <td style="text-align:left">'. getData($person)[0]->vorname. ' '. getData($person)[0]->nachname. '</td>
                    <td style="text-align:left">['. $uid. ']</td>
                </tr>
            ';
            }

            $lektorenTabelle.= '</tbody></table>';

            $contentData_arr = array(
                'datentabelle' => $lektorenTabelle
            );

            // Send mail
            if (!sendSanchoMail(
                'ZeitwunschUpdateMail',
                $contentData_arr,
                $stgMail,
                'Änderung von Zeitwünschen',
                'sancho_header_min_bw.jpg',
                'sancho_footer_min_bw.jpg'
            ))
            {
                $errorReceiverUid_arr[]= $stgMail;
            }
        }
        
        if (isset($errorReceiverUid_arr))
        {
            return error('Mail updated Zeitwuensche could not be sent to :'. implode($errorReceiverUid_arr, ','));
        }

        return success();
    }

    /**
     * Send Mail to Kompetenzfeld about lectors, who teach LV assigend to the Kompetenzfeld, and who updated Zeitwuensche.
     *
     * @param $data_arr
     * @param $stg_bezeichnung
     */
    private function _sendMailToKF($data_arr)
    {
        // Send mail to Komepetenzfeld Leitung
        foreach ($data_arr as $oe_kurzbz => $uid_arr)
        {
            // Get KF Leitung eMail
            $this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
            $result = $this->BenutzerfunktionModel->loadWhere(array(
                'funktion_kurzbz' => 'Leitung',
                'oe_kurzbz' => $oe_kurzbz
            ));
            $kfMail = $result->retval[0]->uid. '@'. DOMAIN;

            $lektorenTabelle = '
                <table><thead>
                    <tr>
                        <th style="text-align:left">Name</th> 
                        <th style="text-align:left">UID</th>
                    </tr>
                </thead><tbody>
            ';

            foreach($uid_arr as $uid)
            {
                $person = $this->PersonModel->getByUid($uid);
                $lektorenTabelle.= '
                    <tr>
                        <td style="text-align:left">'. getData($person)[0]->vorname. ' '. getData($person)[0]->nachname. '</td>
                        <td style="text-align:left">['. $uid. ']</td>
                    </tr>
                ';
            }

            $lektorenTabelle.= '</tbody></table>';

            $contentData_arr = array(
                'datentabelle' => $lektorenTabelle
            );

            // Send mail
            if (!sendSanchoMail(
                'ZeitwunschUpdateMail',
                $contentData_arr,
                $kfMail,
                'Änderung von Zeitwünschen',
                'sancho_header_min_bw.jpg',
                'sancho_footer_min_bw.jpg'
            ))
            {
                $errorReceiverUid_arr[]= $kfMail;
            }
        }

        if (isset($errorReceiverUid_arr))
        {
            return error('Mail updated Zeitwuensche could not be sent to :'. implode($errorReceiverUid_arr, ','));
        }

        return success();
    }

    /**
     * Send Mail to LV Planung about all lectors who updated Zeitwuensche.
     *
     * @param $data_arr
     * @param $stg_bezeichnung
     */
    private function _sendMailToLvPlanung($data_arr)
    {
        $lektorenTabelle = '
                <table><thead>
                    <tr>
                        <th style="text-align:left">Name</th> 
                        <th style="text-align:left">UID</th>
                    </tr>
                </thead><tbody>
            ';

        foreach($data_arr as $lector)
        {
            $person = $this->PersonModel->getByUid($lector->mitarbeiter_uid);
            $lektorenTabelle.= '
                    <tr>
                        <td style="text-align:left">'. getData($person)[0]->vorname. ' '. getData($person)[0]->nachname. '</td>
                        <td style="text-align:left">['. $lector->mitarbeiter_uid. ']</td>
                    </tr>
                ';
        }

        $lektorenTabelle.= '</tbody></table>';

        $contentData_arr = array(
            'datentabelle' => $lektorenTabelle
        );

        // Send mail
        if (!sendSanchoMail(
            'ZeitwunschUpdateMail',
            $contentData_arr,
            MAIL_LVPLAN,
            'Änderung von Zeitwünschen',
            'sancho_header_min_bw.jpg',
            'sancho_footer_min_bw.jpg'
        ))
        {
            $errorReceiver = MAIL_LVPLAN;
        }

        if (isset($errorReceiver))
        {
            return error('Mail updated Zeitwuensche could not be sent to :'. $errorReceiver);
        }

        return success();
    }
}
