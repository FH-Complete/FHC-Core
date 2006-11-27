<?php
/**
 * Klasse telefonnummer (FAS-Online)
 * @create 13-03-2006
 */

class telefonnummer
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean	
	var $errormsg;  // @var string
	var $result = array(); // @var telefonnummer Objekt
	
	//Tabellenspalten
	var $telefonnummer_id; // @var integer
	var $person_id;        // @var integer
	var $name;             // @var string
	var $nummer;	       // @var string
	var $typ;              // @var integer
	var $updateamum;       // @var timestamp
	var $updatevon=0;      // @var string
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur Datenbank
	 *        $telefonnummer_id ID der zu ladenden telefonnummer
	 */
	function telefonnummer($conn, $telefonnummer_id = null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		if($telefonnummer_id != null)
			$this->load($telefonnummer_id);		
	}
	
	/**
	 * Laedt den Datensatz mit der ID die uebergeben wurde 
	 * @param $telefonnummer_id ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($telefonnummer_id)
	{
		//Gueltigkeit von telefonnummer_id pruefen
		if(!is_numeric($telefonnummer_id) || $telefonnummer_id == '')
		{
			$this->errormsg = 'telefonnummer_id muss eine Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM telefonnummer WHERE telefonnummer_pk=$telefonnummer_id";
		
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->telefonnummer_id = $row->telefonnummer_pk;
			$this->name             = $row->name;
			$this->nummer           = $row->nummer;
			$this->person_id        = $row->person_fk;
			$this->typ              = $row->typ;
			$this->updateamum       = $row->creationdate;
			$this->updatevon        = $row->creationuser;	
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Laedt alle Telefonnummern einer Person
	 * @param $person_id Person zu der die Telefonnummern gesucht werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_pers($person_id)
	{
		//Gueltigkeit von person_id pruefen
		if(!is_numeric($person_id) || $person_id == '')
		{
			$this->errormsg = 'person_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * from telefonnummer where person_fk=$person_id";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Der Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$tel_obj = new telefonnummer($this->conn);
			
			$tel_obj->telefonnummer_id = $row->telefonnummer_pk;
			$tel_obj->name             = $row->name;
			$tel_obj->nummer           = $row->nummer;
			$tel_obj->person_id        = $row->person_fk;
			$tel_obj->typ              = $row->typ;
			$tel_obj->updateamum       = $row->creationdate;
			$tel_obj->updatevon        = $row->creationuser;
			
			$this->result[] = $tel_obj;			
		}
		return true;
	}
	
	/** 
	 * Liefert alle Telefonnummern
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		/* Benoetigt zu viel Speicher
		
		$qry = "SELECT * FROM telefonnummer;";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden der Telefonnummern';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$tel_obj = new telefonnummer($this->conn);
			
			$tel_obj->telefonnummer_id = $row->telefonnummer_pk;
			$tel_obj->name             = $row->name;
			$tel_obj->nummer           = $row->nummer;
			$tel_obj->person_id        = $row->person_fk;
			$tel_obj->typ              = $row->typ;
			$tel_obj->updateamum       = $row->creationdate;
			$tel_obj->updatevon        = $row->creationuser;
			
			$this->result[] = $tel_obj;			
		}
				
		return true;
		*/
		return false;
	}
	
	/**
	 * Prueft die gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{
		//Hochkomma und HTML Tags ersetzen
		//$this->name   = htmlentities($this->name, ENT_QUOTES);
		//$this->nummer = htmlentities($this->nummer, ENT_QUOTES);
		
		//Laenge pruefen
		$this->errormsg = 'Eine der Gesamtlaengen wurde ueberschritten';
		if(strlen($this->name)>255)  return false;
		if(strlen($this->nummer)>30) return false;
		
		//Zahlenfelder pruefen
		$this->errormsg = 'Ein Zahlenfeld enthaelt ungueltige Zeichen';
		if(!is_numeric($this->person_id)) return false;
		if(!is_numeric($this->typ))       return false;
		
		$this->errormsg = '';
		return true;
	}	
	
	/**
	 * Speichert den aktuellen Datensatz	 
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $telefonnummer_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz anlegen
			
			//neue ID aus der Sequence holen
			$qry = "SELECT nextval('telefonnummer_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim auslesen der Sequence';
				return false;
			}
			
			$this->telefonnummer_id = $row->id;
			
			$qry = "INSERT INTO telefonnummer (telefonnummer_pk, person_fk, name, nummer, typ, creationdate, creationuser)".
			       " VALUES('$this->telefonnummer_id', '$this->person_id', '$this->name', '$this->nummer', '$this->typ', now(), '$this->updatevon');";
		}
		else 
		{
			//Bestehenden Datensatz aktualisieren
			
			//Pruefen der ID
			if(!is_numeric($this->telefonnummer_id) || $this->telefonnummer_id == '')
			{
				$this->errormsg = 'telefonnummer_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry = "UPDATE telefonnummer SET person_fk='$this->person_id', name='$this->name', typ='$this->typ', nummer='$this->nummer'".
			       " WHERE telefonnummer_pk='$this->telefonnummer_id'";
		}
		
		if(pg_query($this->conn, $qry))
		{
			//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}
						
			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else 
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Loescht den Datensatz mit der uebergebenen ID
	 * @param telefonnummer_id ID des zu leoschenen Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($telefonnummer_id)
	{
		//Pruefen der ID
		if(!is_numeric($telefonnummer_id) || $telefonnummer_id == '')
		{
			$this->errormsg = 'telefonnummer_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//loeschen des Datensatzes
		$qry = "DELETE FROM telefonnummer where telefonnummer_pk='$telefonnummer_id'";
		
		if(pg_query($this->conn, $qry))
		{
			//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}
						
			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else 
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim loeschen eines Datensatzes';
			return false;
		}
	}
}
?>