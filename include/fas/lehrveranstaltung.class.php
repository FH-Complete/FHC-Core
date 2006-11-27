<?php
/**
 * Klasse lehrveranstaltung (FAS-Online)
 * @create 16-03-2006
 */
class lehrveranstaltung
{
	var $conn;    // @var resource DB-Handle
	var $errormsg; // @var string
	var $new;      // @var boolean
	var $result = array(); // @var lehrveranstaltung Objekt	
	
	var $lehrveranstaltung_id;   // @var integer
	var $art;                    // @var integer
	var $ausbildungssemester_id; // @var integer
	var $beschreibung;           // @var string
	var $ectspunkte;             // @var float
	var $fachbereich_id;         // @var integer
	var $kategorie;              // @var integer
	var $kurzbezeichnung;        // @var string
	var $name;                   // @var string
	var $notenlektor_id;         // @var integer
	var $nummer;                 // @var string
	var $nummerintern;           // @var integer
	var $sortierung;             // @var integer
	var $studentenwochenstunden; // @var float
	var $studiengang_id;         // @var integer
	var $studiensemester_id;     // @var integer
	var $updateamum=0;           // @var timestamp
	var $updatevon;              // @var string
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur Datenbank
	 *        $lehrveranstaltung_id ID der zu ladenden Lehrveranstaltung
	 */
	function lehrveranstaltung($conn, $lehrveranstaltung_id=null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		if($lehrveranstaltung_id != null)
			$this->load($lehrveranstaltung_id);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param $lehrveranstaltung_id ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($lehrveranstaltung_id)
	{
		//gueltigkeit von lehrveranstaltung_id pruefen
		if(!is_numeric($lehrveranstaltung_id) || $lehrveranstaltung_id == '')
		{
			$this->errormsg = 'lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM lehrveranstaltung WHERE lehrveranstaltung_pk = '$lehrveranstaltung_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->lehrveranstaltung_id   = $row->lehrveranstaltung_pk;
			$this->art                    = $row->art;
			$this->ausbildungssemester_id = $row->ausbildungssemester_fk;
			$this->beschreibung           = $row->beschreibung;
			$this->ectspunkte             = $row->ectspunkte;
			$this->fachbereich_id         = $row->fachbereich_fk;
			$this->kategorie              = $row->kategorie;
			$this->kurzbezeichnung        = $row->kurzbezeichnung;
			$this->name                   = $row->name;
			$this->notenlektor_id         = $row->notenlektor_fk;
			$this->nummer                 = $row->nummer;
			$this->nummerintern           = $row->nummerintern;
			$this->sortierung             = $row->sortierung;
			$this->studentenwochenstunden = $row->studentenwochenstunden;
			$this->studiengang_id         = $row->studiengang_fk;
			$this->studiensemester_id     = $row->studiensemester_fk;
			$this->updateamum             = $row->creationdate;
			$this->updatevon              = $row->creationuser;
		}
		else 
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		return true;		
	}
	
	/**
	 * Liefert alle Lehrveranstaltungen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{						
		$qry = "SELECT * FROM lehrveranstaltung;";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$lv_obj = new lehrveranstaltung($this->conn);
			
			$lv_obj->lehrveranstaltung_id   = $row->lehrveranstaltung_pk;
			$lv_obj->art                    = $row->art;
			$lv_obj->ausbildungssemester_id = $row->ausbildungssemester_fk;
			$lv_obj->beschreibung           = $row->beschreibung;
			$lv_obj->ectspunkte             = $row->ectspunkte;
			$lv_obj->fachbereich_id         = $row->fachbereich_fk;
			$lv_obj->kategorie              = $row->kategorie;
			$lv_obj->kurzbezeichnung        = $row->kurzbezeichnung;
			$lv_obj->name                   = $row->name;
			$lv_obj->notenlektor_id         = $row->notenlektor_fk;
			$lv_obj->nummer                 = $row->nummer;
			$lv_obj->nummerintern           = $row->nummerintern;
			$lv_obj->sortierung             = $row->sortierung;
			$lv_obj->studentenwochenstunden = $row->studentenwochenstunden;
			$lv_obj->studiengang_id         = $row->studiengang_fk;
			$lv_obj->studiensemester_id     = $row->studiensemester_fk;
			$lv_obj->updateamum             = $row->creationdate;
			$lv_obj->updatevon              = $row->creationuser;
			
			$this->result[] = $lv_obj;
		}		
		
		return true;		
	}
	
	/**
	 * Liefert alle Lehrveranstaltungen zu einem Studiengang/Studiensemester/Ausbildungssemester
	 * @param $studiengang_id ID des Studienganges
	 *        $studiensemester_id ID des Studiensemesters (optional)
	 *        $ausbildungssemester_id ID des ausbildungssemesters (optional)
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_lva($studiengang_id, $studiensemester_id=null, $ausbildungssemester_id=null)
	{						
		//Variablen pruefen
		if(!is_numeric($studiengang_id) || $studiengang_id =='')
		{
			$this->errormsg = 'studiengang_id muss eine gueltige Zahl sein';
			return false;
		}
		if($studiensemester_id != null && (!is_numeric($studiensemester_id) || $studiensemester_id == ''))
		{
			$this->errormsg = 'studiensemester_id muss eine gueltige Zahl sein';
			return false;
		}
		if($ausbildungssemester_id != null && (!is_numeric($ausbildungssemester_id) || $ausbildungssemester_id == ''))
		{
			$this->errormsg = 'ausbildungssemester_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//Select Befehl zusammenbauen
		$qry = "SELECT * FROM lehrveranstaltung WHERE studiengang_fk = '$studiengang_id'";
		
		if($studiensemester_id != null)
			$qry .= " AND studiensemester_fk = '$studiensemester_id'";
		
		if($ausbildungssemester_id != null)
			$qry .= " AND ausbildungssemester_fk = '$ausbildungssemester_id'";
		$qry .= " ORDER BY name";
		//Datensaetze laden
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$lv_obj = new lehrveranstaltung($this->conn);
			
			$lv_obj->lehrveranstaltung_id   = $row->lehrveranstaltung_pk;
			$lv_obj->art                    = $row->art;
			$lv_obj->ausbildungssemester_id = $row->ausbildungssemester_fk;
			$lv_obj->beschreibung           = $row->beschreibung;
			$lv_obj->ectspunkte             = $row->ectspunkte;
			$lv_obj->fachbereich_id         = $row->fachbereich_fk;
			$lv_obj->kategorie              = $row->kategorie;
			$lv_obj->kurzbezeichnung        = $row->kurzbezeichnung;
			$lv_obj->name                   = $row->name;
			$lv_obj->notenlektor_id         = $row->notenlektor_fk;
			$lv_obj->nummer                 = $row->nummer;
			$lv_obj->nummerintern           = $row->nummerintern;
			$lv_obj->sortierung             = $row->sortierung;
			$lv_obj->studentenwochenstunden = $row->studentenwochenstunden;
			$lv_obj->studiengang_id         = $row->studiengang_fk;
			$lv_obj->studiensemester_id     = $row->studiensemester_fk;
			$lv_obj->updateamum             = $row->creationdate;
			$lv_obj->updatevon              = $row->creationuser;
			
			$this->result[] = $lv_obj;
		}	
		
		return true;		
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{	
		$this->name = str_replace("'",'´',$this->name);
		$this->nummer = str_replace("'",'´',$this->nummer);
		$this->kurzbezeichnung = str_replace("'",'´',$this->kurzbezeichnung);
		
		//Laenge Pruefen
		$this->errormsg = 'Eine der Gesamtlaengen wurde ueberschritten';
		if(strlen($this->name)>255)           
		{
			$this->errormsg = 'Name darf nicht laenger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->nummer)>20)
		{
			$this->errormsg = 'Nummer darf nicht laenger als 20 Zeichen sein';
			return false;
		}
		if(strlen($this->kurzbezeichnung)>20)
		{
			$this->errormsg = 'kurzbezeichnung darf nicht laenger als 20 Zeichen sein';
			return false;
		}
				
		if(!is_numeric($this->fachbereich_id))
		{
			$this->errormsg = 'Fachbereich_id ist ungueltig';
			return false;
		}
		if(!is_numeric($this->studiengang_id))         
		{
			$this->errormsg = 'Studiengang_id ist ungueltig';
			return false;
		}
		if(!is_numeric($this->ausbildungssemester_id)) 
		{
			$this->errormsg = 'Ausbildungssemester_id ist ungueltig';
			return false;
		}
		if($this->art!='' && !is_numeric($this->art))
		{
			$this->errormsg = 'Art ist ungueltig';
			return false;
		}
		if($this->studentenwochenstunden!='' && !is_numeric($this->studentenwochenstunden)) 
		{
			$this->errormsg = 'Studentenwochenstunden ist ungueltig';
			return false;
		}
		if($this->kategorie!='' && !is_numeric($this->kategorie))
		{
			$this->errormsg = "Kategorie ist ungueltig";
			return false;
		}
		if($this->ectspunkte!='' && !is_numeric($this->ectspunkte))
		{
			$this->errormsg = 'ECTSPunkte sind ungueltig';
			return false;
		}
		if($this->notentlektor_id!='' && !is_numeric($this->notenlektor_id))
		{
			$this->errormsg = 'Notenlektor ist ungueltig';
			return false;
		}
		if($this->sortierung!='' && !is_numeric($this->sortierung))             
		{
			$this->errormsg = 'Sortierung ist ungueltig';
			return false;
		}
		if($this->nummerintern!='' && !is_numeric($this->nummerintern))
		{
			$this->errormsg = 'NummerIntern ist ungueltig';
			return false;
		}
		
		if(!is_numeric($this->studiensemester_id))     
		{
			$this->errormsg = 'Studiensemester_id ist ungueltig';
			return false;
		}
				
		$this->errormsg = '';
		return true;		
	}
	
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz anlegen
						
			//naechste ID aus der Sequence holen
			$qry = "SELECT nextval('lehrveranstaltung_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Sequence konnte nicht ausgelesen werden';
				return false;
			}
			$this->lehrveranstaltung_id = $row->id;
			
			$qry = "INSERT INTO lehrveranstaltung(lehrveranstaltung_pk, fachbereich_fk, studiengang_fk, ausbildungssemester_fk,".
			       " name, nummer, kurzbezeichnung, beschreibung, art, studentenwochenstunden, creationdate, creationuser,".
			       " kategorie, ectspunkte, studiensemester_fk, notenlektor_fk, sortierung, nummerintern) VALUES(".
			       " '$this->lehrveranstaltung_id', '$this->fachbereich_id', '$this->studiengang_id', '$this->ausbildungssemester_id',".
			       " '$this->name', '$this->nummer', '$this->kurzbezeichnung', '$this->beschreibung', '$this->art',".
			       " '$this->studentenwochenstunden', now(), $this->updatevon, '$this->kategorie', '$this->ectspunkte', '$this->studiensemester_id',".
			       " '$this->notenlektor_id', '$this->sortierung', '$this->nummerintern');";
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			//Pruefen ob lehrveranstaltung_id eine gueltige Zahl ist
			if(!is_numeric($this->lehrveranstaltung_id) || $this->lehrveranstaltung_id == '')
			{
				$this->errormsg = 'lehrveranstaltung_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry = "UPDATE lehrveranstaltung SET fachbereich_fk = '$this->fachbereich_id', studiengang_fk = '$this->studiengang_id',".
			       " ausbildungssemester_fk = '$this->ausbildungssemester_id', name = '$this->name', nummer = '$this->nummer',".
			       " kurzbezeichnung = '$this->kurzbezeichnung', beschreibung = '$this->beschreibung', art = '$this->art',".
			       " studentenwochenstunden = '$this->studentenwochenstunden', kategorie = '$this->kategorie', ".
			       " ectspunkte = '$this->ectspunkte', studiensemester_fk = '$this->studiensemester_id',".
			       " notenlektor_fk = '$this->notenlektor_id', sortierung = '$this->sortierung', nummerintern = '$this->nummerintern'".
			       " WHERE lehrveranstaltung_pk = '$this->lehrveranstaltung_id';";
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
			$this->errormsg = 'Fehler beim speichern des Datensatzes';
			return false;
		}		
	}
	
	/**
	 * Loescht einen Datensatz
	 * @param $lehrveranstaltung_id ID des zu loeeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($lehrveranstaltung_id)
	{
		//lehrveranstaltung_id auf gueltigkeit pruefen
		if(!is_numeric($lehrveranstaltung_id) || $lehrveranstaltung_id == '')
		{
			$this->errormsg = 'lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//Loeschen des Datensatzes
		$qry = "DELETE FROM lehrveranstaltung WHERE lehrveranstaltung_pk = '$lehrveranstaltung_id';";
		
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
			$this->errormsg = 'Fehler beim loeschen des Datensatzes';
			return false;
		}
	}
}
?>