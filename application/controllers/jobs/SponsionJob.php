<?php
/**
 * FH-Complete
 *
 *
 * Cronjobs to be run for inserting the date of Sponsion as Date ZGV
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
          $this->load->model('crm/Prestudent_model', 'PrestudentModel');
  }

  public function insertDate()
  {
    $countBewerbungen = 0;

    //Bewerbungen vom aktuellen Semester + nächstes Semester berücksichtigen
    $date_actual = new DateTime('first day of this month midnight');	// date obj of actual date
    $month = $date_actual->format('m');	// string month of actual timesheet
    $year = $date_actual->format('Y');	// string year of actual timesheet
    $nextYear = $year+1;

    print_r("-----------------------------------------------------------------\n");
    $semester1 = "WS" . $year;
    $semester2 = ($month>= 6) ? "SS" . $nextYear : "SS" . $year;

    $allInteressenten = $this->PrestudentModel->getAllInteressentenWithMasterSponsion($semester1, $semester2)->retval;

        foreach($allInteressenten as $interessent)
        {
          $this->AbschlusspruefungModel->insertDatumSponsionAsZgvmadatum($interessent->prestudent_id, $interessent->sponsion);
           $countBewerbungen++;
        }

    print_r("Anzahl Inserts Bewerbungen: " . $countBewerbungen);
    print_r("\n-----------------------------------------------------------------\n");

    return true;
  }

}
