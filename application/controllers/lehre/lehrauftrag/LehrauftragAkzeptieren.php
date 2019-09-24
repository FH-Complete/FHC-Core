<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The controller LehrauftragAkzeptieren displays all Lehrauftraege of the logged in lector.
 * Lehrauftraege can be accepted by selecting them, entering the password and submitting them.
 */
class LehrauftragAkzeptieren extends Auth_Controller
{
    const APP = 'lehrauftrag';
    const LEHRAUFTRAG_URI = 'lehre/lehrauftrag/LehrauftragAkzeptieren';    // URL prefix for this controller
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
                'index' => 'lehre:r',
                'acceptLehrauftrag' => 'lehre:rw'  // TODO: check ob eigene permission?
            )
        );

        // Load models
        $this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
        $this->load->model('accounting/Vertrag_model', 'VertragModel');
        $this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

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
        // Check if user is lector
        if (!$this->MitarbeiterModel->isLektor($this->_uid))
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
            'studiensemester_selected' => $studiensemester_kurzbz
        );

        $this->load->view('lehre/lehrauftrag/acceptLehrauftrag.php', $view_data);
    }

    /**
     * Set the contract status of Lehrauftrag to 'akzeptiert'.
     * Performed on ajax call.
     */
    public function acceptLehrauftrag()
    {
        $lehrauftrag_arr = $this->input->post();
        
        foreach($lehrauftrag_arr as $lehrauftrag)
        {
            if (!isEmptyArray($lehrauftrag))
            {
                $mitarbeiter_uid = (!is_null($lehrauftrag['mitarbeiter_uid'])) ? $lehrauftrag['mitarbeiter_uid'] : null;
                $vertrag_id = (!is_null($lehrauftrag['vertrag_id'])) ? intval($lehrauftrag['vertrag_id']) : null;

                $result = $this->VertragModel->setStatus($vertrag_id, $mitarbeiter_uid, 'akzeptiert');

                if ($result->retval)
                {
                    $json []= array(
                        'row_index' => $lehrauftrag['row_index'],
                        'akzeptiert' => date('Y-m-d')
                    );
                }
            }
        }
        // output json to ajax
        if (isset($json) && !isEmptyArray($json))
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
