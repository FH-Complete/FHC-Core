<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The controller Lehrauftrag displays all Lehrauftraege within a study semester.
 * Heads of degree programs can order Lehrauftraege, which subsequently will generate the corresponding contracts
 * automatically.
 * Department leaders can approve the ordered Lehrauftraege.
 */
class LehrauftragErteilen extends Auth_Controller
{
    const APP = 'lehrauftrag';
    const LEHRAUFTRAG_URI = 'lehre/lehrauftrag/LehrauftragErteilen';    // URL prefix for this controller
    const BERECHTIGUNG_LEHRAUFTRAG_ERTEILEN = 'lehre/lehrauftrag_erteilen';

    private $_uid;  // uid of the logged user

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set required permissions
        parent::__construct(
            array(
                'index' => 'lehre/lehrauftrag_erteilen:r',
                'approveLehrauftrag' => 'lehre/lehrauftrag_erteilen:rw'
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
        // Set studiengang to be selected in studiengang dropdown
        $oe_kurzbz = $this->input->get('organisationseinheit'); // if provided by selected studiengang
        $oe_kurzbz = ($oe_kurzbz == 'null' ? null : $oe_kurzbz);

        // Retrieve studiengaenge the user is entitled for to populate studiengang dropdown
        if (!$oe_kurzbz_arr = $this->permissionlib->getOE_isEntitledFor(self::BERECHTIGUNG_LEHRAUFTRAG_ERTEILEN))
        {
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
            'organisationseinheit_selected' => $oe_kurzbz,
            'organisationseinheit' => $oe_kurzbz_arr,
            'studiensemester_selected' => $studiensemester_kurzbz
        );

        $this->load->view('lehre/lehrauftrag/approveLehrauftrag.php', $view_data);
    }

    /**
     * Set the contract status of Lehrauftrag to 'erteilt'.
     * Performed on ajax call.
     */
    public function approveLehrauftrag()
    {
        $lehrauftrag_arr = $this->input->post();

        foreach($lehrauftrag_arr as $lehrauftrag)
        {
            if (!isEmptyArray($lehrauftrag))
            {
                $mitarbeiter_uid = (!is_null($lehrauftrag['mitarbeiter_uid'])) ? $lehrauftrag['mitarbeiter_uid'] : null;
                $vertrag_id = (!is_null($lehrauftrag['vertrag_id'])) ? intval($lehrauftrag['vertrag_id']) : null;

                $result = $this->VertragModel->setStatus($vertrag_id, $mitarbeiter_uid, 'erteilt');

                if ($result->retval)
                {
                    $json []= array(
                        'row_index' => $lehrauftrag['row_index'],
                        'erteilt' => date('Y-m-d')
                    );
                }
            }
        }

        // output json to ajax
        if (!isEmptyArray($json))
        {
            $this->outputJsonSuccess($json);
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
