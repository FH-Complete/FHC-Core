<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The controller Lehrauftrag displays all Lehrauftraege within a study semester.
 * Heads of degree programs can order Lehrauftraege, which subsequently will generate the corresponding contracts
 * automatically.
 * Department leaders can approve the ordered Lehrauftraege.
 */
class Lehrauftrag extends Auth_Controller
{
    const APP = 'lehrauftrag';
    const LEHRAUFTRAG_URI = 'lehre/lehrauftrag/Lehrauftrag';    // URL prefix for this controller
    const BERECHTIGUNG_LEHRAUFTRAG_BESTELLEN = 'lehre/lehrauftrag_bestellen';

    private $_uid;  // uid of the logged user

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set required permissions
        parent::__construct(
            array(
                'index' => 'lehre/lehrauftrag_bestellen:r',
                'orderLehrauftrag' => 'lehre/lehrauftrag_bestellen:rw'
            )
        );

        // Load models
        $this->load->model('system/Benutzerrolle_model', 'BenutzerrolleModel');
        $this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
        $this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
        $this->load->model('organisation/Studiengang_model', 'StudiengangModel');
        $this->load->model('accounting/Vertrag_model', 'VertragModel');

        // Load libraries
        $this->load->library('WidgetLib');
        $this->load->library('PermissionLib');

        // Load helpers
        $this->load->helper('array');
        $this->load->helper('url');
        $this->load->helper('hlp_sancho_helper');

        // Load language phrases
        $this->loadPhrases(
            array(
                'global',
                'ui'
            )
        );

        $this->_setAuthUID(); // sets property uid

        $this->setControllerId(); // sets the controller id
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Public methods
    /**
     * Main page of Lehrauftrag
     */
    public function index()
    {
        // Set studiengang selected for studiengang dropdown
        $studiengang_kz = $this->input->get('studiengang'); // if provided by selected studiengang
        $studiengang_kz = ($studiengang_kz == 'null' ? null : $studiengang_kz);

        // Retrieve studiengaenge the user is entitled for to populate studiengang dropdown
        if (!$studiengang_kz_arr = $this->permissionlib->getSTG_isEntitledFor(self::BERECHTIGUNG_LEHRAUFTRAG_BESTELLEN)) {
            show_error('Fehler bei Berechtigungsprüfung');
        }

        // Set studiensemester selected for studiengang dropdown
        $studiensemester_kurzbz = $this->input->get('studiensemester'); // if provided by selected studiensemester
        if (is_null($studiensemester_kurzbz)) // else set next studiensemester as default value
        {
            $studiensemester = $this->StudiensemesterModel->getNext();
            if (hasData($studiensemester))
            {
                $studiensemester_kurzbz = $studiensemester->retval[0]->studiensemester_kurzbz;
            }
            elseif (isError($studiensemester))
            {
                show_error($studiensemester->error);
            }
        }

        $view_data = array(
            'studiengang_selected' => $studiengang_kz,
            'studiengang' => $studiengang_kz_arr,
            'studiensemester_selected' => $studiensemester_kurzbz
        );

        $this->load->view('lehre/lehrauftrag/lehrauftrag.php', $view_data);
    }

    public function orderLehrauftrag()
    {
        $result = array();
        $new_lehrvertrag_data_arr = array();    // information of new lehrvertraege to be used in mail

        foreach ($_POST as $lehrauftrag)
        {
            if (!isEmptyArray($lehrauftrag)) {
                if ($this->VertragModel->save(
                    element('Person_ID', $lehrauftrag),
                    element('LV_ID', $lehrauftrag),
                    element('LE_ID', $lehrauftrag),
                    element('PA_ID', $lehrauftrag),
                    element('Stunden', $lehrauftrag),
                    element('Betrag', $lehrauftrag),
                    element('Studiensemester', $lehrauftrag)
                )->retval)
                {
                    $result []= array(
                        'id' => $lehrauftrag['id'],
                        'Bestellt' => date('Y-m-d')
                    );

                    $new_lehrvertrag_data_arr[] = array(
                        'studiensemester_kurzbz' => $lehrauftrag['Studiensemester'],
                        'studiengang_kz' => $lehrauftrag['studiengang_kz'],
                        'lv_oe_kurzbz' => $lehrauftrag['lv_oe_kurzbz']
                    );
                }
            }
        }

        if (!isEmptyArray($result))
        {
            $this->outputJsonSuccess($result);
        }

        // Send email to Mitarbeiter
        // if(!$this->_sendMail($new_lehrvertrag_data_arr)) // TODO: slows down Bestell-process -> better chronjob?
        {
           // return error information // TODO: implement after decision regarding communication process
        }
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

}
