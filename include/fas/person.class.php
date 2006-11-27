<?php
/**
 * Basisklasse Person (PORTAL)
 * @create 27-11-2006
 */

class person
{
	var $conn;     // @var resource DB-Handle
	var $errormsg; // @var string
	var $new;      // @var boolean
	var $personen = array(); // @var person Objekt
	
	//Tabellenspalten
	var $person_id;        	// integer
	var $sprache;			// varchar(16)
	var $anrede;			// varchar(16)
	var $titelpost;         // varchar(32)
	var $titelpre;          // varchar(64)
	var $nachname;          // varchar(64)
	var $vorname;           // varchar(32)
	var $vornamen;          // varchar(128)
	var $gebdatum;          // date
	var $gebort;            // varchar(128)
	var $gebzeit;           // time
	var $foto;              // oid
	var $anmerkungen;       // varchar(256)
	var $homepage;          // varchar(256)
	var $svnr;              // char(10)
	var $ersatzkennzeichen; // char(10)
	var $familienstand;     // char(1)
	var $anzahlkinder;      // smalint
	var $aktiv;             // boolean
	var $insertamum;        // timestamp
	var $insertvon;         // varchar(16)
	var $updateamum;        // timestamp
	var $updatevon;         // varchar(16)
	var $ext_id;            // bigint
	
	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional eine Person
	 * @param $conn      Datenbank-Connection
	 *        $person_id Person die geladen werden soll (default=null)
	 */
	function person($conn, $unicode=false, $person_id=null)
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
		
		if($person_id != null)
			$this->load($person_id);
	}
	
	/**
	 * Laedt Person mit der uebergebenen ID
	 * @param $person_id ID der Person die geladen werden soll
	 */
	function load($person_id)
	{
		//person_id auf gueltigkeit pruefen
		if(is_numeric($person_id) && $person_id!='')
		{
			$qry = "SELECT person_id, sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen,
                           gebdatum, gebort, gebzeit, foto, anmerkungen, homepage, svnr, ersatzkennzeichen, 
                           familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon, ext_id 
			        FROM tbl_person WHERE person_id='$person_id'";
			
			if(!$result=pg_query($this->conn,$qry))
			{
				$this->errormsg = 'Fehler beim lesen der Personendaten';
				return false;
			}
			
			if($row = pg_fetch_object($result))
			{
				$this->person_id = $row->person_id;
				$this->sprache = $row->sprache;
				$this->anrede = $row->anrede;
				$this->titelpost = $row->titelpost;
				$this->titelpre = $row->titelpre;
				$this->nachname = $row->nachname;
				$this->vorname = $row->vorname;
				$this->vornamen = $row->vornamen;
				$this->gebdatum = $row->gebdatum;
				$this->gebort = $row->gebort;
				$this->gebzeit = $row->gebzeit;
				$this->foto = $row->foto;
				$this->anmerkungen = $row->anmerkungen;
				$this->homepage = $row->homepage;
				$this->svnr = $row->svnr;
				$this->ersatzkennzeichen = $row->ersatzkennzeichen;
				$this->familienstand = $row->familienstand;
				$this->anzahlkinder = $row->anzahlkinder;
				$this->aktiv = $row->aktiv;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->ext_id = $row->ext_id;
			}
			else
			{
				$this->errormsg = 'Es ist kein Personendatensatz mit der ID '.$person_id.' vorhanden';
				return false;
			}
			
			return true;
		}
		else
		{
			$this->errormsg = "Die person_id muss eine gueltige Zahl sein";
			return false;
		}
	}
	
	function validate()
	{
		if(strlen($this->sprache)>16)
		{
			$this->errormsg = "Sprache darf nicht laenger als 16 Zeichen sein";
			return false;
		}
		if(strlen($this->anrede)>16)
		{
			$this->errormsg = "Anrede darf nicht laenger als 16 Zeichen sein";
			return false;
		}
		if(strlen($this->titelpost)>32)
		{
			$this->errormsg = "Titelpost darf nicht laenger als 32 Zeichen sein";
			return false;
		} 
		if(strlen($this->titelpre)>64)
		{
			$this->errormsg = "Titelpre darf nicht laenger als 64 Zeichen sein";
			return false;
		}
		if(strlen($this->nachname)>64)
		{
			$this->errormsg = "Nachname darf nicht laenger als 64 Zeichen sein";
			return false;
		}
		//...
		
	}
	
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	/**
	 * Speichert die Personendaten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * ansonsten der Datensatz mit $person_id upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = "INSERT INTO tbl_person (sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen, 
			                    gebdatum, gebort, gebzeit, foto, anmerkungen, homepage, svnr, ersatzkennzeichen, 
			                    familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon , ext_id)
			        VALUES('".addslashes($this->sprache)."','".addslashes($this->anrede)."','".addslashes($this->titelpost)."','".
			        addslashes($this->titelpre)."','".addslashes($this->nachname)."','".addslashes($this->vorname)."','".
			        addslashes($this->vornamen)."','".addslashes($this->gebdatum)."','".addslashes($this->gebort)."',".
			        ($this->gebzeit!=''?"'".addslashes($this->gebzeit)."'":'null').",".
			        ($this->foto!=''?"'".addslashes($this->foto)."'":'null').",'".addslashes($this->anmerkungen)."','".
			        addslashes($this->homepage)."',".
			        ($this->svnr!=''?"'".addslashes($this->svnr)."'":'null').",".
			        ($this->ersatzkennzeichen!=''?"'".addslashes($this->ersatzkennzeichen)."'":'null').",'".
			        addslashes($this->familienstand)."',".
			        ($this->anzahlkinder!=''?"'".addslashes($this->anzahlkinder)."'":'null').",".
			        ($this->aktiv?'true':'false').",".
			        "'now()','".addslashes($this->insertvon)."','now()', '".addslashes($this->updatevon)."',".
			        ($this->ext_id!=''?"'".addslashes($this->ext_id)."'":'null').");";
		}
		else
		{
			//person_id auf gueltigkeit pruefen
			if(!is_numeric($this->person_id))
			{
				$this->errormsg = 'person_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry = "UPDATE tbl_person SET".
			       " sprache=".$this->addslashes($this->sprache).",".
			       " anrede=".$this->addslashes($this->anrede).",".
			       " titelpost=".$this->addslashes($this->titelpost).",".
			       " titelpre=".$this->addslashes($this->titelpre).",".
			       " nachname=".$this->addslashes($this->nachname).",".
			       " vorname=".$this->addslashes($this->vorname).",".
			       " vornamen=".$this->addslashes($this->vornamen).",".
			       " gebdatum=".$this->addslashes($this->gebdatum).",".
			       " gebort=".$this->addslashes($this->gebort).",".
			       " gebzeit=".$this->addslashes($this->gebzeit).",".
			       " foto=".$this->addslashes($this->foto).",".
			       " anmerkungen=".$this->addslashes($this->anmerkungen).",".
			       " homepage=".$this->addslashes($this->homepage).",".
			       " svnr=".$this->addslashes($this->svnr).",".
			       " ersatzkennzeichen=".$this->addslashes($this->ersatzkennzeichen).",".
			       " familienstand=".$this->addslashes($this->familienstand).",".
			       " anzahlkinder=".$this->addslashes($this->anzahlkinder).",".
			       " aktiv=".($this->aktiv?'true':'false').",".
			       " updateamum='now()', updatevon=".$this->addslashes($this->updatevon).",".
			       " ext_id=".$this->addslashes($this->ext_id).
			       " WHERE person_id='$this->person_id'";			
		}
		
		if(pg_query($this->conn,$qry))
		{
			if($this->new)
			{
				$qry = "Select currval('tbl_person_person_id_seq') as id;";
				if($row=pg_fetch_object(pg_query($this->conn,$qry)))
					$this->person_id=$row->id;
				else 
				{
					$this->errormsg = 'Sequence konnte nicht ausgelesen werden';
					pg_query($this->conn,'ROLLBACK');
					return false;
				}
			}
			//Log schreiben
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern des Person-Datensatzes:'.$qry;
			return false;
		}
	}
}
?>