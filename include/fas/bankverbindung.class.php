<?php
/**
 * Klasse bankverbindung (FAS-Online)
 * @create 07-03-2006
 */
class bankverbindung
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var bankverbindung Objekt
	
	//Tabellenspalten
	var $bankverbindung_id; // @var integer
	var $person_id;         // @var integer
	var $name;              // @var string
	var $anschrift;         // @var string
	var $blz;               // @var string
	var $bic;               // @var string
	var $kontonr;           // @var string
	var $iban;              // @var string
	var $typ;               // @var integer
	var $updateamum;        // @var timestamp
	var $updatevon=0;       // @var string
	
	/**
	 * Konstruktor
	 * @param $conn    Connection zur Datenbank
	 *        $bank_id Zu ladende ID (Default=null)
	 */
	function bankverbindung($conn, $bank_id=null)
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
		if($bank_id != null)
			$this->load($bank_id);
	}
	
	/**
	 * Prueft die gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{		
		//Gesamtlaenge pruefen
		$this->errormsg = 'Eine der Maximiallaengen wurde ueberschritten';
		if(strlen($this->name)>255)
		{
			$this->errormsg = 'Name darf nicht länger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->anschrift)>255) 
		{
			$this->errormsg = 'Anschrift darf nicht länger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->blz)>15)
		{
			$this->errormsg = 'BLZ darf nicht länger als 15 Zeichen sein';
			return false;
		}
		if(strlen($this->bic)>15)
		{
			$this->errormsg = 'BIC darf nicht länger als 15 Zeichen sein';
			return false;
		}
		if(strlen($this->kontonr)>25)
		{
			$this->errormsg = 'KontoNr darf nicht länger als 25 Zeichen sein';
			return false;
		}
		if(strlen($this->iban)>25)
		{
			$this->errormsg = 'IBAN darf nicht länger als 25 Zeichen sein';
			return false;
		}
		
		//Zahlenwerte ueberpruefen
		$this->errormsg = 'Ein Zahlenfeld enthaelt ungueltige Zeichen';
		if(!is_numeric($this->person_id))         return false;
		if(!is_numeric($this->typ))               return false;
		
		$this->errormsg = '';
		return true;
	}
	
	
	/**
	 * Speichert den aktuellen Datensatz
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $bankverbindung_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			
			//Naechste ID aus der Sequence holen
			$qry = "SELECT nextval('bankverbindung_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim auslesen der Sequence';
				return false;
			}
			$this->bankverbindung_id = $row->id;
			
			$qry = "INSERT INTO bankverbindung (bankverbindung_pk, person_fk, name, anschrift, blz, bic,".
			       " kontonr, iban, typ, creationdate, creationuser) VALUES(".
			       " '$this->bankverbindung_id', '$this->person_id', '$this->name', '$this->anschrift',".
			       " '$this->blz', '$this->bic', '$this->kontonr', '$this->iban', '$this->typ', now(), $this->updatevon);";
		}
		else 
		{
			//Datensatz Updaten
			
			//ID pruefen
			if(!is_numeric($this->bankverbindung_id))
			{
				$this->errormsg = 'bankverbindung_id muss eine Zahl sein';
				return false;
			}
			
			$qry="UPDATE bankverbindung SET person_fk='$this->person_id', name='$this->name',".
			     " anschrift='$this->anschrift', blz='$this->blz', bic='$this->bic',".
			     " kontonr='$this->kontonr', iban='$this->iban', typ='$this->typ'".
			     " WHERE bankverbindung_pk=$this->bankverbindung_id";
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
	 * @param $bank_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($bank_id)
	{
		if(!is_numeric($bank_id) || $bank_id == '')
		{
			$this->errormsg = 'bank_id muss eine Zahl sein';
			return false;
		}
		
		$qry="DELETE FROM bankverbindung WHERE bankverbindung_pk=$bank_id";
		
		if(!pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim loeschen der Daten';
			return false;
		}
		else 
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
	}
	
	/**
	 * Liefert die Bankverbindung mit der uebergebenen ID
	 * @param $bank_id ID der bankverbindung
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($bank_id)
	{
		if(!is_numeric($bank_id) || $bank_id == '')
		{
			$this->errormsg = 'bank_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM bankverbindung WHERE bankverbindung_pk=$bank_id";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{						
			$this->bankverbindung_id = $row->bankverbindung_pk;
			$this->person_id         = $row->person_fk;
			$this->name              = $row->name;
			$this->anschrift         = $row->anschrift;
			$this->blz               = $row->blz;
			$this->bic               = $row->bic;
			$this->kontonr           = $row->kontonr;
			$this->iban              = $row->iban;
			$this->typ               = $row->typ;
			$this->updateamum        = $row->creationdate;
			$this->updatevon         = $row->creationuser;
		}
		else 
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Liefert alle Bankverbindungen der Person die uebergeben wird
	 * @param $pers_id ID der Person
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_pers($pers_id)
	{
		if(!is_numeric($pers_id) || $pers_id == '')
		{
			$this->errormsg = 'pers_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM bankverbindung WHERE person_fk=$pers_id";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$bank_obj = new bankverbindung($this->conn);
			
			$bank_obj->bankverbindung_id = $row->bankverbindung_pk;
			$bank_obj->person_id         = $row->person_fk;
			$bank_obj->name              = $row->name;
			$bank_obj->anschrift         = $row->anschrift;
			$bank_obj->blz               = $row->blz;
			$bank_obj->bic               = $row->bic;
			$bank_obj->kontonr           = $row->kontonr;
			$bank_obj->iban              = $row->iban;
			$bank_obj->typ               = $row->typ;
			$bank_obj->updateamum        = $row->creationdate;
			$bank_obj->updatevon         = $row->creationuser;
			
			$this->result[] = $bank_obj;
		}
		return true;
	}
	
	/**
	 * Liefert alle Bankverbindungen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = "SELECT * FROM bankverbindung";
		
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$bank_obj = new bankverbindung($this->conn);
			
			$bank_obj->bankverbindung_id = $row->bankverbindung_pk;
			$bank_obj->person_id         = $row->person_fk;
			$bank_obj->name              = $row->name;
			$bank_obj->anschrift         = $row->anschrift;
			$bank_obj->blz               = $row->blz;
			$bank_obj->bic               = $row->bic;
			$bank_obj->kontonr           = $row->kontonr;
			$bank_obj->iban              = $row->iban;
			$bank_obj->typ               = $row->typ;
			$bank_obj->updateamum        = $row->creationdate;
			$bank_obj->updatevon         = $row->creationuser;
			
			$this->result[] = $bank_obj;
		}
		return true;
	}
	
	function getTypBezeichnung($id)
	{
		switch($id)
		{
			case 1: return 'Privatkonto';
			case 2: return 'Firmenkonto';
			default: return '';
		}
	}
}
?>