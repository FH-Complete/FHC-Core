<?php
/**
 * FH-Complete
 *
 *
 * Cronjobs to be run for inserting the date of Sponsion as Date ZGV for further applications.
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class SponsionJob extends JOB_Controller
{
  /**
   * Constructor
   */
  public function __construct()
  {
          parent::__construct();

          // Load models
          $this->load->model('education/Abschlusspruefung_model', 'AbschlusspruefungModel');

          // Load libraries
          $this->load->library('PermissionLib');

          // Load helpers
          $this->load->helper('hlp_sancho_helper');
  }

  public function insertDate()
  {

  }

  //******************************************************************************************************************
//      PRIVATE FUNCTIONS
//******************************************************************************************************************

private function getAbsolventsWithSponsion()
{
  $mResult = $this->AbschlusspruefungModel->getAbsolventsWithSponsionDate();
  $absolventList = getData($mResult);
  // $vorgesetzte = array();
  // $toSend = array();
}

}
