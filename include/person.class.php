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

class person
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $personen = array(); // person Objekt
	var $done=false;	// boolean
	
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
	var $svnr;			// char(10)
	var $ersatzkennzeichen; // char(10)
	var $familienstand;     // char(1)
	var $anzahlkinder;      // smalint
	var $aktiv;             // boolean
	var $insertamum;        // timestamp
	var $insertvon;         // varchar(16)
	var $updateamum;        // timestamp
	var $updatevon;         // varchar(16)
	var $geschlecht;	// varchar(1)
	var $staatsbuergerschaft;	// varchar(3)
	var $geburtsnation;	// varchar(3);
	var $ext_id;            // bigint
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Person
	// * @param $conn        	Datenbank-Connection
	// *        $person_id      Person die geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function person($conn, $person_id=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else 
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
			
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}
		
		if($person_id != null)
			$this->load($person_id);
	}
	
	// *********************************************************
	// * Laedt Person mit der uebergebenen ID
	// * @param $person_id ID der Person die geladen werden soll
	// *********************************************************
	function load($person_id)
	{
		//person_id auf gueltigkeit pruefen
		if(is_numeric($person_id) && $person_id!='')
		{
			$qry = "SELECT person_id, sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen,
                           gebdatum, gebort, gebzeit, foto, anmerkungen, homepage, svnr, ersatzkennzeichen, 
                           familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon, ext_id 
			        FROM public.tbl_person WHERE person_id='$person_id'";
			
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
				$this->aktiv = ($row->aktiv=='t'?true:false);
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->ext_id = $row->ext_id;
				$this->geschlecht = $row->geschlecht;
				$this->staatsbuergerschaft = $row->staatsbuergerschaft;
				$this->geburtsnation = $row->geburtsnation;
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
			$this->errormsg = 'Die person_id muss eine gueltige Zahl sein';
			return false;
		}
	}
	
	// *******************************************
	// * Prueft die Variablen vor dem Speichern 
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(strlen($this->sprache)>16)
		{
			$this->errormsg = 'Sprache darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->anrede)>16)
		{
			$this->errormsg = 'Anrede darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->titelpost)>32)
		{
			$this->errormsg = 'Titelpost darf nicht laenger als 32 Zeichen sein';
			return false;
		} 
		if(strlen($this->titelpre)>64)
		{
			$this->errormsg = 'Titelpre darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(strlen($this->nachname)>64)
		{
			$this->errormsg = 'Nachname darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if($this->nachname=='' || is_null($this->nachname))
		{
			$this->errormsg = 'Nachname muss eingegeben werden';
			return false;
		}		
		
		if(strlen($this->vorname)>32)
		{
			$this->errormsg = 'Vorname darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(strlen($this->vornamen)>128)
		{
			$this->errormsg = 'Vornamen darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		//ToDo Gebdatum pruefen -> laut bis muss student aelter als 10 Jahre sein
		/*if(strlen($this->gebdatum)==0 || is_null($this->gebdatum))
		{
			$this->errormsg = 'Geburtsdatum muss eingegeben werden';
			return false;
		}*/
		if(strlen($this->gebort)>128)
		{
			$this->errormsg = 'Geburtsort darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if($this->foto!='' && !is_numeric($this->foto))
		{
			$this->errormsg = 'FotoOID ist ungueltig';
			return false;
		}
		/*if(strlen($this->anmerkungen)>256)
		{
			$this->errormsg = 'Anmerkungen darf nicht laenger als 256 Zeichen sein';
			return false;
		}*/
		if(strlen($this->homepage)>256)
		{
			$this->errormsg = 'Homepage darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(strlen($this->svnr)>10)
		{
			$this->errormsg = 'SVNR darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if(strlen($this->ersatzkennzeichen)>10)
		{
			$this->errormsg = 'Ersatzkennzeichen darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if(strlen($this->familienstand)>1)
		{
			$this->errormsg = 'Familienstand ist ungueltig';
			return false;
		}
		if($this->anzahlkinder!='' && !is_numeric($this->anzahlkinder))
		{
			$this->errormsg = 'Anzahl der Kinder ist ungueltig';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = 'Aktiv ist ungueltig';
			return false;
		}
		if(strlen($this->insertvon)>16)
		{
			$this->errormsg = 'Insertvon darf nicht laenger als 16 Zeichen sien';
			return false;
		}
		if(strlen($this->updatevon)>16)
		{
			$this->errormsg = 'Updatevon darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->ext_id!='' && !is_numeric($this->ext_id))
		{
			$this->errormsg = 'Ext_ID ist keine gueltige Zahl';
			return false;
		}
		if(strlen($this->geschlecht)>1)
		{
			$this->errormsg = 'geschlecht darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(strlen($this->geburtsnation)>3)
		{
			$this->errormsg = 'Geburtsnation darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if(strlen($this->staatsbuergerschaft)>3)
		{
			$this->errormsg = 'Staatsbuergerschaft darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if($this->geschlecht!='m' && $this->geschlecht!='w')
		{
			$this->errormsg = 'Geschlecht muß entweder w oder m sein!';
		}
		
		return true;		
	}
	
	// ************************************************
	// * wenn $var '' ist wird "null" zurueckgegeben
	// * wenn $var !='' ist werden datenbankkritische 
	// * Zeichen mit backslash versehen und das Ergebnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	// ************************************************************
	// * Speichert die Personendaten in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz mit $person_id upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{		
		//Variablen auf Gueltigkeit pruefen
		if(!person::validate())
			return false;
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO public.tbl_person (sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen, 
			                    gebdatum, gebort, gebzeit, foto, anmerkungen, homepage, svnr, ersatzkennzeichen, 
			                    familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon,
			                    geschlecht, geburtsnation, staatsbuergerschaft, ext_id)
			        VALUES('.$this->addslashes($this->sprache).','.
					$this->addslashes($this->anrede).','.
					$this->addslashes($this->titelpost).','.
				        $this->addslashes($this->titelpre).','.
				        $this->addslashes($this->nachname).','.
				        $this->addslashes($this->vorname).','.
				        $this->addslashes($this->vornamen).','.
				        $this->addslashes($this->gebdatum).','.
				        $this->addslashes($this->gebort).','.
				        $this->addslashes($this->gebzeit).','.
				        $this->addslashes($this->foto).','.
				        $this->addslashes($this->anmerkungen).','.
				        $this->addslashes($this->homepage).','.
				        $this->addslashes($this->svnr).','.
				        $this->addslashes($this->ersatzkennzeichen).','.
				        $this->addslashes($this->familienstand).','.
				        $this->addslashes($this->anzahlkinder).','.
				        ($this->aktiv?'true':'false').','.
				        $this->addslashes($this->insertamum).','.
				        $this->addslashes($this->insertvon).','.
				        $this->addslashes($this->updateamum).','.
				        $this->addslashes($this->updatevon).','.
				        $this->addslashes($this->geschlecht).','.
				        $this->addslashes($this->geburtsnation).','.
				        $this->addslashes($this->staatsbuergerschaft).','.
				        $this->addslashes($this->ext_id).');';
				        $this->done=true;
		}
		else
		{
			//person_id auf gueltigkeit pruefen
			if(!is_numeric($this->person_id))
			{				
				$this->errormsg = 'person_id muss eine gueltige Zahl sein';
				return false;
			}
			
			//update nur wenn änderungen gemacht
			$qry="SELECT * FROM public.tbl_person WHERE person_id='$this->person_id';";
			if($result = pg_query($this->conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$update=false;			
					if($row->sprache!=$this->sprache) 				$update=true;
					if($row->anrede!=$this->anrede) 					$update=true;
					if($row->titelpost!=$this->titelpost) 					$update=true;
					if($row->titelpre!=$this->titelpre) 					$update=true;
					if($row->nachname!=$this->nachname) 				$update=true;
					if($row->vorname!=$this->vorname) 				$update=true;
					if($row->vornamen!=$this->vornamen) 				$update=true;
					if($row->gebdatum!=$this->gebdatum) 				$update=true;
					if($row->gebort!=$this->gebort) 					$update=true;
					if($row->gebzeit!=$this->gebzeit) 					$update=true;
					if($row->foto!=$this->foto) 						$update=true;
					if($row->anmerkungen!=$this->anmerkungen) 			$update=true;
					if($row->homepage!=$this->homepage) 				$update=true;
					if($row->svnr!=$this->svnr) 						$update=true;
					if($row->ersatzkennzeichen!=$this->ersatzkennzeichen) 	$update=true;
					if($row->familienstand!=$this->familienstand) 			$update=true;
					if($row->anzahlkinder!=$this->anzahlkinder) 			$update=true;
					if($row->aktiv!=$this->aktiv) 					$update=true;
					if($row->geburtsnation!=$this->geburtsnation) 			$update=true;
					if($row->geschlecht!=$this->geschlecht) 				$update=true;
					if($row->staatsbuergerschaft!=$this->staatsbuergerschaft)	$update=true;
					
					
					if($update)
					{
						$qry = 'UPDATE public.tbl_person SET'.
						       ' sprache='.$this->addslashes($this->sprache).','.
						       ' anrede='.$this->addslashes($this->anrede).','.
						       ' titelpost='.$this->addslashes($this->titelpost).','.
						       ' titelpre='.$this->addslashes($this->titelpre).','.
						       ' nachname='.$this->addslashes($this->nachname).','.
						       ' vorname='.$this->addslashes($this->vorname).','.
						       ' vornamen='.$this->addslashes($this->vornamen).','.
						       ' gebdatum='.$this->addslashes($this->gebdatum).','.
						       ' gebort='.$this->addslashes($this->gebort).','.
						       ' gebzeit='.$this->addslashes($this->gebzeit).','.
						       ' foto='.$this->addslashes($this->foto).','.
						       ' anmerkungen='.$this->addslashes($this->anmerkungen).','.
						       ' homepage='.$this->addslashes($this->homepage).','.
						       ' svnr='.$this->addslashes($this->svnr).','.
						       ' ersatzkennzeichen='.$this->addslashes($this->ersatzkennzeichen).','.
						       ' familienstand='.$this->addslashes($this->familienstand).','.
						       ' anzahlkinder='.$this->addslashes($this->anzahlkinder).','.
						       ' aktiv='.($this->aktiv?'true':'false').','.
						       ' updateamum='.$this->addslashes($this->updateamum).','.
						       ' updatevon='.$this->addslashes($this->updatevon).','.
						       ' geschlecht='.$this->addslashes($this->geschlecht).','.
						       ' geburtsnation='.$this->addslashes($this->geburtsnation).','.
						       ' staatsbuergerschaft='.$this->addslashes($this->staatsbuergerschaft).','.
						       ' ext_id='.$this->addslashes($this->ext_id).
						       ' WHERE person_id='.$this->person_id.';';
						       $this->done=true;
					}
				}
			}
		}
		if ($this->done)
		{
			if(pg_query($this->conn,$qry))
			{
				if($this->new)
				{
					$qry = "SELECT currval('tbl_person_person_id_seq') AS id;";
					if($row=pg_fetch_object(pg_query($this->conn,$qry)))
						$this->person_id=$row->id;
					else
					{					
						$this->errormsg = 'Sequence konnte nicht ausgelesen werden';
						return false;
					}
				}
				//Log schreiben
				return true;
				
			}
			else
			{			
				$this->errormsg = 'Fehler beim Speichern des Person-Datensatzes:'.$this->nachname.' '.$qry;
				return false;
			}
		}
		else 
		{
			return true;
		}	
	}
}
?>