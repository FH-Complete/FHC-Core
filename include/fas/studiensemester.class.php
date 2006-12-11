<?php
/**
 * Klasse studiensemester (FAS-Online)
 * @create 15-03-2006
 */
class studiensemester
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var studiensemester Objekt
	
	var $studiensemester_id; // @var integer
	var $aktuell;            // @var boolean
	var $art;                // @var integer ( 1 = Wintersemester, 2 = Sommersemester )
	var $jahr;	            // @var integer
	var $updateamum;         // @var timestamp
	var $updatevon;          // @var string
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur Datenbank
	 *        $stsem_id ID des Studiensemesters das geladen werden soll
	 */
	function studiensemester($conn, $stsem_id=null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		if($stsem_id != null)
			$this->load($stsem_id);
	}
	
	/**
	 * Laedt den Datensatz mit der ID die uebergeben wird
	 * @param stsem_id ID des zu ladenden Datensatzes
	 * @return true wenn  ok, false im fehlerfall;
	 */
	function load($stsem_id)
	{
		//Pruefen ob stsem_id eine gueltige Zahl ist
		if(!is_numeric($stsem_id) || $stsem_id == '')
		{
			$this->errormsg = 'stsem_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//Laden eines Datensatzes
		$qry = "SELECT * FROM studiensemester WHERE studiensemester_pk = '$stsem_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->studiensemester_id = $row->studiensemester_pk;
			$this->aktuell            = ($row->aktuell=='J'?true:false);
			$this->art                = $row->art;
			$this->jahr               = $row->jahr;
			$this->updateamum         = $row->creationdate;
			$this->updatevon          = $row->creationuser;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Laedt das aktuelle Studiensemester
	 * @return true wenn ok, false im Fehlerfall 
	 */
	function load_akt()
	{
		$qry = "SELECT * FROM studiensemester WHERE aktuell='J'";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->studiensemester_id = $row->studiensemester_pk;
			$this->aktuell            = ($row->aktuell=='J'?true:false);
			$this->art                = $row->art;
			$this->jahr               = $row->jahr;
			$this->updateamum         = $row->creationdate;
			$this->updatevon          = $row->creationuser;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Laedt alle studiensemester
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = "SELECT * FROM studiensemester order by jahr, art desc;";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$stsem_obj = new studiensemester($this->conn);
			
			$stsem_obj->studiensemester_id = $row->studiensemester_pk;
			$stsem_obj->aktuell            = ($row->aktuell=='J'?true:false);
			$stsem_obj->art                = $row->art;
			$stsem_obj->jahr               = $row->jahr;
			$stsem_obj->updateamum         = $row->creationdate;
			$stsem_obj->updatevon          = $row->creationuser;
			
			$this->result[] = $stsem_obj;
		}
		return true;
	}
	
	/**
	 * Loescht einen Datensatz
	 * @param $stsem_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($stsem_id)
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
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
}
?>