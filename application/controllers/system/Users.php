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
		
		// Gruppen
		$this->load->model('organisation/Gruppe_model', 'GruppeModel');
		$this->GruppeModel->addOrder('beschreibung');
		$gruppen = $this->GruppeModel->loadWhere(array('aktiv' => true, 'aufnahmegruppe' => true));
		if (hasData($gruppen))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->gruppe_kurzbz = '-1';
			$emptyElement->beschreibung = 'Select a group...';
			array_unshift($gruppen->retval, $emptyElement);
		}
		else
		{
			show_error($gruppen->retval);
		}
		
		// Stufe
		$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
		$this->ReihungstestModel->addSelect('DISTINCT ON(stufe) stufe, stufe AS beschreibung');
		$this->ReihungstestModel->addOrder('stufe');
		$stufen = $this->ReihungstestModel->loadWhere('stufe IS NOT NULL');
		if (hasData($stufen))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->stufe = '-1';
			$emptyElement->beschreibung = 'Select a stufe...';
			array_unshift($stufen->retval, $emptyElement);
		}
		else
		{
			show_error($stufen->retval);
		}
		
		if ($returnUsers == null || isSuccess($returnUsers))
		{
			$viewData = array(
				'studiengang' => $studiengang,
				'studiensemester' => $studiensemester,
				'gruppe' => $gruppe,
				'reihungstest' => $reihungstest,
				'stufe' => $stufe,
				'users' => $users,
				'gruppen' => $gruppen->retval,
				'stufen' => $stufen->retval
			);
			
			$this->load->view('system/users', $viewData);
		}
		else if (isError($returnUsers))
		{
			show_error($returnUsers->retval);
		}
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
			$href = str_replace("/system/Users/linkToStufe", "/system/Users", $_SERVER["REQUEST_URI"]);
			echo "<div>Data correctly saved - <a href=\"" . $href . "\">Back</a></div>";
        }
        else
        {
			echo "<div>Error occurred while saving data, please contact the administrator.</div>";
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
			$href = str_replace("/system/Users/linkToAufnahmegruppe", "/system/Users", $_SERVER["REQUEST_URI"]);
			echo "<div>Data correctly saved - <a href=\"" . $href . "\">Back</a></div>";
        }
        else
        {
			echo "<div>Error occurred while saving data, please contact the administrator.</div>";
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
			s.bezeichnung AS studienplan,
			ps.rt_stufe,
			aufnahmegruppe_kurzbz,
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