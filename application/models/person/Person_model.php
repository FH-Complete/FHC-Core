<?php

class Person_model extends DB_Model
{
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_person';
		$this->pk = 'person_id';
	}
        
         public function getFields()
        {
            $fields =  array(
                   //Tabellenspalten
                "person_id" => NULL,
                "sprache" => NULL,
                "anrede" => NULL,
                "titelpost" => NULL,
                "titelpre" => NULL,
                "nachname" => NULL,
                "vorname" => NULL,
                "vornamen" => NULL,
                "gebdatum" => NULL,
                "gebort" => NULL,
                "gebzeit" => NULL,
                "foto" => NULL,
                "anmerkungen" => NULL,
                "homepage" => NULL,
                "svnr" => NULL,
                "ersatzkennzeichen" => NULL,
                "familienstand" => NULL,
                "anzahlkinder" => NULL,
                "aktiv"=>TRUE,
                "insertamum" => NULL,
                "insertvon" => NULL,
                "updateamum" => NULL,
                "updatevon" => NULL,
                "geschlecht" => "u",
                "staatsbuergerschaft" => NULL,
                "geburtsnation"	=> NULL,
                "ext_id" => NULL,
                "kurzbeschreibung"> NULL,
                "zugangscode" => NULL,
                "foto_sperre" => FALSE,
                "matr_nr"=> NULL
            );
            
            return $this->_success($fields);
        }

	/**
	 * 
	 */
	public function checkBewerbung($email, $studiensemester_kurzbz = NULL)
	{
		$this->db->distinct();

		if(is_null($studiensemester_kurzbz))
		{
			$this->db->select("p.person_id, p.zugangscode, p.insertamum")
					->from("public.tbl_person p")
					->join("public.tbl_kontakt k", "p.person_id=k.person_id")
					->join("public.tbl_benutzer b", "p.person_id=b.person_id", "left")
					->where("k.kontakttyp", 'email')
					->where("(kontakt='" . $email . "'" .
							" OR alias ||'@technikum-wien.at'='" . $email . "'" .
							" OR uid ||'@technikum-wien.at'='" . $email . "')")
					->order_by("p.insertamum", "DESC")
					->limit(1)
			;
		}
		else
		{
			$this->db->select("p.person_id,p.zugangscode,p.insertamum")
					->from("public.tbl_person p")
					->join("public.tbl_kontakt k", "p.person_id=k.person_id")
					->join("public.tbl_benutzer b", "p.person_id=b.person_id", "left")
					->join("public.tbl_prestudent ps", "p.person_id=ps.person_id")
					->join("public.tbl_prestudentstatus pst", "pst.prestudent_id=ps.prestudent_id")
					->where("k.kontakttyp", 'email')
					->where("(kontakt='" . $email . "'" .
							" OR alias ||'@technikum-wien.at'='" . $email . "'" .
							" OR uid ||'@technikum-wien.at'='" . $email . "')")
					->where("studiensemester_kurzbz='" . $studiensemester_kurzbz . "'")
					->order_by("p.insertamum", "DESC")
					->limit(1)
			;
		}
		return $this->db->get()->result_array();
	}
}