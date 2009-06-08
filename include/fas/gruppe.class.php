<?php
/**
 * Klasse gruppe (FAS-Online)
 * @create 15-03-2006
 */
class gruppe
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var gruppe Objekt
	
	var $ausbildungssemester_id; // @var integer
	var $gruppe_id;              // @var integer
	var $name;                   // @var string
	var $nummerintern;           // @var integer
	var $obergruppe_id;          // @var integer
	var $ordnung;                // @var integer
	var $studiengang_id;         // @var integer
	var $typ;                    // @var integer ( Ebene ??)
	var $updateamum;             // @var timestamp
	var $updatevon=0;            // @var string
	var $fullname;               // @var string

	/**
	 * Konstruktor
	 * @param $conn Connection zur Datenbank
	 *        $gruppe_id ID der zu ladenden Gruppe
	 */
	function gruppe($conn, $gruppe_id=null)
	{
		$this->conn = $conn;
		/*
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		*/
		if($gruppe_id != null)
			$this->load($gruppe_id);
	}
	
	/**
	 * Laedt eine Gruppe
	 * @param gruppe_id ID der Gruppe
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($gruppe_id)
	{
		//gruppe_id auf gueltigkeit pruefen
		if(!is_numeric($gruppe_id) || $gruppe_id =='')
		{
			$this->errormsg = 'gruppe_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM gruppe WHERE gruppe_pk='$gruppe_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->ausbildungssemester_id = $row->ausbildungssemester_fk;
			$this->gruppe_id              = $row->gruppe_pk;
			$this->name                   = $row->name;
			$this->nummerintern           = $row->nummerintern;
			$this->obergruppe_id          = $row->obergruppe_fk;
			$this->ordnung                = $row->ordnung;
			$this->studiengang_id         = $row->studiengang_fk;
			$this->typ                    = $row->typ;
			$this->updateamum             = $row->creationdate;
			$this->updatevon              = $row->creationuser;
			
			$this->fullname = $this->getFullName($row->gruppe_pk);
		}
		else 
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		return true;		
	}
	
	/**
	 * Liefert den vollen namen einer Gruppe
	 * @param $gruppe_id
	 * @return voller name, false im Fehlerfall
	 */
	function getFullName($gruppe_id)
	{
		//gruppe_id auf gueltigkeit pruefen
		if(!is_numeric($gruppe_id) || $gruppe_id == '')
		{
			$this->errormsg = 'gruppe_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//gesamten gruppennamen ermitteln
		$qry = "SELECT fas_function_get_fullname_from_gruppe($gruppe_id) as fullname;";
	
		if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
		{
			$this->errormsg = 'Gruppenname konnte nicht ermittelt werden';
			return false;
		}
		
		return $row->fullname;
	}
	
	/**
	 * Laedt alle Gruppen eines Studienganges/studiensemesters/ausbildungssemesters
	 * @param studiengang_id ID des studienganges
	 *        studiensemester_id ID des Studiensemesters (optional)
	 *        ausbildungssemester_id ID des Ausbildungssemesters (optional)
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_gruppen($studiengang_id, $studiensemester_id=null, $ausbildungssemester_id=null)
	{
		//Pruefen ob gueltige Werte uebergeben wurden
		if(!is_numeric($studiengang_id) || $studiengang_id == '')
		{
			$this->errormsg = 'studiengang_id muss eine gueltige Zahl sein';
			return false;
		}
		
		if($studiensemester_id!=null && (!is_numeric($studiensemester_id) || $studiensemester_id == ''))
		{
			$this->errormsg = 'studiensemester_id muss eine gueltige Zahl sein';
			return false;
		}
		
		if($ausbildungssemester_id!=null && (!is_numeric($ausbildungssemester_id) || $ausbildungssemester_id == ''))
		{
			$this->errormsg = 'ausbildungssemester_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//Befehl zusammenbauen
		$qry = "SELECT * FROM gruppe WHERE studiengang_fk='$studiengang_id' ";
		
		if($ausbildungssemester_id!=null)
			$qry .= "AND ausbildungssemester_fk='$ausbildungssemester_id' ";
		
		if($studiensemester_id != null)
			$qry .= "AND studiensemester_fk='$studiensemester_id' ";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		//Daten laden
		while($row = pg_fetch_object($res))
		{
			$grp_obj = new gruppe($this->conn);
			
			$grp_obj->ausbildungssemester_id = $row->ausbildungssemester_fk;
			$grp_obj->gruppe_id              = $row->gruppe_pk;
			$grp_obj->name                   = $row->name;
			$grp_obj->nummerintern           = $row->nummerintern;
			$grp_obj->obergruppe_id          = $row->obergruppe_fk;
			$grp_obj->ordnung                = $row->ordnung;
			$grp_obj->studiengang_id         = $row->studiengang_fk;
			$grp_obj->typ                    = $row->typ;
			$grp_obj->updateamum             = $row->creationdate;
			$grp_obj->updatevon              = $row->creationuser;
			
			$grp_obj->fullname = $this->getFullName($row->gruppe_pk);
			
			$this->result[] = $grp_obj;
		}
				
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die DB
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	
	/**
	 * Loescht einen Datensatz
	 * @param $gruppe_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($gruppe_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
}
?>