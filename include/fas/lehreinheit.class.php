<?php
/**
 * Klasse lehreinheit (FAS-Online)
 * @create 16-03-2006
 */
class lehreinheit
{
	var $conn;	  // @var resource DB-Handle
	var $errormsg; // @var string
	var $new;      // @var boolean
	var $result = array(); // @var lehreinheit Objekt
	
	//Lehreinheit
	var $lehreinheit_id;         // @var integer
	var $studiengang_id;         // @var integer
	var $studiensemester_id;     // @var integer
	var $ausbildungssemester_id; // @var integer
	var $fachbereich_id;         // @var integer
	var $gruppe_id;              // @var integer
	var $koordinator_id;         // @var integer bivar1
	var $lehreinheit_fk;         // @var integer Partizipierende Lehreinheit
	var $lehrform_id;            // @var integer
	var $lehrveranstaltung_id;	 // @var integer
	var $raumtyp_id;             // @var integer
	var $raumtypalternativ_id;	 // @var integer
	var $bemerkungen;            // @var string
	var $bezeichnung;	         // @var string
	var $gesamtstunden;          // @var integer
	var $kurzbezeichnung;		 // @var string
	var $nummer;                 // @var string
	var $planfaktor;             // @var integer
	var $plankostenprolektor;    // @var integer
	var $planlektoren; 	         // @var integer
	var $semesterwochenstunden;  // @var float
	var $start_kw;               // @var integer ivar2
	var $stundenblockung;        // @var integer ivar3
	var $updateamum;             // @var timestamp
	var $updatevon=0;            // @var string
	var $wochenrythmus;          // @var integer ivar1 ( 0=geblockt, 1=einwoechig, 2=zweiwoechig, 3=dreiwoechig, 4=vierwoechig )
	
	//Mitarbeiter-Lehreinheit
	var $mitarbeiter_id;              // @var integer
	var $mitarbeiter_lehreinheit_id;	 // @var integer
	var $lehrfunktion_id;        // @var integer
	var $faktor;                      // @var float
	var $kosten;	                     // @var float
	var $gesamtstunden_mitarbeiter;   // @var float rvar1
	
	//Vars zur RDF Erstellung
	var $studiengang_kurzbz;
	var $studiensemester_kurzbz;
	var $fachbereich_bezeichnung;
	var $ausbildungssemester_semester;
	var $ausbildungssemester_kurzbz;
	var $lehrform_kurzbz;	
	var $gruppe_kurzbz;
	var $koordinator_vorname;
	var $koordinator_nachname;
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur Datenbank
	 *        $lehreinheit_id ID der zu ladenden Lehreinheit
	 */
	function lehreinheit($conn, $lehreinheit_id=null)
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
		if($lehreinheit_id != null)
			$this->load($lehreinheit_id);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param $lehreinheit_id ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id) || $lehreinheit_id == '')
		{
			$this->errormsg = 'lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM lehreinheit WHERE lehreinheit_pk = '$lehreinheit_id'";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->lehreinheit_id         = $row->lehreinheit_pk;
			$this->studiengang_id         = $row->studiengang_fk;
			$this->studiensemester_id     = $row->studiensemester_fk;
			$this->ausbildungssemester_id = $row->ausbildungssemester_fk;
			$this->fachbereich_id         = $row->fachbereich_fk;
			$this->gruppe_id              = $row->gruppe_fk;
			$this->koordinator_id         = $row->bivar1;
			$this->lehrform_id            = $row->lehrform_fk;
			$this->lehrveranstaltung_id   = $row->lehrveranstaltung_fk;
			$this->raumtyp_id             = $row->raumtyp_fk;
			$this->raumtypalternativ_id   = $row->alternativraumtyp_fk;	
			$this->bemerkungen            = $row->bemerkungen;
			$this->bezeichnung            = $row->bezeichnung;
			$this->gesamtstunden          = $row->gesamtstunden;
			$this->kurzbezeichnung        = $row->kurzbezeichnung;
			$this->nummer                 = $row->nummer;
			$this->planfaktor             = $row->planfaktor;
			$this->plankostenprolektor    = $row->plankostenprolektor;
			$this->planlektoren           = $row->planlektoren;
			$this->semesterwochenstunden  = $row->semesterwochenstunden;
			$this->start_kw               = $row->ivar2;
			$this->stundenblockung        = $row->ivar3;
			$this->updateamum             = $row->creationdate;
			$this->updatevon              = $row->creationuser;
			$this->wochenrythmus          = $row->ivar1;
		}
		else 
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		return true;
	}
	
	/**
	 * Laedt eine / mehrere Lehreinheit(en)
	 * @param  $studiengang_id         ID des zu ladenden Studienganges
	 *         $studiensemester_id     ID des zu ladenden Studiensemesters (optional)
	 *         $ausbildungssemester_id ID des zu ladenden Ausbildungssemesters (optional)
	 *         $lehrform_id            ID der zu ladenden Lehrform (optional)
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_einheit($studiengang_id, $studiensemester_id=null, $ausbildungssemester_id=null, $lehrform_id=null)
	{
		//Gueltigkeit der Parameter pruefen
		if(!is_numeric($studiengang_id) || $studiengang_id == '')
		{
			$this->errormsg = 'studiengang_id muss eine gueltige Zahl sein';
			return false;
		}
		if($studiensemester_id!=null && (!is_numeric($studiensemester_id) || $studiensemester_id == ''))
		{
			$this->errormsg = 'studiensemester_id muss eine gueltige Zahl oder null sein';
			return false;
		}
		if($ausbildungssemester_id!=null && (!is_numeric($ausbildungssemester_id) || $ausbildungssemester_id == ''))
		{
			$this->errormsg = 'ausbildungssemester_id muss eine gueltige Zahl oder null sein';
			return false;
		}
		if($lehrform_id!=null && (!is_numeric($lehrform_id) || $lehrform_id == ''))
		{
			$this->errormsg = 'lehrform_id muss eine gueltige Zahl oder null sein';
			return false;
		}
		
		//Select Befehl zusammenbauen
		$qry = "SELECT * FROM lehreinheit WHERE studiengang_fk = '$studiengang_id'";
		
		if($studiensemester_id != null)
			$qry .= " AND studiensemester_fk = '$studiensemester_id'";
		
		if($ausbildungssemester_id != null)
			$qry .= " AND ausbildungssemester_fk = '$ausbildungssemester_id'";
		
		if($lehrform_id != null)
			$qry .= " AND lehrform_fk = '$lehrform_id'";
			
		//Daten auslesen
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$einh_obj = new lehreinheit($this->conn);
			
			$einh_obj->lehreinheit_id         = $row->lehreinheit_pk;
			$einh_obj->studiengang_id         = $row->studiengang_fk;
			$einh_obj->studiensemester_id     = $row->studiensemester_fk;
			$einh_obj->ausbildungssemester_id = $row->ausbildungssemester_fk;
			$einh_obj->fachbereich_id         = $row->fachbereich_fk;
			$einh_obj->gruppe_id              = $row->gruppe_fk;
			$einh_obj->koordinator_id         = $row->bivar1;
			$einh_obj->lehrform_id            = $row->lehrform_fk;
			$einh_obj->lehrveranstaltung_id   = $row->lehrveranstaltung_fk;
			$einh_obj->raumtyp_id             = $row->raumtyp_fk;
			$einh_obj->raumtypalternativ_id   = $row->alternativraumtyp_fk;	
			$einh_obj->bemerkungen            = $row->bemerkungen;
			$einh_obj->bezeichnung            = $row->bezeichnung;
			$einh_obj->gesamtstunden          = $row->gesamtstunden;
			$einh_obj->kurzbezeichnung        = $row->kurzbezeichnung;
			$einh_obj->nummer                 = $row->nummer;
			$einh_obj->planfaktor             = $row->planfaktor;
			$einh_obj->plankostenprolektor    = $row->plankostenprolektor;
			$einh_obj->planlektoren           = $row->planlektoren;
			$einh_obj->semesterwochenstunden  = $row->semesterwochenstunden;
			$einh_obj->start_kw               = $row->ivar2;
			$einh_obj->stundenblockung        = $row->ivar3;
			$einh_obj->updateamum             = $row->creationdate;
			$einh_obj->updatevon              = $row->creationuser;
			$einh_obj->wochenrythmus          = $row->ivar1;
			
			$this->result[] = $einh_obj;			
		}
		return true;		
	}
	
	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * Hochkomma und HTML Tags werden ersetzt
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{
		
		//Hochkomma und HTML Tags codieren
		$this->nummer          = str_replace("'","`",$this->nummer);
		$this->bezeichnung     = str_replace("'","`",$this->bezeichnung);
		$this->kurzbezeichnung = str_replace("'","`",$this->kurzbezeichnung);
		$this->bemerkungen     = str_replace("'","`",$this->bemerkungen);
				
		if(ereg("[^a-zA-Z0-9]", $this->kurzbezeichnung))
		{
			$this->errormsg = "Die Kurzbezeichnung darf keine Umlaute oder Sonderzeichen enthalten";
			return false;
		}
		
		//Gesamtlaenge pruefen
		if(strlen($this->nummer)>20)
		{
			$this->errormsg = 'Nummer darf nicht laenger als 20 Zeichen sein';
			return false;
		}
		if(strlen($this->bezeichnung)>255)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->kurzbezeichnung)>5)
		{
			$this->errormsg = 'Kurzbezeichnung darf nicht laenger als 5 Zeichen sein';
			return false;
		}
		if(strlen($this->bemerkungen)>255)
		{
			$this->errormsg = 'Bemerkung darf nicht laenger als 255 Zeichen sein';
			return false;
		}
			
		//Zahlenfelder pruefen
		if(!is_numeric($this->studiengang_id))
		{
			$this->errormsg = 'Studiengang ist ungueltig';
			return false;
		}
		if(!is_numeric($this->studiensemester_id))
		{
			$this->errormsg = 'Studiensemester ist ungueltig';
			return false;
		}
		if($this->lehrveranstaltung_id!='' && !is_numeric($this->lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id ist ungueltig';
			return false;
		}
		if($this->fachbereich_id!='' && !is_numeric($this->fachbereich_id))
		{
			$this->errormsg = 'Fachbereich_id ist ungueltig';
			return false;
		}
		if($this->ausbildungssemester_id !='' && !is_numeric($this->ausbildungssemester_id)) 
		{
			$this->errormsg = 'Ausbildungssemester_id ist ungueltig';
			return false;
		}
		if($this->lehrform_id!='' && !is_numeric($this->lehrform_id))            
		{
			$this->errormsg = 'Lehrform_id ist ungueltig';
			return false;
		}
		if($this->lehreinheit_fk!='' && !is_numeric($this->lehreinheit_fk))
		{
			$this->errormsg = 'Lehreinheit_fk ist ungueltig';
			return false;
		}
		if($this->gruppe_id!='' && !is_numeric($this->gruppe_id))
		{
			$this->errormsg = 'Gruppe ist ungueltig';
			return false;
		}
		if($this->semesterwochenstunden!='' && !is_numeric($this->semesterwochenstunden))  
		{
			$this->errormsg = 'Semesterwochenstunden muessen eine gueltige Zahl sein';
			return false;
		}
		if($this->gesamtstunden!='' && !is_numeric($this->gesamtstunden))
		{
			$this->errormsg = 'Gesamtstunden muessen eine gueltige Zahl sein';
			return false;
		}
		if($this->plankostenprolektor!='' && !is_numeric($this->plankostenprolektor))
		{
			$this->errormsg = 'Kosten pro Lektor muss eine gueltige Zahl sein';
			return false;
		}
		if($this->planfaktor!='' && !is_numeric($this->planfaktor))
		{
			$this->errormsg = 'Geplanter Faktor muss eine gueltige Zahl sein';
			return false;
		}
		if($this->planlektoren!='' && !is_numeric($this->planlektoren))
		{
			$this->errormsg = 'Anzahl der Lektoren muss eine gueltige Zahl sein';
			return false;
		}
		if($this->raumtyp_id!='' && !is_numeric($this->raumtyp_id))
		{
			$this->errormsg = 'Raumtyp ist ungueltig';
			return false;
		}
		if($this->raumtypalternativ_id!='' && !is_numeric($this->raumtypalternativ_id))
		{
			$this->errormsg = 'Alternativraumtyp ist ungueltig';
			return false;
		}
		if($this->wochenrythmus!='' && !is_numeric($this->wochenrythmus))
		{
			$this->errormsg = 'Wochenrythmus muss eine gueltige Zahl sein';
			return false;
		}
		if($this->start_kw!='' && !is_numeric($this->start_kw))
		{
			$this->errormsg = 'Kalenderwoche muss eine gueltige Zahl sein';
			return false;
		}
		if($this->stundenblockung!='' && !is_numeric($this->stundenblockung))
		{
			$this->errormsg = 'Stundenblockung muss eine gueltige Zahl sein';
			return false;
		}
		if($this->koordinator_id!='' && !is_numeric($this->koordinator_id))
		{
			$this->errormsg = 'Koordinator ist ungueltig';
			return false;
		}
				
		$this->errormsg = '';
		return true;		
	}	
	
	/**
	 * Speichert den aktuellen Datensatz
	 * Wenn new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * ansonsten wird der datensatz mit der ID lehreinheit_id aktualisiert
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
			
			//naechste ID aus Sequence holen
			$qry = "SELECT nextval('lehreinheit_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim auslesen der Sequence';
				return false;
			}
			$this->lehreinheit_id = $row->id;
			
			//Insert Befehl zusammenbauen
			$qry = "INSERT INTO lehreinheit (lehreinheit_pk, studiengang_fk, studiensemester_fk, lehrveranstaltung_fk,".
			       " fachbereich_fk, ausbildungssemester_fk, lehreinheit_fk, lehrform_fk, gruppe_fk, nummer, bezeichnung,".
			       " kurzbezeichnung, semesterwochenstunden, gesamtstunden, plankostenprolektor, planfaktor, planlektoren,".
			       " raumtyp_fk, alternativraumtyp_fk, bemerkungen, ivar1, ivar2, ivar3, bivar1, creationdate, creationuser)".
			       " VALUES('$this->lehreinheit_id', '$this->studiengang_id', '$this->studiensemester_id',".
			       ($this->lehrveranstaltung_id!=''?" '$this->lehrveranstaltung_id'":" null").",".
			       ($this->fachbereich_id!=''?" '$this->fachbereich_id'":" null").",".
			       ($this->ausbildungssemester_id!=''?" '$this->ausbildungssemester_id'":" null").",".
			       ($this->lehreinheit_fk!=''?" '$this->lehreinheit_fk'":" null").",".
			       ($this->lehrform_id!=''?" '$this->lehrform_id'":" null").",".
			       ($this->gruppe_id!=''?" '$this->gruppe_id'":" null").",".
			       ($this->nummer!=''?" '$this->nummer'":" null").",".
			       ($this->bezeichnung!=''?" '$this->bezeichnung'":" null").",".
			       ($this->kurzbezeichnung!=''?" '$this->kurzbezeichnung'":" null").",".
			       ($this->semesterwochenstunden!=''?" '$this->semesterwochenstunden'":" null").",".
			       ($this->gesamtstunden!=''?" '$this->gesamtstunden'":" null").",".
			       ($this->plankostenprolektor!=''?" '$this->plankostenprolektor'":" null").",".
			       ($this->planfaktor!=''?" '$this->planfaktor'":" null").",".
			       ($this->planlektoren!=''?" '$this->planlektoren'":" null").",".
			       ($this->raumtyp_id!=''?" '$this->raumtyp_id'":" null").",".
			       ($this->raumtypalternativ_id!=''?" '$this->raumtypalternativ_id'":" null").",".
			       ($this->bemerkungen!=''?" '$this->bemerkungen'":" null").",".
			       ($this->wochenrythmus!=''?" '$this->wochenrythmus'":" null").",".
			       ($this->start_kw!=''?" '$this->start_kw'":" null").",".
			       ($this->stundenblockung!=''?" '$this->stundenblockung'":" null").",".
			       ($this->koordinator_id!=''?" '$this->koordinator_id'":" null").", now(),$this->updatevon);";
		}
		else 
		{
			//lehreinheit_id auf gueltigkeit pruefen
			if(!is_numeric($this->lehreinheit_id) || $this->lehreinheit_id == '')
			{
				$this->errormsg = 'lehreinheit_id muss eine gueltige Zahl sein';
				return false;
			}
			
			//Update Befehl zusammenbauen
			$qry = "UPDATE lehreinheit SET".
				   " studiengang_fk = '$this->studiengang_id',".
				   " studiensemester_fk = '$this->studiensemester_id',".
			       " lehrveranstaltung_fk = '$this->lehrveranstaltung_id',".
			       " fachbereich_fk = ".($this->fachbereich_id!=''?"'$this->fachbereich_id'":"null").",".
			       " ausbildungssemester_fk = ".($this->ausbildungssemester_id!=''?"'$this->ausbildungssemester_id'":"null").",".
			       " lehreinheit_fk = ".($this->lehreinheit_fk!=''?"'$this->lehreinheit_fk'":"null").",".
			       " lehrform_fk = ".($this->lehrform_id!=''?"'$this->lehrform_id'":"null").",".
			       " gruppe_fk = ".($this->gruppe_id!=''?"'$this->gruppe_id'":"null").",".
			       " nummer = '$this->nummer',".
			       " bezeichnung = '$this->bezeichnung',".
			       " kurzbezeichnung = '$this->kurzbezeichnung',".
			       " semesterwochenstunden = ".($this->semesterwochenstunden!=''?"'$this->semesterwochenstunden'":"null").",".
			       " gesamtstunden = ".($this->gesamtstunden!=''?"'$this->gesamtstunden'":"null").",".
			       " plankostenprolektor = ".($this->plankostenprolektor!=''?"'$this->plankostenprolektor'":"null").",".
			       " planfaktor = ".($this->planfaktor!=''?"'$this->planfaktor'":"null").",".
			       " planlektoren = ".($this->planlektoren!=''?"'$this->planlektoren'":"null").",".
			       " raumtyp_fk = ".($this->raumtyp_id!=''?"'$this->raumtyp_id'":"null").",".
			       " alternativraumtyp_fk = ".($this->raumtypalternativ_id!=''?"'$this->raumtypalternativ_id'":"null").",".
			       " bemerkungen = '$this->bemerkungen',".
			       " ivar1 = ".($this->wochenrythmus!=''?"'$this->wochenrythmus'":"null").",".
			       " ivar2 = ".($this->start_kw!=''?"'$this->start_kw'":"null").",".
			       " ivar3 = ".($this->stundenblockung!=''?"'$this->stundenblockung'":"null").",".
			       " bivar1= ".($this->koordinator_id!=''?"'$this->koordinator_id'":"null").
			       " WHERE lehreinheit_pk = '$this->lehreinheit_id';";
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
			$this->errormsg = 'Fehler beim Speichern des Datensatzes'.$qry.' '.pg_errormessage($this->conn);
			return false;
		}
	}	
	
	/**
	 * Loescht einen Datensatz
	 * @param $lehreinheit_id ID des zu leoschenden DS
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id) || $lehreinheit_id == '')
		{
			$this->errormsg = 'lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		//Pruefen ob diese Lehreinheit Partizipierte Lehreinheiten hat
		$qry = "SELECT count(*) as anz FROM lehreinheit where lehreinheit_fk='$lehreinheit_id'";
		if(!$result = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Auslesen der partizipierenden Lehreinheiten';
			return false;
		}
		else 
		{
			if(!$row=pg_fetch_object($result) || $row->anz>0)
			{
				$this->errormsg = 'Sie kÃ¶nnen diese Lehreinheit nicht lÃ¶schen da noch partizipierende Lehreinheiten vorhanden sind.'.$qry;
				return false;
			}
		}
		
		$qry = "DELETE FROM lehreinheit where lehreinheit_pk = '$lehreinheit_id'";
		
		if(!pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim loeschen des Datensatzes';
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
	 * Laedt alle/id des uebergebenen Mitarbeiter die zu einer Lehreinheit gehoeren
	 * @param $lehreinheit_id ID der Lehreinheit
	 *        $mitarbeiter_id ID des Mitarbeiters (optional)
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_zuteilung($lehreinheit_id, $mitarbeiter_id=null)
	{
		//Variablen pruefen
		if(!is_numeric($lehreinheit_id) || $lehreinheit_id == '')
		{
			$this->errormsg = 'lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		if($mitarbeiter_id != null && (!is_numeric($mitarbeiter_id) || $mitarbeiter_id == ''))
		{
			$this->errormsg = 'mitarbeiter_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM mitarbeiter_lehreinheit where lehreinheit_fk = '$lehreinheit_id'";
		
		if($mitarbeiter_id != null)
			$qry .= " AND mitarbeiter_id = '$mitarbeiter_id'";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$einh_obj = new lehreinheit($this->conn);
			                                              
			$einh_obj->mitarbeiter_lehreinheit_id = $row->mitarbeiter_lehreinheit_pk;
			$einh_obj->lehreinheit_fk             = $row->lehreinheit_fk;
			$einh_obj->lehrfunktion_id            = $row->lehrfunktion_fk;
			$einh_obj->mitarbeiter_id             = $row->mitarbeiter_fk;
			$einh_obj->faktor                     = $row->faktor;
			$einh_obj->kosten                     = $row->kosten;
			$einh_obj->gesamtstunden_mitarbeiter  = $row->rvar1;
			
			$this->result[] = $einh_obj;
		}
		return true;
	}
	
	/**
	 * Laedt die Mitarbeiterzuteilung
	 * @param $mitarbeiter_lehreinheit_id ID der Zuteilung
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_mitarbeiterzuteilung($mitarbeiter_lehreinheit_id)
	{
		//Variablen pruefen
		if(!is_numeric($mitarbeiter_lehreinheit_id) || $mitarbeiter_lehreinheit_id == '')
		{
			$this->errormsg = 'mitarbeiter_lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM mitarbeiter_lehreinheit where mitarbeiter_lehreinheit_pk = '$mitarbeiter_lehreinheit_id'";
				
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$einh_obj = new lehreinheit($this->conn);
			                                              
			$einh_obj->mitarbeiter_lehreinheit_id = $row->mitarbeiter_lehreinheit_pk;
			$einh_obj->lehreinheit_fk             = $row->lehreinheit_fk;
			$einh_obj->lehrfunktion_id            = $row->lehrfunktion_fk;
			$einh_obj->mitarbeiter_id             = $row->mitarbeiter_fk;
			$einh_obj->faktor                     = $row->faktor;
			$einh_obj->kosten                     = $row->kosten;
			$einh_obj->gesamtstunden_mitarbeiter  = $row->rvar1;
			
			$this->result[] = $einh_obj;
		}
		return true;
	}
	
	/**
	 * Prueft die variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars_zuteilung()
	{
		
		if(!is_numeric($this->mitarbeiter_id))  
		{
			$this->errormsg = 'Bitte einen gueltigen Mitarbeiter auswaehlen';
			return false;
		}
		if(!is_numeric($this->lehreinheit_fk))  
		{
			$this->errormsg = 'lehreinheit_fk ist ungueltig';
			return false;
		}
		if(!is_numeric($this->lehrfunktion_id)) 
		{
			$this->errormsg = 'Die Lehrfuntkion ist ungueltig';
			return false;
		}
		if(!is_numeric($this->kosten))
		{
			$this->errormsg = 'Die Kosten muessen eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->faktor))
		{
			$this->errormsg = 'Faktor muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->gesamtstunden_mitarbeiter))
		{
			$this->errormsg = 'Gesamtstunden muss eine gueltige Zahl sein';
			return false;
		}
		
		$this->errormsg = '';
		return true;		
	}
	
	/**
	 * Speichert die Zuteilung eines Mitarbeiters zu einer Lehreinheit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save_zuteilung()
	{
		if(!$this->checkvars_zuteilung())
			return false;
		
		if($this->new)
		{
			$qry = "SELECT nextval('mitarbeiter_lehreinheit_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Sequence konnte nicht ausgelesen werden';
				return false;
			}
			
			$this->mitarbeiter_lehreinheit_id = $row->id;
			
			$qry = "INSERT INTO mitarbeiter_lehreinheit (mitarbeiter_lehreinheit_pk, mitarbeiter_fk, lehreinheit_fk,".
			       " lehrfunktion_fk, kosten, faktor, rvar1, creationdate, creationuser) VALUES(".
			       " '$this->mitarbeiter_lehreinheit_id', '$this->mitarbeiter_id', '$this->lehreinheit_fk', '$this->lehrfunktion_id',".
			       " '$this->kosten', '$this->faktor', '$this->gesamtstunden_mitarbeiter', now(), '$this->updatevon');";			
		}
		else 
		{
			//mitarbeiter_lehreinheit_id auf gueltigkeit pruefen
			if(!is_numeric($this->mitarbeiter_lehreinheit_id) || $this->mitarbeiter_lehreinheit_id =='')
			{
				$this->errormsg = 'mitarbeiter_lehreinheit muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry = "UPDATE mitarbeiter_lehreinheit SET mitarbeiter_fk = '$this->mitarbeiter_id',".
			       " lehreinheit_fk = '$this->lehreinheit_fk', lehrfunktion_fk = '$this->lehrfunktion_id',".
			       " kosten = '$this->kosten', faktor = '$this->faktor', rvar1 = '$this->gesamtstunden_mitarbeiter'".
			       " WHERE mitarbeiter_lehreinheit_pk = '$this->mitarbeiter_lehreinheit_id';";
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
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Loescht die Zuteilung eines Mitarbeiters zu einer Lehreinheit
	 * @param $mitarbeiter_lehreinheit_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete_zuteilung($mitarbeiter_lehreinheit_id)
	{
		//Pruefen ob mitarbeiter_lehreinheit_id eine gueltige Zahl ist
		if(!is_numeric($mitarbeiter_lehreinheit_id) || $mitarbeiter_lehreinheit_id == '')
		{
			$this->errormsg = 'mitarbeiter_lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM mitarbeiter_lehreinheit WHERE mitarbeiter_lehreinheit_pk = '$mitarbeiter_lehreinheit_id';";
		
		if(!pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim loeschen der Zuteilung';
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
	 * Liefert die Lehreinheiten mit den dazugehoerigen Attributen
	 * @param stg   Studiengang
	 *        sem   Semester
	 *        stsem Studiensemester
	 */
	function getLehreinheiten($stg=null, $sem=null, $stsem=null, $lehreinheit_id=null, $include_partizipierungen=false)
	{
		$qry = "SELECT lehreinheit.lehreinheit_pk as lehreinheit_id, 
		               studiengang.studiengang_pk as studiengang_id,
		               (CASE WHEN studiengang.studiengangsart=1 THEN 'B' 
		                     WHEN studiengang.studiengangsart=2 THEN 'M' 
		                     WHEN studiengang.studiengangsart=3 THEN 'D' END) || studiengang.kuerzel as studiengang_kurzbz,
		               studiensemester.studiensemester_pk as studiensemester_id,
		               (CASE WHEN studiensemester.art=1 THEN 'WS'
		                     WHEN studiensemester.art=2 THEN 'SS' END) || studiensemester.jahr as studiensemester_kurzbz,
		               lehreinheit.lehrveranstaltung_fk as lehrveranstaltung_id,
		               lehreinheit.fachbereich_fk as fachbereich_id,
		               fachbereich.name as fachbereich_bezeichnung,
		               lehreinheit.ausbildungssemester_fk as ausbildungssemester_id,
		               ausbildungssemester.semester as ausbildungssemester_semester,
		               ausbildungssemester.name as ausbildungssemester_kurzbz,
		               lehreinheit.lehreinheit_fk as lehreinheit_fk,
		               lehreinheit.lehrform_fk as lehrform_id,
		               lehrform.kurzbezeichnung as lehrform_kurzbz,
		               lehreinheit.gruppe_fk as gruppe_id,
		               fas_function_get_fullname_from_gruppe(lehreinheit.gruppe_fk) as gruppe_kurzbz,
		               lehreinheit.nummer as nummer,
		               lehreinheit.bezeichnung as bezeichnung,
		               lehreinheit.kurzbezeichnung as kurzbezeichnung,
		               lehreinheit.semesterwochenstunden as semesterwochenstunden,
		               lehreinheit.gesamtstunden as gesamtstunden,
		               lehreinheit.plankostenprolektor as plankostenprolektor,
		               lehreinheit.planfaktor as planfaktor,
		               lehreinheit.planlektoren as planlektoren,
		               lehreinheit.raumtyp_fk as raumtyp_id,
		               lehreinheit.alternativraumtyp_fk as raumtypalternativ_id,
		               lehreinheit.bemerkungen as bemerkungen,
		               lehreinheit.ivar1 as wochenrythmus,
		               lehreinheit.ivar2 as kalenderwoche,
		               lehreinheit.ivar3 as stundenblockung,
		               lehreinheit.bivar1 as koordinator_id,		              
		               (Select vorname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_vorname,
		               (Select familienname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_nachname,
		               lehreinheit.creationdate as creationdate,
		               lehreinheit.creationuser as creationuser
				FROM lehreinheit, studiengang, studiensemester, fachbereich, ausbildungssemester, lehrform
				WHERE lehreinheit.studiengang_fk=studiengang.studiengang_pk 
				  AND lehreinheit.studiensemester_fk=studiensemester.studiensemester_pk 
				  AND lehreinheit.fachbereich_fk = fachbereich.fachbereich_pk 
				  AND ausbildungssemester.ausbildungssemester_pk=lehreinheit.ausbildungssemester_fk 
				  AND lehreinheit.lehrform_fk = lehrform.lehrform_pk";
		
		if($stg!=null)
			$qry .= " AND studiengang.studiengang_pk = '$stg'";
		if($sem!=null)
			$qry .= " AND ausbildungssemester.semester= '$sem'";
		if($stsem!=null)			
			$qry .= " AND studiensemester.studiensemester_pk= '$stsem'";
		if($lehreinheit_id!=null)
			$qry .= " AND lehreinheit_pk = '$lehreinheit_id'";
		$qry .= " Order by lehreinheit_fk";
		if($res=pg_query($this->conn, $qry))
		{
			while($row=pg_fetch_object($res))
			{
				$lehreinheit_obj = new lehreinheit($this->conn);
				
				$lehreinheit_obj->lehreinheit_id = $row->lehreinheit_id;
				$lehreinheit_obj->studiengang_id = $row->studiengang_id;
				$lehreinheit_obj->studiengang_kurzbz = $row->studiengang_kurzbz;
				$lehreinheit_obj->studiensemester_id = $row->studiensemester_id;
				$lehreinheit_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$lehreinheit_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$lehreinheit_obj->fachbereich_id = $row->fachbereich_id;
				$lehreinheit_obj->fachbereich_bezeichnung = $row->fachbereich_bezeichnung;
				$lehreinheit_obj->ausbildungssemester_id = $row->ausbildungssemester_id;
				$lehreinheit_obj->ausbildungssemester_semester = $row->ausbildungssemester_semester;
				$lehreinheit_obj->ausbildungssemester_kurzbz = $row->ausbildungssemester_kurzbz;
				$lehreinheit_obj->lehreinheit_fk = $row->lehreinheit_fk;
				$lehreinheit_obj->lehrform_id = $row->lehrform_id;
				$lehreinheit_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
				$lehreinheit_obj->gruppe_id = $row->gruppe_id;
				$lehreinheit_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$lehreinheit_obj->nummer = $row->nummer;
				$lehreinheit_obj->bezeichnung = $row->bezeichnung;
				$lehreinheit_obj->kurzbezeichnung = $row->kurzbezeichnung;
				$lehreinheit_obj->semesterwochenstunden = $row->semesterwochenstunden;
				$lehreinheit_obj->gesamtstunden = $row->gesamtstunden;
				$lehreinheit_obj->plankostenprolektor = $row->plankostenprolektor;
				$lehreinheit_obj->planfaktor = $row->planfaktor;
				$lehreinheit_obj->planlektoren = $row->planlektoren;
				$lehreinheit_obj->raumtyp_id = $row->raumtyp_id;
				$lehreinheit_obj->raumtypalternativ_id = $row->raumtypalternativ_id;
				$lehreinheit_obj->bemerkungen = $row->bemerkungen;
				$lehreinheit_obj->wochenrythmus = $row->wochenrythmus;
				$lehreinheit_obj->start_kw = $row->kalenderwoche;
				$lehreinheit_obj->stundenblockung = $row->stundenblockung;
				$lehreinheit_obj->koordinator_id = $row->koordinator_id;
				$lehreinheit_obj->koordinator_vorname = $row->koordinator_vorname;
				$lehreinheit_obj->koordinator_nachname = $row->koordinator_nachname;
				$lehreinheit_obj->updateamum = $row->creationdate;
				$lehreinheit_obj->updatevon = $row->creationuser;
				
				$this->result[] = $lehreinheit_obj;
				$lehreinheit_id = $row->lehreinheit_id;
				//Laden der Datensaetze die partizipiert sind aber in einem anderen Studiengang/Gruppe sind				
				if($include_partizipierungen)
				{
					if($row->lehreinheit_fk!='' && $row->lehreinheit_fk!='-1')
					{
						$qry = "SELECT lehreinheit.lehreinheit_pk as lehreinheit_id, 
						               studiengang.studiengang_pk as studiengang_id,
						               (CASE WHEN studiengang.studiengangsart=1 THEN 'B' 
						                     WHEN studiengang.studiengangsart=2 THEN 'M' 
						                     WHEN studiengang.studiengangsart=3 THEN 'D' END) || studiengang.kuerzel as studiengang_kurzbz,
						               studiensemester.studiensemester_pk as studiensemester_id,
						               (CASE WHEN studiensemester.art=1 THEN 'WS'
						                     WHEN studiensemester.art=2 THEN 'SS' END) || studiensemester.jahr as studiensemester_kurzbz,
						               lehreinheit.lehrveranstaltung_fk as lehrveranstaltung_id,
						               lehreinheit.fachbereich_fk as fachbereich_id,
						               fachbereich.name as fachbereich_bezeichnung,
						               lehreinheit.ausbildungssemester_fk as ausbildungssemester_id,
						               ausbildungssemester.semester as ausbildungssemester_semester,
						               ausbildungssemester.name as ausbildungssemester_kurzbz,
						               lehreinheit.lehreinheit_fk as lehreinheit_fk,
						               lehreinheit.lehrform_fk as lehrform_id,
						               lehrform.kurzbezeichnung as lehrform_kurzbz,
						               lehreinheit.gruppe_fk as gruppe_id,
						               fas_function_get_fullname_from_gruppe(lehreinheit.gruppe_fk) as gruppe_kurzbz,
						               lehreinheit.nummer as nummer,
						               lehreinheit.bezeichnung as bezeichnung,
						               lehreinheit.kurzbezeichnung as kurzbezeichnung,
						               lehreinheit.semesterwochenstunden as semesterwochenstunden,
						               lehreinheit.gesamtstunden as gesamtstunden,
						               lehreinheit.plankostenprolektor as plankostenprolektor,
						               lehreinheit.planfaktor as planfaktor,
						               lehreinheit.planlektoren as planlektoren,
						               lehreinheit.raumtyp_fk as raumtyp_id,
						               lehreinheit.alternativraumtyp_fk as raumtypalternativ_id,
						               lehreinheit.bemerkungen as bemerkungen,
						               lehreinheit.ivar1 as wochenrythmus,
						               lehreinheit.ivar2 as kalenderwoche,
						               lehreinheit.ivar3 as stundenblockung,
						               lehreinheit.bivar1 as koordinator_id,		              
						               (Select vorname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_vorname,
						               (Select familienname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_nachname,
						               lehreinheit.creationdate as creationdate,
						               lehreinheit.creationuser as creationuser
								FROM lehreinheit, studiengang, studiensemester, fachbereich, ausbildungssemester, lehrform
								WHERE lehreinheit.studiengang_fk=studiengang.studiengang_pk 
								  AND lehreinheit.studiensemester_fk=studiensemester.studiensemester_pk 
								  AND lehreinheit.fachbereich_fk = fachbereich.fachbereich_pk 
								  AND ausbildungssemester.ausbildungssemester_pk=lehreinheit.ausbildungssemester_fk 
								  AND lehreinheit.lehrform_fk = lehrform.lehrform_pk
								  AND lehreinheit_pk='$row->lehreinheit_fk'";
						
						if($result=pg_query($this->conn,$qry))
						{
							if($row=pg_fetch_object($result))
							{
								if($row->studiengang_id!=$stg)
								{
									$lehreinheit_obj = new lehreinheit($this->conn);
									
									$lehreinheit_obj->lehreinheit_id = $row->lehreinheit_id;
									$lehreinheit_obj->studiengang_id = $row->studiengang_id;
									$lehreinheit_obj->studiengang_kurzbz = $row->studiengang_kurzbz;
									$lehreinheit_obj->studiensemester_id = $row->studiensemester_id;
									$lehreinheit_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
									$lehreinheit_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
									$lehreinheit_obj->fachbereich_id = $row->fachbereich_id;
									$lehreinheit_obj->fachbereich_bezeichnung = $row->fachbereich_bezeichnung;
									$lehreinheit_obj->ausbildungssemester_id = $row->ausbildungssemester_id;
									$lehreinheit_obj->ausbildungssemester_semester = $row->ausbildungssemester_semester;
									$lehreinheit_obj->ausbildungssemester_kurzbz = $row->ausbildungssemester_kurzbz;
									$lehreinheit_obj->lehreinheit_fk = $row->lehreinheit_fk;
									$lehreinheit_obj->lehrform_id = $row->lehrform_id;
									$lehreinheit_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
									$lehreinheit_obj->gruppe_id = $row->gruppe_id;
									$lehreinheit_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
									$lehreinheit_obj->nummer = $row->nummer;
									$lehreinheit_obj->bezeichnung = $row->bezeichnung;
									$lehreinheit_obj->kurzbezeichnung = $row->kurzbezeichnung;
									$lehreinheit_obj->semesterwochenstunden = $row->semesterwochenstunden;
									$lehreinheit_obj->gesamtstunden = $row->gesamtstunden;
									$lehreinheit_obj->plankostenprolektor = $row->plankostenprolektor;
									$lehreinheit_obj->planfaktor = $row->planfaktor;
									$lehreinheit_obj->planlektoren = $row->planlektoren;
									$lehreinheit_obj->raumtyp_id = $row->raumtyp_id;
									$lehreinheit_obj->raumtypalternativ_id = $row->raumtypalternativ_id;
									$lehreinheit_obj->bemerkungen = $row->bemerkungen;
									$lehreinheit_obj->wochenrythmus = $row->wochenrythmus;
									$lehreinheit_obj->start_kw = $row->kalenderwoche;
									$lehreinheit_obj->stundenblockung = $row->stundenblockung;
									$lehreinheit_obj->koordinator_id = $row->koordinator_id;
									$lehreinheit_obj->koordinator_vorname = $row->koordinator_vorname;
									$lehreinheit_obj->koordinator_nachname = $row->koordinator_nachname;
									$lehreinheit_obj->updateamum = $row->creationdate;
									$lehreinheit_obj->updatevon = $row->creationuser;
									
									$this->result[] = $lehreinheit_obj;
								}
							}
						}
						else 
						{
							$this->errormsg = 'Fehler beim laden der Partizipierungen aus anderen Studiengaengen';
							return false;
						}
					}
					
					//Laden der uebergeordneten
				
					$qry = "SELECT lehreinheit.lehreinheit_pk as lehreinheit_id, 
					               studiengang.studiengang_pk as studiengang_id,
					               (CASE WHEN studiengang.studiengangsart=1 THEN 'B' 
					                     WHEN studiengang.studiengangsart=2 THEN 'M' 
					                     WHEN studiengang.studiengangsart=3 THEN 'D' END) || studiengang.kuerzel as studiengang_kurzbz,
					               studiensemester.studiensemester_pk as studiensemester_id,
					               (CASE WHEN studiensemester.art=1 THEN 'WS'
					                     WHEN studiensemester.art=2 THEN 'SS' END) || studiensemester.jahr as studiensemester_kurzbz,
					               lehreinheit.lehrveranstaltung_fk as lehrveranstaltung_id,
					               lehreinheit.fachbereich_fk as fachbereich_id,
					               fachbereich.name as fachbereich_bezeichnung,
					               lehreinheit.ausbildungssemester_fk as ausbildungssemester_id,
					               ausbildungssemester.semester as ausbildungssemester_semester,
					               ausbildungssemester.name as ausbildungssemester_kurzbz,
					               lehreinheit.lehreinheit_fk as lehreinheit_fk,
					               lehreinheit.lehrform_fk as lehrform_id,
					               lehrform.kurzbezeichnung as lehrform_kurzbz,
					               lehreinheit.gruppe_fk as gruppe_id,
					               fas_function_get_fullname_from_gruppe(lehreinheit.gruppe_fk) as gruppe_kurzbz,
					               lehreinheit.nummer as nummer,
					               lehreinheit.bezeichnung as bezeichnung,
					               lehreinheit.kurzbezeichnung as kurzbezeichnung,
					               lehreinheit.semesterwochenstunden as semesterwochenstunden,
					               lehreinheit.gesamtstunden as gesamtstunden,
					               lehreinheit.plankostenprolektor as plankostenprolektor,
					               lehreinheit.planfaktor as planfaktor,
					               lehreinheit.planlektoren as planlektoren,
					               lehreinheit.raumtyp_fk as raumtyp_id,
					               lehreinheit.alternativraumtyp_fk as raumtypalternativ_id,
					               lehreinheit.bemerkungen as bemerkungen,
					               lehreinheit.ivar1 as wochenrythmus,
					               lehreinheit.ivar2 as kalenderwoche,
					               lehreinheit.ivar3 as stundenblockung,
					               lehreinheit.bivar1 as koordinator_id,		              
					               (Select vorname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_vorname,
					               (Select familienname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_nachname,
					               lehreinheit.creationdate as creationdate,
					               lehreinheit.creationuser as creationuser
							FROM lehreinheit, studiengang, studiensemester, fachbereich, ausbildungssemester, lehrform
							WHERE lehreinheit.studiengang_fk=studiengang.studiengang_pk 
							  AND lehreinheit.studiensemester_fk=studiensemester.studiensemester_pk 
							  AND lehreinheit.fachbereich_fk = fachbereich.fachbereich_pk 
							  AND ausbildungssemester.ausbildungssemester_pk=lehreinheit.ausbildungssemester_fk 
							  AND lehreinheit.lehrform_fk = lehrform.lehrform_pk
							  AND lehreinheit_fk='$lehreinheit_id'";
					
					if($result=pg_query($this->conn,$qry))
					{
						while($row=pg_fetch_object($result))
						{
							if($row->studiengang_id!=$stg)
							{
								$lehreinheit_obj = new lehreinheit($this->conn);
								
								$lehreinheit_obj->lehreinheit_id = $row->lehreinheit_id;
								$lehreinheit_obj->studiengang_id = $row->studiengang_id;
								$lehreinheit_obj->studiengang_kurzbz = $row->studiengang_kurzbz;
								$lehreinheit_obj->studiensemester_id = $row->studiensemester_id;
								$lehreinheit_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
								$lehreinheit_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
								$lehreinheit_obj->fachbereich_id = $row->fachbereich_id;
								$lehreinheit_obj->fachbereich_bezeichnung = $row->fachbereich_bezeichnung;
								$lehreinheit_obj->ausbildungssemester_id = $row->ausbildungssemester_id;
								$lehreinheit_obj->ausbildungssemester_semester = $row->ausbildungssemester_semester;
								$lehreinheit_obj->ausbildungssemester_kurzbz = $row->ausbildungssemester_kurzbz;
								$lehreinheit_obj->lehreinheit_fk = $row->lehreinheit_fk;
								$lehreinheit_obj->lehrform_id = $row->lehrform_id;
								$lehreinheit_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
								$lehreinheit_obj->gruppe_id = $row->gruppe_id;
								$lehreinheit_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
								$lehreinheit_obj->nummer = $row->nummer;
								$lehreinheit_obj->bezeichnung = $row->bezeichnung;
								$lehreinheit_obj->kurzbezeichnung = $row->kurzbezeichnung;
								$lehreinheit_obj->semesterwochenstunden = $row->semesterwochenstunden;
								$lehreinheit_obj->gesamtstunden = $row->gesamtstunden;
								$lehreinheit_obj->plankostenprolektor = $row->plankostenprolektor;
								$lehreinheit_obj->planfaktor = $row->planfaktor;
								$lehreinheit_obj->planlektoren = $row->planlektoren;
								$lehreinheit_obj->raumtyp_id = $row->raumtyp_id;
								$lehreinheit_obj->raumtypalternativ_id = $row->raumtypalternativ_id;
								$lehreinheit_obj->bemerkungen = $row->bemerkungen;
								$lehreinheit_obj->wochenrythmus = $row->wochenrythmus;
								$lehreinheit_obj->start_kw = $row->kalenderwoche;
								$lehreinheit_obj->stundenblockung = $row->stundenblockung;
								$lehreinheit_obj->koordinator_id = $row->koordinator_id;
								$lehreinheit_obj->koordinator_vorname = $row->koordinator_vorname;
								$lehreinheit_obj->koordinator_nachname = $row->koordinator_nachname;
								$lehreinheit_obj->updateamum = $row->creationdate;
								$lehreinheit_obj->updatevon = $row->creationuser;
								
								$this->result[] = $lehreinheit_obj;
							}
						}
					}
					else 
					{
						$this->errormsg = 'Fehler beim laden der Partizipierungen aus anderen Studiengaengen';
						return false;
					}
					
				}
			}
		}
		else 
		{
			$this->errormsg = "Fehler bei einer SQL Abfrage";
			return false;
		}
		return true;
	}
	
	/**
	 * Liefert die Lehreinheiten mit den dazugehoerigen Attributen
	 * @param stg   Studiengang
	 *        sem   Semester
	 *        stsem Studiensemester
	 */
	function getLehreinheitenfromGruppe($gruppe_id, $stsem)
	{
		
		$qry = "SELECT lehreinheit.lehreinheit_pk as lehreinheit_id, 
		               studiengang.studiengang_pk as studiengang_id,
		               (CASE WHEN studiengang.studiengangsart=1 THEN 'B' 
		                     WHEN studiengang.studiengangsart=2 THEN 'M' 
		                     WHEN studiengang.studiengangsart=3 THEN 'D' END) || studiengang.kuerzel as studiengang_kurzbz,
		               studiensemester.studiensemester_pk as studiensemester_id,
		               (CASE WHEN studiensemester.art=1 THEN 'WS'
		                     WHEN studiensemester.art=2 THEN 'SS' END) || studiensemester.jahr as studiensemester_kurzbz,
		               lehreinheit.lehrveranstaltung_fk as lehrveranstaltung_id,
		               lehreinheit.fachbereich_fk as fachbereich_id,
		               fachbereich.name as fachbereich_bezeichnung,
		               lehreinheit.ausbildungssemester_fk as ausbildungssemester_id,
		               ausbildungssemester.semester as ausbildungssemester_semester,
		               ausbildungssemester.name as ausbildungssemester_kurzbz,
		               lehreinheit.lehreinheit_fk as lehreinheit_fk,
		               lehreinheit.lehrform_fk as lehrform_id,
		               lehrform.kurzbezeichnung as lehrform_kurzbz,
		               lehreinheit.gruppe_fk as gruppe_id,
		               fas_function_get_fullname_from_gruppe(lehreinheit.gruppe_fk) as gruppe_kurzbz,
		               lehreinheit.nummer as nummer,
		               lehreinheit.bezeichnung as bezeichnung,
		               lehreinheit.kurzbezeichnung as kurzbezeichnung,
		               lehreinheit.semesterwochenstunden as semesterwochenstunden,
		               lehreinheit.gesamtstunden as gesamtstunden,
		               lehreinheit.plankostenprolektor as plankostenprolektor,
		               lehreinheit.planfaktor as planfaktor,
		               lehreinheit.planlektoren as planlektoren,
		               lehreinheit.raumtyp_fk as raumtyp_id,
		               lehreinheit.alternativraumtyp_fk as raumtypalternativ_id,
		               lehreinheit.bemerkungen as bemerkungen,
		               lehreinheit.ivar1 as wochenrythmus,
		               lehreinheit.ivar2 as kalenderwoche,
		               lehreinheit.ivar3 as stundenblockung,
		               lehreinheit.bivar1 as koordinator_id,		              
		               (Select vorname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_vorname,
		               (Select familienname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_nachname,
		               lehreinheit.creationdate as creationdate,
		               lehreinheit.creationuser as creationuser
				FROM lehreinheit, studiengang, studiensemester, fachbereich, ausbildungssemester, lehrform
				WHERE lehreinheit.studiengang_fk=studiengang.studiengang_pk 
				  AND lehreinheit.studiensemester_fk=studiensemester.studiensemester_pk 
				  AND lehreinheit.fachbereich_fk = fachbereich.fachbereich_pk 
				  AND ausbildungssemester.ausbildungssemester_pk=lehreinheit.ausbildungssemester_fk 
				  AND lehreinheit.lehrform_fk = lehrform.lehrform_pk
				  AND gruppe_fk in (Select gruppe_pk from gruppe where gruppe_pk=$gruppe_id union Select gruppe_pk from gruppe where gruppe_pk in (Select gruppe_pk from gruppe where obergruppe_fk=$gruppe_id) union Select gruppe_pk from gruppe where obergruppe_fk in (Select gruppe_pk from gruppe where obergruppe_fk in (Select gruppe_pk from gruppe where gruppe_pk=$gruppe_id)))
				  ";
		$qry .= " AND studiensemester.studiensemester_pk= '$stsem'";

		$qry .= " Order by lehreinheit_fk";
		if($res=pg_query($this->conn, $qry))
		{
			while($row=pg_fetch_object($res))
			{
				$lehreinheit_obj = new lehreinheit($this->conn);
				
				$lehreinheit_obj->lehreinheit_id = $row->lehreinheit_id;
				$lehreinheit_obj->studiengang_id = $row->studiengang_id;
				$lehreinheit_obj->studiengang_kurzbz = $row->studiengang_kurzbz;
				$lehreinheit_obj->studiensemester_id = $row->studiensemester_id;
				$lehreinheit_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$lehreinheit_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$lehreinheit_obj->fachbereich_id = $row->fachbereich_id;
				$lehreinheit_obj->fachbereich_bezeichnung = $row->fachbereich_bezeichnung;
				$lehreinheit_obj->ausbildungssemester_id = $row->ausbildungssemester_id;
				$lehreinheit_obj->ausbildungssemester_semester = $row->ausbildungssemester_semester;
				$lehreinheit_obj->ausbildungssemester_kurzbz = $row->ausbildungssemester_kurzbz;
				$lehreinheit_obj->lehreinheit_fk = $row->lehreinheit_fk;
				$lehreinheit_obj->lehrform_id = $row->lehrform_id;
				$lehreinheit_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
				$lehreinheit_obj->gruppe_id = $row->gruppe_id;
				$lehreinheit_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$lehreinheit_obj->nummer = $row->nummer;
				$lehreinheit_obj->bezeichnung = $row->bezeichnung;
				$lehreinheit_obj->kurzbezeichnung = $row->kurzbezeichnung;
				$lehreinheit_obj->semesterwochenstunden = $row->semesterwochenstunden;
				$lehreinheit_obj->gesamtstunden = $row->gesamtstunden;
				$lehreinheit_obj->plankostenprolektor = $row->plankostenprolektor;
				$lehreinheit_obj->planfaktor = $row->planfaktor;
				$lehreinheit_obj->planlektoren = $row->planlektoren;
				$lehreinheit_obj->raumtyp_id = $row->raumtyp_id;
				$lehreinheit_obj->raumtypalternativ_id = $row->raumtypalternativ_id;
				$lehreinheit_obj->bemerkungen = $row->bemerkungen;
				$lehreinheit_obj->wochenrythmus = $row->wochenrythmus;
				$lehreinheit_obj->start_kw = $row->kalenderwoche;
				$lehreinheit_obj->stundenblockung = $row->stundenblockung;
				$lehreinheit_obj->koordinator_id = $row->koordinator_id;
				$lehreinheit_obj->koordinator_vorname = $row->koordinator_vorname;
				$lehreinheit_obj->koordinator_nachname = $row->koordinator_nachname;
				$lehreinheit_obj->updateamum = $row->creationdate;
				$lehreinheit_obj->updatevon = $row->creationuser;
				
				$this->result[] = $lehreinheit_obj;

				$lehreinheit_id = $row->lehreinheit_id;
				//Wenn eine Obergruppe existiert und diese nicht in der selben Gruppe ist
				//dann wird diese auch geladen
				
				if($row->lehreinheit_fk!='' && $row->lehreinheit_fk!='-1'	)
				{
					
					$qry = "SELECT lehreinheit.lehreinheit_pk as lehreinheit_id, 
					               studiengang.studiengang_pk as studiengang_id,
					               (CASE WHEN studiengang.studiengangsart=1 THEN 'B' 
					                     WHEN studiengang.studiengangsart=2 THEN 'M' 
					                     WHEN studiengang.studiengangsart=3 THEN 'D' END) || studiengang.kuerzel as studiengang_kurzbz,
					               studiensemester.studiensemester_pk as studiensemester_id,
					               (CASE WHEN studiensemester.art=1 THEN 'WS'
					                     WHEN studiensemester.art=2 THEN 'SS' END) || studiensemester.jahr as studiensemester_kurzbz,
					               lehreinheit.lehrveranstaltung_fk as lehrveranstaltung_id,
					               lehreinheit.fachbereich_fk as fachbereich_id,
					               fachbereich.name as fachbereich_bezeichnung,
					               lehreinheit.ausbildungssemester_fk as ausbildungssemester_id,
					               ausbildungssemester.semester as ausbildungssemester_semester,
					               ausbildungssemester.name as ausbildungssemester_kurzbz,
					               lehreinheit.lehreinheit_fk as lehreinheit_fk,
					               lehreinheit.lehrform_fk as lehrform_id,
					               lehrform.kurzbezeichnung as lehrform_kurzbz,
					               lehreinheit.gruppe_fk as gruppe_id,
					               fas_function_get_fullname_from_gruppe(lehreinheit.gruppe_fk) as gruppe_kurzbz,
					               lehreinheit.nummer as nummer,
					               lehreinheit.bezeichnung as bezeichnung,
					               lehreinheit.kurzbezeichnung as kurzbezeichnung,
					               lehreinheit.semesterwochenstunden as semesterwochenstunden,
					               lehreinheit.gesamtstunden as gesamtstunden,
					               lehreinheit.plankostenprolektor as plankostenprolektor,
					               lehreinheit.planfaktor as planfaktor,
					               lehreinheit.planlektoren as planlektoren,
					               lehreinheit.raumtyp_fk as raumtyp_id,
					               lehreinheit.alternativraumtyp_fk as raumtypalternativ_id,
					               lehreinheit.bemerkungen as bemerkungen,
					               lehreinheit.ivar1 as wochenrythmus,
					               lehreinheit.ivar2 as kalenderwoche,
					               lehreinheit.ivar3 as stundenblockung,
					               lehreinheit.bivar1 as koordinator_id,		              
					               (Select vorname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_vorname,
					               (Select familienname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_nachname,
					               lehreinheit.creationdate as creationdate,
					               lehreinheit.creationuser as creationuser
							FROM lehreinheit, studiengang, studiensemester, fachbereich, ausbildungssemester, lehrform
							WHERE lehreinheit.studiengang_fk=studiengang.studiengang_pk 
							  AND lehreinheit.studiensemester_fk=studiensemester.studiensemester_pk 
							  AND lehreinheit.fachbereich_fk = fachbereich.fachbereich_pk 
							  AND ausbildungssemester.ausbildungssemester_pk=lehreinheit.ausbildungssemester_fk 
							  AND lehreinheit.lehrform_fk = lehrform.lehrform_pk
							  AND studiensemester.studiensemester_pk= '$stsem'
							  AND lehreinheit_pk='$row->lehreinheit_fk'";
					
					if($result=pg_query($this->conn,$qry))
					{
						if($row=pg_fetch_object($result))
						{
							
							if($row->gruppe_id!=$gruppe_id)
							{
							
								$lehreinheit_obj = new lehreinheit($this->conn);
								
								$lehreinheit_obj->lehreinheit_id = $row->lehreinheit_id;
								$lehreinheit_obj->studiengang_id = $row->studiengang_id;
								$lehreinheit_obj->studiengang_kurzbz = $row->studiengang_kurzbz;
								$lehreinheit_obj->studiensemester_id = $row->studiensemester_id;
								$lehreinheit_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
								$lehreinheit_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
								$lehreinheit_obj->fachbereich_id = $row->fachbereich_id;
								$lehreinheit_obj->fachbereich_bezeichnung = $row->fachbereich_bezeichnung;
								$lehreinheit_obj->ausbildungssemester_id = $row->ausbildungssemester_id;
								$lehreinheit_obj->ausbildungssemester_semester = $row->ausbildungssemester_semester;
								$lehreinheit_obj->ausbildungssemester_kurzbz = $row->ausbildungssemester_kurzbz;
								$lehreinheit_obj->lehreinheit_fk = $row->lehreinheit_fk;
								$lehreinheit_obj->lehrform_id = $row->lehrform_id;
								$lehreinheit_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
								$lehreinheit_obj->gruppe_id = $row->gruppe_id;
								$lehreinheit_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
								$lehreinheit_obj->nummer = $row->nummer;
								$lehreinheit_obj->bezeichnung = $row->bezeichnung;
								$lehreinheit_obj->kurzbezeichnung = $row->kurzbezeichnung;
								$lehreinheit_obj->semesterwochenstunden = $row->semesterwochenstunden;
								$lehreinheit_obj->gesamtstunden = $row->gesamtstunden;
								$lehreinheit_obj->plankostenprolektor = $row->plankostenprolektor;
								$lehreinheit_obj->planfaktor = $row->planfaktor;
								$lehreinheit_obj->planlektoren = $row->planlektoren;
								$lehreinheit_obj->raumtyp_id = $row->raumtyp_id;
								$lehreinheit_obj->raumtypalternativ_id = $row->raumtypalternativ_id;
								$lehreinheit_obj->bemerkungen = $row->bemerkungen;
								$lehreinheit_obj->wochenrythmus = $row->wochenrythmus;
								$lehreinheit_obj->start_kw = $row->kalenderwoche;
								$lehreinheit_obj->stundenblockung = $row->stundenblockung;
								$lehreinheit_obj->koordinator_id = $row->koordinator_id;
								$lehreinheit_obj->koordinator_vorname = $row->koordinator_vorname;
								$lehreinheit_obj->koordinator_nachname = $row->koordinator_nachname;
								$lehreinheit_obj->updateamum = $row->creationdate;
								$lehreinheit_obj->updatevon = $row->creationuser;
								
								$this->result[] = $lehreinheit_obj;
							}
						}
						else 
						{
							$this->errormsg = 'Fehler beim laden der partizipierenden Lehreinheiten'.$qry;
							return false;
						}
					}
					else 
					{
						$this->errormsg = 'Fehler beim laden der partizipierenden Lehreinheiten';
						return false;
					}
				}
				
				//Laden der Datensaetze die partizipiert sind aber in einem anderen Studiengang/Gruppe sind
				$qry = "SELECT lehreinheit.lehreinheit_pk as lehreinheit_id, 
					               studiengang.studiengang_pk as studiengang_id,
					               (CASE WHEN studiengang.studiengangsart=1 THEN 'B' 
					                     WHEN studiengang.studiengangsart=2 THEN 'M' 
					                     WHEN studiengang.studiengangsart=3 THEN 'D' END) || studiengang.kuerzel as studiengang_kurzbz,
					               studiensemester.studiensemester_pk as studiensemester_id,
					               (CASE WHEN studiensemester.art=1 THEN 'WS'
					                     WHEN studiensemester.art=2 THEN 'SS' END) || studiensemester.jahr as studiensemester_kurzbz,
					               lehreinheit.lehrveranstaltung_fk as lehrveranstaltung_id,
					               lehreinheit.fachbereich_fk as fachbereich_id,
					               fachbereich.name as fachbereich_bezeichnung,
					               lehreinheit.ausbildungssemester_fk as ausbildungssemester_id,
					               ausbildungssemester.semester as ausbildungssemester_semester,
					               ausbildungssemester.name as ausbildungssemester_kurzbz,
					               lehreinheit.lehreinheit_fk as lehreinheit_fk,
					               lehreinheit.lehrform_fk as lehrform_id,
					               lehrform.kurzbezeichnung as lehrform_kurzbz,
					               lehreinheit.gruppe_fk as gruppe_id,
					               fas_function_get_fullname_from_gruppe(lehreinheit.gruppe_fk) as gruppe_kurzbz,
					               lehreinheit.nummer as nummer,
					               lehreinheit.bezeichnung as bezeichnung,
					               lehreinheit.kurzbezeichnung as kurzbezeichnung,
					               lehreinheit.semesterwochenstunden as semesterwochenstunden,
					               lehreinheit.gesamtstunden as gesamtstunden,
					               lehreinheit.plankostenprolektor as plankostenprolektor,
					               lehreinheit.planfaktor as planfaktor,
					               lehreinheit.planlektoren as planlektoren,
					               lehreinheit.raumtyp_fk as raumtyp_id,
					               lehreinheit.alternativraumtyp_fk as raumtypalternativ_id,
					               lehreinheit.bemerkungen as bemerkungen,
					               lehreinheit.ivar1 as wochenrythmus,
					               lehreinheit.ivar2 as kalenderwoche,
					               lehreinheit.ivar3 as stundenblockung,
					               lehreinheit.bivar1 as koordinator_id,		              
					               (Select vorname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_vorname,
					               (Select familienname from person join mitarbeiter on (person_fk=person_pk) where mitarbeiter_pk=lehreinheit.bivar1) as koordinator_nachname,
					               lehreinheit.creationdate as creationdate,
					               lehreinheit.creationuser as creationuser
							FROM lehreinheit, studiengang, studiensemester, fachbereich, ausbildungssemester, lehrform
							WHERE lehreinheit.studiengang_fk=studiengang.studiengang_pk 
							  AND lehreinheit.studiensemester_fk=studiensemester.studiensemester_pk 
							  AND lehreinheit.fachbereich_fk = fachbereich.fachbereich_pk 
							  AND ausbildungssemester.ausbildungssemester_pk=lehreinheit.ausbildungssemester_fk 
							  AND lehreinheit.lehrform_fk = lehrform.lehrform_pk
							  AND studiensemester.studiensemester_pk= '$stsem'
							  AND lehreinheit_fk='$lehreinheit_id'";
				
				if($result=pg_query($this->conn,$qry))
				{
					while($row=pg_fetch_object($result))
					{
						
							if($row->gruppe_id!=$gruppe_id)
							{
							
								$lehreinheit_obj = new lehreinheit($this->conn);
								
								$lehreinheit_obj->lehreinheit_id = $row->lehreinheit_id;
								$lehreinheit_obj->studiengang_id = $row->studiengang_id;
								$lehreinheit_obj->studiengang_kurzbz = $row->studiengang_kurzbz;
								$lehreinheit_obj->studiensemester_id = $row->studiensemester_id;
								$lehreinheit_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
								$lehreinheit_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
								$lehreinheit_obj->fachbereich_id = $row->fachbereich_id;
								$lehreinheit_obj->fachbereich_bezeichnung = $row->fachbereich_bezeichnung;
								$lehreinheit_obj->ausbildungssemester_id = $row->ausbildungssemester_id;
								$lehreinheit_obj->ausbildungssemester_semester = $row->ausbildungssemester_semester;
								$lehreinheit_obj->ausbildungssemester_kurzbz = $row->ausbildungssemester_kurzbz;
								$lehreinheit_obj->lehreinheit_fk = $row->lehreinheit_fk;
								$lehreinheit_obj->lehrform_id = $row->lehrform_id;
								$lehreinheit_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
								$lehreinheit_obj->gruppe_id = $row->gruppe_id;
								$lehreinheit_obj->gruppe_kurzbz = $row->gruppe_kurzbz;
								$lehreinheit_obj->nummer = $row->nummer;
								$lehreinheit_obj->bezeichnung = $row->bezeichnung;
								$lehreinheit_obj->kurzbezeichnung = $row->kurzbezeichnung;
								$lehreinheit_obj->semesterwochenstunden = $row->semesterwochenstunden;
								$lehreinheit_obj->gesamtstunden = $row->gesamtstunden;
								$lehreinheit_obj->plankostenprolektor = $row->plankostenprolektor;
								$lehreinheit_obj->planfaktor = $row->planfaktor;
								$lehreinheit_obj->planlektoren = $row->planlektoren;
								$lehreinheit_obj->raumtyp_id = $row->raumtyp_id;
								$lehreinheit_obj->raumtypalternativ_id = $row->raumtypalternativ_id;
								$lehreinheit_obj->bemerkungen = $row->bemerkungen;
								$lehreinheit_obj->wochenrythmus = $row->wochenrythmus;
								$lehreinheit_obj->start_kw = $row->kalenderwoche;
								$lehreinheit_obj->stundenblockung = $row->stundenblockung;
								$lehreinheit_obj->koordinator_id = $row->koordinator_id;
								$lehreinheit_obj->koordinator_vorname = $row->koordinator_vorname;
								$lehreinheit_obj->koordinator_nachname = $row->koordinator_nachname;
								$lehreinheit_obj->updateamum = $row->creationdate;
								$lehreinheit_obj->updatevon = $row->creationuser;
								
								$this->result[] = $lehreinheit_obj;
							}
					}					
				}
				else 
				{
					$this->errormsg = "Fehler beim Auslesen der partizipierenden Lehreinheiten";
					return false;
				}
			}
			
		}
		else 
		{
			$this->errormsg = "Fehler bei einer SQL Abfrage";
			return false;
		}
		//$this->errormsg = $qry;
		//	return false;
		return true;
	}
	
	/**
	 * Setzt eine Partizipierung
	 * @param $quell_lehreinheit_id ... Lehreinheit welche an eine andere Lehreinheit angehaengt wird
	 *        $ziel_lehreinheit_id .... Lehreinheit an welche die andere Lehreinheit angehaengt wird 
	 *
	 * Wenn $ziel_lehreinheit_id = -1 dann wird die zuteilung entfernt
	 * Wenn Ziel Lehreinheit bereits eine Partizipierende ist, dann wird automatisch die uebergeordnete genommen
	 */
	function setPartizipierung($quell_lehreinheit_id, $ziel_lehreinheit_id)
	{
		//Parameter auf gueltigkeit pruefen
		if(is_numeric($quell_lehreinheit_id) && is_numeric($ziel_lehreinheit_id))
		{
			//Keine Aktion bei gleicher ID
			if($quell_lehreinheit_id != $ziel_lehreinheit_id)
			{
				//Wenn Ziel = -1 dann die Partizipierung loeschen
				if($ziel_lehreinheit_id!=-1)
				{
					//Wenn die Quell-Lehreinheit eine Partizipierende Lehreinheit hat dann kann Sie nicht an eine andere
					//angehaengt werden
					$qry = "SELECT count(*) as anz FROM lehreinheit WHERE lehreinheit_fk='$quell_lehreinheit_id'";
					if($result = pg_query($this->conn,$qry))
					{
						if($row = pg_fetch_object($result))
						{
							if($row->anz>0)
							{
								$this->errormsg = 'Operation nicht zulaessig';
								return false;
							}
						}
						else 
						{
							$this->errormsg = 'Fehler beim Auslesen der Quell-Lehreinheit';
							return false;
						}
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Quell-Lehreinheit';
						return false;
					}
					//Nummer der Ziel Lehreinheit ermitteln
					$qry = "SELECT nummer, lehreinheit_fk FROM lehreinheit WHERE lehreinheit_pk='$ziel_lehreinheit_id'";
					if($result = pg_query($this->conn, $qry))
					{
						if($row =  pg_fetch_object($result))
						{							
							if($row->lehreinheit_fk==-1 || $row->lehreinheit_fk==null)
							{
								$nummer = $row->nummer;
							}	
							else 
							{
								//Wenn Ziel Lehreinheit selbst eine Partizipierende Lehreinheit ist,
								//wird die uebergeordnete Lehreinheit genommen
								$ziel_lehreinheit_id = $row->lehreinheit_fk;
								
								$qry = "SELECT nummer FROM lehreinheit WHERE lehreinheit_pk='$ziel_lehreinheit_id'";
								if($result = pg_query($this->conn, $qry))
								{
									if($row =  pg_fetch_object($result))
										$nummer = $row->nummer;
									else 
									{
										$this->errormsg = 'Fehler beim Auslesen der Nummer';
										return false;
									}
								}
								else 
								{
									$this->errormsg = 'Fehler beim Auslesen der Nummer';
									return false;
								}
							}
							//Zuteilung speichern
							$qry = "UPDATE lehreinheit SET lehreinheit_fk='$ziel_lehreinheit_id', 
							                                bemerkungen=(bemerkungen || ' Partizipierende LVA bei $nummer')
							        WHERE lehreinheit_pk = '$quell_lehreinheit_id'";
							
							if(pg_query($this->conn, $qry))
								return true;						
							else 
							{
								$this->errormsg = 'Fehler beim speichern';
								return false;
							}
							
						}
						else 
						{
							$this->errormsg = 'Ziel Lehreinheit konnte nicht ermittelt werden';
							return true;
						}
					}
					else 
					{
						$this->errormsg = 'Ziel Lehreinheit konnte nicht ermittelt werden';
						return false;
					}
				}
				else
				{
					$qry = "SELECT b.nummer as nummer, a.bemerkungen as bemerkung FROM lehreinheit as a, lehreinheit as b where a.lehreinheit_fk=b.lehreinheit_pk AND a.lehreinheit_pk='$quell_lehreinheit_id'";
					if($result = pg_query($this->conn, $qry))
					{
						if($row = pg_fetch_object($result))
						{
							$bemerkung = $row->bemerkung;
							$bemerkung = str_replace('Partizipierende LVA bei '.$row->nummer,'',$bemerkung);
							
							//Loeschen der Zuteilung
							$qry = "UPDATE lehreinheit SET lehreinheit_fk='-1', bemerkungen = '$bemerkung' WHERE lehreinheit_pk='$quell_lehreinheit_id'";
							if(pg_query($this->conn, $qry))
								return true;
							else 
							{
								$this->errormsg = 'Fehler beim speichern';
								return false;
							}
						}
						else 
						{
							$this->errormsg = 'Nummer konnte nicht ermittelt werden';
							return false;
						}
					}
					else 
					{
						$this->errormsg = 'Nummer konnte nicht ermittelt werden';
						return false;
					}					
				}
			}
			else 
			{
				$this->errormsg = 'Quell und Ziel ID sind identisch';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Quell und Ziel ID muessen gueltige Zahlen sein';
			return false;
		}		
	}
}
?>