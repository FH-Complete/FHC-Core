<?php
/**
 * Klasse Nation (FAS-Online)
 * @create 06-04-2006
 */

class nation
{
	var $conn;     
	var $errormsg;  
	var $result = array();
	
	//Tabellenspalten
	var $code;   
	var $sperre; 
	var $kontinent;
	var $entwland;
	var $euflag;
	var $ewrflag;
	var $kurztext;
	var $langtext;
	var $engltext;
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $code      Zu ladende Nation
	 */
	function nation($conn,$code=null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		if($code != null)
			$this->load($code);
	}
	
	/**
	 * Laedt die Funktion mit der ID $adress_id
	 * @param  $code code der zu ladenden Nation
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($code)
	{			
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	
	/**
	 * Laedt alle Nationen
	 * @param ohnesperre wenn dieser Parameter auf true gesetzt ist werden
	 *        nur die nationen geliefert dessen Buerger bei uns studieren duerfen
	 */
	function getAll($ohnesperre=false)
	{
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM nation";
		if($ohnesperre)
			$qry .= " where sperre='N'";
			
		$qry .=" order by kurztext";
		
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$nation_obj = new nation($this->conn);
		
			$nation_obj->code = $row->code;
			$nation_obj->sperre = $row->sperre; 
	        $nation_obj->kontinent = $row->sperre;
	  		$nation_obj->entwland = $row->entwland;
	        $nation_obj->euflag = $row->euflag;
	 		$nation_obj->ewrflag = $row->ewrflag;
			$nation_obj->kurztext = $row->kurztext;
			$nation_obj->langtext = $row->langtext;
			$nation_obj->engltext = $row->engltext;
			
			$this->result[] = $nation_obj;
		}
		return true;
	}
}
?>