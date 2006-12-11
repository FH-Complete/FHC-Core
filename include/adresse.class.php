<?php
/**
 * Klasse Adresse (FAS-Online)
 * @create 13-03-2006
 */

class adresse
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean
	var $errormsg;  // @var string
	var $result = array(); // @var adresse Objekt
	
	//Tabellenspalten
	var $adresse_id;      // @var integer
	var $bismeldeadresse; // @var boolean
	var $gemeinde;        // @var string
	var $name;            // @var string
	var $nation;          // @var string
	var $ort;             // @var string
	var $person_id;       // @var integer
	var $plz;             // @var string
	var $strasse;         // @var string
	var $typ;             // @var integer
	var $updateamum;      // @var timestamp
	var $updatevon=0;      // @var string
	var $zustelladresse;  // @var boolean
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $adress_id ID der Adresse die geladen werden soll (Default=null)
	 */
	function adresse($conn,$adress_id=null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		if($adress_id != null)
			$this->load($adress_id);
	}
	
	/**
	 * Laedt die Funktion mit der ID $adress_id
	 * @param  $adress_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($adress_id)
	{
		
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($adress_id) || $adress_id == '')
		{
			$this->errormsg = 'Adress_id muss eine Zahl sein';
			return false;
		}
		
		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM adresse WHERE adresse_pk=$adress_id";
		
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->adresse_id      = $row->adresse_pk;
			$this->bismeldeadresse = ($row->bismeldeadresse=='J'?true:false);
			$this->gemeinde        = $row->gemeinde;
			$this->name            = $row->name;
			$this->nation          = $row->nation;
			$this->ort             = $row->ort;
			$this->person_id       = $row->person_fk;
			$this->plz             = $row->plz;
			$this->strasse         = $row->strasse;
			$this->typ             = $row->typ;
			$this->updateamum      = $row->creationdate;
			$this->updatevon       = $row->creationuser;
			$this->zustelladresse  = ($row->zustelladresse=='J'?true:false);
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Laedt alle adressen zu der Person die uebergeben wird
	 * @param $pers_id ID der Person zu der die Adressen geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_pers($pers_id)
	{
		//Pruefen ob pers_id eine gueltige Zahl ist
		if(!is_numeric($pers_id) || $pers_id == '')
		{
			$this->errormsg = 'person_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM adresse WHERE person_fk=$pers_id";
		
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$adr_obj = new adresse($this->conn);
		
			$adr_obj->adresse_id      = $row->adresse_pk;
			$adr_obj->bismeldeadresse = ($row->bismeldeadresse=='J'?true:false);
			$adr_obj->gemeinde        = $row->gemeinde;
			$adr_obj->name            = $row->name;
			$adr_obj->nation          = $row->nation;
			$adr_obj->ort             = $row->ort;
			$adr_obj->person_id       = $row->person_fk;
			$adr_obj->plz             = $row->plz;
			$adr_obj->strasse         = $row->strasse;
			$adr_obj->typ             = $row->typ;
			$adr_obj->updateamum      = $row->creationdate;
			$adr_obj->updatevon       = $row->creationuser;
			$adr_obj->zustelladresse  = ($row->zustelladresse=='J'?true:false);
			
			$this->result[] = $adr_obj;
		}
		return true;
	}
	
	/**
	 * Laedt alle Adressen aus der Datenbank
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = "SELECT * FROM adresse";
		
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$adr_obj = new adresse($this->conn);
		
			$adr_obj->adresse_id      = $row->adresse_pk;
			$adr_obj->bismeldeadresse = ($row->bismeldeadresse=='J'?true:false);
			$adr_obj->gemeinde        = $row->gemeinde;
			$adr_obj->name            = $row->name;
			$adr_obj->nation          = $row->nation;
			$adr_obj->ort             = $row->ort;
			$adr_obj->person_id       = $row->person_fk;
			$adr_obj->plz             = $row->plz;
			$adr_obj->strasse         = $row->strasse;
			$adr_obj->typ             = $row->typ;
			$adr_obj->updateamum      = $row->creationdate;
			$adr_obj->updatevon       = $row->creationuser;
			$adr_obj->zustelladresse  = ($row->zustelladresse=='J'?true:false);
			
			$this->result[] = $adr_obj;
		}
		
		return true;
	}
	
	/**
	 * Prueft die Variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{		
		//Zahlenfelder pruefen
		$this->errormsg='Ein Zahlenfeld enthaelt ungueltige Zeichen';
		if(!is_numeric($this->person_id))
		{
			$this->errormsg='Person_id enthaelt ungueltige Zeichen:'.$this->person_id;
			return false;
		}
		if(!is_numeric($this->typ))   
		{
			$this->errormsg='Typ enthaelt ungueltige Zeichen';
			return false;
		}		
		
		//Gesamtlaenge pruefen
		$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
		if(strlen($this->name)>255)
		{
			$this->errormsg = 'Name darf nicht länger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->strasse)>255)
		{
			$this->errormsg = 'Strasse darf nicht länger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->plz)>10)
		{
			$this->errormsg = 'Plz darf nicht länger als 10 Zeichen sein';
			return false;
		}
		if(strlen($this->ort)>255)           
		{
			$this->errormsg = 'Ort darf nicht länger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->nation)>3)          
		{
			$this->errormsg = 'Nation darf nicht länger als 3 Zeichen sein';
			return false;
		}
		if(strlen($this->gemeinde)>255)
		{
			$this->errormsg = 'Gemeinde darf nicht länger als 255 Zeichen sein';
			return false;
		}
				
		$this->errormsg = '';
		return true;		
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank	 
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			
			//naechste ID aus der Sequence holen
			$qry="SELECT nextval('adresse_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn,$qry)))
			{
				$this->errormsg = 'Fehler beim auslesen der Sequence';
				return false;
			}
			$this->adresse_id = $row->id;
			
			$qry="INSERT INTO adresse (adresse_pk, person_fk, name, strasse, plz, typ, ort, nation, creationdate, creationuser,".
			     " gemeinde, bismeldeadresse, zustelladresse) VALUES(".
			     " $this->adresse_id, $this->person_id, '$this->name', '$this->strasse', '$this->plz', $this->typ, '$this->ort',".
			     " '$this->nation', now(), $this->updatevon, '$this->gemeinde', '".($this->bismeldeadresse?'J':'N')."',".
			     " '".($this->zustelladresse?'J':'N')."');";			
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			
			//Pruefen ob adresse_id eine gueltige Zahl ist
			if(!is_numeric($this->adresse_id))
			{
				$this->errormsg = 'adresse_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry="UPDATE adresse SET person_fk='$this->person_id', name='$this->name', strasse='$this->strasse', plz='$this->plz',".
			     " typ='$this->typ', ort='$this->ort', nation='$this->nation', gemeinde='$this->gemeinde',".
			     " bismeldeadresse='".($this->bismeldeadresse?'J':'N')."', zustelladresse='".($this->zustelladresse?'J':'N')."'".
			     " WHERE adresse_pk='$this->adresse_id'";
		}
		
		if(pg_query($this->conn,$qry))
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
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $adress_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($adress_id)
	{
		//Pruefen ob adresse_id eine gueltige Zahl ist
		if(!is_numeric($adress_id) || $adress_id == '')
		{
			$this->errormsg = 'adresse_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//loeschen des Datensatzes
		$qry="DELETE FROM adresse WHERE adresse_pk=$adress_id;";
		
		if(pg_query($this->conn,$qry))
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
			$this->errormsg = 'Fehler beim loeschen der Daten';
			return false;
		}		
	}
}
?>