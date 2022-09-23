<?php
/**
 * FH-Complete
 *
 *
 * Cronjobs to be run for inserting the date of Sponsion as Date ZGV of absolvents
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
    $allAbsolventsWithDateSponsion = $this->AbschlusspruefungModel->getAbsolventsWithSponsionDate()->retval;
    $count = 0;
    $countBewerbungen = 0;

    print_r("-----------------------------------------------------------------\n");
    print_r("Cronjob START\n");
    print_r("Sponsionsdatum als ZGV-Datum eintragen:\n");

    foreach ($allAbsolventsWithDateSponsion as $absolvent)
    {
      $this->AbschlusspruefungModel->insertDatumSponsionAsZgvmadatum($absolvent->prestudent_id, $absolvent->sponsion);

      //get all prestudents of person_id with Status Interessent
      $allBewerbungen =  $this->PrestudentModel->getPrestudentsOfPersonId($absolvent->person_id, 'Interessent')->retval;
      foreach ($allBewerbungen as $bewerbung)
      {
        $this->AbschlusspruefungModel->insertDatumSponsionAsZgvmadatum($bewerbung->prestudent_id, $absolvent->sponsion);
        print_r (" Bewerbung: personId: " .  $absolvent->person_id . " prestudentId: " . $bewerbung->prestudent_id. " DateSponsion: " . $absolvent->sponsion."\n");
        $countBewerbungen++;
      }
      $count++;
    }

    print_r("\nAnzahl Absolventen: " . $count);
    print_r("\nAnzahl Inserts Bewerbungen: " . $countBewerbungen);
    print_r("\nCronjob END\n");
    print_r("-----------------------------------------------------------------\n");

    return true;
  }

}
