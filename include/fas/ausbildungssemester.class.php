<?php
/**
 * Klasse ausbildungssemester (FAS-Online)
 * @create 15-03-2006
 */
class ausbildungssemester
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var ausbildungssemester Objekt
	
	var $ausbildungssemester_id; // @var integer
	var $studiengang_id;         // @var integer
	var $name;                   // @var string
	var $personenstatus;         // @var integer
	var $semester;               // @var integer
	var $updatevon=0;            // @var timestamp
	var $updateamum;             // @var string

	/**
	 * Konstruktor
	 * @param $conn Connection zur Datenbank
	 *        $ausbildungssemester_id ID des zu ladenden Datensatzes
	 */
	function ausbildungssemester($conn, $ausbildungssemester_id=null)
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
		if($ausbildungssemester_id != null)
			$this->load($ausbildungssemester_id);
	}
	
	/**
	 * Laedt einen Datensatz aus der Datenbank
	 * @param $ausbildungssemester_id ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($ausbildungssemester_id)
	{
		if(!is_numeric($ausbildungssemester_id) || $ausbildungssemester_id == '')
		{
			$this->errormsg = 'ausbildungssemester_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM ausbildungssemester WHERE ausbildungssemester_pk = '$ausbildungssemester_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		if($row=pg_fetch_object($res))
		{
			$this->ausbildungssemester_id = $row->ausbildungssemester_pk;
			$this->studiengang_id         = $row->studiengang_fk;
			$this->semester               = $row->semester;
			$this->name                   = $row->name;
			$this->personenstatus         = $row->personenstatus;
			$this->updateamum             = $row->creationdate;
			$this->updatevon              = $row->creationuser;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Liefert alle ausbildungssemester zu einem Studiengang
	 * @param $studiengang_id Studiengang_id des Ausbildungssemesters
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_stg($studiengang_id)
	{
		if(!is_numeric($studiengang_id) || $studiengang_id == '')
		{
			$this->errormsg = 'studiengang_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM ausbildungssemester WHERE studiengang_fk = '$studiengang_id' order by semester";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$ausb_obj = new ausbildungssemester($this->conn);
			
			$ausb_obj->ausbildungssemester_id = $row->ausbildungssemester_pk;
			$ausb_obj->studiengang_id         = $row->studiengang_fk;
			$ausb_obj->semester               = $row->semester;
			$ausb_obj->name                   = $row->name;
			$ausb_obj->personenstatus         = $row->personenstatus;
			$ausb_obj->updateamum             = $row->creationdate;
			$ausb_obj->updatevon              = $row->creationuser;
			
			$this->result[] = $ausb_obj;
		}
		return true;
	}
	
	/**
	 * Liefert alle Ausbildungssemester
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = "SELECT * FROM ausbildungssemester;";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$ausb_obj = new ausbildungssemester($this->conn);
			
			$ausb_obj->ausbildungssemester_id = $row->ausbildungssemester_pk;
			$ausb_obj->studiengang_id         = $row->studiengang_fk;
			$ausb_obj->semester               = $row->semester;
			$ausb_obj->name                   = $row->name;
			$ausb_obj->personenstatus         = $row->personenstatus;
			$ausb_obj->updateamum             = $row->creationdate;
			$ausb_obj->updatevon              = $row->creationuser;
			
			$this->result[] = $ausb_obj;
		}
		return true;
	}
	
	/**
	 * Checkt die Variablen vor dem Speichern
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		if(!checkvars())
			return false;
			
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	
	/**
	 * Loescht den Datensatz mit der ID die uebergeben wurde
	 * @param $ausbildungssemester_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($ausbildungssemester_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
}
?>