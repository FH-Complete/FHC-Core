<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends VileSci_Controller
{
	public function __construct()
    {
        parent::__construct();
        
        // Loads helper message to manage returning messages
		$this->load->helper('message');
		
        // Load the library to use the widgets
        $this->load->library('TemplateLib');
    }
    
    public function index()
	{
		$studiengang = $this->input->post('studiengang');
		$studiensemester = $this->input->post('studiensemester');
		$gruppe = $this->input->post('gruppe');
		$reihungstest = $this->input->post('reihungstest');
		$stufe = $this->input->post('stufe');
		
		$returnUsers = null;
		if ($studiengang != null || $studiensemester != null || $gruppe!= null
			|| $reihungstest != null || $stufe != null)
		{
			$returnUsers = $this->_getUsers($studiengang, $studiensemester, $gruppe, $reihungstest, $stufe);
		}
		
		$users = null;
		if (hasData($returnUsers))
		{
			$users = $returnUsers->retval;
		}
		
		$viewData = array(
			'studiengang' => $studiengang,
			'studiensemester' => $studiensemester,
			'gruppe' => $gruppe,
			'reihungstest' => $reihungstest,
			'stufe' => $stufe,
			'users' => $users
		);
		
		$this->load->view('system/users', $viewData);
	}
	
	public function linkToStufe()
	{
		$prestudentIdArray = $this->input->post('prestudent_id');
		$stufe = $this->input->post('stufe');
		
		// Load model PrestudentstatusModel
        $this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
        
        $result = $this->PrestudentstatusModel->updateStufe($prestudentIdArray, $stufe);
        
        if (isSuccess($result))
        {
			echo "Tutto ok!";
        }
        else
        {
			echo "Kaputt!";
        }
	}
	
	public function linkToAufnahmegruppe()
	{
		$prestudentIdArray = $this->input->post('prestudent_id');
		$aufnahmegruppe = $this->input->post('aufnahmegruppe');
		
		// Load model PrestudentstatusModel
        $this->load->model('crm/Prestudent_model', 'PrestudentModel');
        
        $result = $this->PrestudentModel->updateAufnahmegruppe($prestudentIdArray, $aufnahmegruppe);
        
        if (isSuccess($result))
        {
			echo "Tutto ok!";
        }
        else
        {
			echo "Kaputt!";
        }
	}
	
	private function _getUsers($studiengang, $studiensemester, $gruppe, $reihungstest, $stufe)
	{
		// Load model prestudentm_model
        $this->load->model('crm/Prestudent_model', 'PrestudentModel');
        
		$this->PrestudentModel->addSelect(
			'DISTINCT ON(p.person_id, prestudent_id) p.person_id,
			prestudent_id,
			p.nachname,
			p.vorname,
			p.geschlecht,
			p.gebdatum,
			k.kontakt AS email,
			sg.kurzbzlang,
			sg.bezeichnung,
			sg.orgform_kurzbz,
			sgt.bezeichnung AS typ,
			ps.rt_stufe,
			rtp.punkte'
		);
		
		$this->PrestudentModel->addJoin('public.tbl_rt_person rtp', 'person_id');
		$this->PrestudentModel->addJoin('public.tbl_person p', 'person_id', 'LEFT');
		$this->PrestudentModel->addJoin(
			'(
					SELECT person_id,
						   kontakt
					  FROM public.tbl_kontakt
					 WHERE zustellung = TRUE
					   AND kontakttyp = \'email\'
				  ORDER BY kontakt_id DESC
			) k',
			'person_id',
			'LEFT'
		);
		$this->PrestudentModel->addJoin('public.tbl_prestudentstatus ps', 'prestudent_id');
		$this->PrestudentModel->addJoin('lehre.tbl_studienplan s', 's.studienplan_id = ps.studienplan_id');
		$this->PrestudentModel->addJoin('lehre.tbl_studienordnung so', 'studienordnung_id');
		$this->PrestudentModel->addJoin('public.tbl_studiengang sg', 'sg.studiengang_kz = so.studiengang_kz');
		$this->PrestudentModel->addJoin('public.tbl_studiengangstyp sgt', 'typ');
		
		$this->PrestudentModel->addOrder('p.person_id', 'ASC');
		$this->PrestudentModel->addOrder('prestudent_id', 'ASC');
		
		$parametersArray = array('p.aktiv' => true, 'ps.status_kurzbz' => 'Interessent');
		
		if ($studiengang != null && $studiengang != '-1')
		{
			$parametersArray['sg.studiengang_kz'] = $studiengang;
		}
		
		if ($studiensemester != null && $studiensemester != '-1')
		{
			$parametersArray['ps.studiensemester_kurzbz'] = $studiensemester;
		}
		
		if ($gruppe != null && $gruppe != '-1')
		{
			$parametersArray['aufnahmegruppe_kurzbz'] = $gruppe;
		}
		
		if ($reihungstest != null && $reihungstest != '-1')
		{
			$parametersArray['rtp.rt_id'] = $reihungstest;
		}
		
		if ($stufe != null && $stufe != '-1')
		{
			$parametersArray['ps.rt_stufe'] = $stufe;
		}
		
		return $this->PrestudentModel->loadWhere($parametersArray);
	}
}