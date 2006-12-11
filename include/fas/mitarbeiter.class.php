<?php
/**
 * Klasse Mitarbeiter abgeleitet von Person (FAS-Online)
 * @create 06-03-2006
 */

class mitarbeiter extends person 
{
	
    //Tabellenspalten
	var $mitarbeiter_id;                // @var bigint
	var $beginndatum;                   // @var timestamp
	var $akadgrad;                      // @var boolean
	var $akadgrad_bezeichnung;          // @var boolean
	var $habilitation;                  // @var boolean
	var $mitgliedentwicklungsteam;      // @var boolean	
	var $qualifikation;                 // @var integer
	var $hauptberuflich;                // @var boolean
	var $hauptberuf;                    // @var integer
	var $semesterwochenstunden;         // @var float
	var $persnr;                        // @var string
	var $beendigungsdatum;              // @var timestamp
	var $ausgeschieden;                 // @var boolean
	var $ausgeschieden_bezeichnung;     // @var string
	var $kurzbez;                       // @var string
	var $stundensatz;                   // @var float
	var $ausbildung;                    // @var integer
	var $ausbildung_bezeichnung;        // @var string
	var $aktiv;                         // @var boolean
	var $aktiv_bezeichnung;             // @var boolean
	var $zustelladresse_plz;
	var $zustelladresse_strasse; 
	var $zustelladresse_ort;
	
	/**
	 * Konstruktor
	 */
	function mitarbeiter($conn, $person_id=null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
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
	function checkvars()
	{
	    //Hochkomma herausfiltern
		$this->persnr  = str_replace("'","`", $this->persnr);
		$this->kurzbez = str_replace("'","`", $this->kurzbez);
		
		//Maximallaenge pruefen
		$this->errormsg='Die Maximallaenge eines Feldes wurde ueberschritten';
		if(strlen($this->persnr)>20)
		{
			$this->errormsg='PersonalNr darf nicht l�nger als 20 Zeichen sein';
			return false;
		}
		if(strlen($this->kurzbez)>10)
		{
			$this->errormsg="Kurzbezeichnung darf nicht l�nger als 10 Zeichen sein:".strlen($this->kurzbez);
			return false;
		}
				
		//Zahlenwerte ueberpruefen
		$this->errormsg='Ein Zahlenfeld enthaelt ungueltige Zeichen';
		//if(!is_numeric($this->qualifikation))  return false;
		//if(!is_numeric($this->hauptberuf))     return false;
		if(!is_numeric($this->stundensatz) && $this->stundensatz!='')
		{
			$this->errormsg='Stundensatz muss eine gueltige Zahl sein';
		    return false;
		}
		if(!is_numeric($this->ausbildung) && $this->ausbildung!='')
		{   
			$this->errormsg='ausbildung muss eine gueltige Zahl sein';  
			return false;
		}
		/*if(!is_numeric($this->semesterwochenstunden)) 
		{
			$this->errormsg='SWS muss eine gueltige Zahl sein';
			return false;
		}*/
		
		if($this->kurzbz_exists($this->kurzbez, $this->mitarbeiter_id))
		{
			$this->errormsg = 'Diese Kurzbezeichnung wird bereits verwendet';
			return false;
		}
		
		$this->errormsg = '';
		return true;
	}
	
	
	/**
	 * Speichert die Mitarbeiterdaten in die Datenbank
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Variablen checken		
		if(!$this->checkvars())
			return false;
			
		pg_query($this->conn,"Begin;");
		//Basisdaten speichern
		if(!person::save())
		{
			pg_query($this->conn,"Rollback;");
			return false;
		}
		
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			
			//naechste ID aus Sequence auslesen
			$qry = "SELECT nextval('mitarbeiter_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn,$qry)))
			{
				$this->errormsg = 'Fehler beim auslesen der Sequence';
				return false;
			}
			$this->mitarbeiter_id = $row->id;
			
			$qry = "INSERT INTO mitarbeiter (mitarbeiter_pk, beginndatum, akadgrad, habilitation,".
				   //" mitgliedentwicklungsteam,".
			       //" qualifikation, hauptberuflich, hauptberuf, semesterwochenstunden,".
			       " creationdate, creationuser, persnr,".
			       " person_fk, beendigungsdatum, ausgeschieden, kurzbez, stundensatz, ausbildung, aktiv) VALUES (".
			       " '$this->mitarbeiter_id',".
			       (strlen($this->beginndatum)>0?"'$this->beginndatum'":"NULL") .", '".($this->akadgrad?'J':'N')."',".
			       " '".($this->habilitation?'J':'N')."', ".
			       //"'".($this->mitgliedentwicklungsteam?'J':'N')."',".
			       //" '$this->qualifikation', '".($this->hauptberuflich?'J':'N')."', '$this->hauptberuf', '$this->semesterwochenstunden',".
			       " now(),".
			       " '$this->updatevon', '$this->persnr', '$this->person_id',". 
			       (strlen($this->beendigungsdatum)>0?"'$this->beendigungsdatum'":"null").",".
			       " '".($this->ausgeschieden?'J':'N')."',".
			       (strlen($this->kurzbez)>0?"'$this->kurzbez'":"null").", '$this->stundensatz',".
			       ($this->ausbildung!=''?"'$this->ausbildung'":'null').",'$this->aktiv');";
		}
		else 
		{
			//Bestehenden Datensatz ueberschreiben
			
			//mitarbeiter_id auf Gueltigkeit pruefen
			if(!is_numeric($this->mitarbeiter_id))
			{
				$this->errormsg = 'mitarbeiter_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry = "UPDATE mitarbeiter SET".
			       " beginndatum=".($this->beginndatum!=''?"'$this->beginndatum'":'null').",".
			       " akadgrad='".($this->akadgrad?'J':'N')."',".
			       " habilitation='".($this->habilitation?'J':'N')."',".
			       //" mitgliedentwicklungsteam='".($this->mitgliedentwicklungsteam?'J':'N')."',".
			       //" qualifikation='$this->qualifikation', hauptberuflich='".($this->hauptberuflich?'J':'N')."',".
			       //" hauptberuf='$this->hauptberuf', semesterwochenstunden='$this->semesterwochenstunden',".
			       " persnr=".($this->persnr!=''?"'$this->persnr'":'null').",".
			       " person_fk='$this->person_id',".
			       " beendigungsdatum=".($this->beendigungsdatum!=''?"'$this->beendigungsdatum'":'null').",".
			       " ausgeschieden='".($this->ausgeschieden?'J':'N')."',".
			       " kurzbez=".($this->kurzbez!=''?"'$this->kurzbez'":'null').",". 
			       " stundensatz=".($this->stundensatz!=''?"'$this->stundensatz'":'null').",".
			       " ausbildung=".($this->ausbildung!=''?"'$this->ausbildung'":'null').",".
			       " aktiv='$this->aktiv'".
			       " WHERE mitarbeiter_pk=$this->mitarbeiter_id;";				
		}
		
		if(pg_query($this->conn,$qry))
		{
			//Wenn nicht ausgeschieden dann den Status neu setzen
			//Da sonst beim ruecksetzen des Hakerls ausgeschieden der status bleibt
			if(!$this->ausgeschieden)
			{
				if(!person::updateaktstatus($this->person_id))
				{
					pg_query($this->conn,"Rollback;");
					return false;
				}
			}
			else 
			{
				if(!person::setaktstatus(150,$this->person_id))
				{
					pg_query($this->conn,"Rollback;");
					return false;
				}
			}
			pg_query($this->conn,"Commit;");
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
			pg_query($this->conn,"Rollback;");
			$this->errormsg = 'Fehler beim Speichern des Mitarbeiter-Datensatzes';
			return false;
		}
	}
	
	/**
	 * Ladet die Daten aus der Datenbank
	 * @param $fix         wenn 'true'  Fixangestellte laden
	 *                     wenn 'false' Freie MA laden
	 *        $stgl        wenn 'true'  Studiengangsleiter laden
	 *        $fbl         wenn 'true'  Fachbereichsleiter laden
	 *        $aktiv       wenn 'true'  Aktive MA laden
	 *        $karrenziert wenn 'true'  Karenzierte laden
	 *        $ausgesch    wenn 'true'  Ausgeschiedene laden
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getMitarbeiter($mitarbeiter_id='', $fix='', $stgl='', $fbl='', $aktiv='', $karenziert='', $ausgesch='', $adresse=false, $studiensemester_id='')
	{
		$qry = "SELECT * FROM (mitarbeiter JOIN person ON (person_pk=mitarbeiter.person_fk))";
		if($adresse)
			$qry .= " LEFT JOIN adresse on(person_pk=adresse.person_fk)";
		$qry .= " WHERE true";
			
		if($mitarbeiter_id!='')
			if(is_numeric($mitarbeiter_id))
				$qry .= " AND mitarbeiter_pk = $mitarbeiter_id";
			else 
			{
				$this->errormsg = "mitarbeiter_id muss eine gueltige Zahl sein";
				return false;
			}

		if($studiensemester_id=='')
		{
			$query = "Select studiensemester_pk FROM studiensemester WHERE aktuell='J'";
			if($row = pg_fetch_object(pg_query($this->conn, $query)))
				$studiensemester_id = $row->studiensemester_pk;
		}
		
		if($fix=='true') // Alle Fixangestellten
			$qry .= " AND mitarbeiter_pk IN(SELECT distinct funktion.mitarbeiter_fk FROM funktion WHERE funktion.beschart1=3 AND funktion.studiensemester_fk='$studiensemester_id')";
		
		if($fix=='false') // Freie Mitarbeiter
			$qry .= " AND mitarbeiter_pk IN(SELECT distinct funktion.mitarbeiter_fk FROM funktion WHERE funktion.beschart1=4 AND funktion.studiensemester_fk='$studiensemester_id')";
		
		if($stgl=='true') //Alle Studiengangsleiter
			$qry .= " AND mitarbeiter_pk IN(SELECT distinct funktion.mitarbeiter_fk FROM funktion WHERE funktion.funktion=5 AND funktion.studiensemester_fk='$studiensemester_id')";
		
		
		if($fbl=='true') //Alle Fachbereichsleiter
			$qry .= " AND mitarbeiter_pk IN(SELECT distinct funktion.mitarbeiter_fk FROM funktion WHERE funktion.funktion=6 AND funktion.studiensemester_fk='$studiensemester_id')";
					
		if($aktiv=='true') //Alle aktiven
			$qry .= " AND aktiv=true";
						
		if($karenziert=='true') //Alle Karenzierten
			$qry .= " AND mitarbeiter_pk IN(SELECT distinct funktion.mitarbeiter_fk FROM funktion WHERE funktion.ausmass=5 AND funktion.studiensemester_fk='$studiensemester_id')";
			
		if($ausgesch=='true') // Alle Ausgeschiedenen
		 	$qry .= " AND beendigungsdatum is not null";
				
		$qry .= " ORDER BY familienname";
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$mitarb = new mitarbeiter($this->conn);
			//Personendaten
			$mitarb->person_id                 = $row->person_pk;
			$mitarb->familienname              = $row->familienname;
			$mitarb->angelegtam                = $row->angelegtam;
			$mitarb->vorname                   = $row->vorname;
			$mitarb->anrede                    = $row->anrede;
			$mitarb->vornamen                  = $row->vornamen;
			$mitarb->geschlecht                = $row->geschlecht;
			$mitarb->gebdat                    = $row->gebdat;
			$mitarb->gebort                    = $row->gebort;
			$mitarb->staatsbuergerschaft       = $row->staatsbuergerschaft;
			$mitarb->familienstand             = $row->familienstand;
			$mitarb->familienstand_bezeichnung = $this->getFamilienstandBezeichnung($row->familienstand);
			$mitarb->svnr                      = $row->svnr;
			$mitarb->anzahlderkinder           = $row->anzahlderkinder;
			$mitarb->ersatzkennzeichen         = $row->ersatzkennzeichen;
			$mitarb->bemerkung                 = $row->bemerkung;
			$mitarb->aktstatus                 = $row->aktstatus;
			$mitarb->aktstatus_bezeichnung     = $this->getAktstatusBezeichnung($row->aktstatus);
			$mitarb->bismelden                 = ($row->bismelden=='J'?true:false);
			$mitarb->bismelden_bezeichnung     = ($row->bismelden=='J'?'Ja':'Nein');
			$mitarb->titelpre                  = $row->titel;
			$mitarb->titelpost                 = $row->postnomentitel;
			$mitarb->uid                       = $row->uid;
			$mitarb->gebnation                 = $row->gebnation;
			
			//Mitarbeiterdaten
			$mitarb->mitarbeiter_id            = $row->mitarbeiter_pk;
			$mitarb->beginndatum               = $row->beginndatum;
			$mitarb->akadgrad                  = ($row->akadgrad=='J'?true:false);
			$mitarb->akadgrad_bezeichnung      = ($row->akadgrad=='J'?'Ja':'Nein');
			$mitarb->habilitation              = ($row->habilitation=='J'?true:false);
			$mitarb->habilitation_bezeichnung  = ($row->habilitation=='J'?'Ja':'Nein');
			$mitarb->mitgliedentwicklungsteam  = ($row->mitgliedentwicklungsteam=='J'?true:false);			
			$mitarb->qualifikation             = $row->qualifikation;
			$mitarb->hauptberuflich            = ($row->hauptberuflich=='J'?true:false);
			$mitarb->hauptberuf                = $row->hauptberuf;
			$mitarb->updateamum                = $row->creationdate;
			$mitarb->updatevon                 = $row->creationuser;
			$mitarb->semesterwochenstunden     = $row->semesterwochenstunden;
			$mitarb->persnr                    = $row->persnr;
			$mitarb->beendigungsdatum          = $row->beendigungsdatum;
			$mitarb->ausgeschieden             = ($row->ausgeschieden=='J'?true:false);
			$mitarb->ausgeschieden_bezeichnung = ($row->ausgeschieden=='J'?'Ja':'Nein');
			$mitarb->kurzbez                   = $row->kurzbez;
			$mitarb->stundensatz               = $row->stundensatz;
			$mitarb->ausbildung                = $row->ausbildung;
			$mitarb->ausbildung_bezeichnung    = $this->getAusbildungBezeichnung($row->ausbildung);
			$mitarb->aktiv                     = ($row->aktiv=='t'?true:false);
			$mitarb->aktiv_bezeichnung         = ($row->aktiv=='t'?'Ja':'Nein');
			
			if($adresse)
			{
				$mitarb->zustelladresse_plz = $row->plz;
				$mitarb->zustelladresse_strasse = $row->strasse;
				$mitarb->zustelladresse_ort = $row->ort;
			}
			$this->result[] = $mitarb;
		}
		return true;
	}
	
	/**
	 * Liefert alle Mitarbeiter
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{		
		
		$qry = "Select person_pk, familienname, angelegtam, vorname, anrede, vornamen, geschlecht, gebdat, gebort, staatsbuergerschaft, ".
		       "familienstand, svnr, anzahlderkinder, ersatzkennzeichen, bemerkung, aktstatus, bismelden, titel, postnomentitel, uid, gebnation, ".
		       "mitarbeiter_pk, beginndatum, akadgrad, habilitation, mitgliedentwicklungsteam, qualifikation, hauptberuflich, hauptberuf, ".
		       "mitarbeiter.creationdate, mitarbeiter.creationuser, semesterwochenstunden, persnr, beendigungsdatum, ausgeschieden, ".
		       "kurzbez, stundensatz, ausbildung, aktiv FROM mitarbeiter JOIN person ON(person_pk=person_fk)";
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$mitarb = new mitarbeiter($this->conn);
			//Personendaten
			$mitarb->person_id           = $row->person_pk;
			$mitarb->familienname        = $row->familienname;
			$mitarb->angelegtam          = $row->angelegtam;
			$mitarb->vorname             = $row->vorname;
			$mitarb->anrede              = $row->anrede;
			$mitarb->vornamen            = $row->vornamen;
			$mitarb->geschlecht          = $row->geschlecht;
			$mitarb->gebdat              = $row->gebdat;
			$mitarb->gebort              = $row->gebort;
			$mitarb->staatsbuergerschaft = $row->staatsbuergerschaft;
			$mitarb->familienstand       = $row->familienstand;
			$mitarb->svnr                = $row->svnr;
			$mitarb->anzahlderkinder     = $row->anzahlderkinder;
			$mitarb->ersatzkennzeichen   = $row->ersatzkennzeichen;
			$mitarb->bemerkung           = $row->bemerkung;
			$mitarb->aktstatus           = $row->aktstatus;
			$mitarb->bismelden           = ($row->bismelden=='J'?true:false);
			$mitarb->titelpre            = $row->titel;
			$mitarb->titelpost           = $row->postnomentitel;
			$mitarb->uid                 = $row->uid;
			$mitarb->gebnation           = $row->gebnation;
			//Mitarbeiterdaten
			$mitarb->mitarbeiter_id           = $row->mitarbeiter_pk;
			$mitarb->beginndatum              = $row->beginndatum;
			$mitarb->akadgrad                 = ($row->akadgrad=='J'?true:false);
			$mitarb->habilitation             = ($row->habilitation=='J'?true:false);
			$mitarb->mitgliedentwicklungsteam = ($row->mitgliedentwicklungsteam=='J'?true:false);
			$mitarb->qualifikation            = $row->qualifikation;
			$mitarb->hauptberuflich           = ($row->hauptberuflich=='J'?true:false);
			$mitarb->hauptberuf               = $row->hauptberuf;
			$mitarb->updateamum               = $row->creationdate;
			$mitarb->updatevon                = $row->creationuser;
			$mitarb->semesterwochenstunden    = $row->semesterwochenstunden;
			$mitarb->persnr                   = $row->persnr;
			$mitarb->beendigungsdatum         = $row->beendigungsdatum;
			$mitarb->ausgeschieden            = ($row->ausgeschieden=='J'?true:false);
			$mitarb->kurzbez                  = $row->kurzbez;
			$mitarb->stundensatz              = $row->stundensatz;
			$mitarb->ausbildung               = $row->ausbildung;
			$mitarb->aktiv                    = ($row->aktiv=='t'?true:false);
			
			$this->result[] = $mitarb;
		}
		return true;
		
	}
	
	/**
	 * Laedt die Mitarbeiterdaten der uebergebenen ID
	 * @param $person_id ID der Person die geladen werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($person_id)
	{
		//person_id auf Gueltigkeit pruefen
		if(!is_numeric($person_id) || $person_id=='')
		{
			$this->errormsg = 'Person_id muss eine Zahl sein';
			return false;
		}
		
		$qry = "SELECT person_pk, familienname, angelegtam, vorname, anrede, vornamen, geschlecht, gebdat, gebort, staatsbuergerschaft, ".
		       "familienstand, svnr, anzahlderkinder, ersatzkennzeichen, bemerkung, aktstatus, bismelden, titel, postnomentitel, uid, gebnation, ".
		       "mitarbeiter_pk, beginndatum, akadgrad, habilitation, mitgliedentwicklungsteam, qualifikation, hauptberuflich, hauptberuf, ".
		       "mitarbeiter.creationdate, mitarbeiter.creationuser, semesterwochenstunden, persnr, beendigungsdatum, ausgeschieden, ".
		       "kurzbez, stundensatz, ausbildung, aktiv FROM mitarbeiter JOIN person ON(person_pk=person_fk) where person_pk=$person_id";
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{	
			//Personendaten
			$this->person_id           = $row->person_pk;
			$this->familienname        = $row->familienname;
			$this->angelegtam          = $row->angelegtam;
			$this->vorname             = $row->vorname;
			$this->anrede              = $row->anrede;
			$this->vornamen            = $row->vornamen;
			$this->geschlecht          = $row->geschlecht;
			$this->gebdat              = $row->gebdat;
			$this->gebort              = $row->gebort;
			$this->staatsbuergerschaft = $row->staatsbuergerschaft;
			$this->familienstand       = $row->familienstand;
			$this->svnr                = $row->svnr;
			$this->anzahlderkinder     = $row->anzahlderkinder;
			$this->ersatzkennzeichen   = $row->ersatzkennzeichen;
			$this->bemerkung           = $row->bemerkung;
			$this->aktstatus           = $row->aktstatus;
			$this->bismelden           = ($row->bismelden=='J'?true:false);
			$this->titelpre            = $row->titel;
			$this->titelpost           = $row->postnomentitel;
			$this->uid                 = $row->uid;
			$this->gebnation           = $row->gebnation;
			//Mitarbeiterdaten
			$this->mitarbeiter_id           = $row->mitarbeiter_pk;
			$this->beginndatum              = $row->beginndatum;
			$this->akadgrad                 = ($row->akadgrad=='J'?true:false);
			$this->habilitation             = ($row->habilitation=='J'?true:false);
			$this->mitgliedentwicklungsteam = ($row->mitgliedentwicklungsteam=='J'?true:false);
			$this->qualifikation            = $row->qualifikation;
			$this->hauptberuflich           = ($row->hauptberuflich=='J'?true:false);
			$this->hauptberuf               = $row->hauptberuf;
			$this->updateamum               = $row->creationdate;
			$this->updatevon                = $row->creationuser;
			$this->semesterwochenstunden    = $row->semesterwochenstunden;
			$this->persnr                   = $row->persnr;
			$this->beendigungsdatum         = $row->beendigungsdatum;
			$this->ausgeschieden            = ($row->ausgeschieden=='J'?true:false);
			$this->kurzbez                  = $row->kurzbez;
			$this->stundensatz              = $row->stundensatz;
			$this->ausbildung               = $row->ausbildung;
			$this->aktiv                    = ($row->aktiv=='t'?true:false);
		}
		else 
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		
		return true;
	}
	
		/**
	 * Laedt die Mitarbeiterdaten der uebergebenen ID
	 * @param $mitarbeiter_id ID der Person die geladen werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_mitarbeiter($mitarbeiter_id)
	{
		//person_id auf Gueltigkeit pruefen
		if(!is_numeric($mitarbeiter_id) || $mitarbeiter_id=='')
		{
			$this->errormsg = 'Person_id muss eine Zahl sein';
			return false;
		}
		
		$qry = "SELECT person_pk, familienname, angelegtam, vorname, anrede, vornamen, geschlecht, gebdat, gebort, staatsbuergerschaft, ".
		       "familienstand, svnr, anzahlderkinder, ersatzkennzeichen, bemerkung, aktstatus, bismelden, titel, postnomentitel, uid, gebnation, ".
		       "mitarbeiter_pk, beginndatum, akadgrad, habilitation, mitgliedentwicklungsteam, qualifikation, hauptberuflich, hauptberuf, ".
		       "mitarbeiter.creationdate, mitarbeiter.creationuser, semesterwochenstunden, persnr, beendigungsdatum, ausgeschieden, ".
		       "kurzbez, stundensatz, ausbildung, aktiv FROM mitarbeiter JOIN person ON(person_pk=person_fk) where mitarbeiter_pk=$mitarbeiter_id";
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{	
			//Personendaten
			$this->person_id           = $row->person_pk;
			$this->familienname        = $row->familienname;
			$this->angelegtam          = $row->angelegtam;
			$this->vorname             = $row->vorname;
			$this->anrede              = $row->anrede;
			$this->vornamen            = $row->vornamen;
			$this->geschlecht          = $row->geschlecht;
			$this->gebdat              = $row->gebdat;
			$this->gebort              = $row->gebort;
			$this->staatsbuergerschaft = $row->staatsbuergerschaft;
			$this->familienstand       = $row->familienstand;
			$this->svnr                = $row->svnr;
			$this->anzahlderkinder     = $row->anzahlderkinder;
			$this->ersatzkennzeichen   = $row->ersatzkennzeichen;
			$this->bemerkung           = $row->bemerkung;
			$this->aktstatus           = $row->aktstatus;
			$this->bismelden           = ($row->bismelden=='J'?true:false);
			$this->titelpre            = $row->titel;
			$this->titelpost           = $row->postnomentitel;
			$this->uid                 = $row->uid;
			$this->gebnation           = $row->gebnation;
			//Mitarbeiterdaten
			$this->mitarbeiter_id           = $row->mitarbeiter_pk;
			$this->beginndatum              = $row->beginndatum;
			$this->akadgrad                 = ($row->akadgrad=='J'?true:false);
			$this->habilitation             = ($row->habilitation=='J'?true:false);
			$this->mitgliedentwicklungsteam = ($row->mitgliedentwicklungsteam=='J'?true:false);
			$this->qualifikation            = $row->qualifikation;
			$this->hauptberuflich           = ($row->hauptberuflich=='J'?true:false);
			$this->hauptberuf               = $row->hauptberuf;
			$this->updateamum               = $row->creationdate;
			$this->updatevon                = $row->creationuser;
			$this->semesterwochenstunden    = $row->semesterwochenstunden;
			$this->persnr                   = $row->persnr;
			$this->beendigungsdatum         = $row->beendigungsdatum;
			$this->ausgeschieden            = ($row->ausgeschieden=='J'?true:false);
			$this->kurzbez                  = $row->kurzbez;
			$this->stundensatz              = $row->stundensatz;
			$this->ausbildung               = $row->ausbildung;
			$this->aktiv                    = ($row->aktiv=='t'?true:false);
		}
		else 
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		
		return true;
	}
	
	/**
	 * loescht den Mitarbeiter mit der uebergebenen ID
	 * @param ma_id Mitarbeiter_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($person_id)
	{		
		//person_id auf Gueltigkeit pruefen
		if(!is_numeric($person_id) || $person_id=='')
		{
			$this->errormsg = 'Person_id muss eine Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM funktion where mitarbeiter_fk=(Select mitarbeiter_pk from mitarbeiter where person_fk=$person_id);".
		      " DELETE FROM adresse where person_fk=$person_id;".
		      " DELETE FROM telefonnummer where person_fk=$person_id;".
		      " DELETE FROM email where person_fk=$person_id;".
		      " DELETE FROM mitarbeiter where person_fk=$person_id";
		
		if(!pg_query($this->conn,$qry))
		{			
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
		else 
		{			
			if(!person::delete($person_id))
				return false;
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
		return true;		
	}
	
	/**
	 * Liefert die passende Bezeichnung des Familienstandes
	 * @param $id ID des Familienstandes
	 */
	function getFamilienstandBezeichnung($id)
	{
		switch($id)
		{
			case 1: return 'ledig';
			case 2: return 'verheiratet';
			case 3: return 'geschieden';
			case 4: return 'verwitwet';
			default: return '';
		}
	}
	
	/**
	 * Liefert die passende Bezeichnung der Ausbildung
	 * @param $id ID der Ausbildung
	 */
	function getAusbildungBezeichnung($id)
	{
		switch($id)
		{
			case  1: return 'Universitätsabschluss mit Doktorat als Zweit- oder Dritt- oder PhD-Abschluss';
			case  2: return 'Universitäts- oder Hochschulabschluss auf Diplom oder Magisterebene, Doktor als Erstabschluss';
			case  3: return 'Fachhochschulabschluss auf Diplom- oder Magisterebene';
			case  4: return 'UniversitÃ¤tsabschluss auf Bakkalaureatsebene';
			case  5: return 'Fachhochschulabschluss auf Bakkalaureatsebene';
			case  6: return 'Diplom einer Akademie';
			case  7: return 'Anderer tertiärer Bildungsabschluss';
			case  8: return 'Reifeprüfung einer allgemeinbildenden höheren Schule';
			case  9: return 'Reifeprüfung einer berufsbildenden höheren Schule';
			case 10: return 'Lehrabschlussprüfung';
			case 11: return 'Pflichtschule';
			default: return '';
		}
	}
	
	/**
	 * Liefert die passende Bezeichnung des Aktuellen Status
	 * @param $id ID des Status
	 */
	function getAktstatusBezeichnung($id)
	{
		switch($id)
		{
			case 100: return 'Mitarbeiter';
			case 101: return 'Lektor';
			case 102: return 'Koordinator';
			case 103: return 'Fachbereichsleiter';
			case 104: return 'Studiengangsleiter';
			case 150: return 'Ausgeschieden';
			default: return '';
		}
	}
	
	/**
	 * Prüft ob eine Kurzbezeichnung schon existiert. Falls eine mitarbeiter_id
	 * angegeben wird, dann wird dieser Datensatz von der ueberpruefung ausgeschlossen
	 * ( fuer Update eines Datensatzes)
	 */
	function kurzbz_exists($kurzbz, $mitarbeiter_id='')
	{
		if($kurzbz!='')
		{
			$this->errormsg = '';
			$qry = "SELECT count(*) as anz from mitarbeiter where kurzbez='$kurzbz'";
			if($mitarbeiter_id!='')
				$qry .= " AND mitarbeiter_pk<>".$mitarbeiter_id;
			
			if($result = pg_query($this->conn,$qry))
			{
				while ($row=pg_fetch_object($result))
				{				
					if($row->anz == 0)
					{				
						return false;				
					}
					else
					{ 
						return true;
					}
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim pruefen der Kurzbezeichnung';
				return false;
			}
		}
		return false;
	}	
	
	/**
	 * Liefert die naechste Personalnummer
	 */
	function getNextPersonalnr()
	{
		$qry = "SELECT max(persnr) AS persnr FROM mitarbeiter WHERE length(persnr)=(SELECT max(length(persnr)) FROM mitarbeiter)";
		if($row = pg_fetch_object(pg_query($this->conn,$qry)))
			return $row->persnr+1;
		else 
			return false;
	}
}
?>