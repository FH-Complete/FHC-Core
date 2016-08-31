<?php

class Studienplan_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "lehre.tbl_studienplan";
		$this->pk = "studienplan_id";
	}
	
	public function getStudienplaene($studiengang_kz)
	{
		$this->addJoin("lehre.tbl_studienordnung", "studienordnung_id");
		
		return $this->loadWhere(array("studiengang_kz" => $studiengang_kz));
	}
	
	public function getStudienplaeneBySemester($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester = null, $orgform_kurzbz = null)
	{
		$this->addJoin("lehre.tbl_studienordnung", "studienordnung_id");
		$this->addJoin("lehre.tbl_studienplan_semester", "studienplan_id");
		
		$whereArray = array(
			"tbl_studienplan.aktiv" => "TRUE",
			"tbl_studienordnung.studiengang_kz" => $studiengang_kz,
			"tbl_studienplan_semester.studiensemester_kurzbz" => $studiensemester_kurzbz
		);
		
		if(!is_null($ausbildungssemester))
		{
			$whereArray["tbl_studienplan_semester.semester"] = $ausbildungssemester;
		}
		
		if(!is_null($orgform_kurzbz))
		{
			$whereArray["orgform_kurzbz"] = $orgform_kurzbz;
		}
		
		return $this->StudienplanModel->loadWhere($whereArray);
	}
}