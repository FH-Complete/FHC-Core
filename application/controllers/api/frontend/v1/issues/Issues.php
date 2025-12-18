<?php

defined('BASEPATH') || exit('No direct script access allowed');


class Issues extends FHCAPI_Controller
{
	const DEFAULT_PERMISSION = 'system/issues_verwalten:r';
	// code igniter 
	protected $CI;

	public function __construct() {
		
		parent::__construct(
			array(
				'getOpenIssuesByProperties' => Self::DEFAULT_PERMISSION
			)
		);

		// Loads authentication library and starts authenticationfetc
		$this->load->library('AuthLib');

		$this->load->model('extensions/FHC-Core-Personalverwaltung/Api_model','ApiModel');
		$this->load->model('person/Person_model','PersonModel');
		$this->load->model('system/Fehler_model','FehlerModel');
		$this->load->model('system/Issue_model', 'IssueModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		
		// get CI for transaction management
		$this->CI = &get_instance();
	}

	public function getOpenIssuesByProperties()
	{
		$person_id = $this->input->get('person_id', true);
		$oe_kurzbz = $this->input->get('oe_kurzbz', true);
		$fehlertyp_kurzbz = $this->input->get('fehlertyp_kurzbz', true);
		$apps = $this->input->get('apps', true);
		$behebung_parameter = $this->input->get('behebung_parameter', true);
		$hauptzustaendig = filter_var($this->input->get('hauptzustaendig', true), FILTER_VALIDATE_BOOLEAN);

		if (isset($person_id) && !is_numeric($person_id))
			$this->terminateWithError('person id is not numeric!');

		if (isset($behebung_parameter) && !is_array($behebung_parameter))
			$this->terminateWithError('Behebung parameter invalid');

			$this->addMeta("vorher", $hauptzustaendig);

		$issueRes = $this->IssueModel->getOpenIssuesByProperties(
			$person_id,
			$oe_kurzbz,
			$fehlertyp_kurzbz,
			$apps,
			$behebung_parameter,
			$hauptzustaendig
		);

		if (isError($issueRes))
		{
			$this->terminateWithError(getError($issueRes));
		}

		$this->terminateWithSuccess(hasData($issueRes) ? getData($issueRes) : []);
	}

	public function PersonenMitOffenenIssues()
	{
		
		$sql = <<<EOSQL
SELECT

		person_id, uid, vorname, nachname, count(*) AS openissues ,
		(select count(*) anz_aktiv 
		 from hr.tbl_dienstverhaeltnis dv 
		 where dv.mitarbeiter_uid=uid and dv.von<=now() and 
		       (dv.bis is null OR dv.bis>=now())
		) aktiv
FROM 
		system.tbl_issue 
JOIN 
		system.tbl_fehler USING (fehlercode) 
JOIN 
		public.tbl_person USING (person_id) 
JOIN 
		public.tbl_benutzer USING (person_id) 
JOIN 
		public.tbl_mitarbeiter ON uid = mitarbeiter_uid                         
WHERE 
		app = 'personalverwaltung' AND verarbeitetamum IS NULL 
GROUP BY 
		person_id, uid, vorname, nachname 
HAVING 
		count(*) > 0 
ORDER BY 
		count(*) DESC;
			
EOSQL;
		
		$personenmitissues = $this->IssueModel->execReadOnlyQuery($sql);
		if( hasData($personenmitissues) ) 
		{
			$this->terminateWithSuccess(getData($personenmitissues));
		}
		else
		{
			$this->terminateWithSuccess(array());
		}
	}
}