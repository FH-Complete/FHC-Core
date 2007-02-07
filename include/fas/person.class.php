<?php
/**
 * Basisklasse Person (FAS-Online)
 * @create 06-03-2006
 */

class person
{
	var $conn;     // @var resource DB-Handle
	var $errormsg; // @var string
	var $new;      // @var boolean
	var $result = array(); // @var person Objekt
	
	//Tabellenspalten
	var $person_id;             	// @var integer
	var $aktstatus;             	// @var integer
	var $aktstatus_bezeichnung;  	// @var integer
	var $angelegtam;            	// @var timestamp
	var $anrede;                	// @var string
	var $anzahlderkinder;       	// @var integer
	var $bemerkung;             	// @var string
	var $bismelden;             	// @var boolean
	var $bismelden_bezeichnung; 	// @var boolean
	var $ersatzkennzeichen;     	// @var string
	var $familienname;          	// @var string
	var $familienstand;         	// @var integer ( 1=ledig, 2=verheiratet, 3=?, 4=?, 5=? ) 
	var $familienstand_bezeichnung;	// @var integer ( 1=ledig, 2=verheiratet, 3=?, 4=?, 5=? ) 
	var $gebdat;                	// @var date
	var $gebnation;             	// @var string
	var $gebort;                	// @var string
	var $geschlecht;            	// @var string	
	var $staatsbuergerschaft;   	// @var string
	var $svnr;                  	// @var string
	var $titelpost;             	// @var string Titel nach dem Namen (BA, MA, etc)
	var $titelpre;              	// @var string Titel vor dem Namen (Dr, Mag, Dipl.Ing, etc)
	var $uid;                   	// @var string
	var $vorname;               	// @var string
	var $vornamen;              	// @var string
	var $updateamum;            	// @var timestamp
	var $updatevon=0;             	// @var string
	
	/**
	 * Konstruktor - Uebergibt die Connection und Laedt optional eine Person
	 * @param $conn    Datenbank-Connection
	 *        $pers_id Person die geladen werden soll (default=null)
	 */
	function person($conn, $pers_id=null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		if($pers_id != null)
			$this->load($pers_id);
	}
	
	/**
	 * Laden einen Datensatz mit der Personal_id die uebergeben wird
	 * @param $person_id ID der Person die geladen werden soll
	 */
	function load($person_id)
	{
		//person_id auf gueltigkeit pruefen
		if(is_numeric($person_id) && $person_id!='')
		{
			$qry = "SELECT * FROM person WHERE person_pk=$person_id";
			if(!$res=pg_query($this->conn,$qry))
			{
				$this->errormsg = 'Fehler beim auslesen der Daten';
				return false;
			}
			
			if($row = pg_fetch_object($res))
			{	
				$this->person_id           = $row->person_pk;			
				$this->aktstatus           = $row->aktstatus;
				$this->angelegtam          = $row->angelegtam;
				$this->anrede              = $row->anrede;
				$this->anzahlderkinder     = $row->anzahlderkinder;
				$this->bemerkung           = $row->bemerkung;
				$this->bismelden           = ($row->bismelden=='J'?true:false);
				$this->ersatzkennzeichen   = $row->ersatzkennzeichen;
				$this->familienname        = $row->familienname;
				$this->familienstand       = $row->familienstand;
				$this->gebdat              = $row->gebdat;
				$this->gebnation           = $row->gebnation;
				$this->gebort              = $row->gebort;
				$this->geschlecht          = $row->geschlecht;
				$this->staatsbuergerschaft = $row->staatsbuergerschaft;
				$this->svnr                = $row->svnr;
				$this->titelpre            = $row->titel;
				$this->titlepost           = $row->postnomentitel;
				$this->uid                 = $row->uid;
				$this->vorname             = $row->vorname;
				$this->vornamen            = $row->vornamen;
				$this->updateamum          = $row->creationdate;
				$this->updatevon           = $row->creationuser;
			}
			else 
			{
				$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
				return false;
			}
			
			return true;
		}
		else 
		{
			$this->errormsg = "Die person_id muss eine Zahl sein";
			return false;
		}
	}
	
	// Clean stuff from a string
	 function clean_string1($string)
	 {
	 	$trans = array("ä" => "ae",
	 				   "Ä" => "Ae",
	 				   "ö" => "oe",
	 				   "Ö" => "Oe",
	 				   "ü" => "ue",
	 				   "Ü" => "Ue",
	 				   "á" => "a",
	 				   "à" => "a",
	 				   "é" => "e",
	 				   "è" => "e",
	 				   "ó" => "o",
	 				   "ò" => "o",
	 				   "í" => "i",
	 				   "ì" => "i",
	 				   "ú" => "u",
	 				   "ù" => "u",
	 				   "ß" => "ss");
		$string = strtr($string, $trans);
	    return ereg_replace("[^a-zA-Z0-9]", "", $string);
	    //[:space:]
	 }
 	
	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false wenn Variablen ungueltig sind
	 */
	function checkvars1()
	{
		//Hochkomma  herausfiltern
		$this->familienname      = str_replace("'","`", $this->familienname);
		$this->vorname           = str_replace("'","`", $this->vorname);
		$this->anrede            = str_replace("'","`", $this->anrede);
		$this->vornamen          = str_replace("'","`", $this->vornamen);
		$this->gebort            = str_replace("'","`", $this->gebort);
		$this->svnr              = str_replace("'","`", $this->svnr);
		$this->titelpre          = str_replace("'","`", $this->titelpre);
		$this->titelpost         = str_replace("'","`", $this->titelpost);
		$this->gebnation         = str_replace("'","`", $this->gebnation);
		$this->ersatzkennzeichen = str_replace("'","`", $this->ersatzkennzeichen);
		$this->bemerkung         = str_replace("'","`", $this->bemerkung);
		if(ereg("[^a-zA-Z0-9]", $this->uid))
		{
			$this->errormsg = "UID darf keine Umlaute oder Sonderzeichen enthalten";
			return false;
		}
		
		//Maximallaenge pruefen
		if(strlen($this->familienname)>255)
		{
			$this->errormsg = 'Familienname darf nicht laenger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->vorname)>255)
		{
			$this->errormsg = 'Vorname darf nicht laenger als 255 Zeichen sein';
			return false;
		}
		if(strlen($this->anrede)>20)
		{
			$this->errormsg = 'Anrede darf nicht laenger als 20 Zeichen sein';
			return false;
		}
		if(strlen($this->vornamen)>255)
		{
			$this->errormsg = 'Vornamen darf nicht laenger als 255 Zeichen sein';
		    return false;
		}
		if(strlen($this->geschlecht)>1)
		{
			$this->errormsg = 'Geschlecht darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(strlen($this->gebort)>255)
		{
			$this->errormsg = 'Geburtsort darf nicht laenger als 255 Zeichen sein';
		    return false;
		}
		if(strlen($this->svnr)!=10)
		{
			$this->errormsg = 'SVNR muss 10 Zeichen lang sein';
		    return false;
		}
		if(!is_numeric($this->svnr))
		{
			$this->errormsg = 'SVNR muss eine gueltige Zahl sein';
		    return false;
		}
		
		if($this->svnr=='0000000000') //Leere SVNR wird zum anlegen des neuen Leerdatensatzes benoetigt
			$this->svnr='';
		else 
		{
			//SVNR mit Pruefziffer pruefen
			//Die 4. Stelle in der SVNR ist die Pruefziffer
			//(Summe von (gewichtung[i]*svnr[i])) modulo 11 ergibt diese Pruefziffer
			//Falls nicht, ist die SVNR ungueltig
			$gewichtung = array(3,7,9,0,5,8,4,2,1,6);
			$erg=0;
			//Quersumme bilden
			for($i=0;$i<10;$i++)
				$erg += $gewichtung[$i] * $this->svnr{$i};
			
			if($this->svnr{3}!=($erg%11)) //Vergleichen der Pruefziffer mit Quersumme Modulo 11
			{
				$this->errormsg = 'SVNR ist ungueltig';
				return false;
			}
		}
		if(strlen($this->bismelden)>1)
		{
			$this->errormsg = 'bismelden darf nicht laenger als 1 Zeichen sein';
		    return false;
		}
		if(strlen($this->titelpre)>30)
		{
			$this->errormsg = 'titelpre darf nicht laenger als 30 Zeichen sein';
		    return false;
		}
		if(strlen($this->titelpost)>30)
		{
			$this->errormsg = 'titelpost darf nicht laenger als 30 Zeichen sein';
		    return false;
		}
		if(strlen($this->uid)>20)
		{
			$this->errormsg = 'uid darf nicht laenger als 20 Zeichen sein';         
			return false;
		}
		if(strlen($this->gebnation)>3)
		{
			$this->errormsg = 'Geburtsnation darf nicht laenger als 3 Zeichen sein';
		    return false;
		}
		if(strlen($this->staatsbuergerschaft)>3) 
		{
			$this->errormsg = 'Staatsbürgerschaft darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if(strlen($this->ersatzkennzeichen)>10) 
		{
			$this->errormsg = 'ersatzkennzeichen darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		
		//Zahlenwerte ueberpruefen
		$this->errormsg = 'Ein Zahlenfeld enthaelt ungueltige Zeichen';
		if(!is_numeric($this->familienstand) && $this->familienstand!='')   return false;
		if(!is_numeric($this->anzahlderkinder) && $this->anzahlderkinder!='') return false;
		if(!is_numeric($this->aktstatus) && $this->aktstatus!='')       return false;
		
		if($this->gebdat!='' && (time() - strtotime($this->gebdat))<315360000) // Wenn nicht aelter als 10 Jahre = 315360000 Sekunden
		{
			$this->errormsg = 'Geburtsdatum ist falsch: Person muss �lter als 10 Jahre sein';
			return false;
		}
		
		if($this->uid_exists($this->uid, $this->person_id))
		{			
			$this->errormsg = 'Diese UID existiert bereits';
			return false;
		}
		
		$this->errormsg='';
		return true;
	}
	
	/**
	 * Speichert die Daten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird eingefuegt
	 * ansonsten der datensatz $person_id upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->checkvars1())
			return false;
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{      
			//Naechste ID aus Sequence holen
			$qry = "SELECT nextval('person_seq') as id;";
			if(!$row=pg_fetch_object(pg_query($this->conn,$qry)))
			{
				$this->errormsg = "Fehler beim Auslesen der Sequence";
				return false;
			}
			$this->person_id = $row->id;
						
			$qry = "INSERT INTO person (person_pk, familienname, angelegtam, vorname, anrede, vornamen,".
			       " geschlecht, gebdat, gebort, staatsbuergerschaft, familienstand, svnr, anzahlderkinder,".
			       " ersatzkennzeichen, bemerkung, creationdate,creationuser, aktstatus, bismelden, titel, postnomentitel,".
			       " uid, gebnation) VALUES( $this->person_id,". 
			       " '$this->familienname', now(), '$this->vorname', '$this->anrede', '$this->vornamen',".
			       ($this->geschlecht!=''?"'$this->geschlecht'":"'M'").", ".
			       ($this->gebdat!=''?"'$this->gebdat'":'null').", '$this->gebort', '$this->staatsbuergerschaft',".
			       ($this->familienstand!=''?"'$this->familienstand'":'null').",".
			       " '$this->svnr', ".
			       ($this->anzahlderkinder!=''?"'$this->anzahlderkinder'":'null').",".
			       " '$this->ersatzkennzeichen', '$this->bemerkung', now(),".
			       " '$this->updatevon', '".($this->aktstatus>0?$this->aktstatus:100)."', '".($this->bismelden?'J':'N')."', '$this->titelpre', '$this->titelpost',".
			       (strlen($this->uid)>0?" '$this->uid'":'null').",".
			       " '$this->gebnation');";
			       
		}
		else
		{
			//peson_id auf gueltigkeit pruefen
			if(!is_numeric($this->person_id))
			{
				$this->errormsg = 'person_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry = "UPDATE person SET ".
			       " familienname='$this->familienname',".
			       " vorname='$this->vorname',".
			       " anrede='$this->anrede',".
			       " vornamen='$this->vornamen',".
			       " geschlecht=".($this->geschlecht!=''?"'$this->geschlecht'":"'M'").",".
			       " gebdat=".($this->gebdat!=''?"'$this->gebdat'":'null').",".
			       " gebort='$this->gebort',".
			       " staatsbuergerschaft='$this->staatsbuergerschaft',".
			       " familienstand=".($this->familienstand!=''?"'$this->familienstand'":'0').",".
			       " svnr=".($this->svnr!=''?"'$this->svnr'":'null').",".
			       " anzahlderkinder=".($this->anzahlderkinder!=''?"'$this->anzahlderkinder'":'0').",".
			       " ersatzkennzeichen='$this->ersatzkennzeichen',".
			       " bemerkung='$this->bemerkung',".
			       " aktstatus='$this->aktstatus',".
			       " bismelden='".($this->bismelden?'J':'N')."',".
			       " titel='$this->titelpre',".
			       " postnomentitel='$this->titelpost',".
			       " uid='$this->uid',".
			       " gebnation='$this->gebnation'".
			       " WHERE person_pk='$this->person_id'";			
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
			$this->errormsg = "Fehler beim Speichern des Person-Datensatzes:".$qry;
			return false;
		}
	}
	
	/**
	 * Loescht einen Datensatz
	 * @param $person_id ID des zu loeschenden Datensatzes
	 * @return true wenn OK false im Fehlerfall
	 */
	function delete($person_id)
	{
		//person_id auf Gueltigkeit pruefen
		if(!is_numeric($person_id) || $person_id=='')
		{
			$this->errormsg = 'Person_id muss eine Zahl sein';
			return false;
		}
		
		$qry = "Delete from person where person_pk=$person_id";
		
		if(!pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler beim Loeschen';
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
	 * Holt alle Personen aus der Datenbank
	 * @return true wenn OK, false im Fehlerfall
	 */ 
	function getAll()
	{
		/** Braucht zuviel Speicher
		
		$qry = "SELECT * FROM person";
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler beim auslesen der Datensaetze';
			return false;
		}
			
		while($row = pg_fetch_object($res))
		{
			$pers=new person($this->conn);
			
			$pers->person_id           = $row->person_pk;
			$pers->aktstatus           = $row->aktstatus;
			$pers->angelegtam          = $row->angelegtam;
			$pers->anrede              = $row->anrede;
			$pers->anzahlderkinder     = $row->anzahlderkinder;
			$pers->bemerkung           = $row->bemerkung;
			$pers->bismelden           = ($row->bismelden=='J'?true:false);
			$pers->ersatzkennzeichen   = $row->ersatzkennzeichen;
			$pers->familienname        = $row->familienname;
			$pers->familienstand       = $row->familienstand;
			$pers->gebdat              = $row->gebdat;
			$pers->gebnation           = $row->gebnation;
			$pers->gebort              = $row->gebort;
			$pers->geschlecht          = $row->geschlecht;
			$pers->staatsbuergerschaft = $row->staatsbuergerschaft;
			$pers->svnr                = $row->svnr;
			$pers->titelpre            = $row->titel;
			$pers->titelpost           = $row->postnomentitel;
			$pers->uid                 = $row->uid;
			$pers->vorname             = $row->vorname;
			$pers->vornamen            = $row->vornamen;
			$pers->updateamum          = $row->creationdate;
			$pers->updatevon           = $row->creationuser;
			
			$this->result[] = $pers;
		}
		return true;
		*/
		return false;
	}
		
	/**
	 * Prueft ob die UID schon vergeben ist. Wenn ein zweiter
	 * Parameter angegeben wird, wird diese ID von der ueberpruefung 
	 * ausgeschlossen ( fuer Update eines Datensatzes )
	 */
	function uid_exists($uid, $person_id='')
	{
		if($uid!='')
		{
			$this->errormsg = '';
			//Datenbank Check
			$qry = "SELECT count(*) as anz from person where uid='$uid'";
			if($person_id!='')
				$qry .= " AND person_pk<>".$person_id;

			if($result = pg_query($this->conn,$qry))
			{
				while ($row=pg_fetch_object($result))
				{
					if($row->anz == 0)
					{
						//Wurde deaktiviert weil der Zugriff vom auf den LDAP Server
						//vom der Calva aus nicht funktioniert
						//Ldap Check
						//$ds = ldap_connect(LDAP_SERVER);
						//$dn = "ou=People, dc=technikum-wien, dc=at";
						//$sr = ldap_search($ds,$dn,"uid=$uid");
						
						//if(ldap_count_entries($ds,$sr)>0)
						//	return true;
						//else
							return false;
					}
					else
						return true;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim checken der uid';
				return false;
			}
		}
		return false;
	}
	
	/**
	 * Aktualisiert den AktStatus
	 */
	function updateaktstatus($person_id)
	{
		$mitarbeiter_id = '';
		$qry = "Select mitarbeiter_pk from mitarbeiter where person_fk='$person_id'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
				$mitarbeiter_id = $row->mitarbeiter_pk;
			else 
			{
				$this->errormsg = 'Fehler beim ermitteln der Mitarbeiter_id';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim ermitteln der mitarbeiter_id';
			return false;
		}
		
		//Funktionen holen
		$qry = "Select funktion from funktion where ".
		       "studiensemester_fk = (Select studiensemester_pk from studiensemester where aktuell='J')".
		       " AND mitarbeiter_fk	= '$mitarbeiter_id'";
		if($result = pg_query($this->conn, $qry))
		{
			$fkt=array();
			$i=0;
			while($row=pg_fetch_object($result))
			{
				$fkt[$i]=$row->funktion;
				$i++;
			}
			
			//Aktstatus ermitteln
			if(in_array(5,$fkt)) //STGL
				$aktstatus = 104;
			elseif(in_array(6,$fkt)) //FBL
				$aktstatus = 103;
			elseif(in_array(2,$fkt)) //FBK
				$aktstatus = 102;
			elseif(in_array(1,$fkt)) //LKT
				$aktstatus = 101;
			else
				$aktstatus = 100; //Mitarbeiter
				
			$this->status = $aktstatus;
			//neuen akstatus setzen
			$qry = "Update person set aktstatus = $aktstatus where person_pk = $person_id";
			if(pg_query($qry))
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
				$this->errormsg = 'Fehler beim setzen des Aktstatus';
				return false;
			}
		}
	}
	
	function setaktstatus($status, $person_id)
	{
		$qry = "Update person set aktstatus = '$status' where person_pk='$person_id'";
		if(!pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Setzen des aktuellen Status'.$qry;
			return false;
		}
		return true;
	}
}
?>