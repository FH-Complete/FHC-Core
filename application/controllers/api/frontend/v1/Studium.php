<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

class Studium extends FHCAPI_Controller
{
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getStudienAllSemester'=> self::PERM_LOGGED,
			'getStudiengaengeForStudienSemester'=> self::PERM_LOGGED,
			'getStudienplaeneBySemester'=> self::PERM_LOGGED,
			'getLvEvaluierungInfo'=> self::PERM_LOGGED,
		]);

		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('organisation/Studienordnung_model','StudienordnungModel');
		$this->load->model('organisation/Studiensemester_model',"StudiensemesterModel");
		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('codex/Orgform_model','OrgformModel');
		$this->load->model('person/Person_model','PersonModel');

		
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getStudienAllSemester(){

		$parameter_studiensemester = $this->input->get('studiensemester',true);
        $parameter_studiengang = $this->input->get('studiengang',true);
		$parameter_semester = $this->input->get('semester',true);
		$parameter_studienplan = $this->input->get('studienplan',true);

		$aktuelles_studiensemester = current($this->getDataOrTerminateWithError($this->StudiensemesterModel->getAktOrNextSemester()));

		if($this->getDataOrTerminateWithError($this->StudentModel->isStudent(getAuthUID()))){
			$studentLehrverband =$this->StudentlehrverbandModel->loadWhere(["student_uid" => getAuthUID(), "studiensemester_kurzbz" => $aktuelles_studiensemester->studiensemester_kurzbz]);
			$studentLehrverband = current($this->getDataOrTerminateWithError($studentLehrverband));
			
/*			//load later if needed to avoid warning
			$student_studiensemester = $studentLehrverband->studiensemester_kurzbz;
			$student_studiengang = $studentLehrverband->studiengang_kz;
			$student_semester = $studentLehrverband->semester;*/

			$student_studienplan = $this->getStudienPlanFromPrestudentStatus(getAuthPersonId())->studienplan_id;
			
			if(!isset($parameter_studiensemester)) {
				$student_studiensemester = $studentLehrverband->studiensemester_kurzbz;
				$parameter_studiensemester = $student_studiensemester;
			}
			if(!isset($parameter_studiengang)) {
				$student_studiengang = $studentLehrverband->studiengang_kz;
				$parameter_studiengang = $student_studiengang;
			}
			if(!isset($parameter_semester)) {
				$student_semester = $studentLehrverband->semester;
				$parameter_semester = $student_semester;
			}
			if(!isset($parameter_studienplan))
			$parameter_studienplan = $student_studienplan;
		}  

		if(isset($parameter_studiensemester)){
			$parameter_studiensemester = current($this->getDataOrTerminateWithError($this->StudiensemesterModel->loadWhere(["studiensemester_kurzbz" => $parameter_studiensemester])));
		}

		if(isset($parameter_studiengang)){
			$parameter_studiengang = current($this->getDataOrTerminateWithError($this->StudiengangModel->loadWhere(["studiengang_kz" => $parameter_studiengang])));
		}

		if(isset($parameter_studienplan)){
			$this->StudienplanModel->addJoin("lehre.tbl_studienordnung", "studienordnung_id");
			$this->StudienplanModel->addJoin("lehre.tbl_studienplan_semester", "studienplan_id");
			$parameter_studienplan = $this->StudienplanModel->loadWhere(["studienplan_id" => $parameter_studienplan, "aktiv" => TRUE]);
			$parameter_studienplan = current($this->getDataOrTerminateWithError($parameter_studienplan));
		}

		// fetch studiensemester
		$allStudienSemester = $this->getDataOrTerminateWithError($this->StudiensemesterModel->load());
		
		
		if(isset($parameter_studiensemester) && !empty(array_filter($allStudienSemester, function($studiensemester) use($parameter_studiensemester){
			return $studiensemester->studiensemester_kurzbz == $parameter_studiensemester->studiensemester_kurzbz;
		}))){
			$aktuelles_studiensemester = $parameter_studiensemester;
		}
		
		// fetch studiengaenge
		$studiengaenge = $this->computeStudiengaenge($aktuelles_studiensemester->studiensemester_kurzbz);
		$aktuelles_studiengang = current($studiengaenge);
		if(!$aktuelles_studiengang){
			$aktuelles_studiengang = null;
		}
		if(isset($parameter_studiengang) && !empty(array_filter( $studiengaenge,function($studiengang)use($parameter_studiengang){
			return $studiengang->studiengang_kz == $parameter_studiengang->studiengang_kz;
		}))){
			$aktuelles_studiengang = $parameter_studiengang;
		}
		
		// compute semester and studienplaene
		if($aktuelles_studiengang){
			$studienplaene = $this->computeStudienplaene($aktuelles_studiengang->studiengang_kz, $aktuelles_studiensemester->studiensemester_kurzbz);
		}else{
			$studienplaene =[];
		}

		$semester = array_values(array_unique(array_map(function($item){
			return $item->semester;
		}, $studienplaene))); 
		$aktuelles_semester = current($semester);
		if(!$aktuelles_semester){
			$aktuelles_semester = null;
		}
		if(isset($parameter_semester) && in_array($parameter_semester, $semester)){
			$aktuelles_semester = $parameter_semester;
		}

		$semester_studienplan = array_filter($studienplaene, function($item) use($aktuelles_semester){
			return $item->semester == $aktuelles_semester;
		});

		// fetch current studienplan based on semester
		$aktuelles_studienplan = current($semester_studienplan);
		if(!$aktuelles_studienplan){
			$aktuelles_studienplan = null;
		}
		if(isset($parameter_studienplan) && !empty(array_filter( $semester_studienplan, function($studienplan) use($parameter_studienplan){
			return $studienplan->studienplan_id == $parameter_studienplan->studienplan_id;
		}))){
			$aktuelles_studienplan = $parameter_studienplan ;
		}

		// fetch studienplan lehrveranstaltungen
		if($aktuelles_studienplan){
			$lehrveranstaltungen = $this->computeStudienplanLehrveranstaltungen($aktuelles_studienplan->studienplan_id, $aktuelles_semester);
			foreach($lehrveranstaltungen as $lehrv){
				foreach($lehrv->lehrveranstaltungen as $lv){
					$lvLektoren =$this->computeLektorenFromLehrveranstaltung($lv->lehrveranstaltung_id,$aktuelles_semester, $aktuelles_studiengang->studiengang_kz, $aktuelles_studiensemester->studiensemester_kurzbz);
					$lv->lektoren = $lvLektoren;
				}
			
			}
			$aktuelles_lehrveranstaltungen = $lehrveranstaltungen;
		}else{
			$aktuelles_lehrveranstaltungen = [];
		}

		// result object
		$result = new stdClass();
		$result->studienSemester = [];
		$result->studienSemester["all"]= $allStudienSemester;
		$result->studienSemester["preselected"]=$aktuelles_studiensemester;
		$result->studiengang["all"]=$studiengaenge;
		$result->studiengang["preselected"]=$aktuelles_studiengang;
		$result->semester["all"] =$semester;
		$result->semester["preselected"] =$aktuelles_semester;
		$result->studienplan["all"]=$semester_studienplan;
		$result->studienplan["preselected"]=$aktuelles_studienplan; 
		$result->lehrveranstaltungen=$aktuelles_lehrveranstaltungen;  
		
		
		$this->terminateWithSuccess($result);
	}

	public function getLvEvaluierungInfo($studiensemester_kurzbz, $lehrveranstaltung_id){
		$result = [];
		Events::trigger('lvEvaluierungsInfo', function & () use (&$result) {
			return $result;
		},$lehrveranstaltung_id, $studiensemester_kurzbz);
		$this->terminateWithSuccess($result);
	}

	public function getStudiengaengeForStudienSemester($studiensemester){
		$studiengaenge = $this->computeStudiengaenge($studiensemester);
		$this->terminateWithSuccess($studiengaenge);
	}

	public function getStudienplaeneBySemester(){
		$this->load->library('form_validation');
        $this->form_validation->set_data($this->input->get());
		$this->form_validation->set_rules('studiengang', 'studiengang', 'required');
        $this->form_validation->set_rules('studiensemester', 'studiensemester', 'required');
        if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());
 
        $studiengang = $this->input->get('studiengang',true);
        $studiensemester = $this->input->get('studiensemester',true);
        $studienplaene = $this->computeStudienplaene($studiengang, $studiensemester);
		$this->terminateWithSuccess($studienplaene);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	private function computeStudienplaene($studiengang, $studiensemester){
		$studienplaene = $this->StudienplanModel->getStudienplaeneBySemester($studiengang, $studiensemester);
		$studienplaene = $this->getDataOrTerminateWithError($studienplaene);
		$studienplaene = array_map(function($studienplan){
			$orgform = current($this->getDataOrTerminateWithError($this->OrgformModel->loadWhere(["orgform_kurzbz" => $studienplan->orgform_kurzbz])));
			$studienplan->orgform_bezeichnung = $orgform->bezeichnung;
			return $studienplan;
		},$studienplaene);
		return $studienplaene;
	}

	private function computeStudienplanLehrveranstaltungen($studienplan_id, $semester){

/* 
SELECT tbl_lehrveranstaltung.*,
            tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id,
            tbl_studienplan_lehrveranstaltung.semester as stpllv_semester,
            tbl_studienplan_lehrveranstaltung.pflicht as stpllv_pflicht,
            tbl_studienplan_lehrveranstaltung.koordinator as stpllv_koordinator,
            tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent,
            tbl_studienplan_lehrveranstaltung.sort stpllv_sort,
            tbl_studienplan_lehrveranstaltung.curriculum,
            tbl_studienplan_lehrveranstaltung.export,
            tbl_studienplan_lehrveranstaltung.genehmigung
        FROM lehre.tbl_lehrveranstaltung
        JOIN lehre.tbl_studienplan_lehrveranstaltung
        USING(lehrveranstaltung_id)
        WHERE tbl_studienplan_lehrveranstaltung.studienplan_id=" . $this->db_add_param($studienplan_id, FHC_INTEGER);
        if (defined("CIS_PROFIL_STUDIENPLAN_MODULE_AUSBLENDEN") && CIS_PROFIL_STUDIENPLAN_MODULE_AUSBLENDEN)
            $qry .= " AND tbl_lehrveranstaltung.lehrtyp_kurzbz != 'modul'";
        if (!is_null($semester))
        {
            $qry.=" AND tbl_studienplan_lehrveranstaltung.semester=" . $this->db_add_param($semester, FHC_INTEGER);
        } */
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		
		$query = "
		SELECT tbl_lehrveranstaltung.*,
            tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id,
            tbl_studienplan_lehrveranstaltung.semester as stpllv_semester,
            tbl_studienplan_lehrveranstaltung.pflicht as stpllv_pflicht,
            tbl_studienplan_lehrveranstaltung.koordinator as stpllv_koordinator,
            tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent,
            tbl_studienplan_lehrveranstaltung.sort stpllv_sort,
            tbl_studienplan_lehrveranstaltung.curriculum,
            tbl_studienplan_lehrveranstaltung.export,
            tbl_studienplan_lehrveranstaltung.genehmigung
        FROM lehre.tbl_lehrveranstaltung
        JOIN lehre.tbl_studienplan_lehrveranstaltung
        USING(lehrveranstaltung_id)
        WHERE 
			tbl_lehrveranstaltung.lehre = true AND
			tbl_studienplan_lehrveranstaltung.studienplan_id=? AND tbl_studienplan_lehrveranstaltung.semester=?";
		
        if (defined("CIS_PROFIL_STUDIENPLAN_MODULE_AUSBLENDEN") && CIS_PROFIL_STUDIENPLAN_MODULE_AUSBLENDEN)
			$query .= " AND tbl_lehrveranstaltung.lehrtyp_kurzbz != 'modul'";
		
		$lehrveranstaltungen = $this->LehrveranstaltungModel->execReadOnlyQuery($query,[$studienplan_id, $semester]);
		
		$lehrveranstaltungen = $this->getDataOrTerminateWithError($lehrveranstaltungen);
		usort($lehrveranstaltungen, function($a, $b){
			if($a->lehrtyp_kurzbz == "modul"){
				return -1;
			}
			else if($b->lehrtyp_kurzbz == "modul"){
				return 1;
			}
			return 0;
		});
		$lehrveranstaltungen= array_reduce($lehrveranstaltungen,function($carry, $lehrv){
			if($lehrv->lehrtyp_kurzbz == "modul"){
				$lehrv->lehrveranstaltungen = [];
				array_push($carry, $lehrv);
			}
			else{
				$parent =array_filter($carry, function($item)use($lehrv){
					return $item->studienplan_lehrveranstaltung_id == $lehrv->studienplan_lehrveranstaltung_id_parent;
				});
				$parent = current($parent);
				if($parent){
					$parent->lehrveranstaltungen[] = $lehrv;
				}
			}
			return $carry;
		}, []);
		return $lehrveranstaltungen;
	}

	private function computeStudiengaenge($studiensemester){
		$studiengang_studiensemester_result = $this->StudiengangModel->getStudiengaengeByStudiensemester($studiensemester);
		$studiengang_studiensemester_result = $this->getDataOrTerminateWithError($studiengang_studiensemester_result);
		return $studiengang_studiensemester_result;
	}

	private function getStudienPlanFromPrestudentStatus($person_id){
			$studienplan_id = current($this->getDataOrTerminateWithError($this->PrestudentstatusModel->getLastStatusPerson($person_id)))->studienplan_id;
			$studienplan =current($this->getDataOrTerminateWithError($this->StudienplanModel->loadWhere(["studienplan_id"=>$studienplan_id])));
			return $studienplan;
	}

	private function computeLektorenFromLehrveranstaltung($lehreinheit_id, $semester, $studiengang, $studiensemester){
		$this->load->library('StundenplanLib');
		$lektoren = $this->stundenplanlib->getLektorenFromLehrveranstaltung($lehreinheit_id,$semester, $studiengang,$studiensemester);
		$lektoren = $this->getDataOrTerminateWithError($lektoren) ?? [];
		
		$lektoren = array_map(function($lektor){
			return ["name"=>$this->getDataOrTerminateWithError($this->PersonModel->getFullName($lektor)), "email"=>$lektor."@".DOMAIN];
		},$lektoren);
		
		return $lektoren;
	}


	
	
}

