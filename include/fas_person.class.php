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
/** 
 * Klasse fas_person (FAS-Online)
 * @create 11-12-2006
 */

class fas_person
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 		// @var string
	var $result = array(); 	// @var fachbereich Objekt 
	
	//Tabellenspalten
	var $geburtsnation;		// @var string
	var $anrede;			// @var string
	var $titelpost;			// @var string
	var $titelpre;			// @var string
	var $nachname;		// @var string
	var $vorname;		// @var string
	var $vornamen;		// @var string
	var $gebdatum;		// @var date
	var $gebort;			// @var string
	var $anmerkungen;		// @var string
	var $svnr;			// @var string
	var $ersatzkennzeichen;	// @var string
	var $familienstand;		// @var string
	var $anzahlkinder;		// @var smallint
	var $staatsbuergerschaft;	// @var string
	var $geschlecht;		// @var string
	var $insertamum;		// @var timestamp
	var $insertvon;		// @var string
	var $ext_id;			// @var bigint
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $person_id ID der zu ladenden Person
	 */
	function fas_person($conn, $person_id=null)
	{
		$this->conn = $conn;
		// if($person_id != null) $this->load($person_id);
	}
	
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	function validate($row)
	{
		$this->geburtsnation = str_replace("'",'´',$this->geburtsnation);
		$this->anrede = str_replace("'",'´',$this->anrede);
		$this->titelpost = str_replace("'",'´',$this->titelpost);
		$this->titelpre = str_replace("'",'´',$this->titelpre);
		$this->nachname = str_replace("'",'´',$this->nachname);
		$this->vorname = str_replace("'",'´',$this->vorname);
		$this->vornamen = str_replace("'",'´',$this->vornamen);
		$this->anmerkungen = str_replace("'",'´',$this->anmerkungen);
		$this->svnr = str_replace("'",'´',$this->svnr);
		$this->ersatzkennzeichen = str_replace("'",'´',$this->ersatzkennzeichen);
		
		//Laenge Pruefen
		if(strlen($this->geburtsnation)>3)           
		{
			$this->errormsg = "Geburtsnation darf nicht laenger als 3 Zeichen sein bei <b>$this->person_pk</b> - $this->geburtsnation";
			return false;
		}
		if(strlen($this->anrede)>16)           
		{
			$this->errormsg = "Anrede darf nicht laenger als 16 Zeichen sein bei <b>$this->person_pk</b> - $this->anrede";
			return false;
		}
		if(strlen($this->titelpost)>32)           
		{
			$this->errormsg = "Titelpost darf nicht laenger als 32 Zeichen sein bei <b>$this->person_pk</b> - $this->titelpost";
			return false;
		}
		if(strlen($this->titelpre)>64)           
		{
			$this->errormsg = "Titelpre darf nicht laenger als 64 Zeichen sein bei <b>$this->person_pk</b> - $this->titelpre";
			return false;
		}
		if(strlen($this->nachname)>64)           
		{
			$this->errormsg = "Nachname darf nicht laenger als 64 Zeichen sein bei <b>$this->person_pk</b> - $this->nachname";
			return false;
		}
		if(strlen($this->vorname)>32)           
		{
			$this->errormsg = "Vorname darf nicht laenger als 32 Zeichen sein bei <b>$this->person_pk</b> - $this->vorname";
			return false;
		}
		if(strlen($this->vornamen)>128)           
		{
			$this->errormsg = "Vornamen darf nicht laenger als 128 Zeichen sein bei <b>$this->person_pk</b> - $this->vornamen";
			return false;
		}
		if(strlen($this->anmerkungen)>256)           
		{
			$this->errormsg = "Anmerkungen (Bemerkung) darf nicht laenger als 256 Zeichen sein bei <b>$this->person_pk</b> - $this->bemerkung";
			return false;
		}
		if(strlen($this->svnr)>10)           
		{
			$this->errormsg = "SVNr darf nicht laenger als 8 Zeichen sein bei <b>$this->person_pk</b> - $this->svnr";
			return false;
		}
		if(strlen($this->ersatzkennzeichen)>10)           
		{
			$this->errormsg = "Ersatzkennzeichen darf nicht laenger als 8 Zeichen sein bei <b>$this->person_pk</b> - $this->ersatzkennzeichen";
			return false;
		}
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
			//Pruefen ob person_id gueltig ist
			if($this->person_id == '' || !is_numeric(person_id))
			{
				$this->errormsg = 'person_id ungueltig!';
				return false;
			}
			//Neuen Datensatz anlegen		
			$qry = 'INSERT INTO tbl_person (geburtsnation, anrede, titelpost, titelpre, nachname, vorname, vornamen,
					gebdatum, gebort, anmerkungen, svnr, ersatzkennzeichen, familienstand, anzahlkinder, 
					staatsbuergerschaft, geschlecht, insertamum, insertvon , ext_id ) VALUES ('.
				$this->addslashes($this->geburtsnation).', '.
				$this->addslashes($this->anrede).', '.
				$this->addslashes($this->titelpost).', '.
				$this->addslashes($this->titelpre).', '.
				$this->addslashes($this->nachname).', '.
				$this->addslashes($this->vorname).', '.
				$this->addslashes($this->vornamen).', '.
				$this->addslashes($row->gebdatum).', '.
				$this->addslashes($this->gebort).', '.
				$this->addslashes($this->anmerkungen).', '.
				$this->addslashes($this->svnr).', '.
				$this->addslashes($this->ersatzkennzeichen).', '.
				$this->addslashes($this->familienstand).', '.
				$this->addslashes($this->anzahlkinder).', '.
				$this->addslashes($this->staatsbuergerschaft).', '.
				$this->addslashes($this->geschlecht).', '.
				$this->addslashes($this->insertamum).', '.
				'"FASsync" '.
				$this->addslashes($this->ext_id).'); ';
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			//Pruefen ob person_id gueltig ist
			if($this->person_id == '' || !is_numeric(person_id))
			{
				$this->errormsg = 'person_id ungueltig.';
				return false;
			}
			
			$qry = 'UPDATE tbl_person SET '. 
					'geburtsnation'.$this->addslashes($this->gebnation).', '.
					'anrede='.$this->addslashes($this->anrede).', '.
					'titelpost='.$this->addslashes($this->titelpost).', '.
					'titelpre='.$this->addslashes($this->titelpre).', '.
					'nachname='.$this->addslashes($this->nachname).', '.
					'vorname='.$this->addslashes($this->vorname).', '.
					'vornamen='.$this->addslashes($this->vornamen).', '.
					'gebdatum='.$this->addslashes($this->gebdatum).', '.
					'gebort='.$this->addslashes($this->gebort).', '.
					'anmerkungen='.$this->addslashes($this->anmerkungen).', '.
					'svnr='.$this->addslashes($this->svnr).', '.
					'ersatzkennzeichen='.$this->addslashes($this->ersatzkennzeichen).', '.
					'familienstand='.$this->addslashes($this->familienstand).', '.
					'anzahlkinder='.$this->addslashes($this->anzahlkinder).', '.
					'staatsbuergerschaft='.$this->addslashes($this->staatsbuergerschaft).', '.
					'geschlecht='.$row->addslashes($this->geschlecht).', '.
					'insertamum='.$this->addslashes($this->insertamum).', '.
					'insertvon= FASsync, '.
					'WHERE ext_id = '.$this->addslashes($this->ext_id).';';
		}
		
		if(pg_query($this->conn, $qry))
		{
			/*//Log schreiben
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
			}*/
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}		
	}
}
?>