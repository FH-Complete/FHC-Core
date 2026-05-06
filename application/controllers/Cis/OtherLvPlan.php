<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 *
 */
class OtherLvPlan extends Auth_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            'index' => ['basis/other_lv_plan:r']
        ]);

        // Load Config
        $this->load->config('calendar');
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Public methods

    /**
     * @return void
     */
    public function index()
    {
        $this->load->view('CisRouterView/CisRouterView.php', ['route' => 'OtherLvPlan']);
    }
}
