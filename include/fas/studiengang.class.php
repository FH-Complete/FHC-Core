<?php
/**
 * Klasse studiengang (FAS-Online)
 * @create 14-03-2006
 */
class studiengang
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var studiengang Objekt
	
	var $studiengang_id;     // @var integer
	var $bafirmaaufzeugnis;  // @var boolean
	var $batitelaufzeugnis;  // @var boolean
	var $bescheid;           // @var string
	var $bescheidbgbl1;      // @var string
	var $bescheidbgbl2;      // @var string
	var $bescheidgz;         // @var string
	var $bescheidvom;        // @var date
	var $beschreibung;       // @var string
	var $betreuerstunden;    // @var float
	var $emailkuerzel;       // @var string
	var $endedatum;          // @var date
	var $kennzahl;           // @var integer
	var $kennzahl_neu;       // @var integer
	var $kuerzel;            // @var string
	var $name;               // @var string
	var $organisationsform;  // @var integer	
	var $regelstudiendauer;  // @var integer
	var $regelwochenstunden; // @var integer
	var $standort;           // @var string
	var $startdatum;         // @var date
	var $studiengangsart;    // @var integer
	var $studiensemester_id; // @var integer
	var $telefonnummer;      // @var string
	var $updateamum;         // @var timestamp
	var $updatevon;          // @var string
	var $insertamum;         // @var timestamp
	var $insertvon;          // @var string
	
	/**
	 * Konstruktor
	 * @param conn Connection zur Datenbank
	 *        studiengang_id ID des zu ladenden Studienganges
	 */
	function studiengang($conn, $studiengang_id=null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		if($studiengang_id != null)
			$this->load($studiengang_id);
	}
		
	/**
	 * Laedt einen Studiengang
	 * @param stg_id ID des Studienganges der zu laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($stg_id)
	{
		//Pruefen ob stg_id eine gueltige Zahl ist
		if(!is_numeric($stg_id) || $stg_id == '')
		{
			$this->errormsg = 'stg_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM studiengang WHERE studiengang_pk = '$stg_id'";
		
		if(!$res=pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->studiengang_id    = $row->studiengang_pk;
			$this->name              = $row->name;
			$this->erhalter_id       = $row->erhalter_fk;
			$this->kuerzel           = $row->kuerzel;
			$this->studiengangsart   = $row->studiengangsart;
			$this->organisationsform = $row->organisationsform;
			$this->kennzahl          = $row->kennzahl;
			$this->updateamum        = $row->creationdate;
			$this->updatevon         = $row->creationuser;
			$this->standort          = $row->standort;
			$this->regelstudiendauer = $row->regelstudiendauer;
			$this->emailkuerzel      = $row->emailkuerzel;
			$this->beschreibung      = $row->beschreibung;
			$this->telefonnummer     = $row->telefonnummer;
			$this->bescheid          = $row->bescheid;
			$this->bescheidvom       = $row->bescheidvom;
			$this->bescheidgz        = $row->bescheidgz;
			$this->bescheidbgbl1     = $row->bescheidbgbl1;
			$this->bescheidbgbl2     = $row->bescheidbgbl2;
			$this->kennzahl_neu      = $row->kennzahl_neu;
			$this->nummerintern      = $row->nummerintern;
			$this->bafirmaaufzeugnis = ($row->bafirmaaufzeugnis=='t'?true:false);
			$this->batitelaufzeugnis = ($row->batitelaufzeugnis=='t'?true:false);
		}
		else 
		{
			$this->errormsg = 'Kein Datensatz mit dieser Nummer vorhanden';
			return false;
		}
		return true;
	}
	
	/**
	 * Laedt Studiengang und Studiensemester
	 * @param stg_id   Studiengangs_id
	 *        stsem_id Studiensemester_id
	 */
	function load_stsem($stg_id, $stsem_id)
	{
		//Studiengang laden
		if(!$this->load($stg_id))
			return false;
			
		//pruefen ob stsem_id eine gueltige Zahl ist
		if(!is_numeric($stsem_id) || $stsem_id == '')
		{
			$this->errormsg = 'studiensemester_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM studiengang_studiensemester WHERE studiengang_fk='$this->studiengang_id' ".
		       "AND studiensemester_fk='$stsem_id';";
		       
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->studiensemester_id = $row->studiensemester_fk;
			$this->startdatum         = $row->startdatum;
			$this->endedatum          = $row->endedatum;
			$this->regelwochenstunden = $row->regelwochen;
			$this->betreuerstunden    = $row->rvar1;
		}
		else 
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		return true;		
	}
	
	/**
	 * Liefert alle Studiengaenge
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = "SELECT * FROM studiengang order by name;";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$stg_obj = new studiengang($this->conn);
			
			$stg_obj->studiengang_id    = $row->studiengang_pk;
			$stg_obj->name              = $row->name;
			$stg_obj->erhalter_id       = $row->erhalter_fk;
			$stg_obj->kuerzel           = $row->kuerzel;
			$stg_obj->studiengangsart   = $row->studiengangsart;
			$stg_obj->organisationsform = $row->organisationsform;
			$stg_obj->kennzahl          = $row->kennzahl;
			$stg_obj->updateamum        = $row->creationdate;
			$stg_obj->updatevon         = $row->creationuser;
			$stg_obj->standort          = $row->standort;
			$stg_obj->regelstudiendauer = $row->regelstudiendauer;
			$stg_obj->emailkuerzel      = $row->emailkuerzel;
			$stg_obj->beschreibung      = $row->beschreibung;
			$stg_obj->telefonnummer     = $row->telefonnummer;
			$stg_obj->bescheid          = $row->bescheid;
			$stg_obj->bescheidvom       = $row->bescheidvom;
			$stg_obj->bescheidgz        = $row->bescheidgz;
			$stg_obj->bescheidbgbl1     = $row->bescheidbgbl1;
			$stg_obj->bescheidbgbl2     = $row->bescheidbgbl2;
			$stg_obj->kennzahl_neu      = $row->kennzahl_neu;
			$stg_obj->nummerintern      = $row->nummerintern;
			$stg_obj->bafirmaaufzeugnis = ($row->bafirmaaufzeugnis=='t'?true:false);
			$stg_obj->batitelaufzeugnis = ($row->batitelaufzeugnis=='t'?true:false);
			
			$this->result[] = $stg_obj;
		}
		
		return true;		
	}
	
	/**
	 * Loescht einen Studiengang
	 * @param $stg_id ID des zu loeschenden Studienganges
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($stg_id)
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