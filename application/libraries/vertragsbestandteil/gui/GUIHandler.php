<?php

use phpDocumentor\Reflection\Types\Integer;
use PhpParser\Node\Expr;

require_once __DIR__ . '/FormData.php';

/**
 * GUIHandler takes JSON from GUI and manages the process of
 * storing the data to the database 
 * TODO convert to controller
 */
class GUIHandler
{

    protected $employeeUID;
    protected $userUID;
    protected $CI;

    public function __construct($employeeUID, $userUID)
    {
        $this->employeeUID = $employeeUID;
        $this->userUID = $userUID;
        $this->CI = get_instance();
        $this->CI->load->model('vertragsbestandteil/Dienstverhaeltnis_model',
					'Dienstverhaeltnis_model');
		

    }

    /**
     * main entry (called from VetragsbestandteilLib)
     * @param string $guidata JSON submitted by editor
     * @param string $employeeUID uid of the employee
     * @param string $userUID  uid of the user currently editing the employee data
     * @return string JSON for GUI client
     */
    public function handle($guidata)
    {
        $decoded = json_decode($guidata, true);
        $formDataMapper = new FormData();
		$formDataMapper->mapJSON($decoded);

        // DV
        $dvData = $formDataMapper->getData();
        $this->handleDV($dvData);

		// VBS
		$vbsList = $formDataMapper->getVbs();

        foreach ($vbsList as $vbsID => $vbs) {
            $this->handleVBS($dvData['dienstverhaeltnis_id'] ,$vbs);
        }

        return $formDataMapper->generateJSON();
    }

    /**
     * dienstverhaeltnisid
     * unternehmen
     * vertragsart_kurzbz
     * gueltigkeit
     */
    private function handleDV(&$dv)
    {
        $dienstverhaeltnisid = $dv['dienstverhaeltnisid'];
    
        if (isset($dienstverhaeltnisid) && intval($dienstverhaeltnisid > 0))
        {
            // DV exists
            $ret = $this->updateDV($dv);
        } else {            
            // DV is new
            $ret = $this->insertDV($dv);
            // write back new id
            $dv['dienstverhaeltnisid'] = $ret['dienstverhaeltnis_id'];
        }
        
    }

    private function handleVBS($vbs)
    {
        $vbsMapper = GUIHandlerFactory::getGUIHandler($vbs['type']);
		$vbsMapper->mapJSON($vbs);
		$vbsData = $vbsMapper->getData();

        // merge GUI-Data with DB-Data
        $vbsInstance = $vbsMapper->generateVertragsbestandteil($vbsData['id']);
        
        // TODO Validate?
        
        // store
        $this->VertragsbestandteilLib->store($vbsInstance);

		// GBS
        /*
		foreach ($vbsMapper->getGbs() as $gbs)
		{
			$gbsData = $gbs->getData();
            $this->handleGBS($gbsData);
		}*/
    }


    // GBS without connection to VBS
    private static function handleGBS($gbs)
    {
        // TODO
    }


    // ------------------------------------
    // DV does not have a dedicated handler

    private function insertDV($dvJSON)
    {
        $now = new DateTime();
        $dvJSON['insertvon'] = $this->userUID;
        $dvJSON['insertamum'] = $now->format(DateTime::ATOM);

        $result = $this->CI->Dienstverhaeltnis_model->insert($dvJSON);

        if (isError($result))
        {
            throw Exception($result->msg);
        }

        $record = $this->CI->Dienstverhaeltnis_model->load($result->retval);

        return $record;
    }

    private function updateDV($dvJSON)
    {
        $now = new DateTime();
        $dvJSON['updatevon'] = getAuthUID();
        $dvJSON['updateamum'] = $now->format(DateTime::ATOM);

        unset($dvJSON['insertamum']);
        unset($dvJSON['insertvon']);
        

        $result = $this->CI->Dienstverhaeltnis_model->update($dvJSON['kontakt_id'], $dvJSON);

        if (isError($result))
        {
            return error($result->msg, EXIT_ERROR);
        }

        $record = $this->CI->Dienstverhaeltnis_model->load($result->retval);

        return $record;
    }

    private function deleteDV($dv_id)
    {
        $result = $this->CI->Dienstverhaeltnis_model->delete($dv_id);

        if (isError($result))
        {
            return error($result->msg, EXIT_ERROR);
        }

        return success($dv_id);
    }

}