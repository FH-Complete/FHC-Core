<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class OtherLvPlan extends FHCAPI_Controller
{

    /**
     * Object initialization
     */
    public function __construct()
    {
        parent::__construct([
            'otherLvPlanViewData' => ['basis/other_lv_plan:r'],
        ]);

        $this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
        $this->load->model('person/Benutzer_model', 'BenutzerModel');

    }

    //------------------------------------------------------------------------------------------------------------------
    // Public methods

    /**
     * retrieves viewData for other lv plan view
     * @access public
     * @param  $uid the userID for which the other lv plan is being viewed
     */
    public function otherLvPlanViewData($uid)
    {
        $isMitarbeiterResult = $this->MitarbeiterModel->isMitarbeiter($uid);
        $isMitarbeiter = getData($isMitarbeiterResult);
        $isStudent = !$isMitarbeiter;

        $this->BenutzerModel->addSelect(["foto", "vorname", "nachname"]);
        $this->BenutzerModel->addJoin("tbl_person", "person_id");
        $personResult = $this->BenutzerModel->load([$uid]);
        $person = hasData($personResult) ? getData($personResult) : null;

        $viewData = [
            "user_data" => [
                "username" => $uid,
                "is_student" => $isStudent,
                "is_mitarbeiter" => $isMitarbeiter,
                "foto" => $person[0]->foto,
                "vorname" => $person[0]->vorname,
                "nachname" => $person[0]->nachname,
            ],
        ];

        $this->terminateWithSuccess($viewData);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Private methods

}

