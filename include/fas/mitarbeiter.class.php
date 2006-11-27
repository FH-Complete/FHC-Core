<?php
/**
 * Klasse Mitarbeiter abgeleitet von Benutzer (Portal)
 * @create 27-11-2006
 */

class mitarbeiter extends benutzer
{
	
    //Tabellenspalten
	//var $uid;				
	var $ausbildungcode;
	var $personalnummer;
	var $kurzbz;
	var $lektor;
	var $fixangestellt;
	var $telefonklappe;

	/**
	 * Konstruktor
	 */
	function mitarbeiter($conn, $unicode=false, $person_id=null)
	{
		$this->conn = $conn;
		
		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else 
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
			
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		
		//Mitarbeiter laden
		if($person_id!=null)
			$this->load($person_id);
	}
	
	/**
	 * ueberprueft die Variablen auf Gueltigkeit
	 * @return true wenn gueltig, false im Fehlerfall
	 */
	function validate()
	{	    
		if(strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->uid=='')
		{
			$this->errormsg = 'UID muss eingegeben werden';
			return false;
		}
		if($this->ausbildungcode!='' && !is_numeric($this->ausbildungcode))
		{
			$this->errormsg = 'Ausbildungscode ist ungueltig';
			return false;
		}
		if(strlen($this->kurzbz)>8)
		{
			$this->errormsg = 'kurzbz darf nicht laenger als 8 Zeichen sein';
			return false;
		}
		if(!is_bool($this->lektor))
		{
			$this->errormsg = 'lektor muss boolean sein'.$this->lektor;
			return false;
		}
		if(!is_bool($this->fixangestellt))
		{
			$this->errormsg = 'fixangestellt muss boolean sein';
			return false;
		}
		if(strlen($this->telefonklappe)>25)
		{
			$this->errormsg = 'telefonklappe darf nicht laenger als 25 Zeichen sein';
			return false;
		}
		if(strlen($this->updatevon)>32)
		{
			$this->errormsg = 'updatevon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
			
		return true;
	}
	
	
	/**
	 * Speichert die Mitarbeiterdaten in die Datenbank
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Variablen checken		
		if(!$this->validate())
			return false;
			
		pg_query($this->conn,"Begin;");
		//Basisdaten speichern
		if(!benutzer::save())
		{
			pg_query($this->conn,"Rollback;");
			return false;
		}
		
		if($this->new)
		{
			//Neuen Datensatz anlegen							
			$qry = "INSERT INTO tbl_mitarbeiter(uid, ausbildungcode, personalnummer, kurzbz, lektor, 
			                    fixangestellt, telefonklappe, updateamum, updatevon)
			        VALUES('".addslashes($this->uid)."',".
			 	 	$this->addslashes($this->ausbildungcode).",'".$this->personalnummer."',".
			 	 	$this->addslashes($this->kurzbz).",".($this->lektor?'true':'false').",".
			       ($this->fixangestellt?'true':'false').",".$this->addslashes($this->telefonklappe).
			       ",now(),'".$this->updatevon."');";
		}
		else 
		{
			//Bestehenden Datensatz updaten
			$qry = "UPDATE tbl_mitarbeiter SET".
			       " ausbildungcode=".$this->addslashes($this->ausbildungcode).",".
			       //" personalnummer='$this->personalnummer',".
			       " kurzbz=".$this->addslashes($this->kurzbz).",".
			       " lektor=".($this->lektor?'true':'false').",".
			       " fixangestellt=".($this->fixangestellt?'true':'false').",".
			       " telefonklappe=".$this->addslashes($this->telefonklappe).",".
			       " updateamum=now(),".
			       " updatevon=".$this->addslashes($this->updatevon).
			       " WHERE uid='".addslashes($this->uid)."';";
		}
		
		if(pg_query($this->conn,$qry))
		{
			pg_query($this->conn,"Commit;");
			//Log schreiben
			return true;
		}
		else 
		{			
			pg_query($this->conn,"Rollback;");
			$this->errormsg = 'Fehler beim Speichern des Mitarbeiter-Datensatzes'.$qry;
			return false;
		}
	}
}
?>