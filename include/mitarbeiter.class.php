<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/*
 * Benoetigt: person.class.php
 *            benutzer.class.php
 *            functions.inc.php
 */
class mitarbeiter extends benutzer
{
	var $new;
	var $errormsg;
	var $result=array();

    //Tabellenspalten
	var $ausbildungcode;	//integer
	var $personalnummer;	//serial
	var $kurzbz;			//varchar(8)
	var $lektor;			//boolean
	var $fixangestellt;		//boolean
	var $standort_kurzbz;   //varchar(16)
	var $telefonklappe;		//varchar(25)
	var $ort_kurzbz;		//varchar(8)
	var $ext_id_mitarbeiter;	//bigint
	var $stundensatz;
	var $anmerkung;
	var $bismelden;

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional einen Mitarbeiter
	// * @param $conn        	Datenbank-Connection
	// *        $uid            Mitarbeiter der geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function mitarbeiter($conn, $uid=null, $unicode=false)
	{
		$this->conn = $conn;

		if($unicode!=null)
		{
			if($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else
				$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
	
			if(!pg_query($conn,$qry))
			{
				$this->errormsg= "Encoding konnte nicht gesetzt werden\n";
				return false;
			}
		}

		//Mitarbeiter laden
		if($uid!=null)
			$this->load($uid);
	}

	function load($uid)
	{
		if(!benutzer::load($uid))
			return false;

		$qry = "SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='$uid'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->ausbildungcode = $row->ausbildungcode;
				$this->personalnummer = $row->personalnummer;
				$this->kurzbz = $row->kurzbz;
				$this->lektor = ($row->lektor=='t'?true:false);
				$this->fixangestellt = ($row->fixangestellt=='t'?true:false);
				$this->standort_kurzbz = $row->standort_kurzbz;
				$this->telefonklappe = $row->telefonklappe;
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->stundensatz = $row->stundensatz;
				$this->anmerkung = $row->anmerkung;
				$this->ext_id_mitarbeiter = $row->ext_id;
				$this->bismelden = ($row->bismelden=='t'?true:false);
				return true;
			}
			else
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $uid\n";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden: $qry\n";
			return false;
		}
	}

	// ************************************************
	// * ueberprueft die Variablen auf Gueltigkeit
	// * @return true wenn gueltig, false im Fehlerfall
	// ************************************************
	function validate()
	{
		if(utf8_strlen($this->uid)>16)
		{
			$this->errormsg = "ID darf nicht laenger als 16 Zeichen sein\n";
			return false;
		}
		if($this->uid=='')
		{
			$this->errormsg = "UID muss eingegeben werden ".$this->personalnummer."\n";
			return false;
		}
		if($this->ausbildungcode!='' && !is_numeric($this->ausbildungcode))
		{
			$this->errormsg = "Ausbildungscode ist ungueltig\n";
			return false;
		}
		if($this->personalnummer!='' && !is_numeric($this->personalnummer))
		{
			$this->errormsg = "Personalnummer muss eine gueltige Zahl sein\n";
			return false;
		}
		if(utf8_strlen($this->kurzbz)>8)
		{
			$this->errormsg = "kurzbz darf nicht laenger als 8 Zeichen sein\n";
			return false;
		}
		if(utf8_strlen($this->ort_kurzbz)>8)
		{
			$this->errormsg = "Ort_kurzbz darf nicht laenger als 8 Zeichen sein\n";
			return false;
		}
		if(!is_bool($this->lektor))
		{
			$this->errormsg = "Lektor muss boolean sein ".$this->lektor."\n";
			return false;
		}
		if(!is_bool($this->fixangestellt))
		{
			$this->errormsg = "fixangestellt muss boolean sein\n";
			return false;
		}
		if(utf8_strlen($this->telefonklappe)>25)
		{
			$this->errormsg = "telefonklappe darf nicht laenger als 25 Zeichen sein\n";
			return false;
		}
		if(utf8_strlen($this->updatevon)>32)
		{
			$this->errormsg = "updatevon darf nicht laenger als 32 Zeichen sein\n";
			return false;
		}

		return true;
	}


	// *************************************************
	// * Speichert die Mitarbeiterdaten in die Datenbank
	// * @return true wenn ok, false im Fehlerfall
	// *************************************************
	function save($new=null, $savebenutzer=true)
	{
		//Variablen checken
		if(!$this->validate())
			return false;

		pg_query($this->conn,'BEGIN;');
		
		if($new==null)
			$new = $this->new;
			
		if($savebenutzer)
		{
			//Basisdaten speichern
			if(!benutzer::save())
			{
				pg_query($this->conn,'ROLLBACK;');
				return false;
			}
		}		

		if($new)
		{

			//Neuen Datensatz anlegen
			$qry = "INSERT INTO public.tbl_mitarbeiter(mitarbeiter_uid, ausbildungcode, personalnummer, kurzbz, lektor, ort_kurzbz,
			                    fixangestellt, standort_kurzbz, telefonklappe, anmerkung, stundensatz, updateamum, updatevon, insertamum, insertvon, ext_id, bismelden)

			        VALUES('".addslashes($this->uid)."',".
			 	 	$this->addslashes($this->ausbildungcode).",".
			 	 	$this->addslashes($this->personalnummer).",". //TODO: in Produktivversion nicht angeben
			 	 	$this->addslashes($this->kurzbz).','.
			 	 	($this->lektor?'true':'false').','.
			 	 	$this->addslashes($this->ort_kurzbz).','.
					($this->fixangestellt?'true':'false').','.
					$this->addslashes($this->standort_kurzbz).','.
					$this->addslashes($this->telefonklappe).','.
					$this->addslashes($this->anmerkung).','.
					$this->addslashes($this->stundensatz).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).', '.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).', '.
					$this->addslashes($this->ext_id_mitarbeiter).','.
					($this->bismelden?'true':'false').');';
		}
		else
		{
			//Bestehenden Datensatz updaten
			$qry = 'UPDATE public.tbl_mitarbeiter SET'.
			       ' ausbildungcode='.$this->addslashes($this->ausbildungcode).','.
			       " personalnummer=".$this->addslashes($this->personalnummer).",". //TODO: in Produktivversion nicht angeben
			       ' kurzbz='.$this->addslashes($this->kurzbz).','.
			       ' lektor='.($this->lektor?'true':'false').','.
			       ' fixangestellt='.($this->fixangestellt?'true':'false').','.
			       ' bismelden='.($this->bismelden?'true':'false').','.
			       ' standort_kurzbz='.$this->addslashes($this->standort_kurzbz).','.
			       ' telefonklappe='.$this->addslashes($this->telefonklappe).','.
			       ' ort_kurzbz='.$this->addslashes($this->ort_kurzbz).','.
			       ' anmerkung='.$this->addslashes($this->anmerkung).','.
			       ' stundensatz='.$this->addslashes($this->stundensatz).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).','.
			       ' ext_id='.$this->addslashes($this->ext_id_mitarbeiter).
			       " WHERE mitarbeiter_uid='".addslashes($this->uid)."';";
		}

		if(pg_query($this->conn,$qry))
		{
			pg_query($this->conn,'COMMIT;');
			//Log schreiben
			return true;
		}
		else
		{
			pg_query($this->conn,'ROLLBACK;');
			$this->errormsg = "*****\nFehler beim Speichern des Mitarbeiter-Datensatzes: ".$this->uid."\n".$qry."\n".pg_errormessage($this->conn)."\n*****\n";
			return false;
		}
	}
	/**
	 * gibt array mit allen Mitarbeitern zurueck
	 * @return array mit Mitarbeitern
	 */
	function getMitarbeiter($lektor=true,$fixangestellt=null,$stg_kz=null,$fachbereich_id=null)
	{
		$sql_query='SELECT DISTINCT campus.vw_mitarbeiter.* FROM campus.vw_mitarbeiter
					LEFT OUTER JOIN public.tbl_benutzerfunktion USING (uid)
					WHERE TRUE';
		
		if (!is_null($lektor))
		{
			$sql_query.=' AND';
			if (!$lektor)
				$sql_query.=' NOT';
			$sql_query.=' lektor';
		}

		if (!is_null($fixangestellt))
		{
			$sql_query.=' AND';
			if (!$fixangestellt)
				$sql_query.=' NOT';
			$sql_query.=' fixangestellt';
		}

		if (!is_null($stg_kz))
			$sql_query.=' AND studiengang_kz='.$stg_kz;
		//if ($fachbereich_id!=null)
		//	$sql_query.=' AND fachbereich_id='.$fachbereich_id;
	    $sql_query.=' ORDER BY nachname, vornamen, kurzbz';
	    //echo $sql_query;
		if(!($erg=pg_query($this->conn, $sql_query)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new mitarbeiter($this->conn);
			// Personendaten
			$l->uid=$row->uid;
			$l->titelpre=$row->titelpre;
			$l->titelpost=$row->titelpost;
			$l->vorname=$row->vorname;
			$l->vornamen=$row->vornamen;
			$l->nachname=$row->nachname;
			$l->gebdatum=$row->gebdatum;
			$l->gebort=$row->gebort;
			$l->gebzeit=$row->gebzeit;
			$l->foto=$row->foto;
			$l->anmerkungen=$row->anmerkungen;
			$l->aktiv=$row->aktiv=='t'?true:false;
			$l->homepage=$row->homepage;
			$l->updateamum=$row->updateamum;
			$l->updatevon=$row->updatevon;
			// Lektorendaten
			$l->personalnummer=$row->personalnummer;
			$l->kurzbz=$row->kurzbz;
			$l->lektor=$row->lektor=='t'?true:false;
			$l->bismelden=$row->bismelden=='t'?true:false;
			$l->fixangestellt=$row->fixangestellt=='t'?true:false;
			$l->standort_kurzbz = $row->standort_kurzbz;
			$l->telefonklappe=$row->telefonklappe;
			//$l->ort_kurzbz=$row->ort_kurzbz;
			// Lektor in Array speichern
			$result[]=$l;
		}
		return $result;
	}

	function getMitarbeiterStg($lektor,$fixangestellt, $stge, $fkt_kurzbz)
	{
		$sql_query='SELECT DISTINCT campus.vw_mitarbeiter.*, tbl_benutzerfunktion.studiengang_kz FROM campus.vw_mitarbeiter
					JOIN public.tbl_benutzerfunktion USING (uid)
					WHERE';
		if (!$lektor)
			$sql_query.=' NOT';
		$sql_query.=' lektor';
		if ($fixangestellt!=null)
		{
			$sql_query.=' AND';
			if (!$fixangestellt)
				$sql_query.=' NOT';
			$sql_query.=' fixangestellt';
		}
		if($fkt_kurzbz!='')
		{
			$sql_query.=" AND funktion_kurzbz='$fkt_kurzbz'";
		}
		if ($stge!=null)
		{
			$in='';
			foreach ($stge as $stg)
			{
				$in.=','.$stg;
				if($stg==0)
				{
					$in='';
					break;
				}
			}
			if($in!='')
				$sql_query.=' AND studiengang_kz in (-1'.$in.')';
		}
	    $sql_query.=' ORDER BY studiengang_kz, nachname, vorname, kurzbz';
	    //echo $sql_query;

		if(!($erg=pg_query($this->conn, $sql_query)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new mitarbeiter($this->conn);
			// Personendaten
			$l->uid=$row->uid;
			$l->titelpre=$row->titelpre;
			$l->titelpost=$row->titelpost;
			$l->vorname=$row->vorname;
			$l->vornamen=$row->vornamen;
			$l->nachname=$row->nachname;
			$l->gebdatum=$row->gebdatum;
			$l->gebort=$row->gebort;
			$l->gebzeit=$row->gebzeit;
			$l->foto=$row->foto;
			$l->anmerkungen=$row->anmerkungen;
			$l->aktiv=$row->aktiv=='t'?true:false;
			$l->bismelden=$row->bismelden=='t'?true:false;
			$l->homepage=$row->homepage;
			$l->updateamum=$row->updateamum;
			$l->updatevon=$row->updatevon;
			// Lektorendaten
			$l->personalnummer=$row->personalnummer;
			$l->kurzbz=$row->kurzbz;
			$l->lektor=$row->lektor=='t'?true:false;
			$l->fixangestellt=$row->fixangestellt=='t'?true:false;
			$l->standort_kurzbz = $row->standort_kurzbz;
			$l->telefonklappe=$row->telefonklappe;
			$l->studiengang_kz = $row->studiengang_kz;
			//$l->ort_kurzbz=$row->ort_kurzbz;
			// Lektor in Array speichern
			$result[]=$l;
		}
		return $result;
	}

	function getMitarbeiterZeitsperre($von,$bis)
	{
		$sql_query="SELECT DISTINCT nachname, vorname, uid,titelpre, titelpost, vornamen
				FROM campus.vw_mitarbeiter JOIN campus.tbl_zeitsperre ON (uid=mitarbeiter_uid)
				WHERE ('$von'<=bisdatum AND '$bis'>=bisdatum) OR ('$bis'>=vondatum AND '$von'<=vondatum) ORDER BY nachname";
	    //echo $sql_query;

		if(!($erg=pg_query($this->conn, $sql_query)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new mitarbeiter($this->conn);
			// Personendaten
			$l->uid=$row->uid;
			$l->titelpre=$row->titelpre;
			$l->titelpost=$row->titelpost;
			$l->vorname=$row->vorname;
			$l->vornamen=$row->vornamen;
			$l->nachname=$row->nachname;
			//$l->gebdatum=$row->gebdatum;
			//$l->gebort=$row->gebort;
			//$l->gebzeit=$row->gebzeit;
			//$l->foto=$row->foto;
			//$l->anmerkungen=$row->anmerkungen;
			//$l->aktiv=$row->aktiv=='t'?true:false;
			//$l->homepage=$row->homepage;
			//$l->updateamum=$row->updateamum;
			//$l->updatevon=$row->updatevon;
			// Lektorendaten
			//$l->personalnummer=$row->personalnummer;
			//$l->kurzbz=$row->kurzbz;
			//$l->lektor=$row->lektor=='t'?true:false;
			//$l->fixangestellt=$row->fixangestellt=='t'?true:false;
			//$l->standort_kurzbz = $row->standort_kurzbz;
			//$l->telefonklappe=$row->telefonklappe;
			// Lektor in Array speichern
			$result[]=$l;
		}
		return $result;
	}
	
	// ******************************************
	// * Laedt alle Mitarbeiter einer Lehreinheit
	// * @param lehreinheit_id
	// * @return true wenn ok, false wenn Fehler
	// ******************************************
	function getMitarbeiterFromLehreinheit($lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT uid, vorname, vornamen, nachname, titelpre, titelpost, kurzbz FROM lehre.tbl_lehreinheitmitarbeiter JOIN campus.vw_mitarbeiter ON(mitarbeiter_uid=uid) 
				WHERE lehreinheit_id='$lehreinheit_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new mitarbeiter($this->conn, null, null);
				
				$obj->uid = $row->uid;
				$obj->vorname = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->titelpre = $row->titelpre;
				$obj->titelpost = $row->titelpost;
				$obj->kurzbz = $row->kurzbz;
				$obj->vornamen = $row->vornamen;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	// *************************************************
	// * Laedt alle Mitarbeiter einer Lehrveranstaltung
	// * @param lehrveranstaltung_id
	// * @return true wenn ok, false wenn Fehler
	// *************************************************
	function getMitarbeiterFromLehrveranstaltung($lehrveranstaltung_id)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT uid, vorname, vornamen, nachname, titelpre, titelpost, kurzbz FROM lehre.tbl_lehreinheitmitarbeiter, campus.vw_mitarbeiter, lehre.tbl_lehreinheit
				WHERE lehrveranstaltung_id='$lehrveranstaltung_id' AND mitarbeiter_uid=uid AND tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new mitarbeiter($this->conn, null, null);
				
				$obj->uid = $row->uid;
				$obj->vorname = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->titelpre = $row->titelpre;
				$obj->titelpost = $row->titelpost;
				$obj->kurzbz = $row->kurzbz;
				$obj->vornamen = $row->vornamen;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	function getPersonal($fix, $stgl, $fbl, $aktiv, $karenziert, $ausgeschieden, $studiensemester_kurzbz)
	{
		$qry = "SELECT distinct on(person_id) *, tbl_benutzer.aktiv as aktiv, tbl_mitarbeiter.insertamum, tbl_mitarbeiter.insertvon FROM ((public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid)) JOIN public.tbl_person USING(person_id)) LEFT JOIN public.tbl_benutzerfunktion USING(uid) WHERE true";
		
		if($fix=='true')
			$qry .= " AND fixangestellt=true";
		if($fix=='false')
			$qry .= " AND fixangestellt=false";
		if($stgl)
			$qry .= " AND funktion_kurzbz='stgl'";
		if($fbl)
			$qry .= " AND funktion_kurzbz='fbl'";
		if($aktiv=='true')
			$qry .= " AND tbl_benutzer.aktiv=true";
		if($aktiv=='false')
			$qry .= " AND tbl_benutzer.aktiv=false";
		if($karenziert)
			$qry .= " AND uid IN (SELECT mitarbeiter_uid FROM bis.tbl_bisverwendung WHERE beschausmasscode='5' AND (ende>now() OR ende is null))"; //beginn<(SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz='$studiensemester_kurzbz') AND ende<(SELECT ende FROM public.tbl_studiensemester WHERE studiensemester_kurzbz='$studiensemester_kurzbz')
		if($ausgeschieden)
		{
			$qry.=" AND tbl_benutzer.aktiv=false"; // AND NOT EXISTS(SELECT * FROM bis.tbl_bisverwendung WHERE (ende>now() or ende is null) AND tbl_bisverwendung.mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid)
		}
		//echo $qry;
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new mitarbeiter($this->conn, null, null);
				
				$obj->person_id = $row->person_id;
				$obj->staatsbuergerschaft = $row->staatsbuergerschaft;
				$obj->geburtsnation = $row->geburtsnation;
				$obj->sprache = $row->sprache;
				$obj->anrede = $row->anrede;
				$obj->titelpost = $row->titelpost;
				$obj->titelpre = $row->titelpre;
				$obj->nachname = $row->nachname;
				$obj->vorname = $row->vorname;
				$obj->vornamen = $row->vornamen;
				$obj->gebdatum = $row->gebdatum;
				$obj->gebort = $row->gebort;
				$obj->gebzeit = $row->gebzeit;
				$obj->anmerkungen = $row->anmerkungen;
				$obj->homepage = $row->anmerkungen;
				$obj->svnr = $row->svnr;
				$obj->ersatzkennzeichen = $row->ersatzkennzeichen;
				$obj->familienstand = $row->familienstand;
				$obj->geschlecht = $row->geschlecht;
				$obj->anzahlkinder = $row->anzahlkinder;
				$obj->bnaktiv = ($row->aktiv=='t'?true:false);
				$obj->uid = $row->uid;
				$obj->personalnummer = $row->personalnummer;
				$obj->telefonklappe = $row->telefonklappe;
				$obj->kurzbz = $row->kurzbz;
				$obj->lektor = ($row->lektor=='t'?true:false);
				$obj->fixangestellt = ($row->fixangestellt=='t'?true:false);
				$obj->bismelden = ($row->bismelden=='t'?true:false);
				$obj->stundensatz = $row->stundensatz;
				$obj->ausbildungcode = $row->ausbildungcode;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->standort_kurzbz = $row->standort_kurzbz;
				$obj->anmerkung = $row->anmerkung;				
				$obj->alias = $row->alias;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				
				$this->result[] = $obj;
			}
			return false;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	// ****
	// * Prueft ob die Kurzbz bereits existiert
	// ****
	function kurzbz_exists($kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_mitarbeiter WHERE kurzbz='".addslashes($kurzbz)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
			{
				$this->errormsg = '';
				return true;
			}
			else 
			{
				$this->errormsg = '';
				return false;
			}
				
		}
		else 
		{
			$this->errormsg = 'Fehler bei DatenbankAbfrage';
			return false;
		}
		
	}
	
	// *************************************
	// * Laedt die Mitarbeiter deren
	// * Nachname mit $filter beginnt
	// *************************************
	function getMitarbeiterFilter($filter)
	{
		$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE nachname ~* '".addslashes($filter).".*'";
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new mitarbeiter($this->conn, null, null);
				
				$obj->uid = $row->uid;
				$obj->vorname = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->titelpre = $row->titelpre;
				$obj->titelpost = $row->titelpost;
				$obj->kurzbz = $row->kurzbz;
				$obj->vornamen = $row->vornamen;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>