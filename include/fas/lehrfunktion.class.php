<?php
/**
 * Klasse lehrfunktion (FAS-Online)
 * @create 14-03-2006
 */
class lehrfunktion
{
	var $conn;    // @var resource DB-Connection
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var lehrfunktion Objekt
		
	var $lehrfunktion_id; // @var integer
	var $bezeichnung;     // @var string
	var $standardfaktor;  // @var float
	var $updateamum;      // @var timestamp
	var $updatevon=0;       // @var string
	
	/**
	 * Konstruktor
	 * @param conn Connection zur DB
	 *        lehrfkt_id ID der zu ladenden lehrfunktion
	 */
	function lehrfunktion($conn, $lehrfkt_id=null)
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
		if($lehrfkt_id != null)
			$this->load($lehrfkt_id);
	}
	
	/**
	 * Laedt eine Lehrfunktion
	 * @param lehrfkt_id ID des Datensatzes der zu laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($lehrfkt_id)
	{
		if(!is_numeric($lehrfkt_id) || $lehrfkt_id == '')
		{
			$this->errormsg = 'lehrfunktion_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM lehrfunktion WHERE lehrfunktion_pk = '$lehrfkt_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->lehrfunktion_id = $row->lehrfunktion_pk;
			$this->bezeichnung     = $row->bezeichnung;
			$this->standardfaktor  = $row->standardfaktor;
			$this->updateamum      = $row->creationdate;
			$this->updatevon       = $row->creationuser;
		}
		else 
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		return true;
	}
	
	/**
	 * Laedt alle Lehrfunktionen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = "SELECT * FROM lehrfunktion;";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden der Datensaetze';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$lehrfkt_obj = new lehrfunktion($this->conn);
			
			$lehrfkt_obj->lehrfunktion_id = $row->lehrfunktion_id;
			$lehrfkt_obj->bezeichnung     = $row->bezeichnung;
			$lehrfkt_obj->standardfaktor  = $row->standardfaktor;
			$lehrfkt_obj->updateamum      = $row->creationdate;
			$lehrfkt_obj->updatevon       = $row->creationuser;
			
			$this->result[] = $lehrfkt_obj;
		}
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	
	/**
	 * Loescht den Datensatz mit der ID die uebergeben wird
	 * @param lehrfkt_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($lehrfkt_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
}
?>