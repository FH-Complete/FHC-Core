<?php

// if (! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * This controller Lehrauftrag displays all Lehrauftraege within a study semester.
 * Heads of degree programs can order Lehrauftraege, which subsequently will generate the corresponding contracts
 * automatically.
 * Department leaders can approve the ordered Lehrauftraege.
 */
class Lehrauftrag extends Auth_Controller
{
    const APP = 'lehrauftrag';
    const LEHRAUFTRAG_URI = 'lehre/lehrauftrag/Lehrauftrag';    // URL prefix for this controller

    private $_uid;  // uid of the logged user

    /**
     * Constructor
     */
    public function __construct()
    {
        // TODO: adapt permissions!
        // Set required permissions
        parent::__construct(
            array(
                'index' => 'infocenter:r'
            )
        );

        // Load models
        $this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
        $this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
        $this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
//        $this->load->model('organisation/Studiengang_model', 'StudiengangModel'); // TODO: delete?
        $this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');

        // TODO: check: WidgetLib notwendig?
        // Load libraries
        $this->load->library('WidgetLib');

        // Load language phrases
        $this->loadPhrases(
            array(
                'global',
                'ui'
            )
        );

        $this->_setAuthUID(); // sets property uid

        //TODO: delete test user
        //$this->_uid = 'testuser';

        $this->setControllerId(); // sets the controller id
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Public methods
    /**
     * Main page of Lehrauftrag
     */
    public function index()
    {
        echo '<pre>', print_r($_GET, 1), '</pre>';
        $studiengang_kz = $this->input->get('studiengang');
        $studiensemester_kurzbz = $this->input->get('studiensemester');

        // Set studiengang variable
        if (!isset($studiengang_kz) || !is_numeric($studiengang_kz))
        {
            $benutzerfunktion = $this->BenutzerfunktionModel->getSTGLByUID($this->_uid);

            $studiengang_kz_arr = array();
            if (hasData($benutzerfunktion))
            {
                foreach ($benutzerfunktion->retval as $benutzerfkt)
                {
                    $studiengang_kz_arr[] = $benutzerfkt->studiengang_kz;
                }
            }
            elseif (isError($benutzerfunktion))
            {
                show_error($benutzerfunktion->error);
            }
        }

        // Set studiensemester variable
        if (!isset($studiensemester_kurzbz) || !is_string($studiensemester_kurzbz))
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
            'studiengang' => $studiengang_kz_arr,
            'studiensemester' => $studiensemester_kurzbz
        );
        $this->load->view('lehre/lehrauftrag/lehrauftrag.php', $view_data);
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
