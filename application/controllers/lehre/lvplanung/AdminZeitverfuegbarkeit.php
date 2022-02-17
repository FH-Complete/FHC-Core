<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class AdminZeitverfuegbarkeit extends Auth_Controller
{
    const BERECHTIGUNG_ZEITVERFUEGBARKEIT = 'lehre/zeitverfuegbarkeit';
    private $_uid;  // uid of the logged user

    public function __construct()
    {
        // Set required permissions
        parent::__construct(
            array(
                'index' => 'lehre/zeitverfuegbarkeit:rw',
                'saveZeitverfuegbarkeit' => 'lehre/zeitverfuegbarkeit:rw',
                'deleteZeitverfuegbarkeit' => 'lehre/zeitverfuegbarkeit:rw'
            )
        );

        // Load models
        $this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
        $this->load->model('person/Benutzer_model', 'BenutzerModel');
        $this->load->model('person/Person_model', 'PersonModel');
        $this->load->model('ressource/Zeitsperre_model', 'ZeitsperreModel');

        // Load libraries
        $this->load->library('PermissionLib');
        $this->load->library('AuthLib');
        $this->load->library('WidgetLib');

        // Load helpers
        $this->load->helper('array');

        // Load language phrases
        $this->loadPhrases(
            array(
                'global',
                'ui',
                'lehre'
            )
        );

        $this->_setAuthUID(); // sets property uid

        $this->setControllerId(); // sets the controller id
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Public methods

    public function index()
    {
        // Get Studiengaenge the user is entitled for
        $result = $this->permissionlib->getSTG_isEntitledFor(self::BERECHTIGUNG_ZEITVERFUEGBARKEIT);
        $studiengang_kz_arr = !isEmptyArray($result) ? $result : array();

        // Get lectors of that Studiengaenge
        $result = $this->_getLehreinheitmitarbeiterByStg($studiengang_kz_arr);
        $lektor_arr = hasData($result) ? getData($result) : array();

        // Get available Stundenplan Stunden
        $result = $this->_getStunden();
        $stunde_arr = hasData($result) ? getData($result) : array();

        // Get actual Studiensemester to set min-limit Datepicker
        $result = $this->StudiensemesterModel->getAkt();
        $studsemStart = hasData($result) ? getData($result)[0]->start : '';

        $view_data = array(
            'studiengang_kz_arr' => $studiengang_kz_arr,
            'lektor_arr' => $lektor_arr,
            'stunde_arr' => $stunde_arr,
            'studsemStart' => $studsemStart
        );

        $this->load->view('lehre/lvplanung/adminZeitverfuegbarkeit.php', $view_data);
    }

    /**
     * Save or update Zeitverfuegbarkeit.
     */
    public function saveZeitverfuegbarkeit()
    {
        $zeitsperre_id = $this->input->post('zeitsperre_id');
        $mitarbeiter_uid = $this->input->post('mitarbeiter_uid');
        $zeitsperretyp_kurzbz = $this->input->post('zeitsperretyp_kurzbz');
        $bezeichnung = $this->input->post('bezeichnung');
        $vonDatum = $this->input->post('vondatum');
        $vonStunde = isEmptyString($this->input->post('vonstunde')) ? null : $this->input->post('vonstunde');
        $bisDatum = $this->input->post('bisdatum');
        $bisStunde = isEmptyString($this->input->post('bisstunde')) ? null : $this->input->post('bisstunde');

        if ($this->_validate()) // TODO
        {
            if (is_numeric($zeitsperre_id))
            {
                $result = $this->ZeitsperreModel->update(
                    $zeitsperre_id,
                    array(
                        'vondatum' => $vonDatum,
                        'vonstunde' => $vonStunde,
                        'bisdatum' => $bisDatum,
                        'bisstunde' => $bisStunde,
                        'bezeichnung' => $bezeichnung
                    )
                );
            }
            else
            {
                $result = $this->ZeitsperreModel->save(
                    $zeitsperretyp_kurzbz,
                    $mitarbeiter_uid,
                    $vonDatum,
                    $bisDatum,
                    $vonStunde,
                    $bisStunde,
                    $bezeichnung
                );
            }
        }

        if (isError($result))
        {
            $this->terminateWithJsonError(getError($result));
        }

        $zeitsperre_id = getData($result);

        // Success response to AJAX
        $this->outputJsonSuccess(array(
            'zeitsperre_id' => $zeitsperre_id,
            'msg' => $this->p->t('ui', 'gespeichert')
        ));
    }

    /**
     * Delete Zeitverfuegbarkeit.
     */
    public function deleteZeitverfuegbarkeit()
    {
        $zeitsperre_id = $this->input->post('zeitsperre_id');

        if (!isset($zeitsperre_id) || !is_numeric($zeitsperre_id))
        {
            $this->terminateWithJsonError('Wählen Sie einen Lehrenden aus der Zeitverfügbarkeit-Tabelle aus.');
        }

        // Delete
        $result = $this->ZeitsperreModel->delete($zeitsperre_id);

        if (isError($result))
        {
            $this->terminateWithJsonError(getError($result));
        }

        $this->outputJsonSuccess(array('msg' => $this->p->t('ui', 'geloescht')));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Private methods

    /**
     * Retrieve the UID of the logged user and checks if it is valid
     */
    private function _setAuthUID()
    {
        $this->_uid = getAuthUID();

        if (!$this->_uid) show_error('User authentification failed');
    }

    /**
     * Get all lectors that are assigend to Lehreinheiten in actual or future semester.
     *
     * @param $studiengang_kz_arr Restrict only to given stg-
     * @return mixed
     */
    private function _getLehreinheitmitarbeiterByStg($studiengang_kz_arr)
    {
        $this->MitarbeiterModel->addSelect('lema.mitarbeiter_uid, nachname, vorname');
        $this->MitarbeiterModel->addDistinct('lema.mitarbeiter_uid');
        $this->MitarbeiterModel->addJoin('lehre.tbl_lehreinheitmitarbeiter lema', 'tbl_mitarbeiter.mitarbeiter_uid = lema.mitarbeiter_uid');
        $this->MitarbeiterModel->addJoin('public.tbl_benutzer b', 'lema.mitarbeiter_uid = b.uid');
        $this->MitarbeiterModel->addJoin('public.tbl_person p', 'person_id');
        $this->MitarbeiterModel->addJoin('lehre.tbl_lehreinheit le', 'lehreinheit_id');
        $this->MitarbeiterModel->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id');
        $this->MitarbeiterModel->addJoin('public.tbl_studiensemester ss', 'studiensemester_kurzbz');
        $this->MitarbeiterModel->addOrder('lema.mitarbeiter_uid');

        // Return lektoren assigned to actual or future lehreinheiten
        return $this->MitarbeiterModel->loadWhere('
            lv.studiengang_kz IN (' . implode(', ', $studiengang_kz_arr) . ')
            AND b.aktiv
            AND personalnummer > 0
            AND NOW() <= ss.ende'
        );
    }

    /**
     * Get all available Stunden of Stundentabelle.
     *
     * @return mixed
     */
    private function _getStunden()
    {
        $this->load->model('ressource/Stunde_model', 'StundeModel');
        $this->StundeModel->addOrder('stunde');
        
        return $this->StundeModel->load();
    }

    private function _validate()
    {
        return true;
    }

}
