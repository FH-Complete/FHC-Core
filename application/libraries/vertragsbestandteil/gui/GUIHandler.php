<?php


require_once __DIR__ . '/FormData.php';
require_once __DIR__ . '/GUIHandlerFactory.php';
require_once __DIR__ . '/../../../models/vertragsbestandteil/Dienstverhaeltnis_model.php';
require_once __DIR__ . '/../VertragsbestandteilLib.php';
require_once __DIR__ . '/util.php';

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
        $this->CI->load->library('vertragsbestandteil/VertragsbestandteilLib', 
            null, 'VertragsbestandteilLib');
		

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
        $res = $this->handleDV($dvData);

        if ($res === false)
        {
            // TODO write error message
        } else {

		    // VBS
		    $vbsList = $formDataMapper->getVbs();

            foreach ($vbsList as $vbsID => $vbs) {
                $this->handleVBS($dvData['dienstverhaeltnisid'] ,$vbs);
            }
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
            $res = $this->updateDV($dv);
            if (isSuccess($res))
            {
                return true;
            }
        } else {            
            // DV is new
            $res = $this->insertDV($dv);
            if (isSuccess($res))
            {
                // write back new id
                $dv['dienstverhaeltnisid'] = $res->retval[0]->dienstverhaeltnis_id;
                return true;
            } 
            
        }

        return false;
        
    }

    private function handleVBS($dienstverhaeltnis_id, $vbs)
    {
        /**  @var GUIVertragsbestandteilFunktion */
        $vbsMapper = GUIHandlerFactory::getGUIHandler($vbs['type']);
		$vbsMapper->mapJSON($vbs);
		$vbsData = $vbsMapper->getData();

        // merge GUI-Data with DB-Data
        $vbsInstance = $vbsMapper->generateVertragsbestandteil(isset($vbsData['id'])?$vbsData['id']:null);
        if ($vbsInstance->getDienstverhaeltnis_id() === null) 
        {
            $vbsInstance->setDienstverhaeltnis_id($dienstverhaeltnis_id);
            $vbsInstance->setInsertvon($this->userUID);
            $vbsInstance->setInsertamum((new DateTime())->format("Y-m-d h:m:s"));
        } else {
            $vbsInstance->setUpdatevon($this->userUID);
            $vbsInstance->setUpdateamum((new DateTime())->format("Y-m-d h:m:s"));
        }
        
        // TODO Validate?
        
        // store
         $this->CI->VertragsbestandteilLib->storeVertragsbestandteil($vbsInstance);

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
        $dvJSON['mitarbeiter_uid'] = $this->employeeUID;
        $now = new DateTime();
        $dvJSON['von'] = string2Date($dvJSON['gueltigkeit']->getData()['gueltig_ab']);
        $dvJSON['bis'] = string2Date($dvJSON['gueltigkeit']->getData()['gueltig_bis']);
        $dvJSON['oe_kurzbz'] = $dvJSON['unternehmen'];
        $dvJSON['insertvon'] = $this->userUID;
        $dvJSON['insertamum'] = $now->format(DateTime::ATOM);

        unset($dvJSON['dienstverhaeltnisid']);
        unset($dvJSON['children']);
        unset($dvJSON['gueltigkeit']);
        unset($dvJSON['unternehmen']);

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
        $dvJSON['mitarbeiter_uid'] = $this->employeeUID;
        $dvJSON['von'] = string2Date($dvJSON['gueltigkeit']->getData()['gueltig_ab']);
        $dvJSON['bis'] = string2Date($dvJSON['gueltigkeit']->getData()['gueltig_bis']);
        $dvJSON['oe_kurzbz'] = $dvJSON['unternehmen'];
        $now = new DateTime();
        $dvJSON['updatevon'] = $this->userUID;
        $dvJSON['updateamum'] = $now->format(DateTime::ATOM);
        $dvJSON['dienstverhaeltnis_id'] = $dvJSON['dienstverhaeltnisid'];

        unset($dvJSON['insertamum']);
        unset($dvJSON['insertvon']);
        unset($dvJSON['dienstverhaeltnisid']);
        unset($dvJSON['children']);
        unset($dvJSON['gueltigkeit']);
        unset($dvJSON['unternehmen']);
        
        $result = $this->CI->Dienstverhaeltnis_model->update($dvJSON);
        //$result = $this->CI->Dienstverhaeltnis_model->update($dvJSON['kontakt_id'], $dvJSON);

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