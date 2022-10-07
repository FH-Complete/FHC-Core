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
          $this->load->model('crm/Prestudent_model', 'PrestudentModel');
          $this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
    }

    public function insertDate()
    {
        $countBewerbungen = 0;

        $semester = $this->StudiensemesterModel->getAkt();
        if (isSuccess($semester) && hasData($semester))
        {
            $semester = $semester->retval[0];
            $semester = $semester->studiensemester_kurzbz;
        }
        else
        {
            $semester = '';
        }
        $nextSemester = $this->StudiensemesterModel->getNext();
        if (isSuccess($nextSemester) && hasData($nextSemester))
        {
            $nextSemester = $nextSemester->retval[0];
            $nextSemester = $nextSemester->studiensemester_kurzbz;
        }
        else
        {
            $nextSemester = '';
        }

        $this->logInfo('Start Sponsion Job');
        $this->logInfo('Check Applications of Semester ' . $semester . ' and '. $nextSemester);

        $allInteressenten = $this->PrestudentModel->getAllInteressentenWithMasterSponsion($semester, $nextSemester);

        if (isSuccess($allInteressenten) && hasData($allInteressenten))
        {
            $allInteressenten = $allInteressenten->retval;
            foreach($allInteressenten as $interessent)
            {
            //  $this->AbschlusspruefungModel->insertDatumSponsionAsZgvmadatum($interessent->prestudent_id, $interessent->sponsion);

                $updateArray = array(
                  'zgvmadatum' => $interessent->sponsion,
                  'updateamum' => date('Y-m-d H:i:s'),
                  'updatevon' => 'sponsionJob'
                );

                $prestresult = $this->PrestudentModel->update(
                    $interessent->prestudent_id,
                    $updateArray
                );

                if (isSuccess($prestresult))
                {
                    $this->logInfo('ZGV Master Date for prestudent ID ' . $interessent->prestudent_id . ' was updated to ' . $interessent->sponsion);
                }
                else
                {
                    $this->logError('Update for prestudent ID ' . $interessent->prestudent_id . ' to ' . $interessent->sponsion . ' failed');
                }

                $countBewerbungen++;
            }
        }
        $this->logInfo('Count Total of inserted ZGV Master Dates: '. $countBewerbungen);
        $this->logInfo('End Sponsion Job');
        return true;
    }
}
