<?php
/**
 * Benutzer (PORTAL)
 * @create 27-11-2006
 */

class benutzer extends person
{
	//var $conn;     // @var resource DB-Handle
	//var $errormsg; // @var string
	//var $new;      // @var boolean
	//var $benutzer = array(); // @var person Objekt
	
	//Tabellenspalten
	var $uid;		// varchar(16)
	var $bnaktiv;		// boolean
	var $alias;		// varchar(256)
	//var $person_id;	// integer
		
	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional einen benutzer
	 * @param $conn			Datenbank-Connection
	 *        $benutzer_id	Benutzer der geladen werden soll (default=null)
	 */
	function benutzer($conn, $unicode=false, $benutzer_id=null)
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
		
		if($benutzer_id != null)
			$this->load($benutzer_id);
	}
	
	/**
	 * Laedt Benutzer mit der uebergebenen ID
	 * @param $benutzer_id ID der Person die geladen werden soll
	 */
	function load($benutzer_id)
	{
		
	}
	
	/**
	 * Prueft die Variablen auf gueltigkeit
	 */
	function validate()
	{
		if(strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->uid == '')
		{
			$this->errormsg = 'UID muss eingegeben werden';
			return false;
		}
		if(strlen($this->alias)>256)
		{
			$this->errormsg = 'Alias darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->person_id))
		{
			$this->errormsg = 'person_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = 'aktiv muss ein boolscher wert sein';
			return false;
		}
	}
	
	/**
	 * Speichert die Benutzerdaten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * ansonsten der Datensatz mit $uid upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;
	
		//Personen Datensatz speichern
		if(!person::save())
			return false;
				
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = "INSERT INTO tbl_benutzer (uid, aktiv, alias, person_id) VALUES(".
			       "'".addslashes($this->uid)."',".($this->aktiv?'true':'false').",".
			       $this->addslashes($this->alias).",'".$this->person_id."');";
		}
		else
		{			
			$qry = "UPDATE tbl_benutzer SET".
			       " aktiv=".($this->aktiv?'true':'false').",".
			       " alias=".$this->addslashes($this->alias).",".
			       " person_id='".$this->person_id."'".
			       " WHERE uid='".addslashes($this->uid)."';";
		}
		
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{
			$this->errormsg = "Fehler beim Speichern des Person-Datensatzes:".$qry;
			return false;
		}
	}
}
?>